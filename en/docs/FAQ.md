# ❓ FAQ & Troubleshooting

## General Questions

**Do I need an official payment gateway license?**
❌ No, just a bank card in your own name is enough.

**How long are invoices valid?**
⏳ 30 minutes.

**What if the bank SMS arrives late?**
As long as the invoice hasn't expired, detection still happens; bank SMS delay is usually a few seconds.

**How do I change my token or card?**
From the merchant management bot menu ([@cubepy_bot](https://t.me/cubepy_bot)).

**How do I enable Auto Confirmation?**
Automatic confirmation is enabled by default on all approved accounts; you just need your bank card and the SMS Forwarder app set up correctly.

**Does the phone forwarding SMS need to stay on all the time?**
✅ Yes. If that phone goes offline, bank SMS messages won't reach the system and automatic confirmation stops.

## Technical / Language Questions

**Is PHP supported?**
✅ Yes, the API is public and HTTP/JSON based, usable from any language. Sample code in [docs/examples/](./examples/).

**Is Node.js supported?**
✅ Yes, Node.js sample code is also available.

**Can I use SQLite to store transactions?**
✅ Yes, CubePay places no restriction on your own database. You just store `order_id` and `authority`; your choice of database (SQLite, MySQL, PostgreSQL, …) is entirely up to you.

**Do you have an official library (SDK)?**
Currently a sample PHP client is provided at [`docs/examples/CubePayClient.php`](./examples/CubePayClient.php); for other languages use the plain HTTP samples.

## Platform-Specific Questions

**I use Foxima, where do I start?**
Start with the [ready-made files installation guide](../integrations/faoxima-ready-files/faoxima-ready-files-guide.md); it's faster. If your bot's files are customized, use the [manual guide](../integrations/faoxima-integration-guide.md) instead.

**My store is on WordPress, what do I do?**
See the [WordPress guide](../integrations/wordpress-plugin-guide.md).

---

## 🛠 Troubleshooting

### ❌ 401 Unauthorized Error
- Make sure you're sending the header exactly as `Authorization: Bearer YOUR_API_TOKEN` (not just the raw token).
- Check that no extra space or character got added when copying the token.
- Re-check/regenerate the token from the bot panel.

### ❌ Webhook / Callback Not Received
- The `callback_url` must be a valid, externally reachable **https** address (not `localhost` or an internal IP).
- Check that your server's firewall or Cloudflare isn't blocking POST requests coming from CubePay.
- Check your own server logs for incoming requests to the callback path.

### ❌ Connection Timeout
- Test your server's network connection to `cubevps.ir` with `curl -I https://cubevps.ir`.
- If you're on a server inside Iran facing sanctions/network filtering, check your DNS/network route.

### ❌ SSL Error
- Make sure your server's cURL/OpenSSL version is up to date.
- Never set `CURLOPT_SSL_VERIFYPEER` to `false` in a dev environment; instead update the system's CA certificates.

### ❌ `verify-payment` Always Returns `pending`
- The transaction hasn't been deposited yet, or the bank SMS hasn't been processed yet — wait a few seconds and try again (before the invoice's 30-minute expiry).
- Check that the phone connected to the Forwarder app is online and connected.

### ❌ Foxima Ready-Made Files Didn't Work
- Your Foxima version probably differs from the one these ready-made files were prepared for, or you'd already customized those same files.
- Use the [Foxima manual guide](../integrations/faoxima-integration-guide.md), which changes only a few specific lines.

Didn't find an answer? Ask via [cube_sup](https://t.me/cube_sup).
