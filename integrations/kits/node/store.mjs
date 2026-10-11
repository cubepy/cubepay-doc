// Node.js 22.13+ node:sqlite. One database per merchant; persistent private storage.
import { DatabaseSync } from 'node:sqlite';
import { readFileSync } from 'node:fs';
export class Store {
  constructor(path, client) {
    this.db = new DatabaseSync(path); this.client = client;
    this.db.exec('PRAGMA busy_timeout=10000');
    this.db.exec(readFileSync(new URL('./schema.sql', import.meta.url), 'utf8'));
  }
  async create(order, amountRial, buyer, callback, username) {
    this.db.prepare("INSERT INTO orders(order_id,requested,buyer,state) VALUES (?,?,?,'creating')").run(order, amountRial, String(buyer));
    try {
      const r = await this.client.create(order, amountRial, callback, String(buyer), username);
      if (typeof r.is_test !== 'boolean') throw new Error('Sandbox identity missing; reconcile creation');
      this.db.prepare("UPDATE orders SET authority=?,payable=?,payment_link=?,sandbox=?,state='waiting' WHERE order_id=?")
        .run(r.authority, r.pay_amount, r.payment_link, Number(r.is_test), order);
      return r;
    } catch (e) { this.db.prepare("UPDATE orders SET state='reconcile' WHERE order_id=?").run(order); throw e; }
  }
  async callback(authority) {
    let row; this.db.exec('BEGIN IMMEDIATE');
    try {
      row = this.db.prepare('SELECT * FROM orders WHERE authority=?').get(authority);
      if (!row || !['waiting', 'unpaid'].includes(row.state)) { this.db.exec('COMMIT'); return row?.state ?? 'unknown'; }
      this.db.prepare("UPDATE orders SET state='verifying' WHERE order_id=?").run(row.order_id);
      this.db.exec('COMMIT');
    } catch (e) { this.db.exec('ROLLBACK'); throw e; }
    let r;
    try { r = await this.client.verify(authority, row.order_id, row.requested, row.payable); }
    catch (e) { this.db.prepare("UPDATE orders SET state='reconcile' WHERE order_id=?").run(row.order_id); throw e; }
    let state = { pending: 'waiting', unpaid: 'unpaid', reconcile: 'reconcile', verified: 'paid' }[r.state];
    if (state === 'paid' && row.sandbox) state = 'sandbox_paid';
    this.db.exec('BEGIN IMMEDIATE');
    try {
      this.db.prepare('UPDATE orders SET state=?,verified_amount=? WHERE order_id=?').run(state, r.amount_rial ?? null, row.order_id);
      if (state === 'paid') this.db.prepare('INSERT INTO delivery_outbox(order_id,authority,buyer,amount_rial) VALUES (?,?,?,?)')
        .run(row.order_id, authority, row.buyer, row.requested);
      this.db.exec('COMMIT');
    } catch (e) { this.db.exec('ROLLBACK'); throw e; }
    return state;
  }
}
