<div dir="rtl">

# مهاجرت از API کارتی قدیمی به روتر یکپارچه

روتر یکپارچه بر اساس روش‌های فعال حساب شما، پرداخت کارتی، کریپتو یا صفحهٔ انتخاب روش را برمی‌گرداند. کلید API حساب فروشندگی خودتان را از ربات CubePay بگیرید.

## دو تفاوت لازم

| مورد | API کارتی | روتر یکپارچه |
|---|---|---|
| نشانی | `/smspay/api/create-payment.php` | `/pay/create-order.php` |
| مبلغ | `amount` به ریال | `price_amount` به تومان |

برای مثال، مبلغ `1000000` ریال در API قدیمی، برابر `100000` تومان در روتر است. مبلغ قبلی را بدون تبدیل کپی نکنید. شمارهٔ سفارش و نشانی کال‌بک فروشگاه را حفظ کنید.

## نمونهٔ درخواست PHP

```php
$payload = [
    'order_id' => $orderId,
    'price_amount' => $amountRial / 10,
    'callback_url' => $callbackUrl,
];
$ch = curl_init('https://cubevps.ir/pay/create-order.php');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiToken, 'Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);
```

کلید API فقط در سرور خودتان نگه داشته شود. پاسخ موفق، `pay_page_url` دارد؛ مشتری را به همان آدرس هدایت کنید. دریافت لینک، اثبات پرداخت نیست.

## بررسی نتیجه

در مسیر کارتی، اگر پاسخ `authority` دارد، همان قرارداد `verify-payment.php` را رعایت کنید. در مسیر انتخاب روش یا کریپتو، وضعیت سفارش را طبق مستندات همان مسیر بررسی کنید. مشاهدهٔ صفحهٔ موفقیت به‌تنهایی جای تأیید سرور به سرور را نمی‌گیرد.

اگر فقط کارت فعال است، پیام بانک باید از گوشی فروشنده به CubePay برسد. برای کریپتو، ولت مربوط را ثبت و روش کریپتو را در تنظیمات حساب فعال کنید.

- [مرجع API کارتی](./API-REFERENCE.md)
- [مرجع کریپتو و روتر یکپارچه](./CRYPTO-API-REFERENCE.md)
- [اتصال آمادهٔ Foxima](../integrations/faoxima-guide.md)

</div>
