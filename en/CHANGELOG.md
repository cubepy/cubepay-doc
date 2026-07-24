![CubePay](../cubepay-logo.png)

# 📋 Changelog

All notable changes to this project are recorded here, in chronological order.

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
