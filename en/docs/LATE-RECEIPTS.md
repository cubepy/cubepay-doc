[🇮🇷 فارسی](../../docs/LATE-RECEIPTS.md) · 🇬🇧 English

# 🧾 Receipts after invoice expiry

An eligible regular-card invoice can accept a receipt for an earlier bank transfer until **one hour after its original expiry**, when manual approval is enabled for the merchant. Both receipt submission and the merchant's approval/rejection must fit this same window; submitting near its end does not restart it.

Open the original invoice link to submit the receipt. Its authority, order ID, card and amount remain unchanged. This is not permission to make a fresh payment to an expired invoice. The merchant must verify the actual bank credit and its relation to the order; an image alone is not proof.

Test invoices, VIP, internal top-ups and invoices with an existing receipt are excluded from this new path. After the deadline, contact support rather than paying again. Existing pre-expiry receipt handling remains separate.

The effective offset ceiling for newly created invoices is at least 120 tomans; higher configured limits are preserved and old invoices are not rewritten. This does not guarantee 120 simultaneous invoices: amount spacing and reservations also consume capacity. Ambiguous reuse must be reviewed rather than automatically approved.

A manually approved late payment sends the result for the same authority. Verify it server-to-server and prevent duplicate delivery. Do not permanently delete a local order just because it previously expired. The [integration kits](./INTEGRATION-KITS.md) can recheck an `unpaid` order on a later callback.
