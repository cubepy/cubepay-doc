[🇮🇷 فارسی](../../docs/WEB-PANEL.md) · 🇬🇧 English

# 🖥 Merchant Web Panel

**<https://cubevps.ir/panel/>**

The bot always works and is enough on its own. The web panel is for when a bigger screen helps — browsing invoices, exporting to Excel, managing payment links.

Both act on the same account. Change something in one and the other shows it.

---

## Signing in

1. Enter your **Merchant ID** — the number the bot shows under "🔗 My Panel".
2. A 6-digit code arrives in Telegram. It is valid for **10 minutes**.
3. Enter it. The session stays open for **12 hours of inactivity**.

> 🔒 The code always goes to the Telegram account of the **primary owner**. So a co-owner cannot sign in to the panel — a co-owner works from inside the bot only.

### Why does it sometimes ask for a code again?

Anything that touches money or access needs a fresh confirmation, even mid-session:

- Adding, removing or changing a bank card
- **Saving or deleting a crypto wallet address**
- Fee split, SMS forwarding number, Melipayamak settings
- Creating or deleting a payment link, adding or removing a device
- **Removing a co-owner or withdrawing a pending invite**
- Signing a session out

The reason is simple: if someone steals your session, they must not be able to change where the money goes.

---

## Sections

| Section | What you do there |
|---|---|
| 📊 Dashboard | Sales summary and account status |
| 🧾 Invoices | List, search, cancel, and **Excel export** |
| 🔗 Payment links | Create, enable/disable, delete, **copy and QR** |
| 👛 Wallet | Fee balance and its history |
| 💳 Cards | Add/remove a card, set active, card mode |
| 📱 Devices | Phones forwarding your bank SMS + **SMS text test** |
| ⚙️ Settings | Tokens, wallet, payment methods, co-owner, pay-page theme, sessions |

---

## 🔗 Payment links + QR

You create a link and send it anywhere — Instagram, WhatsApp, a product description. The customer opens it and lands straight on the payment page. No website and no code needed.

Each link has two buttons:

- **📋 Copy link**
- **📱 QR** — a QR image of that link, with a save button. Good for print, a restaurant menu, a shop-window sticker, or a story.

The QR is generated **in your own browser**. No server — ours or anyone else's — sees your link. The image is vector (SVG), so it stays sharp at any size, including large print.

> 👑 This section is closed for VIP accounts. The panel explains why in place.

---

## ⚙️ Settings — what lives where

### Tokens

- **API token** — the one you put in your own code.
- **Sandbox token** — its invoices charge no real fee and need no card. For testing the integration before real money is involved.

### 💰 Crypto payout wallet

The address your crypto settlements are sent to — for USDT (BEP20/BSC), TRX and TON, each currency with its own address.

**Copy the address from your own wallet**; do not type it by hand. The address structure is validated, but no system can tell that a well-formed address belonging to someone else is the wrong one.

Two rules:

- **Changing the address voids the previous approval** and the new address goes back into the admin approval queue. This is deliberate.
- **While crypto is enabled you cannot delete the last wallet** — otherwise crypto stays on with nowhere to send the money.

### 💳 Payment methods

Four switches: card-to-card, crypto, show the card inside the bot, and redirect after payment.

- **At least one of the two payment methods must stay on.**
- **Crypto cannot be turned on from the panel** — only from the bot: "🏪 My Store ← 💳 Payment methods". Turning it off from the panel is fine. (The wallet address, however, you can save right there in the panel.)

"Show the card inside the bot" has its own page → [`CARD-IN-BOT.md`](CARD-IN-BOT.md)

### 👥 Co-owner

A co-owner has **full** access to the account's wallet, cards, invoices and transactions — from inside the bot.

| Action | Where |
|---|---|
| See the current co-owner or a pending invite | Panel and bot |
| **Remove the co-owner** | Panel and bot |
| **Withdraw an invite that was never accepted** | Panel only |
| **Add a co-owner** | Bot only |

**Why is adding not in the panel?** Because becoming a co-owner requires the other person's consent: the bot sends them an invite and they have to press "I accept" themselves. If the panel could make an ID a co-owner directly, that consent would be bypassed.

Path to add: **[@cubepy_bot](https://t.me/cubepy_bot) ← ⚙️ More settings ← 👥 Co-owner ← 🤝 Add co-owner**

> The other person must have pressed `/start` in the bot at least once before, or the invite will not reach them.

---

## What is bot-only

| Action | Path in the bot |
|---|---|
| Adding a co-owner | ⚙️ More settings ← 👥 Co-owner |
| **Turning crypto on** | 🏪 My Store ← 💳 Payment methods |
| Crypto withdrawal | 🏪 My Store ← 💰 Crypto payout wallet |
| Rotating the API token | 🔗 My Panel |
| Creating a manual invoice | 🏪 My Store |
| **Group alerts** | ⚙️ More settings ← 📢 Group alerts → [`GROUP-ALERTS.md`](GROUP-ALERTS.md) |

---

## Questions?

[`FAQ.md`](FAQ.md) · direct support: [cube_sup](https://t.me/cube_sup)
