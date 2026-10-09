[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 🍎 Simple iPhone bank-message setup

**Three steps:** choose a method and prepare its shortcut → enable automatic execution → check receipt.

Both methods are supported. Use Apple's **Shortcuts** app on the iPhone that receives the bank messages.

| Method | Phone requirements |
|---|---|
| 🌐 Internet forwarding | Internet access; no outgoing SMS charge |
| 📨 SMS to CubePay's receiving number | No phone internet required; cellular coverage, outgoing SMS service and credit/SMS allowance; carrier charges apply |

> Already working? Do not delete or rebuild a working setup. Avoid duplicate automations for the same method.

## 1. Open the method you need

In [@cubepy_bot](https://t.me/cubepy_bot), open the SMS confirmation menu and the iPhone Shortcut guide. Select the method's instructions. This selects a guide, not an account-level switch disabling other reception methods.

<details>
<summary><strong>📨 SMS forwarding — without phone internet</strong></summary>

1. Get the **destination receiving number** and check your **registered forwarding SIM number** in the bot. Correct a missing or incorrect SIM number through the bank-SMS forwarding-number setting. The SIM receiving and forwarding bank messages must match the registered number.
2. Create a shortcut named **CubePay SMS** with just **Send Message**.
3. Set the message text to the blue **Shortcut Input** variable, not typed text. Set **To / Recipients** to one of CubePay's receiving numbers shown by the bot.
4. Expand Send Message and disable **Show When Run** if displayed. Approve SMS permission if asked. On a dual-SIM phone, ensure the outgoing SIM matches the number registered in the bot.
5. Save and complete step 2.

**This method needs no Get Contents of URL action or personal URL.** The bank number is the trigger's sender; CubePay's number is the outgoing destination. Seeing CubePay's number when Send Message runs is expected.

</details>

<details>
<summary><strong>🌐 Internet forwarding</strong></summary>

1. Copy the **complete personal connection URL** from the bot's internet instructions. Keep it private and hide it in screenshots.
2. Open [Add the internet shortcut](https://www.icloud.com/shortcuts/a22472a5fe254bb6a4c39414d490f2c0) on the iPhone and select **Add Shortcut**. Its initial name may be **Get Contents of URL**; rename it **CubePay Internet**.
3. Open **Edit** and replace the **entire URL** in the single **Get Contents of URL** action with the personal URL from the bot. Other settings are already configured. Save and complete step 2.

</details>

<details>
<summary><strong>I want both methods</strong></summary>

Create both shortcuts separately, with one bank-message automation for each. Each automation should run only its own method. Do not place Send Message after Get Contents of URL in one execution chain: a network error can stop later actions.

These are independent paths. SMS is sent and incurs carrier charges even when internet is available; this is not automatic SMS-only-on-network-failure fallback. Do not leave both the old and replacement automation active for the same method.

</details>

## 2. Follow the instructions for your iOS version

Find your version under **Settings → General → About → iOS Version**. Follow only the matching section.

<details>
<summary><strong>iOS 27</strong></summary>

1. Create a new shortcut named **Bank messages**. In its editor, choose **Edit → Automation → Message**.
2. Under **When I receive a message**, select the actual bank **Sender**. Leave **Message Contains** empty.
3. Add **Run Shortcut** and select **your chosen method’s shortcut** (CubePay SMS or CubePay Internet). Expand its arrow and set **Input** to the blue **Message** variable. Do not type the variable name.
4. Open **ⓘ → Privacy → Allow Running When Locked** and enable it. Check and enable the same setting for **the chosen method’s shortcut**, then save.

Do not look for **Run Immediately** on this version. If your screen differs, send support your iOS version and a screenshot.

</details>

<details>
<summary><strong>Earlier iOS versions</strong></summary>

1. Open **Shortcuts → Automation → + → Message**. If offered, select **Create Personal Automation** first.
2. Select the bank's actual **Sender** and leave **Message Contains** empty.
3. Select **Run Immediately** if available. In the automation's actions, add **Run Shortcut** and select **your chosen method’s shortcut** (CubePay SMS or CubePay Internet).
4. Expand the action and set **Input** to the received-message variable (**Message** or **Shortcut Input**); do not type its name.
5. If your version offers **Ask Before Running** instead, turn it off and save. If neither option exists, send support your version and a screenshot. Some older versions require manual confirmation for message automations.

</details>

## 3. Check receipt

- For SMS, check cellular coverage, outgoing SMS service, the sending SIM and destination. Approve SMS permission if prompted.
- For internet forwarding, check connectivity and website access permission. If permission is waiting while the phone is locked, review it once with the phone unlocked.
- When the **next genuine bank message** arrives, check CubePay's SMS report. No additional payment is needed just for this check.
- Server receipt is different from invoice confirmation; the deposit must match the invoice's amount and other requirements.

> Pressing ▶️ on CubePay without input supplies no SMS text. An empty-message response in that situation does not prove the automation is broken. The bot's own connection test does not, by itself, test automatic execution on the iPhone.

## Troubleshooting

| Symptom | Check |
|---|---|
| No visible run when a bank message arrives | Compare the actual sender in Messages with **Sender**. Saving a bank as a contact is not by itself evidence of a problem. If the contact has several numbers, check the one associated with this message. |
| No Run Immediately option | For iOS 27, use **ⓘ → Privacy**. |
| Permissions are enabled | Permissions do not prove the automation ran. Check **Sender** and **Input = Message**. Temporarily put **Show Notification** before Run Shortcut: an alert confirms that step ran. No alert alone is inconclusive because notifications may not be permitted. |
| Empty-message response | Input must be the received-message variable. A manual run without input does not test automatic reception. |
| Access error / 403 | Copy the complete personal URL from the bot again. Internet reception must be enabled for the account. |
| Network or website-access error | Check connectivity on the same phone and the shortcut's website permission. |
| Send Message shows CubePay’s number | Expected: Sender is the bank, while To is CubePay. A manual run does not prove automatic reception works. |
| SMS also fails without internet | Keep the SMS path independent; do not put Get Contents of URL before Send Message. Also check cellular coverage, SMS credit and the sending SIM. |
| The message composer stays open | Check Show When Run in Send Message and SMS permissions. Do not assume unattended sending works until confirmed with an actual incoming message. |
| SMS sent but not received in CubePay | Match the destination and outgoing SIM number against the bot’s registered details. |

Send support your **iOS version, Sender/Input screenshots and error text**. Hide the personal URL, secret and bank-account information.

<details>
<summary><strong>Only if the internet template cannot open: manual setup</strong></summary>

Create a **CubePay Internet** shortcut with one **Get Contents of URL** action:

| Setting | Value |
|---|---|
| URL | Complete personal URL from the bot |
| Method | `POST` |
| Request Body | `Form` |
| Field name | `text` |
| Field value | The **Shortcut Input** variable, not typed text |

Then complete step 2. This table applies only to internet forwarding; SMS forwarding uses Send Message.

</details>

Sources: [Apple's automation guide](https://support.apple.com/guide/shortcuts/add-automations-apdfbdbd7123/ios) and [communication triggers](https://support.apple.com/guide/shortcuts/communication-triggers-apdd711f9dff/ios). Updated October 9, 2026; labels may change in later iOS versions.
