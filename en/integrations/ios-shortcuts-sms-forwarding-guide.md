[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 📱 Forwarding bank SMS from an iPhone (iOS Shortcuts)

On Android we have our own forwarder app. The iPhone has no equivalent of it, but there are two ways to do the same job:

1. **The built-in Shortcuts app** — already installed on every iPhone, free, nothing extra to install. This guide covers it in full.
2. **The [SMS Forwarder](https://apps.apple.com/us/app/sms-forwarder-forward-sms/id6693285061) app** from the App Store — several merchants have tested it and it works. If building an Automation feels like too much, this is simpler.

We document the Shortcuts route in full because it is free and depends on no third-party app.

> This runs on the SIM that receives the bank's SMS (you need an iPhone that receives your bank messages — and that phone has to stay powered on and connected to the internet).

---

## Prerequisite

In your account panel ("⚙️ More settings → 📲 Deposit confirmation method") take two things: the **URL** and the **secret**.

```
URL:     https://cubevps.ir/smspay/webhook/sms.php
Secret:  XXXXXXXXXXXXXXXXXXXXXXXX
```

You enter the secret as one of the form fields in step 4 — **not** inside the URL.

🔒 **Why?** The connection is encrypted with HTTPS, so nobody on the network can read the secret. But the web server's access log records the full URL *after* decryption — and so do your browser history and any screenshot you take. With a clean URL, none of them ever see it.

> If you already set the `...sms.php?secret=...` form, it **still works** and nothing breaks. Whenever you like, drop `?secret=...` from the end of the URL — the `secret` field you add in step 4 replaces it.

## Building the Shortcut

1. Open the **Shortcuts** app → **Automation** tab → **+** → **Create Personal Automation**
2. Choose **When I Get a Message Containing...**. If you only want bank messages processed (rather than every SMS), put the bank's sender number or name in the **From** field; otherwise leave it empty so every message is checked (on the server side, messages that aren't deposits are ignored automatically, so leaving it empty is fine too).
3. **Add Action** → search for **Get Contents of URL** and add it.
4. Put your webhook URL in that action. Then tap the small arrow next to the action (Show More):
   - set **Method** to **POST**
   - set **Request Body** to **Form**

   Now add **two fields**. Each time you tap "Add new field", iOS asks what
   **type** the field is and offers two choices: **Text** and **File**.
   Pick **Text** both times; you never need File here.

   > ⚠️ The word "text" means two different things at this step, which is
   > exactly what trips people up: **Text** (capitalised) is the field
   > *type* iOS is asking about, while `text` (lowercase) is the *name* you
   > type for the second field.

   **First field — the webhook secret:**

   | Box | What to put |
   |---|---|
   | Key | `secret` |
   | Value | your own secret (typed or pasted) |

   **Second field — the message text:**

   | Box | What to put |
   |---|---|
   | Key | `text` |
   | Value | the **Shortcut Input** variable (don't type it — see below) |

   To fill in the second field's Value:
   1. Tap the **Value** box, but **don't type anything**
   2. A bar of variables appears above the keyboard → tap **Shortcut Input**
   3. A blue "Shortcut Input" token should drop into the box

   > 🚫 If you type the words `Shortcut Input` by hand instead of picking
   > the variable, that literal text gets sent to the server rather than the
   > message body, and no invoice will ever be confirmed. The token must be
   > **blue**.
   >
   > If the variable bar doesn't appear above the keyboard: press and hold
   > the Value box until the menu opens, then choose **Insert Variable**.

   When you're done it should look exactly like this:

   ```
   secret  →  (your secret, plain text)
   text    →  [Shortcut Input]   ← blue token
   ```

   **Two optional fields (recommended, but not required):**

   | Key | Value | What it buys you |
   |---|---|---|
   | `sender` | the **Sender** variable | tells us which bank the message came from |
   | `time` | the **Current Date** variable | the exact time of the message, for debugging |

   Leaving them out breaks nothing — deposits are still confirmed. They
   only make troubleshooting easier if something goes wrong. The Android
   app sends these same fields.
5. (Optional but recommended) Add a **Text** action combining Current Date + Shortcut Input + the server's reply (Contents of URL), then log it to a note with the **Append to Note** action — that way, if something ever goes wrong, you can see exactly which message arrived and what the server answered.
6. At the top of the screen, turn **Ask Before Running** **off** — otherwise you have to confirm the Shortcut manually every time a bank message arrives, which defeats the whole point of automating it.
7. Save. From now on, every incoming message runs this Automation automatically (without you even unlocking the phone).

## Test it

**The easiest way:** in the bot, go to "🧪 Connection test" → **"🧪 Test webhook"**. It creates a free test invoice and checks the whole path (network, secret, parsing, matching) without you having to move any real money.

If you also want a real-money test, create a small invoice and pay it from a real card; it should be confirmed automatically within a few seconds. For deeper debugging use the log note from step 5.

---

## 💡 Trick: webhook + shortcode at the same time (each backing the other up)

If you also have a SIM you can send from, you can set up **the same Automation** so that, alongside "Get Contents of URL" (the webhook), it also adds a **Send Message** action forwarding the message text to our shortcode — meaning every deposit SMS reaches the system through **two independent paths** at once.

> ℹ️ **Nothing to switch on.** The shortcode path is always active for every merchant — just copy the shortcode itself from "📲 Deposit confirmation method". It does **not** disable the webhook; both run together.

This is completely safe, with no risk of duplicates or double-charging, because the server's matching engine is atomic: each invoice can only move from "pending" to "paid" once. Whichever of the two paths arrives first closes the invoice, and the second one — arriving later — sees that the invoice is already closed and does nothing (it neither changes the wallet again nor sends a second confirmation message).

**Why it helps:** if one path ever has a problem (SMS delivery slows down, say, or the phone's internet drops mid-webhook), the other one takes over and the payment is still confirmed without delay — a free backup layer, from nothing but a Shortcut.

---

## 🆘 If the Shortcut doesn't work

There is also a direct SMS forwarder app for iPhone on the App Store: **[SMS Forwarder](https://apps.apple.com/us/app/sms-forwarder-forward-sms/id6693285061)**

Several merchants have tested it and it works. If building the Automation was too fiddly, this is simpler. Its settings mirror the Android version: set the forwarding destination to **URL** and enter exactly your webhook address.

> ⚠️ If the app only lets you set a URL and has no separate fields, you
> will have to put the secret inside the URL (`...sms.php?secret=...`).
> That works, but it is not as safe — so never share that URL and never
> screenshot it.
