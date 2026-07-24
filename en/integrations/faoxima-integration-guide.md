# 🔧 Manual CubePay Integration into a Foxima Bot

This guide is for merchants who built their bot with **[Foxima](https://github.com/Mmd-Amir/Faoxima)** (or its forks) and have **already customized the following files** — which is why they don't want to fully replace them with the ready-made files.

> 💡 If your files aren't customized, the faster route is [installing with ready-made files](./faoxima-ready-files/faoxima-ready-files-guide.md).

⚠️ **Before you start**, be sure to make a backup of your bot's folder.

## Files You Need to Edit

| File | Path |
|---|---|
| `business_logic_1.php` | `re/rx/function/business_logic_1.php` |
| `successful.php` | `payment/ZarinPay/successful.php` |

These two files are respectively responsible for **creating the payment invoice** and **automatically confirming the transaction**.

## Step 1 — Add the transaction-creation function

Inside `business_logic_1.php`, wherever the current gateway's (e.g. ZarinPal's) payment invoice is created, add/replace this function:

```php
function cubepay_create_payment($amount_toman, $order_id, $callback_url, $description = '')
{
    $token = "YOUR_API_TOKEN"; // Better to read this from panel settings, not hardcode it

    $data = [
        "amount" => $amount_toman * 10, // Toman to Rial
        "order_id" => (string) $order_id,
        "callback_url" => $callback_url,
        "type" => "card",
        "description" => $description,
    ];

    $ch = curl_init("https://cubevps.ir/smspay/api/create-payment.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$token}",
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
```

Then, in the same file, wherever the user clicks "Pay", call this function and redirect the user to `payment_link`.

## Step 2 — Add the verification function in successful.php

Inside `successful.php`, wherever the current gateway's callback is received and validated, add this function:

```php
function cubepay_verify_payment($authority)
{
    $token = "YOUR_API_TOKEN";

    $ch = curl_init("https://cubevps.ir/smspay/api/verify-payment.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["authority" => $authority]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer {$token}",
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Where the callback is received:
$authority = $_REQUEST['authority'] ?? null;

if ($authority) {
    $result = cubepay_verify_payment($authority);

    if (!empty($result['success'])) {
        // Call whatever logic you used for a successful ZarinPal confirmation here
        // e.g. charge the user's wallet, or deliver the service
    }
}
```

## Step 3 — Enter your API token

Get your API token from [@cubepy_bot](https://t.me/cubepy_bot) → "🔗 My Panel" and put it in place of `YOUR_API_TOKEN` in both files (preferably read it from a central config file, not hardcoded directly).

## Step 4 — Final Testing

Do a test payment and make sure:
1. The payment link opens correctly.
2. After the deposit, the callback reaches `successful.php`.
3. `cubepay_verify_payment` returns `success: true`.
4. The service-delivery/top-up logic runs correctly.

## If Something Goes Wrong

- Your fork's file structure might differ from the original Foxima version; adapt the functions above to your own file's logical structure.
- For complete parameter and error details → [docs/API-REFERENCE.md](../docs/API-REFERENCE.md)
- For troubleshooting → [docs/FAQ.md](../docs/FAQ.md)
- If you'd rather use the ready-made files instead of manual editing (and your files aren't customized) → [ready-made files installation guide](./faoxima-ready-files/faoxima-ready-files-guide.md)
