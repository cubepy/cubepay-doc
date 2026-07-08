<?php
/**
 * نمونه‌ی دریافت callback از CubePay بعد از پرداخت موفق، با استفاده از
 * CubePayClient.php — این آدرس رو به‌عنوان callback_url موقع create-payment
 * می‌فرستید.
 */

declare(strict_types=1);
require __DIR__ . '/../CubePayClient.php';

// 🚩 این خط رو با اطلاعات خودتون پر کنید:
$cubepay = new CubePayClient('YOUR_API_TOKEN', 'https://YOUR-CUBEPAY-DOMAIN/smspay');

// نکته‌ی امنیتی مهم: خود این کال‌بک صرفاً یه اطلاع‌رسانیه، نه تاییدیه‌ی
// نهایی — همیشه verify رو صدا بزنید و فقط با success:true واقعاً سرویس رو
// تحویل بدید. handleCallback() این کارو خودکار برای‌تون انجام می‌ده.
$result = $cubepay->handleCallback();

if (!empty($result['success'])) {
    // ✅ پرداخت واقعاً تایید شد — اینجا سرویس رو فعال/کیف‌پول رو شارژ کنید
    // مثال: chargeWallet($result['order_id'], $result['amount']);
    http_response_code(200);
    echo 'ok';
} else {
    // اگه status=verified بود یعنی قبلاً پردازش شده (طبیعیه، دوباره کاری نکنید)
    http_response_code(200);
    echo 'already-handled-or-failed: ' . ($result['message'] ?? 'unknown');
}
