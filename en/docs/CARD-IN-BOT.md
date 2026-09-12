[🇮🇷 فارسی](../../docs/CARD-IN-BOT.md) · 🇬🇧 English

<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 🤖 Showing the card inside the bot — who has to do what?

With this feature your customer gets, alongside the payment button, **a message inside Telegram itself** carrying the card number, the card holder, the exact amount and the payment deadline — without ever leaving Telegram.

```
💳 Card-to-card payment

🔢 Card number: 6219861900412221
👤 Card holder: ...
💰 Exact amount: 24,322 Toman
⏳ Deadline: 30 minutes
```

---

## ⚠️ Read these two sentences first

These two get confused constantly:

1. **The toggle inside `@cubepy_bot` does not "turn the feature on".** It only announces *your preference* in the API response (the `show_card_in_bot` field). Whoever actually sends the message to the customer is **your own bot's code**, not us.
2. **So almost everyone needs an update on their side too, on top of the toggle.** Only code that already reads that field works with the toggle alone.

The table below tells you exactly which group you're in.

---

## 📋 Summary — which one are you?

| Your integration | Is the toggle enough? | What's needed |
|---|---|---|
| 🟩 **Foxima** | ❌ No | Waiting on a Foxima update |
| 🟦 **Mirzabot, paid build** (custom gateway) | ❌ No | Update `cubepay-gateway.php` + bot token + toggle |
| 🟨 **Mirzabot, free build** | ❌ No | Waiting on a Mirzabot update |
| 🟪 **Your own code** | ❌ No | Add a few lines to your bot |
| ⬜ **WooCommerce / a website** (no bot) | — | This feature isn't for you |

---

## 🟩 Foxima

As of v1.0.0 the CubePay gateway lives **inside Foxima's own code**, not in a file you control. So this feature has to be added to Foxima itself first.

**Current status:** the code is written and tested and proposed to the Foxima team as a pull request; we're waiting for their review.

- **Until it's merged:** there is nothing you can do, and flipping the toggle has no effect. The customer only sees the payment button — which works perfectly well.
- **After it's merged:** update your bot, then turn the toggle on in `@cubepy_bot` under **🏪 My Store → 💳 Payment methods → 🤖 Show card in bot**.

> 📌 If you're still on our old ready-made files, that approach is retired — update to Foxima v1.0.0.

📖 Full guide: [`faoxima-guide.md`](../integrations/faoxima-guide.md)

---

## 🟦 Mirzabot, paid build (custom payment gateway)

**Three steps:**

1. Replace the bridge file with the current version:
   📁 [`integrations/mirzabot-custom-gateway/cubepay-gateway.php`](../../integrations/mirzabot-custom-gateway/cubepay-gateway.php)
2. Put your own Telegram bot token at the top of that file:
   ```php
   const TELEGRAM_BOT_TOKEN = '123456:AA...';   // from @BotFather
   ```
   ⚠️ This step is **mandatory** — the file runs outside your bot and cannot message the customer without it. Leave it empty and the feature stays quietly off.
3. Turn the toggle on in `@cubepy_bot` (path above).

📖 Full guide: [`mirzabot-custom-gateway/README.md`](../../integrations/mirzabot-custom-gateway/README.md)

---

## 🟨 Mirzabot, free build

Here the CubePay gateway lives **inside Mirzabot's own code**. The feature has to land there first.

**Current status:** written, tested, and proposed upstream as a pull request; waiting for the Mirzabot team.

- **Until it's merged:** nothing you can do; the toggle has no effect.
- **After it's merged:** update your bot from the official Mirzabot repository, then flip the toggle.

📖 Full guide: [`mirzabot-ready-files-guide.md`](../../integrations/mirzabot-ready-files/mirzabot-ready-files-guide.md)

---

## 🟪 Your own code

The create-invoice response **always** carries the card details — whether the toggle is on or off:

```json
{
  "card": { "number": "6219861900412221", "holder": "...", "sheba": null },
  "pay_amount_toman": 24322,
  "expires_in_minutes": 30,
  "show_card_in_bot": true
}
```

So you have two options:

- **The simple way:** ignore the toggle and always show the card. It's your call.
- **The flexible way:** read `show_card_in_bot`, so later you can switch it on and off from inside `@cubepy_bot` without touching code.

Copy-paste ready code: [Rendering the payment inside your bot](../integrations/generic-integration-guide.md)

📖 Field details: [`API-REFERENCE.md`](./API-REFERENCE.md) · [`CRYPTO-API-REFERENCE.md`](./CRYPTO-API-REFERENCE.md)

---

## ⬜ WooCommerce or a normal website

This feature is for selling through a Telegram bot. When a customer buys on your site there is no bot and no chat to send a message to — they simply see the CubePay payment page, which already shows the card number and the exact amount in full.

**Nothing to do.** Turn the toggle on if you like; nothing changes for a website.

---

## 🧩 Things that apply to every group

- **It doesn't replace the payment button, it sits next to it.** The customer sees both the card message and the web payment link, and uses whichever they prefer.
- **It only works on the card path.** If both card and crypto are enabled on your account, no card has been assigned at invoice-creation time (the customer hasn't chosen yet), so there is no card to show and nothing is sent.
- **Never cache and reuse the card.** With card rotation on, each invoice may get a different card — always show the one returned for *that* invoice.
- **The amount must be transferred to the exact digit.** Those few extra Toman are intentional; automatic bank-SMS matching depends on them.

---

## 🩺 I turned the toggle on but no message arrives

First check this: **is a card registered on your account under "💳 Manage cards"?**

If that list is empty, the API returns an **empty** card:

```json
"card": { "number": "", "holder": "", "sheba": null }
```

and because every integration checks that the card number isn't empty before sending, no message goes out — silently, with no error.

The confusing part is that **the web payment page still shows a card** in this situation, because it also tries older places a card can live. So it's easy to assume everything is fine.

**Fix:** in `@cubepy_bot` go to **💳 Manage cards** → "➕ Add new card" and register your card.

If a card was registered and still nothing arrives, check these two:

- Are **both card and crypto** enabled on your account? Then no card is assigned at invoice creation and nothing is sent (see "Things that apply to every group" above).
- Is the file/code on your side a recent version that reads `show_card_in_bot`? (see the table at the top)

---

🤖 Questions? [@cubepy_bot](https://t.me/cubepy_bot)
