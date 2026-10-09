[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 🍎 Simple iPhone bank-message setup

**Three steps:** prepare the shortcut → enable automatic execution → check receipt.

Use Apple's **Shortcuts** app on the iPhone receiving your bank messages. Keep it powered on and online. This guide uses internet forwarding only; no third-party app or paid outgoing SMS is required.

> Already working? No need to rebuild it. Do not create duplicate automations for the same bank.

## 1. Add the prepared shortcut

1. In [@cubepy_bot](https://t.me/cubepy_bot), open the SMS confirmation menu and the iPhone Shortcut guide. Copy your **complete personal connection URL**. Keep it private and hide it in screenshots.
2. On the iPhone, open [Add the simple shortcut](https://www.icloud.com/shortcuts/a22472a5fe254bb6a4c39414d490f2c0) and select **Add Shortcut**. Its initial name may be **Get Contents of URL**; rename it **CubePay**.
3. Open **Edit**. The shortcut has just one **Get Contents of URL** action. Replace its **entire URL** with the personal URL from the bot and save. The other settings are already configured.

**Now complete step 2 so an incoming message can run it.**

## 2. Follow the instructions for your iOS version

Find your version under **Settings → General → About → iOS Version**. Follow only the matching section.

<details>
<summary><strong>iOS 27</strong></summary>

1. Create a new shortcut named **Bank messages**. In its editor, choose **Edit → Automation → Message**.
2. Under **When I receive a message**, select the actual bank **Sender**. Leave **Message Contains** empty.
3. Add **Run Shortcut** and select **CubePay**. Expand its arrow and set **Input** to the blue **Message** variable. Do not type the variable name.
4. Open **ⓘ → Privacy → Allow Running When Locked** and enable it. Check and enable the same setting for **CubePay**, then save.

Do not look for **Run Immediately** on this version. If your screen differs, send support your iOS version and a screenshot.

</details>

<details>
<summary><strong>Earlier iOS versions</strong></summary>

1. Open **Shortcuts → Automation → + → Message**. If offered, select **Create Personal Automation** first.
2. Select the bank's actual **Sender** and leave **Message Contains** empty.
3. Select **Run Immediately** if available. In the automation's actions, add **Run Shortcut** and select **CubePay**.
4. Expand the action and set **Input** to the received-message variable (**Message** or **Shortcut Input**); do not type its name.
5. If your version offers **Ask Before Running** instead, turn it off and save. If neither option exists, send support your version and a screenshot. Some older versions require manual confirmation for message automations.

</details>

## 3. Check receipt

- Approve access to CubePay's website if prompted. If permission is waiting while the phone is locked, review it once with the phone unlocked.
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
| Template has Send Message or Append to Note | This is the older template. The current guide uses the simple template in step 1. If migrating, do not leave both old and new automations enabled for the same bank. |

Send support your **iOS version, Sender/Input screenshots and error text**. Hide the personal URL, secret and bank-account information.

<details>
<summary><strong>Only if the template cannot open: manual setup</strong></summary>

Create a **CubePay** shortcut with one **Get Contents of URL** action:

| Setting | Value |
|---|---|
| URL | Complete personal URL from the bot |
| Method | `POST` |
| Request Body | `Form` |
| Field name | `text` |
| Field value | The **Shortcut Input** variable, not typed text |

Then complete step 2. Send Message and Notes logging actions are not required.

</details>

Sources: [Apple's automation guide](https://support.apple.com/guide/shortcuts/add-automations-apdfbdbd7123/ios) and [communication triggers](https://support.apple.com/guide/shortcuts/communication-triggers-apdd711f9dff/ios). Updated October 9, 2026; labels may change in later iOS versions.
