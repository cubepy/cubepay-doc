<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 🔌 CubePay WooCommerce Plugin (Card-to-Card + Crypto)

If your store is built on **WordPress + WooCommerce**, instead of a manual API integration, you can use this ready-made plugin — installation takes a few minutes and requires no coding.

---

## ✅ Prerequisites

- WordPress + **WooCommerce** active on your site
- PHP 8.1 or above (most hosts today have this)
- Your merchant account on the merchant management bot (`@cubepy_bot`) must be **approved**

---

## ⬇️ Download

Download the latest version of the plugin here (zip file):

**[⬇️ Download smspay-gateway.zip](https://github.com/cubepy/cubepay-doc/releases/latest/download/smspay-gateway.zip)**

> If the link above doesn't work, grab the latest version from this repository's [Releases](https://github.com/cubepy/cubepay-doc/releases) section.

---

## 🚀 Installation (3 Steps)

### 1) Upload the plugin

In your WordPress dashboard: **Plugins ← Add New ← Upload Plugin**, select the downloaded zip file, **Install**, then click **Activate**.

### 2) Get your info from the Telegram bot

In the `@cubepy_bot` bot, from the "🔗 My Panel" menu, copy:
- **API Token**
- **Card-to-card server address** (like `https://cubevps.ir/smspay`) — always required
- 🆕 If you want to accept crypto too: **Unified server address** (like `https://cubevps.ir/pay`)

### 3) Configure the plugin

In WordPress go to: **WooCommerce ← Settings ← Payments ← "CubePay" ← Manage**

- ✅ Enable it
- 📋 Paste the API token and the card-to-card server address
- 🆕 **Unified server address (optional):** if you fill this in, and crypto is also enabled on your merchant account (in the bot, "⚙️ Payment Methods"), the customer will choose between card and crypto at checkout. If left empty, it works exactly as before — card-to-card only.
- 💰 Choose the **store currency** correctly (Toman or Rial) — this really matters, since the gateway only accepts a specific Rial/Toman format, and choosing wrong will multiply/divide the amount by 10!
- Save

That's it! Now the payment option will appear for customers on your store's checkout page.

---

## 🧪 Test It

Create a test order with a small amount and go through the full payment flow — after the deposit (card or crypto), you should be automatically returned to WooCommerce's "Order received" page.

---

## ❓ Having Trouble?

- Make sure your merchant account is **approved** on the bot and you've registered a **bank card** (without a card, the card gateway won't create invoices).
- For crypto, make sure both "crypto" is enabled on the bot and the unified server address is filled in the plugin settings.
- If the payment went through but the WooCommerce order wasn't marked "paid," ask CubePay support to check the order log (Order notes).
- For anything else: 🎫 [CubePay Support](https://t.me/cube_sup)

---

📚 More complete technical docs: [API docs (Card)](../docs/API-REFERENCE.md) · [API docs (Crypto/Unified)](../docs/CRYPTO-API-REFERENCE.md)
