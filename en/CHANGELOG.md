![CubePay](../cubepay-logo.png)

# 📋 Changelog

All notable changes to this project are recorded here, in chronological order.

---

## [2.1.7] — A permanent download link for the SMS Forwarder app

### Changed

- **The Android app is now downloaded from GitHub, and its link no longer changes.** The new address:

  ```
  https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk
  ```

  It always points at the latest build, so you can bookmark it or put it in your own internal guide and forget about it. The download button inside the bot now goes to the same place.

### Fixed

- **The build served by the docs and the build served by the bot's button were not the same.** The file was kept in two separate places and one of them had fallen behind. Both now come from a single source, so that mismatch can no longer happen.

> If you already have the app installed, there is nothing to do — just use the new link for future updates.

---

## [2.1.6] — VIP intake capacity + running both systems together

### Added

- **[A guide to running both systems together](./integrations/using-both-systems-guide.md)** — if you have a VIP subscription you don't have to choose one. Both tokens are valid at the same time and you can decide **per order** which path it takes: normal for ordinary orders (no fee, money straight to your own card), VIP when the forwarder phone is off or you want the income in crypto.
- A **👑 CubePay VIP questions** section in [`docs/FAQ.md`](./docs/FAQ.md).

### Changed

- **VIP intake is now capped.** Because this service involves holding funds in custody, the number of merchants is controlled. When capacity is full, activation requests are refused and **no payment link is created** — the purchase button is hidden in the panel rather than letting you fail on click.

### 📌 Three rules worth knowing

- **If you are already a VIP merchant, your renewal is never blocked.** The cap applies only to new merchants — someone who has been a customer for months should not lose their place to a newcomer.
- **A payment link you receive holds a slot for up to 2 hours.** There is a gap between getting the link and paying; this stops several people buying the same last remaining slot at once.
- **If your payment is confirmed, the service is activated regardless.** Money is never taken without the service being provided.

---

## [2.1.5] — More reliable exchange rate for withdrawals

### Changed

- **The toman-to-crypto rate now comes from several independent sources.** Previously, if the single rate source was unreachable the system silently fell back to a fixed number — and if that number had drifted from the market, withdrawals used the wrong rate with nobody aware. Now if one source is down, the next is tried.
- **Implausible rates are rejected.** Each new rate is compared against the last known-good one; if it differs by more than the allowed margin it is not accepted. This guards against the worst case: some sources publish the **official** currency rate rather than the free-market one, which is about 35% lower and would have paid out roughly 50% more crypto than you are owed.
- **A genuine market jump is still recognised.** If every source reports a new rate and two independent sources agree on it, the market really did move and the new rate is accepted — so the system does not get stuck on a stale one.

> The rate is still **locked at the moment you submit a withdrawal request**, so later market movement does not affect it.

---

## [2.1.4] — Fixed an accounting issue in withdrawals

### Fixed

- **A withdrawal that had failed could, in rare circumstances, also be recorded as settled.** When sending crypto fails, the amount returns to your available balance immediately; but it was possible for that same withdrawal to later be marked successful too, leaving the ledger inconsistent.

  Now **every withdrawal has exactly one final outcome** — either successful or returned, never both. That guarantee is enforced at the database level, not only in application logic.

> This never occurred on any merchant's account; it was found during a routine review. No action is needed from you.

---

## [2.1.3] — Admin panel fix

### Fixed

- The settings button in the admin panel's merchant list did not open.

### Security

- **Sensitive values are no longer included in admin panel responses or stored in the change history.** Merchant tokens were being surfaced and retained in places that had no need for them. The VIP token is now shown only at the moment it is explicitly requested.

> These changes affect only the admin panel (which was already restricted to the bot owner) and change nothing in the merchant-facing API.

---

## [2.1.2] — Monthly cap for VIP merchants

### Added

- **A default monthly cap of 100,000,000 toman.** Until now the monthly cap applied only when an admin had set it for that specific merchant; it now applies to everyone, like the daily cap.

### 📌 Note

Your account's caps are always visible in the panel under **📖 Fees & limits**. If an admin has not set a specific cap for your account, the general default applies — and if that default later changes, your account follows it immediately. For larger businesses, caps can be raised individually.

---

## [2.1.1] — Migrating to VIP without code changes

### Added

- **Now you only need to swap the token.** If you use the unified router (`POST /pay/create-order.php`) — that is, a Foxima bot, the WooCommerce plugin, or your own code — moving to VIP requires **no code changes at all**. Put the `vip_` token where the old one was, and that's it. Same URL, same fields, same response.
- So that your existing code keeps working exactly as before, the VIP path accepts the same amount field name and returns the same `method` key in its response.

### 📌 For normal merchants

Nothing changes. A normal token never starts with `vip_`, so the existing path is untouched.

---

## [2.1.0] — 👑 CubePay VIP: settlement handled by CubePay (no SMS Forwarder needed)

An entirely **optional, parallel** capability alongside the existing system — it replaces nothing. It is for merchants who cannot install an SMS Forwarder: the money is collected on a CubePay treasury card, the fee is deducted, and the merchant withdraws in crypto whenever they want. A merchant who does not enable it sees no difference at all.

### Added

- **A monthly VIP subscription** — activated with a crypto payment link. Once paid, the account is enabled automatically, and a renewal warning is sent 3 days before expiry.
- **A dedicated VIP token** (plus a sandbox variant), separate from your normal API token. When the subscription expires, only that token stops working **for creating invoices** — the dashboard, your balance and **withdrawals** keep working.
- **Your balance moves through four stages** — "pending" → "available" → "settling" → "settled". Every toman is traceable and no figure is ever edited by hand.
- **Automated crypto withdrawals** — the rate is locked when you submit the request, payouts only go to an admin-approved address, and on failure the amount returns **exactly once**.
- **Caps are enforced on the server** — per invoice, per day and per month; not only in the panel.
- **Duplicate invoice detection** — based on merchant + amount + customer + time window, not amount alone, so two genuine orders of the same value are not blocked by mistake.
- **A dedicated panel** — a VIP tab in the merchant panel: dashboard, invoices, wallet, withdrawals, and a **📖 Fees & limits** tab showing every critical number in one place.
- **Documentation** — [`docs/CUBEPAY-VIP-API-REFERENCE.md`](./docs/CUBEPAY-VIP-API-REFERENCE.md).

### Changed

- **Manual invoice creation is impossible in this system** — not from the merchant panel, not from the bot, not even by an admin. The only way is an API call from your own site or bot.
- **`order_id` is unique forever** — unlike the existing card path, it cannot be reused even after an invoice is cancelled.
- All amounts in this system are in **toman** (not rial).

### 🔒 Fully separate from the existing paths

The card-to-card SMS Forwarder path and the normal merchants' 3% crypto gateway are **unchanged**. Accounts, fees and tokens are entirely separate, and the fee wallet is never checked on the VIP path.

---

## [2.0.1] — Fix: crypto payment callbacks were never actually sent

### Fixed

- **Crypto callback was never fired** — due to a missing line in the IPN webhook, when a crypto payment reached a final state (`finished`/`failed`/`expired`), the notification to the merchant's `callback_url` never actually happened — even though the function was fully written and the HMAC signature implemented inside it. That meant merchants relying solely on the callback (rather than `check-order-status.php`) were never told automatically that their customer's crypto payment had completed. Now fixed.
- **No retries and no delivery confirmation** — the callback used to be sent exactly once, and the result (whether it actually reached the merchant's server) was never checked. It now retries up to 3 times with delays, and inspects and logs the merchant server's HTTP response code.

### 📌 Recommendation for merchants

Even though this bug is fixed, we recommend polling [`check-order-status.php`](./docs/CRYPTO-API-REFERENCE.md#status-check-polling-optional) periodically for orders still "pending", in addition to relying on the callback — networks are always unpredictable, and this is one extra layer of certainty.

---

## [2.0.0] — CubePay becomes a Payment OS (long-polling, risk engine, sandbox, developer portal, settlement, queue)

This is the largest update to CubePay so far — it effectively turns the gateway from a "card-to-card confirmer" into a more complete financial backend. Nothing in the existing API contract (`create-payment`, `verify-payment`) changed; everything here is additive, not a replacement.

### Added

- **Long-polling for payment status** — instead of answering immediately, the request stays open for up to 20 seconds and responds the moment the status changes. Real-time confirmation latency dropped from ~4 seconds (the old polling) to ~1 second, with no WebSocket or new infrastructure. The duration is configurable.
- **A real wallet ledger** — the "Wallet" tab now separates "available balance" from "pending" (fees reserved against open, not-yet-expired invoices), together with the full history of every wallet transaction.
- **Risk engine (customer phase)** — each invoice gets a 0-100 risk score on the first visit to the payment page (based on repeated IPs, several different merchants from one IP, deviation from the merchant's average amount, suspicious User-Agent). It is only an advisory signal and never blocks a payment.
- **Risk engine (merchant phase)** — the `🚨 Merchant risk` admin menu lists merchants with a high deposit-mismatch rate, a high failure rate, a high average customer risk, or a sudden jump in volume.
- **Merchant dashboard (analytics tab)** — 14-day turnover chart, success rate, average transaction amount, busiest hours, and the ratio of automatic to manual confirmations.
- **Sandbox mode** — every merchant gets a test token (`test_...`) separate from the real one; invoices created with it neither deduct a real fee nor need a real card, and the payment page shows "simulate success/failure" buttons instead of a card number. For testing an integration before going live.
- **Developer portal** — full documentation plus a live API explorer; you can create and simulate an invoice directly with the test token, without writing any code.
- **API versioning** — all the main endpoints are now also available under `/api/v1/` (the old paths keep working unchanged), so future updates never break existing merchants.
- **Settlement engine for the crypto wallet** — on-demand withdrawal requests (not only scheduled ones); VIP merchants get an immediate real settlement, everyone else goes through admin approval.
- **SMS processing queue optimisation** — the SMS webhook now answers the forwarder app immediately after recording the payment, and performs the slow network work (Telegram, merchant webhook) afterwards — to avoid timeouts and pointless retries under load.

### Fixed

- Multi-payment router bug: on the "both payment methods" path, card invoices were never stored, so card payments always came back as "pending" even after a successful payment. They are now stored and read correctly.
- The "📚 API documentation" link (both in the bot and in the Mini App), which pointed at an empty/abandoned GitHub repository, now points at the real developer portal.

### Security

- Risk scores and anomaly signals are advisory input for manual review only, and make no automatic decisions (no blocking, no rejecting payments).
- The sandbox simulation endpoint verifies that the invoice really is a test invoice before doing anything; on a real invoice it always returns 403.

---

## [1.13.1] — Fix: crypto payment callbacks were never actually sent

> ℹ️ Numbering note: this entry and the Persian `2.0.1` describe the same fix. The two changelogs drifted apart in numbering — match entries by content, not by version tag.

### Fixed

- **Crypto callback was never fired** — due to a missing call in the IPN webhook handler, when a crypto payment reached a final state (`finished`/`failed`/`expired`), the notification to the merchant's `callback_url` never actually happened — even though the function that builds and signs (HMAC) the payload was fully implemented. This meant merchants relying solely on the callback (instead of `check-order-status.php`) never learned that their customer's crypto payment had completed. This is now fixed.
- **No retries, no delivery verification** — callback delivery previously happened only once, and the result (whether it actually reached the merchant's server) was never checked. It now retries up to 3 times with delays, and checks/logs the merchant server's HTTP response code.

### 📌 Recommendation for merchants

Even though this bug is fixed, we still recommend also running a periodic check with [`check-order-status.php`](./docs/CRYPTO-API-REFERENCE.md#status-check-polling-optional) for orders still "pending", in addition to relying on the callback — networks are unpredictable, and this is an extra layer of reliability.

---

## [1.13.0] — Security, multi-wallet support, and crypto callbacks to the merchant's site

### Added

- **Crypto payment callback to the merchant's site** — until now, only card payments notified the merchant's `callback_url`; now crypto payments do too (via a new `callback_url` parameter in `create-crypto-payment.php` and the unified router), signed with HMAC-SHA256 (key = the merchant's API token) to prevent forgery.
- After a successful/failed crypto payment, the customer's browser automatically returns to the merchant's site (exactly like the existing card-payment behavior).
- **WooCommerce plugin v1.1.0** — support for crypto payments via a new optional settings field (fully backward-compatible); new technical docs: [`docs/CRYPTO-API-REFERENCE.md`](./docs/CRYPTO-API-REFERENCE.md)
- **Multiple crypto wallets per merchant** — previously each merchant could only register one address/currency; if their customers paid in several currencies, settling the other currencies would get stuck. Now a separate address is registered for each currency (USDT-BEP20 / TRX / TON).
- The "Merchant Crypto Wallets" admin panel now lists per (merchant, currency) pair, not just per merchant.

### Fixed

- 🔴 **Important security bug:** the cron-job financial scripts (weekly/daily settlement, commission withdrawal, expiring abandoned invoices) had no protection and could be run directly from a browser. They now only run from the command line (CLI).
- 🔴 Logs, SQLite files, and config files were in web-accessible folders, and directory listing was also enabled; closed off with `.htaccess`.
- A display bug on the crypto payment page that caused the deposit address not to show after selecting a currency (the payment itself was recorded correctly, only the display was broken).

### Security

- HMAC-SHA256 signature for crypto payment callbacks (full details in [`docs/CRYPTO-API-REFERENCE.md`](./docs/CRYPTO-API-REFERENCE.md))
- Sensitive cron-job scripts restricted to CLI execution only
- Direct web access to logs/configs/local databases blocked

---

## [1.12.0] — Crypto payments + unified card/crypto integration

### Added

- **Crypto payments** — in addition to card-to-card, customers can now also pay with **USDT (BEP20/BSC network), TRX, or TON**. Settlement happens through NOWPayments and its Sub-partner mechanism (each merchant has a separate, independent account).
- **The customer picks the currency themselves** — exactly like well-known crypto gateways; the merchant doesn't need to specify the currency in advance.
- **Unified payment router (`pay/create-order.php`)** — a single endpoint that, depending on the merchant's settings, creates a card invoice directly, creates a crypto invoice directly, or shows the customer a "Card or Crypto?" page and routes them based on their choice.
- **Independent enable/disable per payment method** — from the new "💳 Payment Methods" menu, a merchant can turn card-to-card and crypto on/off independently (with the restriction that at least one must always stay enabled).
- **Merchant-chosen crypto payout schedule** — weekly, daily, or instant (immediately after every successful payment).
- **Crypto wallet management** — merchants register their receiving address and currency from "💰 Crypto Payout Wallet," see their live unsettled balance, and can withdraw early (ahead of their configured schedule) with the "💸 Withdraw Now" button.
- **Admin panel for managing wallet whitelisting** — a complete list of merchant-registered addresses, separated into "pending" and "approved."
- **60-minute validity period for crypto invoices** (matching card invoices) + a live countdown on the payment page.
- **Full no-code testing** — "🧾 Create Manual Invoice" now has three options: card, crypto, or both (to see exactly what a real customer experiences, under any settings combination).
- Redesigned the crypto payment page and the "choose method" page using the same design language as the card-to-card page (Vazirmatn font, ticket-style layout, brand color) so the user experience is consistent across the whole flow.

### Changed

- The platform fee on crypto transactions is calculated as a percentage (not a fixed Toman amount), since the amount is in crypto, not Rial.
- The Toman commission wallet (for card-to-card) and each merchant's crypto balance are kept **completely independent, with no overlap whatsoever**, even if the merchant enables both methods at once.

### Fixed

- Transaction date/time display in the bot, which used to show the Gregorian calendar in the server's time zone, was converted to the Persian (Jalali) calendar and real local time.

### Security

- Incoming IPN authentication from NOWPayments is checked via an HMAC-SHA512 signature.
- Creating a crypto payment from the customer side (in the browser) never has access to the merchant's API token; all sensitive requests happen server-side.

---

## [1.11.0] — Interactive merchant web panel + SMS connection test

### Added

- **The merchant web panel is now complete** — in addition to earlier features (wallet, transactions, discrepancies), you can now do the following right from the Mini App: fully manage cards (add/remove/enable/rotation mode), change the fee-compensation percentage, create manual invoices, choose and test the bank-SMS receiving method (webhook/MeliPayamak), view referral info, and export to Excel.
- **"🧪 Test SMS Connection" button** — creates a free test invoice and sends a synthetic bank SMS through the real path (webhook or MeliPayamak) so you can confirm your pipeline is healthy without an actual deposit.
- **A separate forwarding number for the MeliPayamak method** — for when your Telegram account differs from the phone/SIM linked to your bank.
- **Terms warning before the initial top-up** — before paying, you explicitly confirm you've read the terms and documentation, and that the amount is non-refundable.

### Changed

- The bot's menu was fully reorganized and categorized (grouped buttons with submenus instead of one long list) and buttons are now arranged two-by-two.
- The "SMS Connection Guide" button was renamed to "Choose Deposit Confirmation Method."
- A Forwarder app connection guide was added for the MeliPayamak method too (previously only available for webhook).

### Fixed

- 🔴 The number format MeliPayamak sends for the SMS sender (without a leading zero) used to be flagged as invalid.
- A temporary issue that caused the merchant web panel to sometimes get stuck on "Loading" was fixed.

---

## [1.10.0] — Second SMS confirmation method (MeliPayamak) + menu reorganization

### Added

- **A second deposit-detection method: SMS forwarding via MeliPayamak** — in addition to the webhook (URL), merchants can now forward their bank SMS directly (SMS-to-SMS) to a MeliPayamak shortcode; the merchant is identified by the sender's phone number, without needing the phone to have constant internet access
- Merchants can define a **forwarding number separate from their account's registration number** (for when the Telegram account uses one phone/SIM but the bank SMS comes from a different number)
- From a new settings page ("📡 Bank SMS Receiving Method"), the admin can:
  - Turn webhook and MeliPayamak on/off independently (disabling either one deactivates that method's tokens)
  - Configure/regenerate the MeliPayamak shortcode and its global token
- A step-by-step Forwarder app guide for the MeliPayamak method (destination = Phone Number/SMS instead of a URL), alongside the webhook guide
- **Admins can register an IBAN (Sheba) number for each merchant** (from within "Manage Merchants")
- **A platform IBAN number** for merchant wallet top-ups, separate from and alongside the existing card
- **Terms warning before the initial top-up**: the initial top-up message now explicitly states that paying means accepting the terms, having fully read the GitHub documentation, and that the amount is non-refundable; the merchant must explicitly confirm this warning before seeing the actual payment button
- The admin menu was split into two levels: the main menu now only has "🛠 Management" and "🏪 My Store"; all other sections (merchants, fees & stats, system settings, announcements & logs) moved under "Management"

### Changed

- The merchant and admin menus, which had gotten crowded, were redesigned into smaller categories with buttons arranged two-by-two (instead of stacked vertically)
- The "SMS Connection Guide" button was renamed to "Choose Deposit Confirmation Method" for clarity

### Fixed

- 🔴 **Phone number detection bug:** the number format MeliPayamak sends for the SMS sender (without a leading zero, like `9123456789`) used to be incorrectly flagged as invalid, causing real transactions to go unconfirmed; this format is now supported

---

## [1.9.1] — Payment UX fixes + security

### Fixed

Misleading "already charged" message on the wallet top-up result page — now a success message with the real balance is shown in this case too

The payment page amount changed from Toman to Rial to match banking apps

A "Return to bot" button was added to the payment/wallet top-up pages

Removed the "Web dashboard" reply-keyboard button from menus — replaced with the Telegram Menu Button (more stable initData)

### Added

Detection of mismatched deposit amounts (less/more than the invoice amount) + notification to the merchant with manual approve/reject buttons

---

## [1.9.0] — Security and documentation

### Added

- **Mandatory initial top-up** — a new merchant must deposit a test amount (default 40,000 Toman) before their token/webhook is activated; enforced both in the bot and in the API itself
- **API rate limiting** — in addition to the SMS webhook, `create-payment` and `verify-payment` now also have rate limits (60 per minute per merchant)
- Complete documentation overhaul: `START-HERE.md` (path-selection guide), `generic-integration-guide.md` (integration guide without Foxima), `API-REFERENCE.md` (technical reference separate from the README)

### Changed

- Invoice expiry time changed from 30 to 15 minutes, configurable (platform default)
- Ability to set a custom expiry time for each manual invoice (5 to 1440 minutes)

---

## [1.8.0] — Important fee bug fix + referral program

### Fixed

- 🔴 **Important fee logic bug:** the split percentage used to also reduce the amount deducted from the wallet. Now the fixed fee is **always deducted in full** from the merchant's wallet; the percentage only determines how much of it is passed on to the customer
- 🔴 **Important security bug:** if a merchant hadn't registered a card, their invoice would use the platform owner's card instead of erroring out (risk of money going to the wrong person). Now, without a card, no invoice (automatic or manual) can be created at all

### Added

- **Referral program** — each merchant gets a dedicated invite code/link; a percentage (default 15%, adjustable) of the referred merchant's actual fees is added to the referrer's wallet — deducted from the platform's profit, not from the referred merchant, so it's inherently safe from abuse
- The admin can now also view their own merchant account (token, cards) from within the bot
- A dedicated, separate card for "merchant wallet top-ups" — completely distinct from the platform's own sales cards

---

## [1.7.0] — UX and branding

### Added

- Invoice messages (wallet top-up / manual invoice) are now **automatically edited** after payment — no more leftover unusable buttons
- Redesigned and removed the "ZarinPal" name across all Faoxima layers (admin chat, web panel, gateway status list) — replaced with "CubePay"
- A "ready-made files" package for quickly connecting Faoxima, without manual editing

---

## [1.6.0] — Bonuses and wallet safety

### Added

- Tiered wallet top-up bonus (5% to 20% depending on the amount)
- A complete, documented review of the financial path: atomic locking, blocking transactions when balance is insufficient, direct notification to the merchant when balance runs low

---

## [1.5.0] — Multi-card support and more security

### Added

- Optional IBAN (Sheba) display next to the card (only for instant transfers)
- Increased the unique invoice identifier (authority) length from 32 to 50 characters
- **Manual invoices** — create a payment link from the bot, no coding required
- Live display of the merchant's fee amount in "My Panel"

---

## [1.4.0] — Transparency and reporting

### Added

- Terms-and-conditions confirmation during merchant registration
- Full transaction Excel (CSV) export from the bot
- Official FAQ and Changelog documentation

---

## [1.3.0] — Authentication and stability

### Added

- Mandatory Telegram channel membership to use the bot
- Phone number verification by sharing a real Telegram contact (not manual typing)
- A "Main Menu" button in every section
- A guide for downloading and configuring the SMS Forwarder app, with a prominent warning about the phone needing constant internet access

### Fixed

- Business name/card name not being escaped in Telegram HTML messages, which could break the message entirely
- The "Manage Merchants" section was showing/editing an old, unused column for the card

---

## [1.2.0] — Full multi-merchant support

### Added

- Multiple cards per merchant + rotation mode (manual / random / hourly)
- Configurable custom fee per merchant
- Automatic wallet top-up from within the bot
- Direct Telegram notification to the merchant after every successful transaction
- Overall transaction statistics for the admin (daily/weekly/monthly/total)
- Full merchant management from the bot (edit, change status, delete)

### Fixed

- Fee reservation was made atomic so the wallet can't go negative under high concurrency
- Prevented duplicate registration with a unique lock on the Telegram ID

### Security

- Prevented SSRF on merchants' callback addresses
- Rate limiting on the SMS webhook

---

## [1.1.0] — Becoming a platform

### Added

- Converted from a single-user system into a multi-merchant platform
- A separate merchant management bot (registration, admin approval, merchant panel)
- Official PHP SDK (`CubePayClient.php`)

---

## [1.0.0] — Initial release

### Added

- Creating and confirming card-to-card transactions with automatic detection from bank SMS
