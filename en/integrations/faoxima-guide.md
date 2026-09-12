[🇮🇷 فارسی](../../integrations/faoxima-guide.md) · 🇬🇧 English

<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 🤖 Connecting CubePay to a Foxima bot

🎉 **As of Foxima v1.0.0, CubePay is a native, first-class gateway inside Foxima itself.**

No files to replace, no code to edit, no ready-made files to download. You enter your API token and you're done.

---

## ⚙️ Setup in 3 steps

### Step 1 — Get your API token

Register in [@cubepy_bot](https://t.me/cubepy_bot) and, once your account is approved, copy your API token from **"🔗 My panel"**.

### Step 2 — Enter the token in your bot

In your own Foxima bot:

```
Admin panel → 💎 Finance & reports → the "🟦 CubePay" row → ⚙️ Settings
```

Then tap **"🔑 Set CubePay API token"** and send your token.

The same screen also lets you configure:

| Option | What it does |
|---|---|
| 🏷️ Display name | The name the customer sees in the payment-method list |
| ⬇️ Min / ⬆️ Max | The smallest and largest top-up amount allowed through this gateway |
| 💰 Cashback | The cashback percentage you return to the customer |
| ⚖️ CubePay fee | Passes the fee on to the customer (from `v1.0.1`) |
| 📚 Help text | A note shown to the customer before paying |

### Step 3 — Turn the gateway on

Go back to the "🟦 CubePay" row and switch its status on. Done ✅

> 💳 **Prerequisite:** in `@cubepy_bot` you must have registered a card under **"💳 Manage cards"**, and set up SMS confirmation (the Android app or the iPhone shortcut). Without those, card-to-card payments cannot be confirmed.

---

## ✨ What works automatically

**🔹 Both card-to-card and crypto through one gateway**

You don't need two separate gateways. Foxima talks to our unified router (`pay/create-order.php`), which decides based on which methods you enabled in `@cubepy_bot`:

| Your CubePay setting | What the customer sees |
|---|---|
| Card only | The card-to-card payment page directly |
| Crypto only | The crypto payment page directly |
| Both | A "Card or crypto?" page where they choose |

**🔹 The customer is not thrown out of the bot**

Foxima sends `redirect_after_payment: false`, so after the payment is confirmed the customer's browser is not redirected anywhere — they just see a "payment confirmed" message on the same page, and your bot delivers the result.

**🔹 Automatic confirmation from the bank SMS**

As always: the moment the bank SMS arrives, the invoice is confirmed and the service delivered within seconds.

---

## 🔄 Were you using our ready-made files?

Earlier versions of this guide shipped a set of drop-in PHP files that replaced Foxima's own files and took the CubePay token in the **"ZarinPey"** gateway slot. **That approach is retired** — it is no longer needed or supported.

After updating to v1.0.0:

1. Enter your token in the **"🟦 CubePay"** row this time (not ZarinPey)
2. You can switch the old ZarinPey row off
3. You no longer need our `business_logic_1.php` or any of the other ready-made files

> ⚠️ **Back up your bot directory and database before updating.** An update from the official repository may bring other changes unrelated to CubePay.

If you are stuck on an older Foxima version and genuinely cannot update, the old files remain in this repository's git history (in the commits before their removal) — but updating is what we recommend.

---

## 🤖 Showing the card inside the bot

This feature lets the customer pay without leaving Telegram: alongside the payment button they also get a message carrying the card number, the card holder, the exact amount and the deadline.

✅ **Available from Foxima `v1.0.1`** ([PR #17](https://github.com/Mmd-Amir/Faoxima/pull/17)). If you are on `v1.0.0` or older, update the bot first.

**To enable it:** in `@cubepy_bot` go to **🏪 My Store → 💳 Payment methods → 🤖 Show card in bot**.

> ⚠️ Before flipping the toggle, make sure you have registered a card under **"💳 Manage cards"** — otherwise the API returns an empty card and no message is sent.

**Three things to know:**

- **It doesn't replace the payment button, it sits next to it.** The customer uses whichever they prefer.
- **It only works on the card path.** If both card and crypto are enabled, no card is assigned at invoice-creation time, so nothing is sent.
- **The amount must be transferred to the exact digit** — those few extra Toman are intentional and automatic confirmation depends on them.

📖 Full explanation, and how this compares across integration types: [Showing the card inside the bot](../docs/CARD-IN-BOT.md)

---

## ⚖️ Passing the fee on to the customer

CubePay's fee comes out of your wallet. If you would rather the customer paid it, `v1.0.1` adds that option to the same gateway settings screen ([PR #23](https://github.com/Mmd-Amir/Faoxima/pull/23)):

```
Admin panel → 💎 Finance & reports → 🟦 CubePay → ⚙️ Settings → ⚖️ CubePay fee
```

| Value | Behaviour |
|---|---|
| `0` (default) | Off — you pay the fee, exactly as before |
| `1` to `100` | That percentage is added to the invoice (decimals allowed, e.g. `9.9`) |
| above `100` | That many Toman are added to the invoice |

Example with `3`: the customer wants to top up 100,000 Toman → they pay **103,000** and are credited **100,000**.

> 📌 Only the invoice amount grows; the credit the customer receives is still the amount they asked for.

📖 Details and the calculation: [Passing the fee to the customer](./customer-fee-passthrough-guide.md)

---

## 🩺 If it doesn't work

| Symptom | Likely cause |
|---|---|
| "Error creating payment link" | Wrong API token, or your CubePay account is not approved yet |
| The link is created but the page shows a card error | You haven't registered a card under "💳 Manage cards" in `@cubepy_bot` |
| The customer pays but nothing is confirmed | The amount wasn't transferred to the exact digit, or the bank SMS isn't reaching CubePay |
| The gateway doesn't appear for customers | You didn't turn its status on, or the amount is outside your min/max |

⚠️ **The amount must be exactly the number shown on the payment page.** Those few extra Toman are intentional — automatic bank-SMS matching depends on them.

To debug the SMS path, use **"🧪 Connection test"** in `@cubepy_bot`: "🧪 Test SMS (forwarding)" and "🧪 Test webhook".

---

## 🔗 Related

- 📚 [Full API reference](../docs/API-REFERENCE.md) — if you want to call the API directly
- 🪙 [Crypto & unified-router reference](../docs/CRYPTO-API-REFERENCE.md)
- 👑 [CubePay VIP](../docs/CUBEPAY-VIP-API-REFERENCE.md) — settlement handled by CubePay; just swap in a `vip_…` token, nothing else changes
- ❓ [FAQ](../docs/FAQ.md)

---

🤖 Questions? [@cubepy_bot](https://t.me/cubepy_bot)
