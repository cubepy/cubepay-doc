# 🚦 Start Here

Welcome! This page will guide you to the right place in 4 simple steps.

## Step 1 — Merchant registration

Open the merchant management bot:

**[@cubepy_bot](https://t.me/cubepy_bot)**

After registering, your account will be reviewed and approved by an admin.

## Step 2 — Add a bank card and connect SMS forwarding

From inside the bot:
- **💳 Manage Cards** → add your own bank card.
- **📲 SMS Connection Guide** → install the Forwarder app on your phone so bank SMS messages are sent to the CubePay system.

> ⚠️ The phone running this app must always be connected to the internet; otherwise automatic payment detection will stop.

## Step 3 — Get your API token

Grab your API token from **"🔗 My Panel"**. You'll send this token in every request like this:

```
Authorization: Bearer YOUR_API_TOKEN
```

## Step 4 — Pick your integration path

Depending on what you're using, follow one of these guides:

| If you use... | Go to this guide |
|---|---|
| A sales bot built with **Foxima** (clean/unmodified) | [Install with ready-made files](../integrations/faoxima-ready-files/faoxima-ready-files-guide.md) |
| A Foxima bot you've already customized | [Manual integration guide](../integrations/faoxima-integration-guide.md) |
| A **WordPress/WooCommerce** store | [WordPress guide](../integrations/wordpress-plugin-guide.md) |
| Your own site or bot with custom code | [Generic integration guide](../integrations/generic-integration-guide.md) |
| Just want to test the API | [docs/API-REFERENCE.md](../docs/API-REFERENCE.md) and [docs/examples/](../docs/examples/) |

## Have a question?

- Frequently asked questions → [docs/FAQ.md](../docs/FAQ.md)
- Technical issue/error → the Troubleshooting section in [docs/FAQ.md](../docs/FAQ.md)
- Direct support → [cube_sup](https://t.me/cube_sup)
