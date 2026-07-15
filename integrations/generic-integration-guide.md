# ⚙️ راهنمای اتصال عمومی (هر پلتفرم/زبان)

این راهنما برای کسانیه که ربات یا سایت‌شون رو با کد اختصاصی خودشون ساختن و پلتفرم آماده‌ای (مثل Foxima یا وردپرس) استفاده نمی‌کنن.

## مراحل اتصال

### ۱. دریافت توکن API

از [@cubepy_bot](https://t.me/cubepy_bot)، بعد از ثبت‌نام و تایید حساب، توکن API رو از بخش «🔗 پنل من» بردارید.

### ۲. ذخیره‌ی سفارش قبل از پرداخت

قبل از تماس با API، یک رکورد برای سفارش با وضعیت `pending` در دیتابیس خودتون بسازید (هر دیتابیسی — SQLite, MySQL, PostgreSQL و غیره کاملاً قابل قبوله):

```
orders: id, order_id (unique), amount, status, authority (nullable)
```

### ۳. فراخوانی create-payment

```
POST https://cubevps.ir/smspay/api/create-payment.php
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json

{
  "amount": 200000,
  "order_id": "ORD123",
  "callback_url": "https://yoursite.com/cubepay/callback",
  "type": "card"
}
```

`authority` و `payment_link` دریافتی رو در رکورد سفارش خودتون ذخیره کنید.

### ۴. هدایت کاربر به لینک پرداخت

کاربر رو مستقیم به `payment_link` دریافتی هدایت کنید (ریدایرکت یا نمایش لینک/دکمه).

### ۵. دریافت Callback

روی آدرسی که به‌عنوان `callback_url` دادید، یک Endpoint بسازید که:
- درخواست POST (یا querystring) شامل `authority`, `order_id`, `status` رو می‌پذیره
- بلافاصله `verify-payment` رو صدا می‌زنه (مرحله‌ی بعد)

### ۶. فراخوانی verify-payment

```
POST https://cubevps.ir/smspay/api/verify-payment.php
Authorization: Bearer YOUR_API_TOKEN
Content-Type: application/json

{
  "authority": "bdc9e0497c121d6187750d53798dae81"
}
```

اگه `success: true` برگشت، سفارش رو در دیتابیس خودتون به `paid` تغییر بدید و محصول/سرویس رو تحویل بدید.

⚠️ چون فقط اولین verify موفق `success: true` می‌ده، قبل از پردازش دوباره چک کنید که سفارش از قبل `paid` نشده باشه (جلوگیری از تحویل دوباره).

## نمونه کد

نمونه‌ی کامل در PHP، Python، Node.js و cURL در پوشه‌ی [`docs/examples/`](../docs/examples/) موجوده.

## چک‌لیست قبل از رفتن به Production

- [ ] `callback_url` روی https و از بیرون در دسترسه
- [ ] قبل از تحویل، `verify-payment` رو صدا زدید (نه فقط اعتماد به callback)
- [ ] در برابر verify تکراری (HTTP 409) محافظت دارید
- [ ] لاگ خطاها رو برای دیباگ ذخیره می‌کنید

سوالی موند؟ [docs/FAQ.md](../docs/FAQ.md) رو ببینید یا با [cube_sup](https://t.me/cube_sup) در ارتباط باشید.
