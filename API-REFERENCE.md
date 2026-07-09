<div align="center"><img src="cubepay-logo.png" alt="CubePay" width="220"></div>

# 📚 مرجع کامل API

این فایل جزئیات فنی کامل API رو داره (برای کسانی که مستقیم با API کار می‌کنن، نه از طریق SDK). اگه فقط می‌خواید سریع وصل بشید، به‌جاش [`generic-integration-guide.md`](generic-integration-guide.md) رو ببینید.

---

## 🚀 شروع سریع (تماس مستقیم با API)

برای استفاده از API نیاز به یک **توکن دسترسی (API Token)** دارید. این توکن رو از ربات مدیریت فروشندگان (`@cubepy_bot`) بعد از ثبت‌نام و تایید حساب دریافت می‌کنید.

توکن باید در هدر هر درخواست به این شکل ارسال بشه:

```
Authorization: Bearer YOUR_API_TOKEN
```

⚠️ نکته‌ی مهم: برخلاف بعضی درگاه‌ها، آدرس‌های زیر با پسوند `.php` هستن — حتماً دقیقاً همون‌طور که تو پنل فروشنده‌تون نوشته کپی کنید.

---

## 🛒 ایجاد تراکنش (Create Payment)

**Endpoint:**
```
POST https://cubevps.ir/smspay/api/create-payment.php
```

### 📋 پارامترها

| نام | نوع | اجباری | توضیحات |
|---|---|---|---|
| `amount` | int | ✅ | مبلغ تراکنش به **ریال** (حداقل ۱۰۰۰ ریال) |
| `order_id` | string | ✅ | شناسه‌ی یکتای سفارش شما |
| `callback_url` | string | ✅ | آدرسی که بعد از پرداخت موفق به آن اطلاع داده می‌شود |
| `type` | string | ✅ | فعلاً فقط `card` |
| `customer_user_id` | string | ❌ | شناسه‌ی مشتری شما (مثلاً آیدی عددی تلگرام) |
| `description` | string | ❌ | توضیح سفارش |

### ✅ نمونه پاسخ موفق

```json
{
  "success": true,
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "payment_link": "https://cubevps.ir/smspay/pay.php?authority=bdc9e0497c121d6187750d53798dae81",
  "pay_amount": 200720,
  "pay_amount_toman": 20072
}
```

📌 **`pay_amount_toman` را جدی بگیرید:** این مبلغ دقیق قابل‌پرداخت است، نه لزوماً همون عددی که خودتون فرستادید. سیستم برای اینکه بتونه پیامک‌های بانکی رو دقیق تشخیص بده، چند تومان تصادفی به مبلغ اضافه می‌کنه (مثلاً ۲۰,۰۰۰ می‌شه ۲۰,۰۷۲). **لینک `payment_link` رو مستقیم به مشتری بدید** — خود صفحه‌ی پرداخت این مبلغ دقیق رو به‌وضوح نشون می‌ده، نیازی نیست خودتون چیزی محاسبه کنید.

### ❌ نمونه پاسخ خطا

```json
{
  "success": false,
  "message": "مبلغ نامعتبر است (ریال، حداقل ۱۰۰۰)."
}
```

---

## 🔍 تایید تراکنش (Verify Payment)

**Endpoint:**
```
POST https://cubevps.ir/smspay/api/verify-payment.php
```

### 📋 پارامترها

| نام | نوع | اجباری | توضیحات |
|---|---|---|---|
| `authority` | string | ✅ | کدی که از مرحله‌ی ایجاد تراکنش دریافت کرده‌اید |

### ✅ نمونه پاسخ (اولین تایید موفق)

```json
{
  "success": true,
  "message": "پرداخت تایید شد.",
  "order_id": "ORD123",
  "amount": 200000,
  "status": "verified"
}
```

### 📌 پاسخ‌های دیگر (بسته به وضعیت فعلی تراکنش)

| وضعیت | HTTP Status | معنی |
|---|---|---|
| `verified` (تکراری) | `409` | این تراکنش قبلاً یک‌بار تایید شده — دوباره سرویس نسازید |
| `pending` | `402` | هنوز پرداختی ثبت نشده |
| `expired` / `failed` | `410` | مهلت تراکنش تمام شده یا ناموفق بوده |
| authority نامعتبر | `404` | چنین تراکنشی یافت نشد |

⚠️ فقط **اولین** فراخوانی موفق `verify-payment` مقدار `success: true` برمی‌گردونه. این عمداً این‌طوریه تا اگه به‌هر دلیلی (رفرش کاربر، تلاش دوباره‌ی خودتون و…) دوبار صدا زده بشه، سرویس/شارژ رو دوبار به کاربر ندید.

---

## ⏳ اعتبار و قوانین تراکنش

- هر فاکتور **۳۰ دقیقه** اعتبار داره؛ بعدش منقضی می‌شه و پرداخت دیگه امکان‌پذیر نیست.
- اگه با یک `order_id` تکراری دوباره `create-payment` بزنید، و فاکتور قبلی هنوز **در انتظار پرداخت** باشه، همون `authority`/`payment_link` قبلی برگردونده می‌شه (فاکتور جدید ساخته نمی‌شه).
- مبلغ‌ها همه‌جا (درخواست و پاسخ) به **ریال** هستن، مگر جایی که صراحتاً «تومان» نوشته شده باشه (مثل `pay_amount_toman`).

---

## 🔄 اطلاعات ارسالی به `callback_url`

به‌محض تشخیص واریز (از روی پیامک بانکی)، این درخواست به آدرس `callback_url` شما ارسال می‌شه:

**به‌صورت POST (بدنه‌ی JSON):**
```json
{
  "success": true,
  "status": "paid",
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "order_id": "ORD123",
  "amount": 200000
}
```

**و همزمان به‌صورت querystring هم به همون آدرس اضافه می‌شه** (برای سازگاری با بک‌اندهایی که فقط GET رو می‌خونن):
```
?authority=...&order_id=...&status=paid
```

📌 بعد از دریافت این کال‌بک، حتماً `verify-payment` رو صدا بزنید تا مطمئن بشید (این کال‌بک صرفاً یه اطلاع‌رسانیه، نه تاییدیه‌ی نهایی).

---

## 💻 نمونه کدها

### 🐘 PHP

```php
<?php
$accessToken = "YOUR_API_TOKEN";

$data = [
    "amount" => 200000,
    "order_id" => "ORD123",
    "callback_url" => "https://yourbot.example.com/callback.php",
    "type" => "card",
    "description" => "شارژ کیف پول",
    "customer_user_id" => "123456789",
];

$ch = curl_init("https://cubevps.ir/smspay/api/create-payment.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer {$accessToken}",
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if (!empty($result['success'])) {
    echo "لینک پرداخت: " . $result['payment_link'];
} else {
    echo "خطا: " . $result['message'];
}
```

### 🐍 Python

```python
import requests

url = "https://cubevps.ir/smspay/api/create-payment.php"
headers = {
    "Content-Type": "application/json",
    "Authorization": "Bearer YOUR_API_TOKEN",
}
data = {
    "amount": 200000,
    "order_id": "ORD123",
    "callback_url": "https://yourbot.example.com/callback",
    "type": "card",
    "description": "شارژ کیف پول",
}

response = requests.post(url, json=data, headers=headers)
print(response.json())
```

### 🟢 Node.js

```javascript
const axios = require("axios");

const data = {
  amount: 200000,
  order_id: "ORD123",
  callback_url: "https://yourbot.example.com/callback",
  type: "card",
};

axios.post("https://cubevps.ir/smspay/api/create-payment.php", data, {
  headers: {
    "Content-Type": "application/json",
    "Authorization": "Bearer YOUR_API_TOKEN",
  },
})
  .then((res) => console.log(res.data))
  .catch((err) => console.error(err.response?.data));
```

### 💻 cURL

```bash
curl -X POST https://cubevps.ir/smspay/api/create-payment.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "amount": 200000,
    "order_id": "ORD123",
    "callback_url": "https://yourbot.example.com/callback",
    "type": "card"
  }'
```

---

## ⚠️ کدها و پیام‌های رایج

| پیام | HTTP Status | دلیل |
|---|---|---|
| توکن ارسال نشده / نامعتبر است | `401` | هدر Authorization خالیه یا توکن اشتباهه |
| حساب فروشندگی شما هنوز تایید نشده... | `403` | هنوز ادمین درخواست شما رو تایید نکرده |
| مبلغ نامعتبر است | `422` | کمتر از ۱۰۰۰ ریال یا عدد نیست |
| order_id / callback_url نامعتبر است | `422` | فرمت یا طول اشتباهه (callback باید https معتبر و غیرداخلی باشه) |
| موجودی کیف‌پول کارمزد کافی نیست | `402` | باید کیف‌پول کارمزدتون رو شارژ کنید |
| ظرفیت فاکتور همزمان پر است | `503` | خیلی به‌ندرت پیش میاد؛ چند لحظه بعد دوباره امتحان کنید |

---


---

## 🔗 لینک‌ها

🤖 ربات مدیریت فروشندگان: [@cubepy_bot](https://t.me/cubepy_bot)
