# Buyer identity in CubePay invoices

Send `customer_user_id` as a string from the authenticated buyer record when creating an invoice. `customer_username` is optional and should omit `@`; a missing username must not hide the numeric ID. Never substitute the merchant, panel viewer, or an ID parsed from the order number.

- [Faoxima PR #57](https://github.com/Mmd-Amir/Faoxima/pull/57) forwards the buyer ID already passed by its bot and API. Until upstream merges it, use the [guarded installer ZIP](../../downloads/CubePay-Faoxima-buyer-identity.zip), [checksum](../../downloads/CubePay-Faoxima-buyer-identity.sha256) and [Persian installation instructions](../../integrations/faoxima-buyer-identity/README-fa.md). It backs up the file and only replaces a compatible function. The reported merchant version 1.1.15 was not found among inspected upstream tags; compatibility is checked against the function, not a version label. Do not blindly replace a customized file with the reference copy.
- [Mirzabot PR #122](https://github.com/mahdiMGF2/mirzabot/pull/122) forwards checkout identity through its built-in CubePay integration. This is a proposed change, not confirmation of an official Mirzabot release.
- The [custom-gateway bridge](../../integrations/mirzabot-custom-gateway/cubepay-gateway.php) now forwards `user_id` from the authenticated Mirzabot request; username is only available if the source supplies it. Preserve private configuration when updating an existing bridge.

```json
{"customer_user_id":"7000000123","customer_username":"buyer_name"}
```

The card API `/smspay/api/create-payment.php` uses `amount` in rial; the unified `/pay/create-order.php` uses `price_amount` in toman. Adding metadata does not change amount units, fees, expiry, callbacks or payment verification. Identity must be preserved through the unified router's card and method-selection paths; this guide does not promise display for every crypto or VIP path. The [integration kits](INTEGRATION-KITS.md) already accept both fields.

Create a new invoice after installation and inspect its buyer identity; payment is unnecessary for this check. Existing invoices with no identity cannot be retroactively attributed. Opening a normal payment link in Telegram or sending `/start` alone does not identify the original invoice buyer.

Android display is prepared for a future app release; public APK 2.6.3 build 65 does not include it. This patch publishes no new APK.
