<div align="center">

<img src="./assets/cubepay-logo.png" alt="CubePay Logo" width="360"/>

**پرداخت کارت‌به‌کارت با تایید خودکار پیامکی**

بدون نیاز به درگاه بانکی رسمی · بدون کارمزد شتاب 🚀

![Status](https://img.shields.io/badge/status-active-brightgreen)
![License](https://img.shields.io/badge/license-MIT-blue)
![API](https://img.shields.io/badge/API-v1-orange)

</div>

---

CubePay یک API ساده برای ساخت و تایید تراکنش‌های کارت‌به‌کارت است. مشتری شما مبلغ را مستقیم به کارت شما واریز می‌کند و تاییدش کاملاً خودکار (از روی پیامک بانکی) انجام می‌شود — دقیقاً مثل یک درگاه پرداخت، ولی بدون نیاز به نماد و ثبت درگاه رسمی.

## 📑 فهرست مطالب

- [شروع سریع](#-شروع-سریع)
- [ایجاد تراکنش](#-ایجاد-تراکنش)
- [تایید تراکنش](#-تایید-تراکنش)
- [اعتبار و قوانین تراکنش](#-اعتبار-و-قوانین-تراکنش)
- [Callback](#-اطلاعات-ارسالی-به-callback_url)
- [نمونه کدها](#-نمونه-کدها)
- [کدهای خطا](#-کدها-و-پیام‌های-رایج)
- [نکات مهم](#-نکات-مهم)
- [سوالات متداول](#-سوالات-متداول)
- [لینک‌ها](#-لینک‌ها)

---

## 🚀 شروع سریع

برای استفاده از API نیاز به یک **توکن دسترسی (API Token)** دارید. این توکن را از ربات مدیریت فروشندگان (`@cubepy_bot`) بعد از ثبت‌نام و تایید حساب دریافت می‌کنید.

توکن باید در هدر هر درخواست به این شکل ارسال شود:

```
Authorization: Bearer YOUR_API_TOKEN
```

> ⚠️ **نکته‌ی مهم:** برخلاف بعضی درگاه‌ها، آدرس‌های زیر با پسوند `.php` هستند — حتماً دقیقاً همان‌طور که در پنل فروشنده‌تان نوشته شده کپی کنید.

---

## 🛒 ایجاد تراکنش

**Endpoint**

```
POST https://cubevps.ir/smspay/api/create-payment.php
```

### پارامترهای درخواست

| نام                | نوع    | اجباری | توضیحات                                             |
| ------------------ | ------ | ------ | --------------------------------------------------- |
| `amount`           | int    | ✅      | مبلغ تراکنش به **ریال** (حداقل ۱۰۰۰ ریال)            |
| `order_id`         | string | ✅      | شناسه‌ی یکتای سفارش شما                             |
| `callback_url`     | string | ✅      | آدرسی که بعد از پرداخت موفق به آن اطلاع داده می‌شود |
| `type`             | string | ✅      | فعلاً فقط `card`                                    |
| `customer_user_id` | string | ❌      | شناسه‌ی مشتری شما (مثلاً آیدی عددی تلگرام)          |
| `description`      | string | ❌      | توضیح سفارش                                         |

### پاسخ موفق

```json
{
  "success": true,
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "payment_link": "https://cubevps.ir/smspay/pay.php?authority=bdc9e0497c121d6187750d53798dae81",
  "pay_amount": 200720,
  "pay_amount_toman": 20072
}
```

📌 **`pay_amount_toman` را جدی بگیرید:** این مبلغ دقیق قابل‌پرداخت است، نه لزوماً همان عددی که خودتان فرستادید. سیستم برای اینکه بتواند پیامک‌های بانکی را دقیق تشخیص دهد، چند تومان تصادفی به مبلغ اضافه می‌کند (مثلاً ۲۰,۰۰۰ می‌شود ۲۰,۰۷۲). **لینک `payment_link` را مستقیم به مشتری بدهید** — خود صفحه‌ی پرداخت این مبلغ دقیق را به‌وضوح نشان می‌دهد.

### پاسخ خطا

```json
{
  "success": false,
  "message": "مبلغ نامعتبر است (ریال، حداقل ۱۰۰۰)."
}
```

---

## 🔍 تایید تراکنش

**Endpoint**

```
POST https://cubevps.ir/smspay/api/verify-payment.php
```

### پارامترهای درخواست

| نام         | نوع    | اجباری | توضیحات                                        |
| ----------- | ------ | ------ | ---------------------------------------------- |
| `authority` | string | ✅      | کدی که از مرحله‌ی ایجاد تراکنش دریافت کرده‌اید |

### پاسخ (اولین تایید موفق)

```json
{
  "success": true,
  "message": "پرداخت تایید شد.",
  "order_id": "ORD123",
  "amount": 200000,
  "status": "verified"
}
```

### پاسخ‌های دیگر (بسته به وضعیت فعلی تراکنش)

| وضعیت                | HTTP Status | معنی                                                    |
| -------------------- | ----------- | ------------------------------------------------------- |
| `verified` (تکراری)  | `409`       | این تراکنش قبلاً یک‌بار تایید شده — دوباره سرویس نسازید |
| `pending`            | `402`       | هنوز پرداختی ثبت نشده                                   |
| `expired` / `failed` | `410`       | مهلت تراکنش تمام شده یا ناموفق بوده                     |
| authority نامعتبر    | `404`       | چنین تراکنشی یافت نشد                                   |

⚠️ فقط **اولین** فراخوانی موفق `verify-payment` مقدار `success: true` برمی‌گرداند. این عمداً این‌طور است تا اگر به‌هر دلیلی (رفرش کاربر، تلاش دوباره‌ی خودتان و…) دوبار صدا زده شود، سرویس/شارژ را دوبار به کاربر ندهید.

---

## ⏳ اعتبار و قوانین تراکنش

- هر فاکتور **۳۰ دقیقه** اعتبار دارد؛ بعدش منقضی می‌شود و پرداخت دیگر امکان‌پذیر نیست.
- اگر با یک `order_id` تکراری دوباره `create-payment` بزنید و فاکتور قبلی هنوز **در انتظار پرداخت** باشد، همان `authority`/`payment_link` قبلی برگردانده می‌شود (فاکتور جدید ساخته نمی‌شود).
- مبلغ‌ها همه‌جا (درخواست و پاسخ) به **ریال** هستند، مگر جایی که صراحتاً «تومان» نوشته شده باشد (مثل `pay_amount_toman`).

---

## 🔄 اطلاعات ارسالی به `callback_url`

به‌محض تشخیص واریز (از روی پیامک بانکی)، این درخواست به آدرس `callback_url` شما ارسال می‌شود:

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

**و همزمان به‌صورت querystring هم به همان آدرس اضافه می‌شود** (برای سازگاری با بک‌اندهایی که فقط GET را می‌خوانند):

```
?authority=...&order_id=...&status=paid
```

📌 بعد از دریافت این کال‌بک، حتماً `verify-payment` را صدا بزنید تا مطمئن شوید (این کال‌بک صرفاً یک اطلاع‌رسانی است، نه تاییدیه‌ی نهایی).

---

## 💻 نمونه کدها

<details>
<summary><strong>🐘 PHP</strong></summary>

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

</details>

<details>
<summary><strong>🐍 Python</strong></summary>

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

</details>

<details>
<summary><strong>🟢 Node.js</strong></summary>

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

</details>

<details>
<summary><strong>💻 cURL</strong></summary>

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

</details>

---

## ⚠️ کدها و پیام‌های رایج

| پیام                                  | HTTP Status | دلیل                                                            |
| -------------------------------------- | ----------- | ---------------------------------------------------------------- |
| توکن ارسال نشده / نامعتبر است          | `401`       | هدر Authorization خالی است یا توکن اشتباه است                    |
| حساب فروشندگی شما هنوز تایید نشده...   | `403`       | هنوز ادمین درخواست شما را تایید نکرده                             |
| مبلغ نامعتبر است                       | `422`       | کمتر از ۱۰۰۰ ریال یا عدد نیست                                    |
| order_id / callback_url نامعتبر است    | `422`       | فرمت یا طول اشتباه است (callback باید https معتبر و غیرداخلی باشد) |
| موجودی کیف‌پول کارمزد کافی نیست        | `402`       | باید کیف‌پول کارمزدتان را شارژ کنید                               |
| ظرفیت فاکتور همزمان پر است             | `503`       | خیلی به‌ندرت پیش می‌آید؛ چند لحظه بعد دوباره امتحان کنید          |

---

## 📌 نکات مهم

- ✔ مبلغ‌ها همه‌جا به **ریال** ارسال/دریافت می‌شوند (تومان × ۱۰)
- ✔ فعلاً فقط `type: "card"` پشتیبانی می‌شود
- ✔ همیشه از `pay_amount_toman` برای نمایش مبلغ نهایی به مشتری استفاده کنید، نه مبلغ خام درخواستی
- ✔ اگر بین شما و پلتفرم توافق کارمزد وجود داشته باشد، محاسبه‌اش کاملاً خودکار است — نیازی نیست خودتان چیزی به مبلغ اضافه کنید
- ✔ `verify-payment` را فقط یک‌بار در ازای هر تراکنش، بعد از دریافت کال‌بک صدا بزنید

---

## ❓ سوالات متداول

**آیا نیاز به درگاه بانکی رسمی دارم؟**
❌ نه، فقط یک کارت بانکی به نام خودتان کافی است.

**فاکتورها چند دقیقه اعتبار دارند؟**
⏳ ۳۰ دقیقه.

**اگر پیامک بانک دیر برسد چه؟**
تا وقتی فاکتور منقضی نشده، تشخیص انجام می‌شود؛ تاخیر پیامک بانک معمولاً چند ثانیه است.

**چطور توکن یا کارتم را عوض کنم؟**
از منوی ربات مدیریت فروشندگان (`@cubepy_bot`).

---

## 🔗 لینک‌ها

🤖 ربات مدیریت فروشندگان: [@cubepy_bot](https://t.me/cubepy_bot)

---

<div align="center">

ساخته‌شده با ❤️ برای توسعه‌دهندگان ایرانی

</div>
