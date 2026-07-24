<p align="center">
  <img src="./assets/demo-banner.jpg" alt="CubePay Demo" width="100%">
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
هر واریز، خودش را تأیید می‌کند — پرداخت کارت‌به‌کارت و ارز دیجیتال، با تشخیص خودکار پیامک بانکی یا شبکه‌ی بلاک‌چین 🚀
</p>

CubePay یک سرویس API برای ساخت و تأیید خودکار تراکنش‌هاست — چه کارت‌به‌کارت، چه ارز دیجیتال (USDT / TRX / TON). برای پرداخت کارتی، مشتری شما مبلغ رو مستقیم به کارت شما واریز می‌کنه و سیستم از روی پیامک بانکی، پرداخت رو در کمتر از ۳۰ ثانیه تشخیص و تأیید می‌کنه؛ برای پرداخت کریپتویی، مشتری خودش ارز دلخواهش رو انتخاب می‌کنه و تاییدش کاملاً خودکار از روی شبکه‌ی بلاک‌چین انجام می‌شه — بدون نیاز به نماد اعتماد الکترونیکی (اینماد)، بدون نیاز به درگاه بانکی رسمی و بدون کارمزد شتاب.

> 📌 این ریپو مستندات یک سرویس API آنلاین است، نه یک کتابخانه‌ی قابل نصب. برای شروع فقط به یک توکن API از [@cubepy_bot](https://t.me/cubepy_bot) نیاز دارید.

**تازه‌کار هستید؟ 👉 از اینجا شروع کنید: [START-HERE.md](./START-HERE.md)**

---

## ✨ امکانات

| | |
|---|---|
| ✅ تأیید خودکار پرداخت (زیر ۳۰ ثانیه) | ✅ Callback خودکار به سرور شما |
| ✅ محافظت در برابر تأیید دوباره (idempotent) | ✅ بدون نیاز به نماد یا درگاه رسمی |
| ✅ مدیریت کامل از طریق ربات تلگرام | ✅ ساخت فاکتور دستی از پنل |
| ✅ کیف پول و مدیریت چند کارت | ✅ گزارش کامل تراکنش‌ها |
| ✅ همکار حساب (چند مدیر) | ✅ اتصال HTTPS رمزنگاری‌شده |
| 🆕 پرداخت با ارز دیجیتال (USDT · TRX · TON) | 🆕 یک endpoint یکپارچه: کارت یا کریپتو، انتخاب با مشتری |

🔐 **امنیت:** بدون ذخیره‌سازی اطلاعات کارت خریدار · قفل اتمیک کیف پول (Atomic Wallet Lock) · پشتیبانی مستقیم و پاسخ‌گویی واقعی

---

## 🗂 نقشه‌ی مستندات این ریپو

```
cubepay-doc/
├── START-HERE.md                 ← تازه‌کارید؟ اول این رو بخونید
├── docs/
│   ├── API-REFERENCE.md          ← مرجع کامل فنی API (Endpoints, پارامترها, خطاها)
│   ├── FAQ.md                    ← سوالات متداول
│   ├── openapi.yaml              ← اسپک OpenAPI 3.0 (برای Postman/Swagger)
│   └── examples/                 ← نمونه کد آماده به ازای هر زبان
│       ├── CubePayClient.php
│       ├── php-example.php
│       ├── python-example.py
│       ├── node-example.js
│       ├── laravel-example.php
│       └── curl-example.sh
└── integrations/                 ← راهنمای اتصال به پلتفرم‌های خاص
    ├── generic-integration-guide.md
    ├── wordpress-plugin-guide.md
    ├── faoxima-integration-guide.md
    └── faoxima-ready-files/
        └── faoxima-ready-files-guide.md
```

---

## 🔌 اتصال به پلتفرم‌های آماده

اگه از یکی از این پلتفرم‌ها استفاده می‌کنید، لازم نیست خودتون API رو صفر تا صد پیاده‌سازی کنید:

| پلتفرم | راهنما | توضیح |
|---|---|---|
| 🤖 **Foxima** (و فورک‌هاش) | [نصب با فایل آماده](./integrations/faoxima-ready-files/faoxima-ready-files-guide.md) | فقط چند فایل PHP رو جایگزین می‌کنید — سریع‌ترین روش |
| 🤖 **Foxima** (ویرایش دستی) | [راهنمای اتصال دستی](./integrations/faoxima-integration-guide.md) | اگه فایل‌های ربات‌تون شخصی‌سازی شده و نمی‌خواید کامل جایگزین بشه |
| 🌐 **وردپرس / ووکامرس** | [راهنمای وردپرس](./integrations/wordpress-plugin-guide.md) | نصب CubePay روی فروشگاه وردپرسی |
| ⚙️ **هر پلتفرم دیگه** | [راهنمای اتصال عمومی](./integrations/generic-integration-guide.md) | اتصال مستقیم به API، مستقل از پلتفرم |

---

## 🚀 شروع سریع (خلاصه)

```
Authorization: Bearer YOUR_API_TOKEN
```

**روش پیشنهادی — یک endpoint برای هر دو نوع پرداخت** (بسته به این‌که کدوم روش(ها) رو برای اکانتتون فعال کردید، خودکار فاکتور کارتی، کریپتویی، یا صفحه‌ی انتخاب روش می‌سازه):

```
POST https://cubevps.ir/pay/create-order.php
```

**یا در صورت نیاز، مستقیم به هرکدوم:**

```
POST https://cubevps.ir/smspay/api/create-payment.php   ← فقط کارت‌به‌کارت
POST https://cubevps.ir/smspay/api/verify-payment.php
POST https://cubevps.ir/crypto/api/create-crypto-payment.php   ← فقط ارز دیجیتال
```

جزئیات کامل پارامترها، پاسخ‌ها، کدهای خطا و قوانین تراکنش در 👉 **[docs/API-REFERENCE.md](./docs/API-REFERENCE.md)**

نمونه کد در PHP، Python، Node.js، Laravel و cURL 👉 **[docs/examples/](./docs/examples/)**

---

## 🧭 نحوه‌ی کار سیستم

```mermaid
flowchart TD
    A[ربات / سایت شما] -->|"1. create-order"| B[CubePay API]
    B -->|"2. لینک پرداخت"| A
    A -->|"3. هدایت مشتری"| C{کارت یا کریپتو؟}
    C -->|کارت‌به‌کارت| D1[واریز به کارت بانکی]
    C -->|ارز دیجیتال| D2[واریز به آدرس ولت]
    D1 -->|"تشخیص خودکار از پیامک بانکی"| B
    D2 -->|"تایید خودکار از شبکه‌ی بلاک‌چین"| B
    B -->|"4. Callback"| A
    A -->|"5. verify-payment"| B
    B -->|"6. تایید نهایی"| E[✅ سفارش تکمیل شد]
```

---

## ⚖️ مقایسه با روش‌های دیگر

| ویژگی | CubePay | درگاه بانکی رسمی | کارت‌به‌کارت دستی |
|---|:---:|:---:|:---:|
| نیاز به نماد/ثبت درگاه | ❌ | ✅ | ❌ |
| تأیید خودکار پرداخت | ✅ | ✅ | ❌ |
| کارمزد شتاب | ❌ | ✅ | ❌ |
| پذیرش ارز دیجیتال | ✅ | ❌ | ❌ |
| Callback خودکار | ✅ | ✅ | ❌ |
| مدیریت از طریق ربات تلگرام | ✅ | ❌ | ❌ |
| زمان راه‌اندازی | چند دقیقه | چند روز/هفته | فوری |

> این جدول صرفاً مقایسه‌ی فنی امکانات است؛ شرایط قانونی و کارمزد واقعی هر روش رو خودتون بررسی کنید.

---

## ❓ سوالات متداول و عیب‌یابی

پرتکرارترین سوالات (پشتیبانی PHP/Node/SQLite، فعال‌سازی Auto Confirmation، خطای 401، Webhook دریافت نمی‌شه، SSL Error و…) در 👉 **[docs/FAQ.md](./docs/FAQ.md)**

---

## 🤝 مشارکت · 🔒 امنیت · 📝 تغییرات

- گزارش باگ یا Pull Request → [CONTRIBUTING.md](./CONTRIBUTING.md)
- گزارش آسیب‌پذیری امنیتی → [SECURITY.md](./SECURITY.md)
- تاریخچه‌ی نسخه‌ها → [CHANGELOG.md](./CHANGELOG.md)
- آیین رفتاری → [CODE_OF_CONDUCT.md](./CODE_OF_CONDUCT.md)
- مجوز استفاده → [LICENSE](./LICENSE)


## 🔗 لینک‌ها

🤖 ربات مدیریت فروشندگان: [@cubepy_bot](https://t.me/cubepy_bot)
💬 پشتیبانی: [cube_sup](https://t.me/cube_sup) · 📧 info@cubevps.ir
