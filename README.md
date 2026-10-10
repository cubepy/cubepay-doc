<p align="center">
  <img src="./assets/cubepay-banner.svg" alt="CubePay — اتصال فروشگاه به تأیید خودکار پرداخت" width="100%">
</p>

<p align="center">
  <a href="https://github.com/cubepy/cubepay-doc/actions/workflows/docs-checks.yml"><img src="https://github.com/cubepy/cubepay-doc/actions/workflows/docs-checks.yml/badge.svg?branch=main" alt="وضعیت بررسی مستندات"></a>
  <a href="https://github.com/cubepy/cubepay-doc/releases/tag/android-latest"><img src="https://img.shields.io/badge/Android-Download_APK-35bca5?logo=android&amp;logoColor=white" alt="دانلود رسمی اندروید"></a>
  <a href="./en/README.md"><img src="https://img.shields.io/badge/docs-فارسی_%2F_English-d2b365" alt="مستندات فارسی و انگلیسی"></a>
</p>

<h1 align="center">پرداخت مشتری، متصل به فروشگاه شما</h1>

<p align="center">ساخت لینک پرداخت، تشخیص واریز بانکی و دریافت نتیجه در سایت یا ربات.<br>پرداخت کارت‌به‌کارت و ارز دیجیتال، با مدیریت از ربات و پنل وب.</p>

<p align="center">
  <a href="./START-HERE.md"><b>شروع کار</b></a> ·
  <a href="https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk"><b>دانلود اندروید</b></a> ·
  <a href="./integrations/ios-shortcuts-sms-forwarding-guide.md"><b>آموزش آیفون</b></a> ·
  <a href="./docs/API-REFERENCE.md"><b>مستندات API</b></a> ·
  <a href="https://t.me/cube_sup"><b>پشتیبانی</b></a>
</p>

<p align="center">🇮🇷 فارسی · <a href="./en/README.md">🇬🇧 English</a></p>

<div dir="rtl">

## از کجا شروع کنم؟

| فروشنده هستم | توسعه‌دهنده هستم |
|---:|---:|
| در [ربات کیوب‌پی](https://t.me/cubepy_bot) ثبت‌نام کنید و مراحل تأیید حساب را انجام دهید. | [راهنمای اتصال](./integrations/generic-integration-guide.md)، قرارداد API و نمونه‌کدها را ببینید. |
| پیامک بانک را با [اندروید](./integrations/android-sms-forwarder-guide.md) یا [آیفون](./integrations/ios-shortcuts-sms-forwarding-guide.md) وصل کنید. | [کارت‌به‌کارت](./docs/API-REFERENCE.md) · [ارز دیجیتال و مسیر یکپارچه](./docs/CRYPTO-API-REFERENCE.md) · [OpenAPI](./docs/openapi.yaml) |
| از [پنل وب](./docs/WEB-PANEL.md) لینک پرداخت بسازید یا فروشگاهتان را متصل کنید. | [PHP](./docs/examples/php-example.php) · [Python](./docs/examples/python-example.py) · [Node.js](./docs/examples/node-example.js) |

> **بدون اینترنت هم روش پیامکی دارید:** ارسال اینترنتی و ارسال پیامک به سرشماره دو مسیر جدا هستند. روش پیامکی به آنتن، امکان ارسال SMS و ثبت شمارهٔ فرستنده نیاز دارد و هزینهٔ اپراتور دارد. راهنمای گوشی خودتان را دنبال کنید.

## داخل کیوب‌پی

<table>
  <tr><th>گزارش ارسال پیام بانک در اندروید</th><th>گزارش فروش در پنل فروشنده</th></tr>
  <tr>
    <td width="50%"><a href="./integrations/android-sms-forwarder-guide.md"><img src="./assets/product/android-reports.png" alt="اپ اندروید: نتیجهٔ تست اتصال و گزارش ارسال پیام بانک" width="100%"></a></td>
    <td width="50%"><a href="./docs/WEB-PANEL.md"><img src="./assets/product/merchant-reports.png" alt="پنل فروشنده: مقایسهٔ فروش روزانه، هفتگی و ماهانه" width="100%"></a></td>
  </tr>
</table>

تصاویر رابط محصول با **داده‌های نمایشی** هستند. گزارش ارسال پیام، وضعیت پرداخت و تحویل سفارش هرکدام معنی جداگانه دارند.

## مسیر یک پرداخت

![ساخت فاکتور، پرداخت مشتری، بررسی نتیجه و تحویل سفارش توسط فروشگاه](./assets/payment-flow.svg)

1. فروشگاه یک فاکتور یا لینک پرداخت می‌سازد.
2. مشتری پرداخت می‌کند؛ پیام بانک یا نتیجهٔ شبکهٔ ارز دیجیتال بررسی می‌شود.
3. فروشگاه نتیجه را دریافت و از طریق API بررسی می‌کند؛ سپس سفارش را تحویل می‌دهد.

**تأیید پرداخت به‌تنهایی به معنی تحویل محصول نیست.** منطق تحویل سفارش در سایت یا ربات فروشنده اجرا می‌شود.

## اتصال به ابزارهای شما

| ابزار | مسیر راه‌اندازی |
|---:|---:|
| Foxima | [تنظیم درگاه در ربات](./integrations/faoxima-guide.md) |
| Mirzabot | [راهنمای اتصال میرزا](./integrations/mirzabot-ready-files/mirzabot-ready-files-guide.md) |
| WordPress / WooCommerce | [راهنمای وردپرس](./integrations/wordpress-plugin-guide.md) |
| سایت یا ربات اختصاصی | [اتصال مستقیم به API](./integrations/generic-integration-guide.md) |
| بدون سایت و ربات | [ساخت لینک پرداخت در پنل وب](./docs/WEB-PANEL.md) |

## دانلود و اطلاعات قابل بررسی

- **اپ رسمی اندروید:** [دریافت APK](https://github.com/cubepy/cubepay-doc/releases/download/android-latest/CubePay.apk) · [نسخه و تغییرات انتشار](https://github.com/cubepy/cubepay-doc/releases/tag/android-latest) · [راهنما و SHA-256 فایل](./integrations/android-sms-forwarder-guide.md).
- **آیفون:** [آموزش Shortcuts اینترنتی و پیامکی](./integrations/ios-shortcuts-sms-forwarding-guide.md).
- **مستندات:** [تاریخچهٔ تغییرات](./CHANGELOG.md) · [پرسش‌های متداول](./docs/FAQ.md) · [راهنمای گزارش خصوصی آسیب‌پذیری](./SECURITY.md).
- **این مخزن:** مستندات سرویس میزبانی‌شده و مثال‌های اتصال است؛ سورس سرور یا اپلیکیشن در آن منتشر نشده است. نشان بررسی مستندات، وضعیت زندهٔ سرویس پرداخت را نشان نمی‌دهد.

<details>
<summary>اطلاعیه برای مشترکان قبلی VIP</summary>

پایان خدمات VIP برای **۲۹ مهر ۱۴۰۵، ساعت ۲۳:۵۹:۵۹ به وقت ایران** برنامه‌ریزی شده است. برای اتصال جدید از مسیر عادی استفاده کنید. [مستندات مسیر قبلی](./docs/CUBEPAY-VIP-API-REFERENCE.md) برای مراجعهٔ مشترکان موجود حفظ شده است.

</details>

---

[ربات فروشندگان](https://t.me/cubepy_bot) · [پشتیبانی](https://t.me/cube_sup) · [مشارکت در مستندات](./CONTRIBUTING.md) · [مجوز مخزن](./LICENSE)

</div>
