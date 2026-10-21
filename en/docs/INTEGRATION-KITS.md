[🇮🇷 فارسی](../../docs/INTEGRATION-KITS.md) · 🇬🇧 English

# 📦 PHP, Python and Node.js integration kits

These kits connect a custom shop or bot to the **regular card-payment API**. They are server-side examples, not a Telegram plugin or phone application. Existing integrations remain available; crypto is outside this kit's scope.

**[Download all three kits](https://github.com/cubepy/cubepay-doc/raw/refs/heads/main/downloads/CubePay-integration-kits.zip)** · [Browse source](../../integrations/kits/README.md)

## Quick start

1. Obtain the merchant API token and store it only in server-side secret configuration.
2. Choose PHP 8.1+ with cURL/PDO SQLite, Python 3.10+ with standard-library SQLite, or Node.js 22.13+ with `node:sqlite`.
3. Set `CUBEPAY_TOKEN` and `CUBEPAY_DB`. Use an absolute private, persistent SQLite path outside the web root, with one database per merchant. Create its private directory first; do not use a network filesystem.
4. Run the example inside the selected kit folder, replacing all sample values:

```bash
php example.php create ORDER-unique-001 200000 BUYER-ID https://your-shop.example/cubepay/callback
# OR
python3 example.py create ORDER-unique-001 200000 BUYER-ID https://your-shop.example/cubepay/callback
# OR
node example.mjs create ORDER-unique-001 200000 BUYER-ID https://your-shop.example/cubepay/callback
```

These commands call the live API and create an invoice; start with a test merchant account. They are CLI tools, not HTTP routes. Keep every file of the selected language folder together. No Composer/PyPI/npm dependency installation is needed.

Amounts are integer **rials**: 20,000 tomans = 200,000 rials. Use a unique local order ID and trusted server-side buyer/amount data. Redirect the shopper to the returned `payment_link`; `pay_amount` includes any customer fee and offset. The verify amount can include the customer fee but excludes the offset.

## Callback and delivery

Implement an HTTPS route in your application. Read only `authority` from the callback, validate its type, then call Store's `callback(authority)` method (await it in Node). The Store looks up the saved order and verifies it server-to-server. Never trust a callback's success flag, buyer, order ID or amount. Use the same persistent database for creation and callbacks. Do not invoke a shell command with callback input.

On temporary errors return HTTP 503 without exposing tokens or raw responses. Return JSON `success: true` only for `paid` or `sandbox_paid`, and retain the state so test results cannot be treated as real payments.

| Local state | Meaning |
|---|---|
| `paid` | A single delivery_outbox row is persisted; process it idempotently |
| `sandbox_paid` | Test result; no real delivery row |
| `pending`, `unpaid` | No delivery; a later callback can recheck the same authority |
| `reconcile`, stale `verifying` | Review authoritative records; do not auto-deliver or request another payment |
| `unknown` | Authority is not in the local database; reject delivery |

The kit does not deliver products or credit your shop wallet. Process queued `delivery_outbox` rows in your own worker. If delivery uses the same database, apply the credit and mark the row done in one transaction with a unique order constraint. For an external fulfillment API, the receiver must enforce `authority` as an idempotency key before the row is marked done. An outbox alone cannot guarantee exactly-once effects in a non-idempotent external system.

HTTP 409 says verify was already consumed but carries no verified amount; it is not independent proof of payment. Unknown create outcomes must be reconciled, not blindly retried with a new order ID. Optional `customer_user_id` and `customer_username` must come from trusted shop records. Tokens must never enter a browser, APK, URL or public repository.

## Late receipts and existing integrations

Eligible expired regular-card invoices allow receipt submission and the seller's decision within one hour after the original expiry. This does not extend the payment deadline. Keep local expired orders for a possible later verified callback. [Late-receipt rules](./LATE-RECEIPTS.md).

[Full Persian callback example and delivery contract](../../integrations/kits/README-fa.md) · [Card API](./API-REFERENCE.md) · [Crypto/unified API](./CRYPTO-API-REFERENCE.md) · [User help](https://cubevps.ir/panel/help/).


---

[Buyer identity in invoices](BUYER-IDENTITY.md)
