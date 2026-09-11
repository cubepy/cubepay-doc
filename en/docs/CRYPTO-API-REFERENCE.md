<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 📚 API Reference — Unified Payments & Crypto

This file complements [`API-REFERENCE.md`](./API-REFERENCE.md) (which only covers card-to-card). Here you'll find the endpoints for **crypto payments** and the **unified router** (card + crypto, chosen by the customer).

> 📌 If you only use WooCommerce, you don't need to read this file — just fill in the "Unified server address" field in the plugin settings too. This documentation is for direct integration (without the plugin) or building a custom connection.

---

## 🚀 Recommended endpoint: create a unified order

**Endpoint:**
```
POST https://cubevps.ir/pay/create-order.php
```

Depending on the merchant's settings ("⚙️ More Settings → 💳 Payment Methods" in the bot), this endpoint decides on its own:
- Only card enabled → creates a card invoice directly and returns its link
- Only crypto enabled → creates a crypto invoice directly
- Both enabled → returns a link to a "Card or Crypto?" page; the customer chooses and is routed to the actual invoice from there

### 📋 Parameters

| Name | Type | Required | Description |
|---|---|---|---|
| `order_id` | string | ✅ | Your unique order identifier |
| `price_amount` | number | ✅ | Amount in **Toman** (not Rial — unlike the older card endpoint) |
| `callback_url` | string | ❌ | The address notified of the final outcome (whether card or crypto) |
| `redirect_after_payment` | bool | ❌ | Affects the **card path** only: pass `false` and the customer's browser is not redirected to `callback_url` after payment is confirmed (they see a "payment confirmed" message on the same page); `callback_url` is still called server-to-server, unchanged. If you omit it, your account setting applies — "🏪 My Store → 💳 Payment methods → 🔀 Redirect after payment" in the bot — and an explicit value always overrides it. New accounts start with the toggle **off**; accounts that already existed were left **on**, unchanged. It applies equally when the customer picks the card option on the "Card or Crypto?" page. (The crypto path has its own result page by nature, so this parameter does not affect it.) |

### ✅ Sample Response

```json
{
  "success": true,
  "method": "card",
  "pay_page_url": "https://cubevps.ir/smspay/pay.php?authority=...",
  "authority": "bdc9e0497c..."
}
```

The `method` value can be `card`, `crypto`, or `choice`. For `choice`, the `authority` field is absent (since the method isn't decided yet) — you'll learn the final outcome only through `callback_url`.

When `method` is `card`, the response also carries the fields below — for rendering the payment **inside your own bot** instead of sending the customer to a web page:

```json
{
  "card": { "number": "6219861900412221", "holder": "Card holder name", "sheba": null },
  "pay_amount_toman": 24322,
  "expires_at": "2026-09-11T18:27:00+03:30",
  "expires_in_minutes": 30
}
```

⚠️ With card rotation enabled, each invoice may get a different card — always show the one returned for *that* invoice.

---

## 🪙 Direct Crypto Endpoints (if you don't want to use the unified router)

### Create a crypto invoice

```
POST https://cubevps.ir/crypto/api/create-crypto-payment.php
```

| Name | Type | Required | Description |
|---|---|---|---|
| `order_id` | string | ✅ | Your unique order identifier |
| `price_amount` | number | ✅ | Amount in **Toman** |
| `callback_url` | string | ❌ | Address notified of the final outcome |

The customer picks the currency (USDT-BEP20 / TRX / TON) themselves on the returned page (`pay_page_url`) — this endpoint doesn't immediately generate a deposit address, it only creates an invoice "awaiting currency selection."

### Status Check (Polling, Optional)

```
GET https://cubevps.ir/crypto/api/check-crypto-payment-status.php?payment_id=XXXX
```
You get `payment_id` from the callback, or from client-side polling (on the payment page itself).

---

## 🔄 Data Sent to `callback_url`

### For Card Payments
Exactly like [`API-REFERENCE.md`](./API-REFERENCE.md#-data-sent-to-callback_url) — includes `authority`. Call `verify-payment.php` for final confirmation.

### For Crypto Payments
```json
{
  "success": true,
  "status": "paid",
  "order_id": "ORD123",
  "payment_id": "4546796864",
  "pay_currency": "usdtbsc",
  "amount": "0.26",
  "sig": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
}
```
(both as a POST with a JSON body, and as querystring parameters on the same URL)

The `status` value can be `paid`, `failed`, or `expired`.

### ⚠️ Always Check the Signature (`sig`)

Since this callback reaches your site (a public address), anyone could theoretically send a forged request to that same address. To confirm its authenticity, rebuild `sig` using your own API token and compare:

```php
$expectedSig = hash_hmac('sha256', $orderId . '|' . $status . '|' . $amount, $apiToken);
if (!hash_equals($expectedSig, $sig)) {
    // Forged — ignore it
    exit;
}
```

If the signature doesn't match, completely ignore that request and don't mark the order as "paid."

---

## ⚠️ Important Notes

- Unlike the card endpoints (which take the amount in **Rial**), all crypto/unified endpoints take the amount in **Toman**.
- Every payment (card or crypto) is valid for **60 minutes**.
- Each crypto currency has a minimum allowed amount (a few tens of thousands of Toman depending on the day's rate) — if the amount is lower, you get a clear error message.
- The platform fee on crypto payments is calculated as a percentage (not a fixed Toman amount like with card payments).
