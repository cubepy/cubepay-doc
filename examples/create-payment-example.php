<?php
/**
 * نمونه‌ی ساخت فاکتور پرداخت با استفاده از CubePayClient.php
 * این فایل رو کپی کنید، ۲ خط پرچم‌دار رو با اطلاعات خودتون پر کنید، همین.
 */

declare(strict_types=1);
require __DIR__ . '/CubePayClient.php';

// 🚩 این ۲ خط رو با اطلاعات خودتون پر کنید:
$cubepay = new CubePayClient('YOUR_API_TOKEN', 'https://YOUR-CUBEPAY-DOMAIN/smspay');

$amountToman = 20000; // مبلغی که می‌خواید از مشتری بگیرید
$orderId = 'order-' . time();

$result = $cubepay->createPayment(
    $amountToman * 10,                                  // تبدیل به ریال
    $orderId,
    'https://yourbot.example.com/callback.php',         // فایل کال‌بک شما (نمونه‌ش تو callback.php هست)
    'خرید اشتراک'                                        // توضیح اختیاری
);

if ($result['success']) {
    echo "لینک پرداخت: {$result['payment_link']}\n";
    echo "مبلغ دقیق قابل‌پرداخت: " . number_format($result['pay_amount_toman']) . " تومان\n";
    // 📌 این لینک رو به مشتری بدید (نه فقط مبلغ رند رو نشون بدید)
} else {
    echo "خطا: {$result['message']}\n";
}
