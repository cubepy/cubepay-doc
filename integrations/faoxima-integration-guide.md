🇮🇷 فارسی · [🇬🇧 English](../en/integrations/faoxima-integration-guide.md)

# 🔧 اتصال دستی CubePay به ربات Foxima

این راهنما برای فروشنده‌هایی است که ربات‌شان را با **[Foxima](https://github.com/Mmd-Amir/Faoxima)** (یا فورک‌های آن) ساخته‌اند و **قبلاً فایل‌های زیر را شخصی‌سازی کرده‌اند** — به همین دلیل نمی‌خواهند آن‌ها را با فایل‌های آماده کامل جایگزین کنند.

> 💡 اگه فایل‌هاتون شخصی‌سازی نشده، روش سریع‌تر [نصب با فایل آماده](./faoxima-ready-files/faoxima-ready-files-guide.md) است.

⚠️ **قبل از شروع** حتماً از پوشه‌ی ربات خود یک نسخه‌ی پشتیبان (Backup) تهیه کنید.

## فایل‌های موردنیاز برای ویرایش

| فایل | مسیر |
|---|---|
| `business_logic_1.php` | `re/rx/function/business_logic_1.php` |
| `successful.php` | `payment/ZarinPay/successful.php` |

این دو فایل به‌ترتیب مسئول **ایجاد فاکتور پرداخت** و **تأیید خودکار تراکنش** هستند.

🆕 **این راهنما حالا کریپتو (USDT/TRX/TON) رو هم پوشش می‌ده.** اگه فقط کارت‌به‌کارت می‌خواید، فقط قسمتِ اول هر تابع کافیه؛ اگه کریپتو رو هم رو حساب فروشندگی‌تون (تو ربات CubePay) فعال کردید، قسمتِ دومِ هر تابع رو هم اضافه کنید.

## قدم ۱ — افزودن تابع ایجاد تراکنش

داخل `business_logic_1.php`، جایی که فاکتور پرداخت درگاه فعلی (مثلاً زرین‌پی) ساخته می‌شه، این تابع رو اضافه/جایگزین کنید:

```php
function cubepay_create_payment($amount_toman, $order_id, $callback_url, $description = '')
{
    $token = "YOUR_API_TOKEN"; // بهتره از تنظیمات پنل بخونید، نه هاردکد

    // روتر یکپارچه — بسته به تنظیمات حساب شما تو ربات CubePay («⚙️ تنظیمات
    // بیشتر → 💳 روش‌های پرداخت»)، خودش تصمیم می‌گیره فاکتور کارتی بسازه،
    // کریپتویی بسازه، یا صفحه‌ی «کارت یا کریپتو؟» به مشتری نشون بده.
    // برخلاف endpoint قدیمیِ فقط-کارتی، مبلغ اینجا «تومان»ه، نه ریال.
    $data = [
        "order_id" => (string) $order_id,
        "price_amount" => $amount_toman,
        "callback_url" => $callback_url,
    ];

    $ch = curl_init("https://cubevps.ir/pay/create-order.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$token}",
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    // 📌 نکته: وقتی هر دو روش فعال باشن، هنوز authority نداریم (چون مشتری
    // هنوز روش رو انتخاب نکرده) — این طبیعیه، فقط pay_page_url رو نیاز دارید.
    return $result;
}
```

سپس در همون فایل، جایی که کاربر روی «پرداخت» کلیک می‌کنه، این تابع رو صدا بزنید و کاربر رو به `pay_page_url` (یا `payment_link`، بسته به این‌که کدوم فیلد پر شده) هدایت کنید.

## قدم ۲ — افزودن تابع تایید در successful.php

داخل `successful.php`، جایی که Callback درگاه فعلی رو دریافت و اعتبارسنجی می‌کنه، این کد رو اضافه کنید — حالا **دو نوع کال‌بک** ممکنه برسه: کارتی (`authority`) یا کریپتویی (`sig`):

```php
function cubepay_verify_card_payment($authority)
{
    $token = "YOUR_API_TOKEN";

    $ch = curl_init("https://cubevps.ir/smspay/api/verify-payment.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["authority" => $authority]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$token}",
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/** کال‌بک کریپتویی authority نداره؛ به‌جاش با امضای HMAC تایید می‌شه */
function cubepay_verify_crypto_callback($orderId, $status, $amount, $sig)
{
    $token = "YOUR_API_TOKEN";
    $expectedSig = hash_hmac('sha256', $orderId . '|' . $status . '|' . $amount, $token);

    if (!hash_equals($expectedSig, (string) $sig)) {
        return ['success' => false, 'message' => 'امضای نامعتبر — این کال‌بک جعلی است.'];
    }

    return ['success' => $status === 'paid', 'status' => $status];
}

// در قسمتی که Callback دریافت می‌شه:
$authority = $_REQUEST['authority'] ?? null;
$sig = $_REQUEST['sig'] ?? null;

if ($authority) {
    // مسیر کارت‌به‌کارت
    $result = cubepay_verify_card_payment($authority);

    if (!empty($result['success'])) {
        // همون منطقی که برای تایید موفق پرداخت زرین‌پی استفاده می‌کردید رو اینجا صدا بزنید
        // مثلاً: شارژ کیف پول کاربر یا تحویل سرویس
    }
} elseif ($sig) {
    // مسیر کریپتویی
    $orderId = $_REQUEST['order_id'] ?? '';
    $status = $_REQUEST['status'] ?? '';
    $amount = $_REQUEST['amount'] ?? '';
    $result = cubepay_verify_crypto_callback($orderId, $status, $amount, $sig);

    if (!empty($result['success'])) {
        // همون منطق تحویل سرویس، این‌بار برای پرداخت کریپتویی
    }
}
```

## قدم ۳ — وارد کردن توکن API

توکن API رو از [@cubepy_bot](https://t.me/cubepy_bot) → «🔗 پنل من» بگیرید و به‌جای `YOUR_API_TOKEN` در هر دو فایل بذارید (ترجیحاً از یک فایل تنظیمات مرکزی بخونیدش، نه هاردکد مستقیم).

## قدم ۴ — تست نهایی

یک پرداخت آزمایشی انجام بدید و مطمئن بشید:
1. لینک پرداخت به‌درستی باز می‌شه.
2. بعد از واریز، Callback به `successful.php` می‌رسه.
3. `cubepay_verify_payment` مقدار `success: true` برمی‌گردونه.
4. منطق تحویل سرویس/شارژ به‌درستی اجرا می‌شه.

## اگه مشکلی پیش اومد

- ساختار فایل‌های فورک شما ممکنه با نسخه‌ی اصلی Foxima فرق داشته باشه؛ توابع بالا رو با ساختار منطقی فایل خودتون تطبیق بدید.
- برای جزئیات کامل پارامترها و خطاها → [docs/API-REFERENCE.md](../docs/API-REFERENCE.md)
- برای عیب‌یابی → [docs/FAQ.md](../docs/FAQ.md)
- اگه ترجیح می‌دید به‌جای ویرایش دستی از فایل‌های آماده استفاده کنید (و فایل‌هاتون شخصی‌سازی نشده) → [راهنمای نصب با فایل آماده](./faoxima-ready-files/faoxima-ready-files-guide.md)
