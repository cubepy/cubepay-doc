<?php
/**
 * نمونه‌ی دریافت callback از CubePay بعد از پرداخت موفق.
 * این آدرس رو به‌عنوان callback_url موقع create-payment می‌فرستید.
 */

declare(strict_types=1);

$apiToken = 'YOUR_API_TOKEN';

// کال‌بک هم POST (JSON) و هم querystring داره؛ هر کدوم بود رو بخونید
$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?: [];
$authority = $body['authority'] ?? ($_GET['authority'] ?? null);
$orderId   = $body['order_id']  ?? ($_GET['order_id']  ?? null);

if (!$authority || !$orderId) {
    http_response_code(400);
    exit('missing params');
}

// نکته‌ی امنیتی مهم: خود این کال‌بک صرفاً یه اطلاع‌رسانیه، نه تاییدیه‌ی
// نهایی — همیشه verify-payment رو صدا بزنید و فقط با success:true واقعاً
// سرویس/شارژ رو تحویل بدید.
$ch = curl_init('https://cubevps.ir/smspay/api/verify-payment.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['authority' => $authority], JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiToken,
]);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode((string) $response, true);

if (!empty($result['success'])) {
    // ✅ پرداخت واقعاً تایید شد — اینجا سرویس رو فعال/کیف‌پول رو شارژ کنید
    // مثال: chargeWallet($orderId, $result['amount']);
    http_response_code(200);
    echo 'ok';
} else {
    // اگه status=verified بود یعنی قبلاً پردازش شده (طبیعیه، دوباره کاری نکنید)
    http_response_code(200);
    echo 'already-handled-or-failed: ' . ($result['message'] ?? 'unknown');
}
