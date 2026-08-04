[🇮🇷 فارسی](../../docs/ENDPOINT-MIGRATION.md) · 🇬🇧 English

# 🔀 Migrating to the unified router

> **If you want CubePay VIP and your current integration is old, do this page first — it takes 5 minutes.**

## 🧭 Which address are you on right now?

| | Address | Amount | Tokens it understands |
|---|---|---|---|
| 🕰 Legacy — card | `/smspay/api/create-payment.php` | **rial**, field `amount` | normal only |
| 🕰 Legacy — crypto | `/crypto/api/create-crypto-payment.php` | toman | normal only |
| ✅ New — unified router | `/pay/create-order.php` | **toman**, field `price_amount` | **both normal and VIP** |

The legacy addresses are not going away, and your current integration keeps working with a normal token. However:

- A `vip_` token is only valid on the unified router — on a legacy address you get a guidance error.
- On the router, **both tokens work on one address** and you can switch between them at any time ([running both systems together](../integrations/using-both-systems-guide.md)).

## 🛠 The migration — exactly two changes

### 1) The address

```diff
- https://cubevps.ir/smspay/api/create-payment.php
+ https://cubevps.ir/pay/create-order.php
```

### 2) The amount unit and field name

```diff
- "amount": 500000        ← rial (50,000 toman)
+ "price_amount": 50000   ← toman (the same 50,000 toman)
```

⚠️ **Don't miss this one** — if you change the address but keep sending rial, every invoice you create will be 10× the real price.

The other fields (`order_id`, `callback_url`) stay the same.

## ✅ Before and after — one complete example

Before (legacy):

```bash
curl -X POST https://cubevps.ir/smspay/api/create-payment.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": 500000, "order_id": "ORD-1", "callback_url": "https://yoursite.com/cb.php"}'
```

After (unified router):

```bash
curl -X POST https://cubevps.ir/pay/create-order.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"price_amount": 50000, "order_id": "ORD-1", "callback_url": "https://yoursite.com/cb.php"}'
```

The response still carries `authority`, so your existing `verify-payment.php` code works **unchanged**. (Only VIP invoices get an `invoice_uid` instead of `authority`, with their status read from `check-order-status.php` — [details](./CUBEPAY-VIP-API-REFERENCE.md).)

## 🤖 Using the ready-made files?

- **Foxima:** the current [ready-made files](../../integrations/faoxima-ready-files/) are already on the router — just replace the required files with the new versions and you're done.
- **WordPress / generic guide / official Mirzabot:** these have been on the router from the start — nothing to do.

## ❓ How do I know this is my problem?

If your `vip_` token gets this message, you are exactly here:

```
This is a CubePay VIP token and does not work on this address (the legacy card-to-card API)...
```

Make the two changes above and it is solved.

---

- 👑 Full VIP reference: [`CUBEPAY-VIP-API-REFERENCE.md`](./CUBEPAY-VIP-API-REFERENCE.md)
- 🔀 Both systems together: [`using-both-systems-guide.md`](../integrations/using-both-systems-guide.md)
- 🤖 Questions? Open a ticket from inside the bot: [@cubepy_bot](https://t.me/cubepy_bot)
