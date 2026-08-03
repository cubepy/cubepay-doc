[🇮🇷 فارسی](../../docs/CUBEPAY-VIP-API-REFERENCE.md) · 🇬🇧 English

<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 👑 API Reference — CubePay VIP (Settlement handled by CubePay)

> ✅ **Status:** this module is **deployed and live** on `cubevps.ir`. The full path — buy subscription → VIP token issued → create invoice → customer pays card-to-card → fee deducted → balance released → crypto withdrawal — has been tested end to end with real money. For the architecture and the reasoning behind each design decision, see [`MANAGED-SETTLEMENT-ARCHITECTURE.md`](../../docs/MANAGED-SETTLEMENT-ARCHITECTURE.md) (Persian only for now).
>
> This feature ("CubePay VIP", or "special merchants") is entirely **optional and separate** from the existing card-to-card / SMS Forwarder path documented in [`API-REFERENCE.md`](./API-REFERENCE.md) and [`CRYPTO-API-REFERENCE.md`](./CRYPTO-API-REFERENCE.md). It is only for merchants who have bought a VIP subscription; nothing changes and nothing needs migrating for everyone else.

---

## 🔑 Authentication — the VIP token is separate

CubePay VIP has its **own token**, which is not the same as your normal API token:

| Token | Prefix | Where it comes from | Where it is used |
|---|---|---|---|
| Normal merchant token | — | your existing token | **only** for `request-activation.php` (you don't have a VIP token yet) |
| VIP token | `vip_` | issued automatically after the subscription payment | every other endpoint in this module |
| VIP sandbox token | `vipsb_` | issued at the same time as the VIP token | Sandbox mode — no real effect |

```
Authorization: Bearer vip_xxxxxxxxxxxxxxxxxxxxxxxx
```

```
Base URL: https://cubevps.ir/managed-settlement/
```

> 🚫 **The fee wallet plays no part here.** The initial fee-wallet top-up applies only to normal merchants, and none of the VIP endpoints check it.

---

## 🔀 Migrating to VIP: just swap the token

If you already call the **unified router** (`POST /pay/create-order.php`) — that is, you followed the [Foxima](../integrations/faoxima-integration-guide.md), [WordPress](../integrations/wordpress-plugin-guide.md), or [generic integration](../integrations/generic-integration-guide.md) guide — moving to VIP requires **no code changes at all**. Just put the `vip_…` token where the old one was:

```diff
- 'Authorization: Bearer ' . $apiToken        // normal token
+ 'Authorization: Bearer ' . $vipApiToken     // VIP token
```

Same `POST /pay/create-order.php`, same `price_amount`, same `pay_page_url` in the response. The router recognises the token and hands the request to the VIP module:

```mermaid
flowchart LR
    A["POST /pay/create-order.php"] --> B{Token starts with vip_?}
    B -->|No| C["Normal path — card / crypto / chooser page"]
    B -->|Yes| D["VIP module — CubePay treasury card"]
```

| | Normal path | With a VIP token |
|---|---|---|
| URL | `/pay/create-order.php` | same |
| Amount field | `price_amount` (toman) | same (`amount_toman` also accepted) |
| Response | `success`, `method`, `pay_page_url` | same + `invoice_uid` |
| Card shown to the customer | the merchant's own card | the CubePay treasury card |

Small things that do change when you swap the token:

- `order_id` is unique **forever** in VIP (on the normal path it could be reused once the invoice expired).
- The default per-invoice cap is 1,000,000 toman.
- The fee wallet is not checked — the VIP path works even if you never topped it up for the normal path.
- `method` in the response is always `card` (the customer pays card-to-card into the treasury card), so code that already branched on `method` keeps working.

Calling `POST /managed-settlement/api/create-order.php` directly still works and is equally valid for a new integration — the router path exists so that an **existing** integration doesn't have to be touched.

### The full flow

```mermaid
flowchart TD
    A["POST api/request-activation.php (normal token)"] --> B["NOWPayments payment link"]
    B --> C["Pay the monthly subscription"]
    C --> D["VIP token issued automatically"]
    D --> E["POST api/create-order.php for each order"]
    E --> F["Customer pays card-to-card (CubePay treasury card)"]
    F --> G["SMS auto-detected → fee deducted → 'pending' balance"]
    G --> H["Your callback_url + GET api/check-order-status.php"]
    H --> I["Cron job: 'pending' → 'available'"]
    I --> J["POST api/register-wallet.php → admin approval"]
    J --> K["POST api/request-withdrawal.php"]
    K --> L["GET api/list-withdrawals.php — track status"]
```

---

## 💰 Every critical number, in one place

> This is the same table the merchant panel shows under the **"📖 Fees & limits"** tab. The values below are **defaults**; the exact numbers for your own account always come from `GET api/dashboard.php` (an admin can set them per merchant).

| Item | Default | Where it is controlled |
|---|---|---|
| CubePay fee per invoice | **10%** | `default_fee_percent` (global) or `fee_percent` (per merchant) |
| Min/max fee per invoice | no limit | `fee_min_toman` / `fee_max_toman` |
| VIP monthly subscription | **1,000,000 toman / 1 month** | `subscription_fee_toman` / `subscription_months` |
| Subscription expiry warning | **3 days before** | `subscription_warn_days_before` |
| Max amount per invoice | **1,000,000 toman** | `default_per_tx_limit_toman` / `per_tx_limit_toman` |
| Daily cap | set by the admin | `default_daily_limit_toman` / `daily_limit_toman` |
| Monthly cap | **100,000,000 toman** | `default_monthly_limit_toman` / `monthly_limit_toman` |
| Minimum withdrawal | set by the admin | `default_min_withdrawal_toman` / `min_withdrawal_toman` |
| Maximum withdrawal | no cap | `max_withdrawal_toman` |
| Duplicate guard | **3 similar invoices in 15 minutes** | `duplicate_guard_max_count` / `duplicate_guard_window_minutes` |

> 📌 **How are the daily and monthly caps counted?** They sum your **paid** invoices in that window — not the invoices you created. An expired or cancelled invoice does not count towards the cap, and Sandbox invoices are never counted at all. Your current usage is in `dashboard.php` under `today_paid_toman` and `this_month_paid_toman`.
>
> If the admin has not set a cap specifically for your account, the global default above applies — and if the admin later changes that default, your account follows it immediately. A `null` in the `dashboard.php` response means that cap does not apply to you.

### Network-level withdrawal minimums

On top of the toman limits above, each network has its own minimum. These figures are **approximate** and move with the exchange rate — the real value is fetched from NOWPayments when you submit the request, and anything below it is rejected with a `422`:

| Network | Approximate minimum |
|---|---|
| TON | ≈ 0.12 TON |
| TRX | ≈ 0.74 TRX |
| USDT (TRC20) | ≈ 11.2 USDT |

### What gets deducted from your money?

```
Invoice amount
  − CubePay fee (%, snapshotted at the moment of payment)
  = amount added to your "pending" balance

Withdrawal amount (toman) ÷ locked rate
  − network fee (network only, not CubePay)
  = crypto that arrives in your wallet
```

- CubePay charges **no separate withdrawal fee**; only the network fee is deducted.
- The fee percentage is recorded (snapshotted) **at the moment the invoice is paid** — changing the percentage later has no effect on older invoices.
- The subscription cost is entirely separate and is never taken out of your sales balance.

---

## 1️⃣ Activate and renew the subscription

```
POST /managed-settlement/api/request-activation.php
Authorization: Bearer <your **normal** merchant token>
```

The first, mandatory step. It returns a **NOWPayments crypto payment link**; no VIP token is issued until it is paid.

### ✅ Example response

```json
{
  "success": true,
  "invoice_url": "https://nowpayments.io/payment/?iid=1234567890",
  "amount_toman": 1000000,
  "amount_usd": 11.50,
  "months": 1,
  "message": "Open the payment link to activate. Your VIP token is issued automatically once payment clears."
}
```

| Case | Response |
|---|---|
| You already have an active subscription | `already_active: true` + `subscription_expires_at` — no new link is created |
| You have an open payment link less than 2 hours old | `reused: true` + the same link — not a fresh duplicate |

The toman price is converted to crypto at the live USD rate. Once the payment is confirmed (via IPN) the profile becomes `active`, `subscription_expires_at` is extended, and the VIP token is sent to you on Telegram.

---

## 🔁 Subscription lifecycle

| Stage | What happens |
|---|---|
| Subscription paid | `vip_` and `vipsb_` tokens are issued, validity extended by `months` |
| 3 days before expiry | a renewal warning is sent (once, `subscription_warned_at`) |
| After expiry | the VIP token stops working for **creating invoices** |
| After expiry | the dashboard, listings, wallet registration and **withdrawals** keep working |

> 💡 The money you earned is yours. Expiry only blocks **new invoices**; your balance is untouched and remains withdrawable whenever you want. `create-order.php` is the only endpoint that requires an active subscription.

---

## 2️⃣ Create an invoice

```
POST /managed-settlement/api/create-order.php
```

The only way to create an invoice in this module — no manual invoices from the panel, the bot, or the admin.

### 📋 Parameters

| Name | Type | Required | Description |
|---|---|---|---|
| `order_id` | string | ✅ | your unique order identifier — **unlike the existing card path, reusing an `order_id` is forbidden forever here** (not just while the invoice is open) |
| `amount_toman` | number | ✅ | amount in **toman** — default cap 1,000,000 toman (an admin can change it per merchant) |
| `callback_url` | string | ❌ | where the final result is announced |
| `customer_ref` | string | ❌ | your customer/user identifier — used only to sharpen duplicate detection |

### ✅ Example response

```json
{
  "success": true,
  "method": "card",
  "invoice_uid": "b1a2c3d4-...-...-...-000000000000",
  "pay_page_url": "https://cubevps.ir/smspay/pay.php?authority=...",
  "amount_toman": 500000,
  "expires_in_minutes": 60
}
```

### ❌ Important errors

| Message | HTTP Status | Reason |
|---|---|---|
| Invoice amount exceeds the allowed cap | `422` | this merchant's per-transaction cap (the response includes `per_tx_limit_toman`) |
| Daily/monthly settlement cap reached | `422` | the sum of today's / this month's paid invoices has hit the cap |
| This order_id has already been used | `409` | duplicate `order_id` — to change the amount, [cancel the previous invoice first](#3-cancel-an-invoice) |
| Too many similar invoices | `429` | duplicate guard (default: 3 similar invoices in 15 minutes) |
| Invalid VIP token | `401` | you sent the normal token, not the `vip_` one |
| Your CubePay VIP subscription has expired | `403` | comes with `subscription_expired: true` — [renew it](#1-activate-and-renew-the-subscription) |

📌 The amount is **not editable** once the invoice exists — to change it, cancel the old invoice (if still unpaid) and create a new one.

---

## 3️⃣ Cancel an invoice

```
POST /managed-settlement/api/cancel-order.php
```

| Name | Type | Required | Description |
|---|---|---|---|
| `invoice_uid` or `order_id` | string | ✅ (one of the two) | the invoice in question |

Only unpaid invoices can be cancelled. Cancelling does **not** free the `order_id` for reuse (reusing an `order_id` is forbidden forever) — create the new invoice with a new `order_id`.

---

## 4️⃣ Check invoice status

```
GET /managed-settlement/api/check-order-status.php?invoice_uid=...
```
or `?order_id=...`

### `status` values

| Value | Meaning |
|---|---|
| `pending` | not paid yet |
| `paid` | payment confirmed and credited to your balance |
| `expired` | the window closed without payment |
| `canceled` | you cancelled it |
| `held_for_review` | flagged by the duplicate guard — payment and crediting continue normally, it is only marked for admin review |

---

## 5️⃣ List invoices

```
GET /managed-settlement/api/list-invoices.php?page=1&per_page=20&status=paid
```

`status` is optional (one of the values above). The output is paginated (`page`, `per_page`, `total`).

---

## 6️⃣ Register a wallet address

```
POST /managed-settlement/api/register-wallet.php
```

| Name | Type | Required | Description |
|---|---|---|---|
| `currency` | string | ✅ | e.g. `usdttrc20` |
| `network` | string | ✅ | e.g. `TRC20` |
| `address` | string | ✅ | destination wallet address |

Every registration (first time or an address change) starts as `verification_status: "pending"` — until an admin approves it (`verified`) it cannot be used for withdrawals. An already-approved address stays untouched until an admin explicitly disables it.

---

## 7️⃣ Request a crypto withdrawal

```
POST /managed-settlement/api/request-withdrawal.php
```

**Required header:** `Idempotency-Key: <a unique string from your side>` — if the same request arrives twice with the same key, only one withdrawal is recorded.

| Name | Type | Required | Description |
|---|---|---|---|
| `wallet_id` | int | ✅ | from the `register-wallet.php` response |
| `amount_toman` | number | ✅ | must be between `min_withdrawal_toman` and `max_withdrawal_toman` (read them from `dashboard.php`) |

### ✅ Example response

```json
{
  "success": true,
  "withdrawal_uid": "a1b2c3...",
  "status": "processing",
  "currency": "usdttrc20",
  "rate_locked": 58000.0,
  "crypto_amount_gross": 8.620000,
  "network_fee_crypto": 0.500000,
  "crypto_amount_net": 8.120000,
  "provider_payout_id": "5000123456",
  "note": "The final status is updated via webhook / periodic reconciliation."
}
```

The conversion rate (`rate_locked`) is locked at the exact moment you submit the request. The crypto amount has at most **6 decimal places** (NOWPayments' official limit for payouts).

### Possible `status` values

```
rate_locked → processing → completed          ← success
rate_locked → processing → balance_returned   ← failed, amount returned
rate_locked → failed                          ← never sent, amount returned
```

| Status | Meaning | Effect on your balance |
|---|---|---|
| `rate_locked` | rate locked, not sent yet | "available" → "settling" |
| `processing` | sent to NOWPayments, awaiting confirmation | no change |
| `completed` | crypto reached your address | "settling" → "settled" |
| `failed` | sending to NOWPayments failed | returned to "available" |
| `balance_returned` | sent but rejected by the network | returned to "available" |

Only the raw NOWPayments status `finished` counts as success; `failed`, `rejected`, `rejected_not_checked` and `cancelled` count as failure, and the rest (`new`, `creating`, `waiting`, `processing`, `sending`) are intermediate and never touch your balance.

The amount is returned **exactly once** — never lost, never doubled. If the webhook never arrives, a ten-minute cron job asks NOWPayments for the status directly and applies the same logic, so a withdrawal never sits in `processing` forever.

---

## 8️⃣ List / track withdrawal requests

```
GET /managed-settlement/api/list-withdrawals.php?page=1&per_page=20
```

Each row includes `tracking_id` (the NOWPayments reference, or one entered manually by an admin), `status`, and the transparent amounts and fees (`crypto_amount_net`, `network_fee_crypto`, `rate_locked`).

---

## 9️⃣ Dashboard

```
GET /managed-settlement/api/dashboard.php
```

### ✅ Example response

```json
{
  "success": true,
  "status": "active",
  "settlement_tier": "vip",
  "payout_frequency": "daily",
  "subscription": {
    "expires_at": "2026-09-03 14:20:00",
    "days_left": 31,
    "is_active": true,
    "fee_toman": 1000000
  },
  "vip_api_token": "vip_a4fd1be3944e...",
  "vip_sandbox_token": "vipsb_7c21ee08b1...",
  "fees": {
    "percent": 10,
    "min_toman": null,
    "max_toman": null,
    "sample_on_100k": 10000,
    "net_on_100k": 90000
  },
  "settlement": {
    "hold_hours": 0,
    "note": "Once the invoice is paid, the amount becomes withdrawable on the next cron run (every 5 minutes)."
  },
  "limits": {
    "per_tx_limit_toman": 1000000,
    "daily_limit_toman": 10000000,
    "monthly_limit_toman": 100000000,
    "min_withdrawal_toman": 500000,
    "max_withdrawal_toman": null
  },
  "today_paid_toman": 2500000,
  "this_month_paid_toman": 18500000,
  "gross_sales_toman": 42000000,
  "total_fee_toman": 4200000,
  "total_settled_toman": 30000000,
  "pending_toman": 1200000,
  "available_toman": 6600000,
  "settling_toman": 0,
  "settled_toman": 30000000
}
```

The four balances (`pending_toman`, `available_toman`, `settling_toman`, `settled_toman`) map exactly onto "pending", "available", "settling" and "settled" as designed in the architecture document.

---

## 🔄 What is sent to your `callback_url` (after an invoice is paid)

```json
{
  "success": true,
  "status": "paid",
  "order_id": "ORD123",
  "invoice_uid": "b1a2c3d4-...",
  "amount_toman": 500000,
  "sig": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
}
```

Exactly as on the existing crypto path, rebuild `sig` with your own `api_token` and compare (HMAC-SHA256 over `order_id|status|amount_toman`) — if it doesn't match, ignore the request:

```php
$expectedSig = hash_hmac('sha256', $orderId . '|paid|' . $amountToman, $apiToken);
if (!hash_equals($expectedSig, $sig)) { exit; }
```

If your server is briefly unreachable, the callback is retried up to 3 times with a delay. On top of relying on the callback, polling `check-order-status.php` periodically is recommended — the same advice as for the existing crypto path.

---

## 🚧 Fully separate from the normal merchants' "3% crypto gateway"

A frequently asked question: normal CubePay merchants already had a crypto withdrawal path with a **3%** fee. Does VIP get mixed up with it? **No.** The two are separate at every layer:

| Layer | The 3% path (normal merchants) | CubePay VIP |
|---|---|---|
| Database tables | `sp_crypto_payments`, `sp_crypto_settlements` | `mst_*` (8 independent tables) |
| Fee setting | `platform_fee_percent = 3.0` in `crypto-config.php` | `default_fee_percent` in `mst_global_config` |
| Token | normal API token | `vip_` token |
| Fee wallet | required | **never checked** |
| Code | contains no reference to `mst_*` | contains no reference to `sp_crypto_*` |

The only shared resource is the **NOWPayments account** itself. To stop an admin "withdraw all commission" action from sweeping up balances that belong to VIP merchants, a reserve guard was added: before any bulk withdrawal, the total VIP liability ("pending" + "available" + "settling") is converted to USD, multiplied by a 1.2 safety factor, and withheld from the withdrawable amount.

---

## ⚠️ Important notes

- Every endpoint in this module takes and returns amounts in **toman** (not rial) — unlike the older card endpoints.
- `order_id` values in this module are **completely separate** from those on the existing card/crypto paths — they never collide.
- Settlement is **crypto only**; there is currently no rial withdrawal or card/IBAN registration in this module.
- To test without real effects, use the `vipsb_` token — no real API calls, no ledger entries, and no impact on your real balance or daily cap.
- **Manual** invoice creation is impossible anywhere — not the merchant panel, not the bot, not the admin panel. The only way is `create-order.php` with a VIP token.
- Balances are never edited directly; all four numbers are computed with `SUM()` over the **append-only** ledger (`mst_ledger_entries`).

---

## 🔗 Related

- 🏗️ Full architecture and internal logic: [`MANAGED-SETTLEMENT-ARCHITECTURE.md`](../../docs/MANAGED-SETTLEMENT-ARCHITECTURE.md) (Persian only)
- 💳 The existing card-to-card path (unchanged): [`API-REFERENCE.md`](./API-REFERENCE.md)
- 🪙 The existing direct-crypto path (unchanged): [`CRYPTO-API-REFERENCE.md`](./CRYPTO-API-REFERENCE.md)
- 🤖 Merchant management bot: [@cubepy_bot](https://t.me/cubepy_bot)
