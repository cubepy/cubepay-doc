# Filter health in CubePay 2.7.0

Open **Merchant → Bank connection → Filter health test**, or **Advanced settings → Filters → Filter health test**. Select the filter before choosing a check.

## Check an earlier sample locally

The direct APK can read a selected inbox message with permission. All variants support entering a sample manually. The sender, keywords, AND/OR logic, source and selected receiving SIM are compared on the phone; the sample is not resent or included in the support report. An unknown receiving SIM cannot confirm a SIM-specific filter. A matching sample does not prove background reception or delivery.

## Check configured delivery destinations

A synthetic message without bank text or a deposit amount is sent to each enabled official CubePay webhook and account-approved SMS destination. Custom destinations can still forward normal messages; only local filter inspection is available for them in diagnostics. A malformed template is rejected before transmission. Redirects are disabled for diagnostic requests.

Phone-sent status, carrier delivery and CubePay receipt are separate results. Every destination needs its own receipt; HTTP 200 alone does not pass the test. Outgoing SMS incurs carrier charges and uses the selected available SIM. The test does not silently change the selected SIM.

## Check a new bank message

1. Sign in, enable the filter and grant its required permissions.
2. Prepare the live test and select your own card for that bank.
3. Wait for the app to display the card and **exact test amount**. Transfer only that amount to that card.
4. The test waits for a new bank message for 20 minutes and reports reception, matching and each destination separately. You may close the page to check background reception and return later.

The bank transfer is real; CubePay does not refund it. Only the message processing is diagnostic. Your bank may not send an SMS for the amount, and an unsupported message format can remain inconclusive. No result alone does not identify which permission, sender, keyword, SIM or transport failed.

Internet is required for initial preparation. After that, the SMS route works without phone internet. Reconnect later and use **Check CubePay receipt**; saved receipt can also be queried after the test ends. Ordinary payments continue on their existing paths.

**Never use the dedicated test amount for a real order.** One globally unique amount per merchant remains reserved, including after test expiry or cancellation, so delayed copies from older phones cannot become payments. Current/historical invoice amounts are excluded when reserving it, and future invoice allocation avoids overlapping tolerance windows. Existing invoice amounts, states, balances and financial history remain unchanged. Tests cannot create a mismatch, settle an invoice, credit a wallet or send a store payment callback.

## Support and limitations

Copy the support report for filter identity, time and stage results. It excludes bank message text, card number, session credentials and connection secrets. Changing a filter invalidates the old result; start a new test. A successful result applies to that message and the settings at the time, not every future message.

Live preparation and server receipt require the accompanying server update installed by the CubePay administrator. Until it is ready, the app does not request a transfer; local sample checking remains available.
