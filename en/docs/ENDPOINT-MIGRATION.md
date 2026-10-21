# Migrating from the legacy card API to the unified router

The router returns a card payment, a crypto payment, or a method-selection page according to your merchant settings. Obtain your merchant API token from the CubePay bot.

## Required changes

| Item | Card API | Unified router |
|---|---|---|
| Endpoint | `/smspay/api/create-payment.php` | `/pay/create-order.php` |
| Amount | `amount` in rial | `price_amount` in toman |

For example, `1000000` rial becomes `100000` toman. Do not copy the old amount without conversion. Preserve your order ID and callback URL.

## PHP request

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

Keep the API token on your own server. A successful response supplies `pay_page_url`; direct the customer there. Creating a link does not prove payment.

## Verify the result

For a card response containing `authority`, follow the existing `verify-payment.php` contract. For method selection or crypto, follow the status checks documented for that flow. A success page does not replace server-side verification.

Card confirmation requires forwarding the merchant's bank message to CubePay. For crypto, register the appropriate wallet and enable crypto in the merchant settings.

- [Card API reference](./API-REFERENCE.md)
- [Crypto and unified API](./CRYPTO-API-REFERENCE.md)
- [Foxima integration](../integrations/faoxima-guide.md)
