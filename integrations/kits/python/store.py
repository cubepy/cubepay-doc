"""Single-merchant SQLite example. Keep the DB outside the web root, on persistent storage."""
import sqlite3
from cubepay import CubePayError


SCHEMA = """
CREATE TABLE IF NOT EXISTS orders (
 order_id TEXT PRIMARY KEY, authority TEXT UNIQUE, requested INTEGER NOT NULL,
 payable INTEGER, buyer TEXT NOT NULL, state TEXT NOT NULL, payment_link TEXT,
 sandbox INTEGER NOT NULL DEFAULT 1, verified_amount INTEGER
);
CREATE TABLE IF NOT EXISTS delivery_outbox (
 order_id TEXT PRIMARY KEY REFERENCES orders(order_id), authority TEXT UNIQUE NOT NULL,
 buyer TEXT NOT NULL, amount_rial INTEGER NOT NULL, state TEXT NOT NULL DEFAULT 'queued'
);
"""


class Store:
    def __init__(self, path, client):
        self.db = sqlite3.connect(path, timeout=10, isolation_level=None)
        self.db.row_factory = sqlite3.Row
        self.db.executescript(SCHEMA)
        self.client = client

    def create(self, order, amount_rial, buyer, callback, username=None):
        # UNIQUE local order is committed before the remote call. An ambiguous request is never retried blindly.
        self.db.execute("INSERT INTO orders(order_id,requested,buyer,state) VALUES (?,?,?,'creating')",
                        (order, amount_rial, str(buyer)))
        try:
            r = self.client.create(order, amount_rial, callback, str(buyer), username)
            if type(r.get("is_test")) is not bool:
                raise CubePayError("Sandbox identity missing; reconcile this creation")
            self.db.execute("UPDATE orders SET authority=?,payable=?,payment_link=?,sandbox=?,state='waiting' WHERE order_id=?",
                            (r["authority"], r["pay_amount"], r["payment_link"], int(r["is_test"]), order))
            return r
        except Exception:
            self.db.execute("UPDATE orders SET state='reconcile' WHERE order_id=?", (order,))
            raise

    def callback(self, authority):
        # Callback order_id/amount/status/customer are deliberately ignored.
        self.db.execute("BEGIN IMMEDIATE")
        try:
            row = self.db.execute("SELECT * FROM orders WHERE authority=?", (authority,)).fetchone()
            if not row:
                self.db.execute("COMMIT")
                return "unknown"
            if row["state"] not in ("waiting", "unpaid"):
                self.db.execute("COMMIT")
                return row["state"]
            self.db.execute("UPDATE orders SET state='verifying' WHERE order_id=?", (row["order_id"],))
            self.db.execute("COMMIT")
        except Exception:
            if self.db.in_transaction:
                self.db.execute("ROLLBACK")
            raise
        try:
            result = self.client.verify(authority, row["order_id"], row["requested"], row["payable"])
        except Exception:
            self.db.execute("UPDATE orders SET state='reconcile' WHERE order_id=?", (row["order_id"],))
            raise
        state = {"pending": "waiting", "unpaid": "unpaid", "reconcile": "reconcile", "verified": "paid"}[result["state"]]
        if state == "paid" and row["sandbox"]:
            state = "sandbox_paid"
        self.db.execute("BEGIN IMMEDIATE")
        try:
            self.db.execute("UPDATE orders SET state=?,verified_amount=? WHERE order_id=?",
                            (state, result.get("amount_rial"), row["order_id"]))
            if state == "paid":
                # Deliver the locally ordered value, NOT an amount or buyer taken from a callback.
                self.db.execute("INSERT INTO delivery_outbox(order_id,authority,buyer,amount_rial) VALUES (?,?,?,?)",
                                (row["order_id"], authority, row["buyer"], row["requested"]))
            self.db.execute("COMMIT")
        except Exception:
            self.db.execute("ROLLBACK")
            # The API may already have verified: leave 'verifying' for reconciliation, never blind re-verification.
            raise
        return state
