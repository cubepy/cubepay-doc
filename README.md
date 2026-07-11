<div align="center">

<img src="./cubepay-logo.png" alt="CubePay Logo" width="360"/>

**پرداخت کارت‌به‌کارت با تایید خودکار پیامکی**

بدون نیاز به درگاه بانکی رسمی · بدون کارمزد شتاب 🚀

![Status](https://img.shields.io/badge/status-active-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)
![API](https://img.shields.io/badge/API-v1-orange)
[![Try in Swagger Editor](https://img.shields.io/badge/API%20Docs-Swagger%20Editor-85EA2D?logo=swagger)](https://editor.swagger.io/?url=https://raw.githubusercontent.com/cubepy/cubepay-doc/main/openapi.yaml)

</div>

---

CubePay یک API ساده برای ساخت و تایید تراکنش‌های کارت‌به‌کارت است. مشتری شما مبلغ را مستقیم به کارت شما واریز می‌کند و تاییدش کاملاً خودکار (از روی پیامک بانکی) انجام می‌شود — دقیقاً مثل یک درگاه پرداخت، ولی بدون نیاز به نماد و ثبت درگاه رسمی.

## 🧭 از کجا شروع کنم؟

بسته به این‌که چی دارید، یکی از این مسیرها رو انتخاب کنید:

### ۱) ربات من Faoxima هست (یا فورکی از اون)

دو راه دارید:

| روش | مناسب برای | فایل |
|---|---|---|
| 📁 فایل آماده (بدون ویرایش دستی) | سریع‌تر، بی‌خطاتر | [`foxima-ready-files/`](./foxima-ready-files/) + [راهنما](./foxima-ready-files-guide.md) |
| 📄 ویرایش دستی (فقط ۲ خط) | کسایی که می‌خوان خودشون کد رو ببینن/کنترل کنن | [`foxima-integration-guide.md`](./foxima-integration-guide.md) |

### ۲) یه ربات/سایت دیگه دارم (نه Faoxima) و PHP بلدم

📄 [`generic-integration-guide.md`](./generic-integration-guide.md) — فقط فایل [`CubePayClient.php`](./CubePayClient.php) رو بردارید، توکن‌تون رو بذارید، ۲-۳ خط کد بنویسید. تمام.

### ۳) برنامه‌نویس ندارم یا زبان دیگه‌ای (Python/Node/…) دارم

📄 [`API-REFERENCE.md`](./API-REFERENCE.md) — مرجع کامل و فنی API با نمونه‌کد PHP، Python، Node.js و cURL. این فایل رو به برنامه‌نویس خودتون هم می‌تونید بدید.

---

## 🚀 گرفتن توکن API

برای هر مسیری که بالا انتخاب کردید، اول به این نیاز دارید:

**توکن دسترسی (API Token)** — از ربات مدیریت فروشندگان (`@cubepy_bot`) بعد از ثبت‌نام و تایید حساب دریافت می‌کنید.

⚠️ آدرس‌های API با پسوند `.php` هستند — دقیقاً همان‌طور که در پنل فروشنده‌تان نوشته شده کپی کنید (جزئیات کامل در [`API-REFERENCE.md`](./API-REFERENCE.md)).

---

## 📌 نکات مهم (خلاصه)

- ✔ مبلغ‌ها همه‌جا به **ریال** ارسال/دریافت می‌شوند (تومان × ۱۰)
- ✔ هر فاکتور مدت‌زمان مشخصی اعتبار دارد؛ بعدش منقضی می‌شود
- ✔ `verify-payment` را فقط یک‌بار در ازای هر تراکنش، بعد از دریافت callback صدا بزنید
- ✔ گوشی‌ای که اپ فورواردر پیامک روش نصبه، باید همیشه به اینترنت وصل باشد — رایج‌ترین دلیل تاییدنشدن تراکنش‌ها همینه

جزئیات کامل و کدهای خطا: [`API-REFERENCE.md`](./API-REFERENCE.md)

---

## 📚 مستندات بیشتر

| فایل | محتوا |
|---|---|
| [`API-REFERENCE.md`](./API-REFERENCE.md) | مرجع کامل و فنی API (endpoint ها، پارامترها، کدهای خطا، نمونه‌کد) |
| [`openapi.yaml`](./openapi.yaml) | مشخصات OpenAPI — [باز کردن در Swagger Editor](https://editor.swagger.io/?url=https://raw.githubusercontent.com/cubepy/cubepay-doc/main/openapi.yaml) |
| [`FAQ.md`](./FAQ.md) | سوالات متداول |
| [`CHANGELOG.md`](./CHANGELOG.md) | تاریخچه‌ی نسخه‌ها و تغییرات |
| [`CubePayClient.php`](./CubePayClient.php) | SDK رسمی PHP (تک‌فایل، بدون نیاز به Composer) |
| [`examples/`](./examples/) | نمونه‌کدهای تکمیلی |

---

## 🔗 لینک‌ها

🤖 ربات مدیریت فروشندگان (ثبت‌نام، توکن، کارت‌ها): [@cubepy_bot](https://t.me/cubepy_bot)

🆘 پشتیبانی: [@cube_sup](https://t.me/cube_sup)

📘 مستندات تعاملی API: [باز کردن در Swagger Editor](https://editor.swagger.io/?url=https://raw.githubusercontent.com/cubepy/cubepay-doc/main/openapi.yaml)

---

<div align="center">

ساخته‌شده با ❤️ برای توسعه‌دهندگان ایرانی

</div>
