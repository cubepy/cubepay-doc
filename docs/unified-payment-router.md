# 🔀 پرداخت یکپارچه: کارت به کارت + کریپتو (Unified Router)

از این به بعد فروشنده‌ها به‌جای صدا زدن مستقیم `create-payment.php` (کارت) یا
`create-crypto-payment.php` (کریپتو)، فقط یه endpoint واحد رو صدا می‌زنن.
این endpoint بسته به تنظیمات «💳 روش‌های پرداخت» شما در پنل، خودش تصمیم
می‌گیره کدوم روش(ها) در دسترسه:

- ✅ فقط کارت فعاله → مستقیم فاکتور کارتی می‌سازه
- ✅ فقط کریپتو فعاله → مستقیم فاکتور کریپتویی می‌سازه
- ✅ هر دو فعال باشن → یه صفحه‌ی «کارت یا کریپتو؟» برمی‌گردونه؛ مشتری خودش
  انتخاب می‌کنه

> 🧪 برای تست بدون کدنویسی، از منوی ربات مدیریت فروشندگان «🧾 ساخت فاکتور
> دستی» استفاده کنید و روش «🔀 هر دو» رو انتخاب کنید.
>
> ⚙️ فعال/غیرفعال‌کردن هر روش: «⚙️ تنظیمات بیشتر → 💳 روش‌های پرداخت»
> 💰 موجودی و برداشتِ کریپتو: «⚙️ تنظیمات بیشتر → 💰 ولت پرداخت کریپتو»

---

## 🛒 ساخت سفارش (Create Order)

**Endpoint:**

```
POST https://cubevps.ir/pay/create-order.php
```

### 📋 پارامترها

| نام            | نوع    | اجباری | توضیحات                                                                 |
| --------------- | ------ | ------ | ------------------------------------------------------------------------ |
| `order_id`      | string | ✅      | شناسه‌ی یکتای سفارش شما                                                  |
| `price_amount`  | number | ✅      | مبلغ به **تومان** (نه ریال — برخلاف `create-payment.php` قدیمی)          |
| `callback_url`  | string | ❌      | فقط برای مسیر کارت لازمه؛ برای مسیر کریپتو/انتخاب هم توصیه می‌شه بدید    |

### ✅ نمونه پاسخ‌ها

**اگه فقط یه روش فعال باشه:**

```json
{
  "success": true,
  "method": "card",
  "pay_page_url": "https://cubevps.ir/smspay/pay.php?authority=..."
}
```

```json
{
  "success": true,
  "method": "crypto",
  "pay_page_url": "https://cubevps.ir/crypto/pay.php?token=..."
}
```

**اگه هر دو روش فعال باشن:**

```json
{
  "success": true,
  "method": "choice",
  "pay_page_url": "https://cubevps.ir/pay/choose.php?token=..."
}
```

📌 در هر سه حالت، کافیه مشتری رو مستقیم به `pay_page_url` هدایت کنید —
نیازی نیست خودتون بدونید پشت‌صحنه کدوم روش انتخاب شده.

اگه با یه `order_id` تکراری دوباره درخواست بزنید و فاکتور قبلی هنوز معتبر
باشه، همون `pay_page_url` قبلی برگردونده می‌شه (`"duplicate": true`).

### ❌ نمونه پاسخ خطا

```json
{
  "success": false,
  "message": "order_id و price_amount (به تومان) الزامی هستند."
}
```

---

## 🔍 استعلام وضعیت نهایی (Check Order Status)

**Endpoint:**

```
GET https://cubevps.ir/pay/check-order-status.php?order_id=YOUR_ORDER_ID
```

با هدر `Authorization: Bearer YOUR_API_TOKEN`.

این endpoint برای مسیر **کریپتو** و مسیر **انتخاب (هر دو فعال)** کار
می‌کنه — چون این دو مسیر بر پایه‌ی `order_id` قابل پیگیریَن.

### ✅ نمونه پاسخ‌ها

```json
{ "success": true, "status": "choosing_method" }
```

```json
{ "success": true, "method": "card", "status": "verified" }
```

```json
{ "success": true, "method": "crypto", "status": "waiting" }
```

مقادیر ممکن برای `status`:

| مسیر    | مقادیر ممکن                                                                 |
| ------- | ----------------------------------------------------------------------------- |
| کارت    | `verified`, `pending`, `expired`, `failed`                                    |
| کریپتو  | `choosing_currency`, `waiting`, `confirming`, `sending`, `finished`, `failed`, `expired` |
| انتخاب  | `choosing_method` (کاربر هنوز روش رو انتخاب نکرده), `expired`                  |

فقط `verified` (کارت) و `finished` (کریپتو) به معنای «پرداخت قطعی و
نهایی»ه — بقیه رو در انتظار یا ناموفق در نظر بگیرید.

⚠️ **نکته‌ی مهم درباره‌ی مسیر فقط-کارتیِ مستقیم:** اگه فقط کارت فعال باشه
(بدون کریپتو)، `create-order.php` مستقیماً یه فاکتور کارتی می‌سازه که فقط
با `authority` قابل پیگیریه، نه `order_id`. تو این حالت، این endpoint
جواب نمی‌ده و باید طبق مستندات قدیمی از
[`verify-payment.php`](./README.md#-تایید-تراکنش-verify-payment) با
`authority` (که از کال‌بک یا از پاسخ داخلیِ سیستم کارتی می‌گیرید) استفاده
کنید.

**پیشنهاد برای پیاده‌سازیِ ساده:** اول `check-order-status.php` رو با
`order_id` امتحان کنید؛ اگه `success: false` برگردوند، به‌عنوان fallback
سراغ `verify-payment.php` با `authority` برید.

---

## 🔄 کال‌بک برای مسیر کریپتو

وقتی یه پرداخت کریپتویی به نتیجه‌ی نهایی برسه (`finished`/`failed`/
`expired`) و شما `callback_url` داده باشید، هم یه `POST` با بدنه‌ی JSON و
هم همون اطلاعات به‌صورت querystring به همون آدرس فرستاده می‌شه:

```json
{
  "success": true,
  "status": "paid",
  "order_id": "ORD123",
  "payment_id": "np_1234567",
  "pay_currency": "usdttrc20",
  "amount": "12.5",
  "sig": "hmac-sha256-hex..."
}
```

📌 `status` اینجا `paid` (نه `finished`) برگردونده می‌شه تا با فرمت
کال‌بک کارتی یکی باشه.

### ✅ اعتبارسنجی امضا (`sig`)

برای اطمینان از اینکه این درخواست واقعاً از طرف کیوب‌پیه، امضا رو با
توکن API خودتون بازسازی و مقایسه کنید:

```php
$expected = hash_hmac('sha256', $order_id . '|' . $status . '|' . $amount, $your_api_token);
if (!hash_equals($expected, $sig)) {
    // درخواست جعلیه، نادیده بگیرید
}
```

📌 مثل مسیر کارتی، کال‌بک صرفاً یه اطلاع‌رسانیه؛ برای اطمینان کامل بازم
پیشنهاد می‌شه بعدش `check-order-status.php` رو صدا بزنید (idempotent و
امن‌تره، چون بدون نیاز به اعتبارسنجی امضا مستقیم با توکن خودتون احراز
هویت می‌شه).

---

## 💻 نمونه کد PHP (کامل)

```php
<?php
$accessToken = "YOUR_API_TOKEN";

// ۱. ساخت سفارش
$data = [
    "order_id"     => "ORD123",
    "price_amount" => 25000, // تومان
    "callback_url" => "https://yourbot.example.com/payment.php?check_cubepay=ORD123",
];

$ch = curl_init("https://cubevps.ir/pay/create-order.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer {$accessToken}",
]);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!empty($result['success'])) {
    // مشتری رو به اینجا هدایت کنید — فارغ از اینکه کارتیه، کریپتویی، یا صفحه‌ی انتخاب
    header("Location: " . $result['pay_page_url']);
    exit;
}

// ۲. بعداً، برای استعلام وضعیت:
$ch = curl_init("https://cubevps.ir/pay/check-order-status.php?order_id=" . urlencode("ORD123"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$accessToken}"]);
$status = json_decode(curl_exec($ch), true);
curl_close($ch);
```

---

## ❓ سوالات متداول (تکمیلی)

**آیا هنوز می‌تونم مستقیم `create-payment.php` یا `create-crypto-payment.php`
رو صدا بزنم؟** فنی هنوز کار می‌کنن، ولی دیگه توصیه نمی‌شه — چون اگه بعداً
کریپتو رو هم فعال کنید، کدتون باید دستی بین دو حالت سوییچ کنه. با
`create-order.php` این کار خودکاره.

**اگه بعد از فعال کردن کریپتو، فاکتورهای قدیمیِ کارتی که با
`create-payment.php` ساخته شدن هنوز کار می‌کنن؟** بله، مسیر verify قدیمی
(`authority`-based) دست‌نخورده می‌مونه.
