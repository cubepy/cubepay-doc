# اتصال CubePay با php

پیش‌نیاز: **PHP 8.1+ با cURL و PDO SQLite**. فایل‌های این پوشه را کنار هم نگه دارید؛ CubePay.php، Store.php و schema.sql لازم‌اند. نصب از Composer، PyPI یا npm لازم نیست.

## شروع در سرور لینوکسی

از توکن حساب آزمایشی CubePay برای شروع استفاده کنید. فرمان create زیر واقعاً API را صدا می‌زند و فاکتور می‌سازد؛ قبل از اجرای آن مقادیر نمونه را جایگزین کنید.

```bash
# در پوشهٔ همین بسته؛ مسیر /home/SHOP را با مسیر خصوصی خود جایگزین کنید.
install -d -m 700 /home/SHOP/private/cubepay
export CUBEPAY_DB=/home/SHOP/private/cubepay/orders.sqlite
# توکن را در تنظیمات محرمانهٔ سرویس بگذارید؛ در Bash بدون نمایش و ثبت در history:
read -rs -p 'CubePay token: ' CUBEPAY_TOKEN; echo
export CUBEPAY_TOKEN
php example.php create ORDER-unique-001 200000 BUYER-ID https://your-shop.example/cubepay/callback
```

۲۰۰٬۰۰۰ ریال یعنی ۲۰٬۰۰۰ تومان. `ORDER-unique-001` را با شمارهٔ یکتای سفارش خودتان و `BUYER-ID` را با شناسهٔ واقعی خریدار عوض کنید. خروجی شامل `authority`، `pay_amount` و `payment_link` است؛ مشتری را به `payment_link` هدایت کنید. مبلغ، خریدار و نشانی callback باید از سفارش سمت سرور شما بیایند.

## وصل کردن callback و تحویل

فایل example فقط ابزار خط فرمان است. در برنامهٔ وب خودتان کلاس Store را وارد کنید، مسیر HTTPS کال‌بک بسازید و authority ورودی را به متد callback بدهید. [قرارداد کامل callback و تحویل](../README-fa.md) را قبل از راه‌اندازی بخوانید.

```bash
# این فرمان verify را صدا می‌زند؛ فقط برای فاکتور ذخیره‌شدهٔ همین دیتابیس:
php example.php callback AUTHORITY_FROM_CREATE
```

وضعیت `paid` یک ردیف تحویل در جدول `delivery_outbox` می‌سازد؛ پردازشگر فروشگاه باید آن را با جلوگیری از تحویل تکراری انجام دهد. `sandbox_paid` آزمایشی است و تحویل واقعی ندارد. وضعیت `reconcile` نیازمند تطبیق سابقه است و نباید خودکار به موفق تبدیل شود.

از یک دیتابیس ماندگار و خصوصی برای هر فروشنده استفاده کنید. دیتابیس همهٔ اجراهای create و callback باید یکی باشد؛ آن را در public_html یا حافظهٔ موقت قرار ندهید.

[راهنمای فارسی](../../../docs/INTEGRATION-KITS.md) · [English](../../../en/docs/INTEGRATION-KITS.md)
