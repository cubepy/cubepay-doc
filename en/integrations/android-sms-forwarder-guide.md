[🇮🇷 فارسی](../../integrations/android-sms-forwarder-guide.md) · 🇬🇧 English

# 🤖 Forwarding bank SMS from Android — the dedicated CubePay SMS Forwarder app

Besides the general-purpose "SMS Forwarder" apps mentioned in the FAQ, there is a **dedicated CubePay app** for Android — pre-configured for CubePay's webhook format, lighter, and needing no extra setup (no SMS filters, no request-body formatting, and so on).

> This runs on the SIM that receives the bank's SMS — you need an Android phone that receives your bank messages, and it has to stay powered on and connected to the internet.

---

## Prerequisite

In your account panel ("⚙️ More settings → 📲 Deposit confirmation method") take these three values:

| | Value |
|---|---|
| **URL** | `https://cubevps.ir/smspay/webhook/sms.php` |
| **Method** | `POST` |
| **Request body template** | `{"secret":"YOUR_SECRET","text":"{msg}","time":"{time}"}` |

🔒 **Why is the secret in the body rather than the URL?** The connection is encrypted with HTTPS, so nobody on the network can read the secret. But the web server's access log records the full URL *after* decryption — and so do your browser history and any screenshot you take of that screen. With a clean URL, none of them ever see it.

> **If you already configured the `...sms.php?secret=...` form, it still works** — nothing breaks and there is no rush to change it. Whenever you get around to it, drop `?secret=...` from the end of the URL and add `"secret"` to the body template instead.
>
> If your forwarder app has no "custom request body" and only sends GET, keep the old form (secret in the URL) — it remains supported.

The webhook is **always on** and there is no button to switch it off. (The on/off button further down that screen belongs to the **second path**; see the "Second path" section below.)

## Download and install

📥 **[Download CubePay SMS Forwarder (APK)](https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk)**

This link always serves the **latest published build** and never changes — feel free to bookmark it. The file is served straight from GitHub, so whenever a new version ships, the same link gives you that one.

> Older builds and per-version release notes are on the [Releases page of this repository](https://github.com/cubepy/cubepay-doc/releases).

🔒 **SHA-256:** `8c8f1492a61aad5316395855c5c909ac78a0128f264208e4db7dffb877f58e07`
(Compare the hash of the downloaded file against this value to be sure it hasn't been tampered with — any SHA-256 tool on your phone or computer will do. This value is synced automatically with the real file every day, so it never needs updating by hand.)

Because this app is not installed from Google Play, Android shows an "unknown source" warning during installation — that is normal:

1. Download the `CubePay.apk` file.
2. Tap the downloaded file to start the installation; if you see "For your security, your phone is not allowed to install unknown apps", tap **Settings**, allow installs from that browser/file manager, then go back and continue.
3. After installing, open the app and grant the **SMS read permission** — without it the app cannot see the bank's messages.

## Set the webhook URL

1. In the app, go to **Settings → Destination address (Webhook URL)**.
2. Enter exactly the webhook URL you copied from your account panel (the "Prerequisite" section above).
3. Save.

## Important Android note: turn off battery optimisation

Unlike iOS, Android usually kills apps that sit idle in the background to save battery — which means the forwarder app may stop seeing messages after a few hours unless you do this:

- Go to **Phone settings → Battery / battery usage → CubePay SMS Forwarder** and set it to "Unrestricted" (depending on the brand: Battery saver exception / Auto-start / App standby).
- Some brands (Xiaomi, Huawei, Samsung, …) add their own battery-management layer — look for "Auto-start" or "Protected apps" in that brand's settings too, and add the app there.

Without this, bank messages may occasionally arrive late or not at all, even with the phone powered on and online.

## 💡 Second path: forward to the MeliPayamak number (webhook backup)

The webhook needs the phone's internet connection **at the very moment the SMS arrives**. If the phone sleeps, loses data, or Android kills the app (see the section above), the message never reaches the server and the invoice is not confirmed. The second path covers exactly that gap: the message text is forwarded over **SMS** (not the internet) to the MeliPayamak number.

**These two are not alternatives — they run together.** Turning the second path on does *not* disable the webhook.

Setting it up takes two steps:

1. **In your account panel** (bot or mini app): go to "📲 Deposit confirmation method" and turn on the **"Second path (MeliPayamak number)"** button. The number you should forward to is shown right there.
2. **In the forwarder app**: inside **the same filter you already created for the webhook** (don't create a new one), open "Destination numbers" and add that number. From then on, every message goes out through both paths at once.

> ⚠️ **Don't skip step 1.** While that button is off, the server **discards** any SMS forwarded to the number — yet the forwarder app still reports "sent successfully", because the app only knows the message left the phone and has no idea the server rejected it. This is the one case where the app's green report does not mean the path is working.

No need to worry about being charged twice: if the same message arrives via both paths, the server detects the duplicate and ignores it. Each invoice can only move from "pending" to "paid" once — whichever path arrives first closes it, and the second does nothing.

Forwarded messages are billed at your own carrier's SMS rate.

## Test it

Create a test (Sandbox) or small real invoice, pay it from a real card, and watch the bank SMS arrive and the invoice get confirmed automatically within a few seconds. To debug, use the "🧪 Test SMS connection" button in your account panel.

---

## 🆘 If the app doesn't work

- Make sure you didn't deny the SMS permission (in Android settings: **Apps → CubePay SMS Forwarder → Permissions**).
- Check battery optimisation again (the section above).
- Copy the webhook URL from the panel and paste it again — a stray space or extra character may have crept in, or the URL inside the app may be an old one. If the webhook returns **HTTP 403**, that is exactly what happened: the `secret` in the app no longer matches the `secret` currently in the panel.
- If you are forwarding to the MeliPayamak number but transactions still aren't confirmed, check that the "Second path (MeliPayamak number)" button in the panel is **on** — while it's off the server discards those messages (see the "Second path" section above).
- If it still doesn't work, use the general-purpose "SMS Forwarder" apps (mentioned in [docs/FAQ.md](../docs/FAQ.md)) as an alternative — their setup is similar: set the forwarding destination to **URL** and enter the same webhook address.
- Or ask via [cube_sup](https://t.me/cube_sup).
