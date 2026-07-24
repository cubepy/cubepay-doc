<p align="center">
  <img src="../assets/demo-banner.jpg" alt="CubePay Demo" width="100%">
</p>

<p align="center">
  <a href="https://github.com/cubepy/cubepay-doc/releases"><img src="https://img.shields.io/github/v/release/cubepy/cubepay-doc?label=Latest%20Release&color=blue" alt="Latest Release"></a>
  <a href="https://github.com/cubepy/cubepay-doc/blob/main/LICENSE"><img src="https://img.shields.io/github/license/cubepy/cubepay-doc" alt="License"></a>
  <a href="https://github.com/cubepy/cubepay-doc/stargazers"><img src="https://img.shields.io/github/stars/cubepy/cubepay-doc?style=social" alt="Stars"></a>
  <a href="https://github.com/cubepy/cubepay-doc/network/members"><img src="https://img.shields.io/github/forks/cubepy/cubepay-doc?style=social" alt="Forks"></a>
  <a href="https://github.com/cubepy/cubepay-doc/issues"><img src="https://img.shields.io/github/issues/cubepy/cubepay-doc" alt="Issues"></a>
  <a href="https://github.com/cubepy/cubepay-doc/pulls"><img src="https://img.shields.io/github/issues-pr/cubepy/cubepay-doc" alt="Pull Requests"></a>
  <a href="https://github.com/cubepy/cubepay-doc/commits/main"><img src="https://img.shields.io/github/last-commit/cubepy/cubepay-doc" alt="Last Commit"></a>
</p>

<h1 align="center">💳 CubePay</h1>

<p align="center">
Every deposit verifies itself — card-to-card and crypto payments, with automatic detection from bank SMS or the blockchain 🚀
</p>

CubePay is an API service for creating and automatically confirming transactions — whether card-to-card or crypto (USDT / TRX / TON). For card payments, your customer deposits the amount directly to your own card, and the system detects and confirms the payment from the bank SMS in under 30 seconds; for crypto payments, the customer picks their preferred currency themselves, and confirmation happens fully automatically from the blockchain network — no e-commerce trust seal (Enamad), no official bank gateway, and no instant-transfer fees.

> 📌 This repo is the documentation for an online API service, not an installable library. To get started you just need an API token from [@cubepy_bot](https://t.me/cubepy_bot).

**New here? 👉 Start here: [START-HERE.md](./START-HERE.md)**

---

## ✨ Features

| | |
|---|---|
| ✅ Automatic payment confirmation (under 30 seconds) | ✅ Automatic callback to your server |
| ✅ Protection against duplicate confirmation (idempotent) | ✅ No official gateway/license required |
| ✅ Full management via Telegram bot | ✅ Create manual invoices from the panel |
| ✅ Wallet and multi-card management | ✅ Complete transaction reports |
| ✅ Account co-owners (multiple admins) | ✅ Encrypted HTTPS connection |
| 🆕 Crypto payments (USDT · TRX · TON) | 🆕 One unified endpoint: card or crypto, chosen by the customer |

🔐 **Security:** No buyer card data is stored · Atomic Wallet Lock · Direct support with real responsiveness

---

## 🗂 Documentation Map for This Repo

```
cubepay-doc/
├── START-HERE.md                 ← New here? Read this first
├── docs/
│   ├── API-REFERENCE.md          ← Full technical reference for the card API (Endpoints, params, errors)
│   ├── CRYPTO-API-REFERENCE.md   ← 🆕 Technical reference for crypto payments and the unified router
│   ├── FAQ.md                    ← Frequently asked questions
│   ├── openapi.yaml              ← OpenAPI 3.0 spec (for Postman/Swagger)
│   └── examples/                 ← Ready-made code samples per language
│       ├── CubePayClient.php
│       ├── php-example.php
│       ├── python-example.py
│       ├── node-example.js
│       ├── laravel-example.php
│       └── curl-example.sh
└── integrations/                 ← Guides for connecting to specific platforms
    ├── generic-integration-guide.md
    ├── wordpress-plugin-guide.md
    ├── faoxima-integration-guide.md
    └── faoxima-ready-files/
        └── faoxima-ready-files-guide.md
```

---

## 🔌 Connecting to Ready-Made Platforms

If you use one of these platforms, you don't need to implement the API from scratch yourself:

| Platform | Guide | Description |
|---|---|---|
| 🤖 **Foxima** (and its forks) | [Install with ready-made files](./integrations/faoxima-ready-files/faoxima-ready-files-guide.md) | Just replace a few PHP files — the fastest method |
| 🤖 **Foxima** (manual editing) | [Manual integration guide](./integrations/faoxima-integration-guide.md) | If your bot's files are customized and you don't want a full replacement |
| 🌐 **WordPress / WooCommerce** | [WordPress guide](./integrations/wordpress-plugin-guide.md) | Installing CubePay on a WordPress store |
| ⚙️ **Any other platform** | [Generic integration guide](./integrations/generic-integration-guide.md) | Direct API connection, platform-independent |

---

## 🚀 Quick Start (Summary)

```
Authorization: Bearer YOUR_API_TOKEN
```

**Recommended approach — one endpoint for both payment types** (depending on which method(s) you've enabled for your account, it automatically creates a card invoice, a crypto invoice, or a method-selection page):

```
POST https://cubevps.ir/pay/create-order.php
```

**Or, if needed, go directly to either one:**

```
POST https://cubevps.ir/smspay/api/create-payment.php   ← card-to-card only
POST https://cubevps.ir/smspay/api/verify-payment.php
POST https://cubevps.ir/crypto/api/create-crypto-payment.php   ← crypto only
```

Complete parameter, response, error-code, and transaction-rule details in 👉 **[docs/API-REFERENCE.md](./docs/API-REFERENCE.md)** (card) and **[docs/CRYPTO-API-REFERENCE.md](./docs/CRYPTO-API-REFERENCE.md)** (crypto / unified)

Code samples in PHP, Python, Node.js, Laravel, and cURL 👉 **[docs/examples/](./docs/examples/)**

---

## 🧭 How the System Works

```mermaid
flowchart TD
    A[Your bot / site] -->|"1. create-order"| B[CubePay API]
    B -->|"2. Payment link"| A
    A -->|"3. Redirect customer"| C{Card or crypto?}
    C -->|Card-to-card| D1[Deposit to bank card]
    C -->|Crypto| D2[Deposit to wallet address]
    D1 -->|"Automatic detection from bank SMS"| B
    D2 -->|"Automatic confirmation from the blockchain"| B
    B -->|"4. Callback"| A
    A -->|"5. verify-payment"| B
    B -->|"6. Final confirmation"| E[✅ Order completed]
```

---

## ⚖️ Comparison With Other Methods

| Feature | CubePay | Official Bank Gateway | Manual Card-to-Card |
|---|:---:|:---:|:---:|
| Requires trust seal/gateway registration | ❌ | ✅ | ❌ |
| Automatic payment confirmation | ✅ | ✅ | ❌ |
| Instant-transfer fee | ❌ | ✅ | ❌ |
| Accepts crypto | ✅ | ❌ | ❌ |
| Automatic callback | ✅ | ✅ | ❌ |
| Managed via Telegram bot | ✅ | ❌ | ❌ |
| Setup time | Minutes | Days/weeks | Instant |

> This table is purely a technical feature comparison; check the legal requirements and actual fees of each method yourself.

---

## ❓ FAQ & Troubleshooting

The most common questions (PHP/Node/SQLite support, enabling Auto Confirmation, 401 error, webhook not received, SSL error, etc.) in 👉 **[docs/FAQ.md](./docs/FAQ.md)**

---

## 🤝 Contributing · 🔒 Security · 📝 Changelog

- Report a bug or open a Pull Request → [CONTRIBUTING.md](./CONTRIBUTING.md)
- Report a security vulnerability → [SECURITY.md](./SECURITY.md)
- Version history → [CHANGELOG.md](./CHANGELOG.md)
- Code of conduct → [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)
- License → [LICENSE](./LICENSE)


## 🔗 Links

🤖 Merchant management bot: [@cubepy_bot](https://t.me/cubepy_bot)
💬 Support: [cube_sup](https://t.me/cube_sup) · 📧 info@cubevps.ir
