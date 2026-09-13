# 🚦 Start Here

Welcome! This page will guide you to the right place in 4 simple steps.

## Step 1 — Merchant registration

Open the merchant management bot:

**[@cubepy_bot](https://t.me/cubepy_bot)**

After registering, your account will be reviewed and approved by an admin.

## Step 2 — Add a bank card and connect SMS forwarding

From inside the bot:
- **💳 Manage Cards** → add your own bank card.
- **📲 SMS Connection Guide** → bank SMS messages have to reach CubePay for payments to confirm automatically:
  - [Android guide](./integrations/android-sms-forwarder-guide.md) — CubePay's own app, pre-configured
  - [iPhone guide](./integrations/ios-shortcuts-sms-forwarding-guide.md) — via Shortcuts, no app to install

> ⚠️ The phone running this app must always be connected to the internet; otherwise automatic payment detection will stop.

## Step 3 — Get your API token

Grab your API token from **"🔗 My Panel"**. You'll send this token in every request like this:

```
Authorization: Bearer YOUR_API_TOKEN
```

> 🖥 **There is a web panel too:** <https://cubevps.ir/panel/> — sign in with the same Merchant ID and a Telegram code. It beats the bot for invoice lists, Excel exports and payment links. The bot alone is enough; the panel is optional → [Web panel guide](./docs/WEB-PANEL.md)

## Step 4 — Pick your integration path

Depending on what you're using, follow one of these guides:

| If you use... | Go to this guide |
|---|---|
| A sales bot built with **Foxima** | [Foxima guide](./integrations/faoxima-guide.md) |
| A **WordPress/WooCommerce** store | [WordPress guide](./integrations/wordpress-plugin-guide.md) |
| Your own site or bot with custom code | [Generic integration guide](./integrations/generic-integration-guide.md) |
| A bot or service written in **Python** | [Complete Python example](./docs/examples/python-example.py) — one file, create to confirm |
| **Node.js** or another language | [docs/examples/](./docs/examples/) and [docs/API-REFERENCE.md](./docs/API-REFERENCE.md) |
| You can't install an SMS Forwarder and want CubePay to collect the money and settle in crypto | [docs/CUBEPAY-VIP-API-REFERENCE.md](./docs/CUBEPAY-VIP-API-REFERENCE.md) |
| You have a VIP subscription and want to keep using the normal system alongside it | [Running both systems together](./integrations/using-both-systems-guide.md) |
| Just want to test the API | [docs/API-REFERENCE.md](./docs/API-REFERENCE.md) and [docs/examples/](./docs/examples/) |
| No website or bot — you just want to send a payment link | [Web panel guide](./docs/WEB-PANEL.md) — create a link, get its QR, send it |
| You have staff and want them to see payments in a group | [Group alerts guide](./docs/GROUP-ALERTS.md) |

## Have a question?

- Frequently asked questions → [docs/FAQ.md](./docs/FAQ.md)
- Technical issue/error → the Troubleshooting section in [docs/FAQ.md](./docs/FAQ.md)
- Direct support → [cube_sup](https://t.me/cube_sup)
