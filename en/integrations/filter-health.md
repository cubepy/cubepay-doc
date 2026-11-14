# Filter check

Open **Diagnostics** in the bottom bar, between **Reports** and **Merchant**, then **Check bank-message recognition**. Select a saved bank filter. No merchant sign-in, internet connection, card selection or test transfer is required. If you edited the filter, select **Register filter changes (ثبت تغییرات فیلتر)**, then **Save and apply (ذخیره و اعمال)** in Settings.

Choose an existing bank SMS. A separate dialog shows whether the message matches, the filter and sender, and the reason if it does not. The result stays on the page. The message is checked locally and is not resent. If a specific receiving SIM is configured, select the actual SIM; “I don't know” cannot confirm a full match.

To observe a new message, keep the screen open with the waiting notice visible. If needed, tap **Check new message** and grant receive permissions. The next message of the filter's source is compared, with its sender shown explicitly. Leaving the screen stops observation. No arriving message alone does not establish that the filter is broken.

**Actual incoming messages continue through normal forwarding and payment processing.** This screen only observes matching; it does not isolate a new bank transfer as a test payment. The check itself makes no network or payment request and cannot consume or resend messages. A match is not proof of server delivery or payment confirmation.

Manual text entry is available in the SMS chooser, and is the primary entry method in distributions without inbox-reading permission. Save changes in Settings before checking an edited filter.

Account history and invoice tracking are entries in Merchant. These two views require merchant sign-in and internet because they read account records. The filter check does not.

The previous three-part flow with destination tests, card selection and reserved test amounts has been removed from the app UI. No new server installation is needed. Keep previously installed server protections for old reserved amounts and delayed test messages.
