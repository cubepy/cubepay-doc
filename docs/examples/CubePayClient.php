<?php
/**
 * ============================================================
 *  CubePay PHP SDK — یک فایل، بدون نیاز به Composer یا کتابخونه‌ی اضافه
 * ============================================================
 *
 * نحوه‌ی استفاده (فقط ۲ خط پایین رو با اطلاعات خودتون پر کنید):
 *
 *   require 'CubePayClient.php';
 *   $cubepay = new CubePayClient('TOKEN_شما', 'https://DOMAIN-شما/smspay');
 *
 *   // ساخت فاکتور:
 *   $result = $cubepay->createPayment(200000, 'ORDER123', 'https://yourbot.example.com/callback.php');
 *   if ($result['success']) {
 *       echo $result['payment_link'];       // لینک رو به مشتری بدید
 *       echo $result['pay_amount_toman'];   // مبلغ دقیق (با آفست) که باید نمایش بدید
 *   }
 *
 *   // تایید بعد از کال‌بک:
 *   $verify = $cubepay->verifyPayment($result['authority']);
 *   if ($verify['success']) {
 *       // پرداخت واقعاً تایید شد — همینجا سرویس/شارژ رو تحویل بدید
 *   }
 *
 * همه‌ی متدها یه آرایه‌ی PHP برمی‌گردونن، هیچ‌وقت Exception پرتاب نمی‌کنن —
 * همیشه کلید 'success' (true/false) رو چک کنید.
 */

declare(strict_types=1);

final class CubePayClient
{
    private string $token;
    private string $baseUrl;
    private int $timeoutSeconds;

    /**
     * @param string $token   توکن API که از ربات مدیریت فروشندگان گرفتید
     * @param string $baseUrl آدرس پایه، بدون اسلش آخر (مثلاً https://cubevps.ir/smspay)
     */
    public function __construct(string $token, string $baseUrl, int $timeoutSeconds = 15)
    {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * ساخت یک فاکتور پرداخت جدید.
     *
     * @param int         $amountRial   مبلغ به ریال (حداقل ۱۰۰۰) — یادتون نره: تومان × ۱۰
     * @param string      $orderId      شناسه‌ی یکتای سفارش شما
     * @param string      $callbackUrl  آدرسی که بعد از پرداخت موفق بهش خبر داده می‌شه
     * @param string|null $description  توضیح اختیاری
     * @param string|null $customerId   شناسه‌ی اختیاری مشتری شما (مثلاً آیدی تلگرام)
     *
     * @return array{
     *   success: bool, message?: string,
     *   authority?: string, payment_link?: string,
     *   pay_amount?: int, pay_amount_toman?: int
     * }
     */
    public function createPayment(
        int $amountRial,
        string $orderId,
        string $callbackUrl,
        ?string $description = null,
        ?string $customerId = null
    ): array {
        $payload = [
            'amount' => $amountRial,
            'order_id' => $orderId,
            'callback_url' => $callbackUrl,
            'type' => 'card',
        ];
        if ($description !== null) {
            $payload['description'] = $description;
        }
        if ($customerId !== null) {
            $payload['customer_user_id'] = $customerId;
        }

        return $this->request('POST', '/api/create-payment.php', $payload);
    }

    /**
     * تایید نهایی یک تراکنش — فقط اولین فراخوانی موفق، success:true برمی‌گردونه.
     *
     * @return array{success: bool, message?: string, order_id?: string, amount?: int, status?: string}
     */
    public function verifyPayment(string $authority): array
    {
        return $this->request('POST', '/api/verify-payment.php', ['authority' => $authority]);
    }

    /**
     * کمک‌کننده: از داخل فایل کال‌بک شما صدا زده می‌شه — خودش authority رو از
     * GET/POST/JSON پیدا می‌کنه، verify رو صدا می‌زنه، و نتیجه رو برمی‌گردونه.
     * دیگه لازم نیست خودتون $_GET/$_POST/php://input رو دستی بخونید.
     */
    public function handleCallback(): array
    {
        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true) ?: [];
        $authority = $body['authority'] ?? ($_POST['authority'] ?? ($_GET['authority'] ?? null));
        $orderId = $body['order_id'] ?? ($_POST['order_id'] ?? ($_GET['order_id'] ?? null));

        if (!$authority) {
            return ['success' => false, 'message' => 'authority در کال‌بک یافت نشد.'];
        }

        $result = $this->verifyPayment((string) $authority);
        $result['order_id'] = $result['order_id'] ?? $orderId;
        return $result;
    }

    private function request(string $method, string $path, array $payload): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err !== '') {
            return ['success' => false, 'message' => 'خطا در اتصال: ' . $err];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'پاسخ نامعتبر از سرور دریافت شد.'];
        }
        return $decoded;
    }
}
