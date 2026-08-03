🇮🇷 فارسی · [🇬🇧 English](../en/integrations/generic-integration-guide.md)

<div align="center"><img src="../cubepay-logo.png" alt="CubePay" width="220"></div>

# 🔌 راهنمای اتصال CubePay به ربات/سایت خودتون (بدون نیاز به Foxima)

این راهنما برای کسانیه که ربات یا سایت خودشون رو دارن (هر چیزی، نه لزوماً Foxima) و می‌خوان درگاه CubePay رو بهش وصل کنن.

⏱ زمان تخمینی: کمتر از ۱ ساعت

---

## قبل از شروع، این رو از ربات مدیریت (`@cubepy_bot`) بردارید:

🔑 **توکن API** — از «🔗 پنل من»

---

## 🆕 روش پیشنهادی — یک endpoint برای کارت + کریپتو

اگه می‌خواید مشتری‌هاتون (بسته به تنظیماتِ حساب فروشندگیِ خودتون تو ربات، از «⚙️ تنظیمات بیشتر → 💳 روش‌های پرداخت») بین کارت‌به‌کارت و ارز دیجیتال (USDT/TRX/TON) انتخاب کنن، کافیه فقط همین یه endpoint رو صدا بزنید:

> 👑 **اگر اشتراکِ CubePay VIP دارید:** همین کد بدونِ هیچ تغییری کار می‌کنه — فقط به‌جای توکنِ عادی، توکنِ `vip_…` رو بذارید. اون‌وقت به‌جای کارتِ خودتون، کارتِ خزانه‌ی CubePay به مشتری نشون داده می‌شه و تسویه‌ی شما ارزی انجام می‌شه. جزئیات: [`docs/CUBEPAY-VIP-API-REFERENCE.md`](../docs/CUBEPAY-VIP-API-REFERENCE.md#-مهاجرت-به-vip-فقط-توکن-را-عوض-کنید)

```php
$apiToken = 'توکن_شما';
$orderId  = 'order-' . time();
$amountToman = 20000; // مبلغ به تومان (نه ریال)

$ch = curl_init('https://cubevps.ir/pay/create-order.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'order_id' => $orderId,
        'price_amount' => $amountToman,
        'callback_url' => 'https://yoursite.com/callback.php',
    ]),
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!empty($response['success'])) {
    header('Location: ' . $response['pay_page_url']);
    exit;
}
```

بسته به تنظیمات، خودش تصمیم می‌گیره فاکتور کارتی بسازه، کریپتویی بسازه، یا صفحه‌ی «کارت یا کریپتو؟» نشون بده. جزئیات کامل (فرمتِ callback، چک امضای HMAC برای کریپتو، کدهای خطا) در 👉 **[docs/CRYPTO-API-REFERENCE.md](../docs/CRYPTO-API-REFERENCE.md)**

---

## روش قدیمی‌تر — فقط کارت‌به‌کارت (با کلاینتِ آماده)

اگه فقط کارت‌به‌کارت می‌خواید (بدون کریپتو)، می‌تونید به‌جای صدا زدن مستقیمِ API، از کلاینتِ آماده هم استفاده کنید. این فایل رو از ریپازیتوری بردارید:

📁 فایل **[`CubePayClient.php`](../docs/examples/CubePayClient.php)**

### قدم ۱ — ساخت فاکتور (وقتی مشتری می‌خواد پرداخت کنه)

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

📌 اگه از روتر یکپارچه (بخش بالا) استفاده می‌کنید، `callback.php`‌تون باید هم حالتِ کارتی (`authority`) هم کریپتویی (`sig` + امضای HMAC) رو بشناسه — نمونه‌کدش در [docs/CRYPTO-API-REFERENCE.md](../docs/CRYPTO-API-REFERENCE.md#-اطلاعات-ارسالی-به-callback_url) هست.

---

## نکات مهم

- ✔️ روترِ یکپارچه و endpoint کریپتویی، مبلغ رو به **تومان** می‌گیرن؛ `CubePayClient` قدیمی به **ریال**
- ✔️ `handleCallback()` خودش idempotent هست — اگه دوبار صدا زده بشه، دومی خودکار رد می‌شه، نگران تحویل دوباره نباشید
- ✔️ هیچ‌وقت فقط به رسیدن callback اعتماد نکنید — برای کارتی `handleCallback()` خودش verify واقعی رو انجام می‌ده؛ برای کریپتو، امضای HMAC رو حتماً چک کنید
- ✔️ کارت مقصد و ولت کریپتو رو از تنظیمات خودتون تو `@cubepy_bot` تعیین می‌کنید، نه تو کد

---

## سوالات بیشتر

برای جزئیات کامل‌تر (کدهای خطا، فرمت دقیق پاسخ‌ها، نمونه‌کد Python/Node):
- کارت‌به‌کارت → [`docs/API-REFERENCE.md`](../docs/API-REFERENCE.md)
- کریپتو / روتر یکپارچه → [`docs/CRYPTO-API-REFERENCE.md`](../docs/CRYPTO-API-REFERENCE.md)
