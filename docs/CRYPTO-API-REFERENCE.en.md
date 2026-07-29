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

### ✅ Sample Response

```json
{
  "success": true,
  "method": "card",
  "pay_page_url": "https://cubevps.ir/smspay/pay.php?authority=...",
  "authority": "bdc9e0497c..."
}
```

The `method` value can be `card`, `crypto`, or `choice`. For `choice`, the `authority` field is absent (since the method isn't decided yet) — you'll learn the final outcome only through `callback_url` or the `check-order-status.php` endpoint below.

---

## 🔍 Check Order Status

**Endpoint:**
```
GET https://cubevps.ir/pay/check-order-status.php?order_id=YOUR_ORDER_ID
```
With header `Authorization: Bearer YOUR_API_TOKEN`.

The only way to poll a single `order_id` regardless of whether the
customer chose card, crypto, or hasn't decided yet — no webhook
required. Always works for the **crypto** and **choice** paths.

### ✅ Sample Responses

```json
{ "success": true, "status": "choosing_method" }
```
```json
{ "success": true, "method": "card", "status": "verified" }
```
```json
{ "success": true, "method": "crypto", "status": "waiting" }
```

Possible `status` values:

| Path | Possible values |
|---|---|
| Card | `verified`, `pending`, `expired`, `failed` |
| Crypto | `choosing_currency`, `waiting`, `confirming`, `sending`, `finished`, `failed`, `expired` |
| Choice | `choosing_method` (customer hasn't picked a method yet) |

Only `verified` (card) and `finished` (crypto) mean "definitely paid" —
treat everything else as pending or failed.

⚠️ **Exception — direct card-only path:** if only card is enabled
(without crypto), this endpoint returns `success: false`, because that
path can only be tracked by `authority`, not `order_id`. In that case,
use [`verify-payment.php`](./API-REFERENCE.md#-verify-payment) with the
`authority` you got from the `create-order.php` response.

**Simple approach to cover all three cases with one function:** try
`check-order-status.php` with `order_id` first; if it returns
`success: false`, fall back to `verify-payment.php` with `authority`.

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
GET https://cubevps.ir/crypto/api/check-crypto-payment-status.php?token=XXXX
```

| Parameter | Description |
|---|---|
| `token` ✅ preferred | The `public_token` found in the crypto `pay_page_url` |
| `payment_id` (legacy compatibility) | NOWPayments' numeric payment ID |
| `order_id` (requires `Authorization: Bearer`) | For when a currency hasn't been chosen yet and `payment_id` is unknown |

⚠️ **Why `token` is safer than `payment_id`:** `token` is an
unguessable random string, but `payment_id` is a plain sequential
number that can be brute-forced — without `token`, anyone could
theoretically view the status of other merchants' invoices just by
trying numbers. Avoid putting `payment_id` in a public URL exposed to
the customer's browser.

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
