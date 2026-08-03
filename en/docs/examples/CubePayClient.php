<?php
/**
 * ============================================================
 *  CubePay PHP SDK — a single file, no Composer or extra libraries needed
 * ============================================================
 *
 * Usage (just fill in the 2 lines below with your own details):
 *
 *   require 'CubePayClient.php';
 *   $cubepay = new CubePayClient('YOUR_TOKEN', 'https://YOUR-DOMAIN/smspay');
 *
 *   // Create an invoice:
 *   $result = $cubepay->createPayment(200000, 'ORDER123', 'https://yourbot.example.com/callback.php');
 *   if ($result['success']) {
 *       echo $result['payment_link'];       // give this link to the customer
 *       echo $result['pay_amount_toman'];   // the exact amount (with the offset) you must display
 *   }
 *
 *   // Verify after the callback:
 *   $verify = $cubepay->verifyPayment($result['authority']);
 *   if ($verify['success']) {
 *       // the payment really is confirmed — deliver the service/credit right here
 *   }
 *
 * Every method returns a PHP array and never throws an exception —
 * always check the 'success' key (true/false).
 */

declare(strict_types=1);

final class CubePayClient
{
    private string $token;
    private string $baseUrl;
    private int $timeoutSeconds;

    /**
     * @param string $token   the API token you got from the merchant management bot
     * @param string $baseUrl base URL, without a trailing slash (e.g. https://cubevps.ir/smspay)
     */
    public function __construct(string $token, string $baseUrl, int $timeoutSeconds = 15)
    {
        $this->token = $token;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Create a new payment invoice.
     *
     * @param int         $amountRial   amount in RIAL (minimum 1000) — remember: toman x 10
     * @param string      $orderId      your unique order identifier
     * @param string      $callbackUrl  the URL notified after a successful payment
     * @param string|null $description  optional description
     * @param string|null $customerId   optional identifier for your customer (e.g. a Telegram ID)
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
     * Final confirmation of a transaction — only the first successful call returns success:true.
     *
     * @return array{success: bool, message?: string, order_id?: string, amount?: int, status?: string}
     */
    public function verifyPayment(string $authority): array
    {
        return $this->request('POST', '/api/verify-payment.php', ['authority' => $authority]);
    }

    /**
     * Helper: call this from your own callback file — it finds the authority in
     * GET/POST/JSON by itself, calls verify, and returns the result.
     * You no longer need to read $_GET/$_POST/php://input manually.
     */
    public function handleCallback(): array
    {
        $raw = file_get_contents('php://input');
        $body = json_decode((string) $raw, true) ?: [];
        $authority = $body['authority'] ?? ($_POST['authority'] ?? ($_GET['authority'] ?? null));
        $orderId = $body['order_id'] ?? ($_POST['order_id'] ?? ($_GET['order_id'] ?? null));

        if (!$authority) {
            return ['success' => false, 'message' => 'No authority found in the callback.'];
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
            return ['success' => false, 'message' => 'Connection error: ' . $err];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'message' => 'Received an invalid response from the server.'];
        }
        return $decoded;
    }
}
