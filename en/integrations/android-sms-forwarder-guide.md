[🇮🇷 فارسی](../../integrations/android-sms-forwarder-guide.md) · 🇬🇧 English

# 🤖 Set up CubePay on Android

> 📖 **[CubePay help center (Persian)](https://cubevps.ir/panel/help/)** — Searchable, mobile-friendly guides; no sign-in required.

Public version: **2.8.1, build 78**. Guide updated October 17, 2026.

CubePay forwards messages from your selected banks using **internet forwarding** and/or **SMS to the receiving number**. SMS forwarding does not require phone internet, but requires cellular coverage, outgoing SMS service and credit/SMS allowance. Carrier charges apply.

## 📥 Download and update

[Download the official CubePay.apk](https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk)

Requires **Android 5.0+**. Install over the previous official version; do not uninstall it. Build 78 uses the same application ID and signing certificate. This release does not enforce an update.

🔒 **SHA-256:** `2d9cb99bc6568de6589693efb9f8b764be6a931e18c5bc03ae8ee35ac2df3cce`

The download URL is stable. The [release page](https://github.com/cubepy/cubepay-doc/releases/tag/android-latest) identifies the public file. See the installed version and build code in app settings.

## 🏦 Easy bank setup

1. Open **Easy bank setup** and sign in using your merchant ID and the code sent by [@cubepy_bot](https://t.me/cubepy_bot). After leaving the screen, you can enter an existing code while it remains valid; the server validates it.
2. Select your bank and confirm one of its deposit messages as a sample. The direct APK can select an earlier SMS with permission. Bazaar/Play variants wait for a new message. Selecting a sample does not resend it to confirm an invoice.
3. Grant required permissions and run the **connection test**. A green result confirms an internet response, not SMS delivery or invoice confirmation.
4. Confirm and activate the bank. Check the reports when the next genuine bank message arrives; no additional payment is needed just to set up the app.

Existing connections, notification filters and custom settings remain available in **Advanced settings**. Do not recreate a working connection.

## 📨 Forward by SMS without phone internet

1. In the bot's SMS confirmation menu, check the **bank SMS forwarding number**. The number of the SIM that sends the forwarded message to CubePay must match the registered number. The bank sender and receiving SIM are separate filter settings.
2. Obtain the current destination receiving number from the bot. Do not confuse it with the bank's sender number.
3. Enable **simultaneous SMS forwarding to CubePay** in bank setup. For an existing connection, check its destination number and sending SIM in advanced settings. Do not create a duplicate filter.
4. On dual-SIM phones, select the registered SIM. Check SEND_SMS permission, cellular coverage and credit/SMS allowance.

Simultaneous forwarding sends chargeable SMS even when internet is available; it is not SMS-only-on-network-failure fallback. It starts disabled for new account-based setup and is enabled by your choice.

**The former per-merchant “second path” reception toggle was removed.** Administrators can still disable either method for the whole service; check its current status in the bot. Internet testing alone does not verify the SIM number or SMS delivery.

## Simple filter check

Open **Diagnostics → Filter check** in the bottom navigation. Select a filter and an existing bank SMS, or keep the screen open for a new message. A clear result dialog shows whether it matches and why. No sign-in or internet is needed; previous messages are not resent. New real messages continue through normal forwarding.

Connection status and invoice tracking are separate entries in Diagnostics and require account sign-in. [Full filter check guide](./filter-health.md)

## New message delivery, destination status and widget

Starting with **2.8.1**, automatic inbox scanning and missed-message recovery are removed. Turning on the phone or opening the app does not scan or resend inbox history. Scheduled recovery from 2.8.0 is cancelled; unfinished recovered deliveries stop while existing reports remain.

New messages already recorded by the live receiver retain their delivery queue and configured retries for transient internet errors. Resuming that queue does not read inbox history. Successful deliveries are not resent, and SMS sends with an uncertain modem outcome are not automatically repeated. Clearing completed reports preserves queued live deliveries and duplicate-prevention identities.

CubePay retries delayed by at least one minute still use the safe server endpoint from 2.8.0. No new server installation is needed if that update is installed; older installations still need the server update. Invoice deadlines are not extended and expired invoices are not automatically confirmed. There is no fallback to ordinary ingress when safe retry support is unavailable.

Reports retain separate internet/SMS destination states and filter names. Transport success alone does not confirm a payment. The optional home-screen widget counts locally recognized deposit messages, pending messages and messages needing review for today in Iran time. It does not count confirmed invoices; one message with several destinations counts once.

Selecting an earlier SMS for the local filter test remains available. The selected message is checked only on the phone and is never resent. SMS-reading permission is used for this explicit selection, not automatic inbox recovery.

## 🔐 Permissions and background operation

- Grant the required SMS permissions. The app's own notification permission differs from **bank notification access**, which is needed only for notification-based connections.
- If Android restricts a sideloaded app, enable **Allow restricted settings**, when available, in app information and then grant the required permission. A permission-help button is not evidence that a restriction is active.
- Set battery use to **Unrestricted** and check manufacturer-specific autostart settings where available.
- Force-stopping the app or preventing it from running can affect both delivery methods. SMS forwarding still needs the phone and permissions to operate.

## 📋 Read the statuses correctly

New reports show the filter name and bank sender beside each internet/SMS result, including connection tests. The same identity is included in CSV exports. It is saved at send time, so later renaming or deleting a filter does not alter past reports. Older reports explicitly show that the filter was not recorded; it is not guessed from a shared destination.


- **Setup ready:** settings are saved; actual message receipt is not guaranteed.
- **Internet test successful:** the server responded to the test.
- **Message sent:** the delivery stage ran; this alone does not confirm payment.
- **Payment confirmed:** the invoice's financial status succeeded. Store fulfillment is separate; a missing fulfillment record does not by itself mean payment failed.

Test records differ from actual bank messages. No recent message alone does not mean the connection is down.

## 🆘 Troubleshooting

| Problem | Action |
|---|---|
| A previous login code is still active | Use existing-code entry while valid. You do not need to wait merely to request another code. |
| DNS / unable to resolve host | Check the app's connection, VPN routing and DNS. A browser loading the website does not prove the app uses the same network path. |
| HTTP 401 or 403 | Check the connection credentials and server response; not every access error has the same cause. |
| SMS sent but invoice not confirmed | Check the registered outgoing SIM, destination, message text/amount and invoice status. |
| Bank message arrives but no app report | Check permissions, battery restrictions, SMS/notification source and that bank's sender/keyword filter. |

If needed, send the app's copyable diagnostic report to [support](https://t.me/cube_sup), without credentials, full bank messages or sensitive details. Do not manually replay an old deposit message just to test.

## 🔧 Manual setup and compatibility

Account sign-in is recommended. Custom connections use your own account details:

- URL: `https://cubevps.ir/smspay/webhook/sms.php`
- Method: `POST`
- Example body: `{"secret":"YOUR_SECRET","text":"{msg}","time":"{time}"}`

Keep working custom templates unchanged unless necessary. Existing `?secret=...` URLs and import links remain supported. Keep connection URLs and credentials private.

## ✨ Changes in 2.6.2

Refined UI, active login-code recovery, corrected system-bar spacing on health screens, permission guidance, green successful test results and removal of the VIP section. These UI changes preserve bank-message reception/forwarding logic and existing settings formats.

Builds, unit tests and upgrade signing identity were checked. This does not claim testing every physical phone, carrier or VPN.

[Privacy policy](../../docs/PRIVACY-POLICY.md) · [iPhone setup](ios-shortcuts-sms-forwarding-guide.md)
