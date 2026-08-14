[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 📱 Forwarding bank SMS from an iPhone (iOS Shortcuts) — no third-party app

The "webhook" method for receiving bank SMS automatically is usually handled on Android by an "SMS Forwarder" app. The iPhone has no such app (Apple does not let third-party apps read SMS), but the **built-in Shortcuts app** can do exactly the same job — completely free, with nothing extra to install.

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
   - add two fields:
     - `secret` → the same secret that is in your webhook URL (you can put it here instead; it doesn't have to stay in the URL)
     - `text` → from the variable list, pick **Shortcut Input** (this is the text of the message that arrived)
5. (Optional but recommended) Add a **Text** action combining Current Date + Shortcut Input + the server's reply (Contents of URL), then log it to a note with the **Append to Note** action — that way, if something ever goes wrong, you can see exactly which message arrived and what the server answered.
6. At the top of the screen, turn **Ask Before Running** **off** — otherwise you have to confirm the Shortcut manually every time a bank message arrives, which defeats the whole point of automating it.
7. Save. From now on, every incoming message runs this Automation automatically (without you even unlocking the phone).

## Test it

Create a test (Sandbox) or small real invoice, pay it from a real card, and watch the bank SMS arrive and the invoice get confirmed automatically within a few seconds. To debug, use the log note from step 5, or the "🧪 Test SMS connection" button in your account panel.

---

## 💡 Trick: webhook + MeliPayamak at the same time (each backing the other up)

If you also have a SIM dedicated to MeliPayamak, you can set up **the same Automation** so that, alongside "Get Contents of URL" (the webhook), it also adds a **Send Message** action forwarding the message text to the MeliPayamak number — meaning every deposit SMS reaches the system through **two independent paths** at once.

> ⚠️ **One step in the panel comes first:** go to "📲 Deposit confirmation method" and turn on the **"Second path (MeliPayamak number)"** button (the number itself is shown right there). While it is off, the server **discards** any SMS forwarded to that number, so the Send Message action has no effect — and the Shortcut shows no error, because as far as iOS is concerned the message was sent successfully. Turning this on does **not** disable the webhook; both run together.

This is completely safe, with no risk of duplicates or double-charging, because the server's matching engine is atomic: each invoice can only move from "pending" to "paid" once. Whichever of the two paths arrives first closes the invoice, and the second one — arriving later — sees that the invoice is already closed and does nothing (it neither changes the wallet again nor sends a second confirmation message).

**Why it helps:** if one path ever has a problem (the MeliPayamak server slows down, say, or the phone's internet drops mid-webhook), the other one takes over and the payment is still confirmed without delay — a free backup layer, from nothing but a Shortcut.

---

## 🆘 If the Shortcut doesn't work

There is also a direct SMS forwarder app for iPhone on the App Store: **[SMS Forwarder](https://apps.apple.com/us/app/sms-forwarder-forward-sms/id6693285061)**

If building the Automation was too fiddly, or it didn't work for whatever reason, give this app a try — it may do the job. Its settings mirror the Android version: set the forwarding destination to **URL** and enter exactly your webhook address.
