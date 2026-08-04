[🇮🇷 فارسی](../../integrations/android-sms-forwarder-guide.md) · 🇬🇧 English

# 🤖 Forwarding bank SMS from Android — the dedicated CubePay SMS Forwarder app

Besides the general-purpose "SMS Forwarder" apps mentioned in the FAQ, there is a **dedicated CubePay app** for Android — pre-configured for CubePay's webhook format, lighter, and needing no extra setup (no SMS filters, no request-body formatting, and so on).

> This runs on the SIM that receives the bank's SMS — you need an Android phone that receives your bank messages, and it has to stay powered on and connected to the internet.

---

## Prerequisite

In your account panel ("⚙️ More settings → 📲 Deposit confirmation method") select the **"🔗 Webhook"** method and copy your own webhook URL — it looks something like this:

```
https://cubevps.ir/smspay/webhook/sms.php?secret=XXXXXXXXXXXXXXXXXXXXXXXX
```

## Download and install

📥 **[Download CubePay SMS Forwarder (APK)](https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk)**

This link always serves the **latest published build** and never changes — feel free to bookmark it. The file is served straight from GitHub, so whenever a new version ships, the same link gives you that one.

> Older builds and per-version release notes are on the [Releases page of this repository](https://github.com/cubepy/cubepay-doc/releases).

🔒 **SHA-256:** `fbf377c51e8d11f33d732b99d4ae1f937c5e6868efd24aec6847d37d1639859f`
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

## Test it

Create a test (Sandbox) or small real invoice, pay it from a real card, and watch the bank SMS arrive and the invoice get confirmed automatically within a few seconds. To debug, use the "🧪 Test SMS connection" button in your account panel.

---

## 🆘 If the app doesn't work

- Make sure you didn't deny the SMS permission (in Android settings: **Apps → CubePay SMS Forwarder → Permissions**).
- Check battery optimisation again (the section above).
- Copy the webhook URL from the panel and paste it again — a stray space or extra character may have crept in.
- If it still doesn't work, use the general-purpose "SMS Forwarder" apps (mentioned in [docs/FAQ.md](../docs/FAQ.md)) as an alternative — their setup is similar: set the forwarding destination to **URL** and enter the same webhook address.
- Or ask via [cube_sup](https://t.me/cube_sup).
