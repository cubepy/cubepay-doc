
CREATE TABLE IF NOT EXISTS orders (
 order_id TEXT PRIMARY KEY, authority TEXT UNIQUE, requested INTEGER NOT NULL,
 payable INTEGER, buyer TEXT NOT NULL, state TEXT NOT NULL, payment_link TEXT,
 sandbox INTEGER NOT NULL DEFAULT 1, verified_amount INTEGER
);
CREATE TABLE IF NOT EXISTS delivery_outbox (
 order_id TEXT PRIMARY KEY REFERENCES orders(order_id), authority TEXT UNIQUE NOT NULL,
 buyer TEXT NOT NULL, amount_rial INTEGER NOT NULL, state TEXT NOT NULL DEFAULT 'queued'
);
