<div align="center"><img src="cubepay-logo.png" alt="CubePay" width="220"></div>

# 🔌 راهنمای اتصال CubePay به ربات/سایت خودتون (بدون نیاز به Foxima)

این راهنما برای کسانیه که ربات یا سایت خودشون رو دارن (هر چیزی، نه لزوماً Foxima) و می‌خوان درگاه CubePay رو بهش وصل کنن. تمام کاری که لازمه، فقط **۲ تیکه کد** هست.

⏱ زمان تخمینی: کمتر از ۱ ساعت

---

## قبل از شروع، این ۲ تا رو از ربات مدیریت (`@cubepy_bot`) بردارید:

1. 🔑 **توکن API** — از «🔗 پنل من»
2. 📁 فایل **`CubePayClient.php`** — از همین ریپازیتوری

---

## قدم ۱ — ساخت فاکتور (وقتی مشتری می‌خواد پرداخت کنه)

```php
require 'CubePayClient.php';

$cubepay = new CubePayClient('توکن_شما', 'https://cubevps.ir/smspay');

$result = $cubepay->createPayment(
    200000,                                  // مبلغ به ریال (تومان × ۱۰)
    'order-' . time(),                       // شناسه‌ی یکتای سفارش شما
    'https://yoursite.com/callback.php'      // آدرس فایل قدم ۲
);

if ($result['success']) {
    // این لینک رو به مشتری بدید (نه فقط مبلغ رو نمایش بدید، چون آفست داره)
    echo $result['payment_link'];
    echo $result['pay_amount_toman']; // مبلغ دقیق قابل‌پرداخت
} else {
    echo $result['message'];
}
```

---

## قدم ۲ — دریافت تاییدیه (فایل `callback.php` خودتون)

```php
require 'CubePayClient.php';

$cubepay = new CubePayClient('توکن_شما', 'https://cubevps.ir/smspay');

// این تابع خودش authority رو از GET/POST/JSON پیدا می‌کنه و verify می‌زنه
$result = $cubepay->handleCallback();

if (!empty($result['success'])) {
    // ✅ پرداخت واقعاً تایید شد — اینجا سرویس/شارژ رو تحویل بدید
    $orderId = $result['order_id'];
    $amount = $result['amount']; // ریال

    // مثال: chargeUserWallet($orderId, $amount);
} else {
    // یعنی یا هنوز تایید نشده، یا قبلاً پردازش شده — کاری نکنید
}
```

📌 **این ۲ فایل کل کاریه که لازمه.** بقیه (تشخیص پیامک، رزرو کارمزد، رزرو کارت) خودکار سمت CubePay انجام می‌شه.

---

## نکات مهم

- ✔️ مبلغ‌ها همه‌جا **ریال**ن (تومان × ۱۰)
- ✔️ `handleCallback()` خودش idempotent هست — اگه دوبار صدا زده بشه، دومی خودکار رد می‌شه، نگران تحویل دوباره نباشید
- ✔️ هیچ‌وقت فقط به رسیدن callback اعتماد نکنید — `handleCallback()` خودش verify واقعی رو انجام می‌ده، پس این نگرانی از قبل حل شده
- ✔️ کارت مقصد رو از تنظیمات خودتون تو `@cubepy_bot` (بخش «💳 مدیریت کارت‌ها») تعیین می‌کنید، نه تو کد

---

## سوالات بیشتر
برای جزئیات کامل‌تر (کدهای خطا، فرمت دقیق پاسخ‌ها، نمونه‌کد Python/Node) به [`README.md`](README.md) مراجعه کنید.
