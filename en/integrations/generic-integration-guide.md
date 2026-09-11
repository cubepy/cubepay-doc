<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 🔌 Guide: Connecting CubePay to Your Own Bot/Site (No Foxima Required)

This guide is for anyone with their own bot or site (anything, not just Foxima) who wants to hook up the CubePay gateway. All you need is **2 pieces of code**.

⏱ Estimated time: less than 1 hour

---

## Before you start, grab these 2 things from the management bot (`@cubepy_bot`):

1. 🔑 **API Token** — from "🔗 My Panel"
2. 📁 The **[`CubePayClient.php`](../../docs/examples/CubePayClient.php)** file — from this same repository

> 👑 **Have a CubePay VIP subscription?** Use your `vip_…` token instead of the normal one. The customer is then shown the CubePay treasury card and your settlement is paid out in crypto. Details, including how to migrate an existing integration without code changes: [`docs/CUBEPAY-VIP-API-REFERENCE.md`](../docs/CUBEPAY-VIP-API-REFERENCE.md)

---

## Step 1 — Create an invoice (when a customer wants to pay)

```php
require 'CubePayClient.php';
$cubepay = new CubePayClient('YOUR_TOKEN', 'https://cubevps.ir/smspay');

$result = $cubepay->createPayment(
    200000,                                  // Amount in Rial (Toman × 10)
    'order-' . time(),                       // Your unique order identifier
    'https://yoursite.com/callback.php'      // The Step 2 file's address
);

if ($result['success']) {
    // Give this link to the customer (don't just display the amount, since there's an offset)
    echo $result['payment_link'];
    echo $result['pay_amount_toman']; // The exact payable amount
} else {
    echo $result['message'];
}
```

---

## 🤖 Showing the payment inside your bot (no web page)

If you sell through a bot, you do not have to send the customer to a web page. The create-invoice response also returns **the card itself and the exact amount**, so you can print it right there in the chat:

```php
$res = json_decode(curl_exec($ch), true);

if (!empty($res['success']) && !empty($res['card'])) {
    $card = $res['card'];
    $text = "💳 To complete your purchase, transfer exactly this amount:\n\n"
        . "🔢 Card number:\n<code>{$card['number']}</code>\n"
        . "👤 Holder: {$card['holder']}\n\n"
        . "💰 Exact amount: <b>" . number_format($res['pay_amount_toman']) . "</b> Toman\n"
        . "⏳ Valid for: {$res['expires_in_minutes']} minutes\n\n"
        . "⚠️ The amount must match to the last digit — auto-confirmation only works with this exact number.";
}
```

Nothing else changes: as soon as the deposit lands, your usual `callback_url` is called and your bot confirms to the customer — who never had to leave Telegram.

You can offer both: a "Pay on the web page" button using `pay_page_url`, and a "Show card number" button that prints the text above.

⚠️ **Three rules:**

1. **The amount must be exactly `pay_amount_toman`** — the few extra Toman are deliberate and are what makes bank-SMS matching reliable.
2. **Never cache and reuse the card.** With card rotation on, each invoice may get a different card; always show the one returned for that invoice.
3. **Do not show a card before creating the invoice** — no card is assigned to a payment until the invoice exists.

---

## Step 2 — Receive confirmation (your own `callback.php` file)

```php
require 'CubePayClient.php';
$cubepay = new CubePayClient('YOUR_TOKEN', 'https://cubevps.ir/smspay');

// This function finds the authority from GET/POST/JSON itself and calls verify
$result = $cubepay->handleCallback();

if (!empty($result['success'])) {
    // ✅ Payment truly verified — deliver the service/credit here
    $orderId = $result['order_id'];
    $amount = $result['amount']; // Rial
    // Example: chargeUserWallet($orderId, $amount);
} else {
    // Either not yet confirmed, or already processed — do nothing
}
```

📌 **These 2 files are all you need.** Everything else (SMS detection, fee reservation, card reservation) happens automatically on the CubePay side.

---

## Important Notes

- ✔️ Amounts are always in **Rial** (Toman × 10)
- ✔️ `handleCallback()` is idempotent on its own — if called twice, the second call is automatically rejected; don't worry about double delivery
- ✔️ Never trust just the callback's arrival — `handleCallback()` already does real verification internally, so this concern is already handled
- ✔️ You set the destination card in your own settings inside `@cubepy_bot` ("💳 Manage Cards" section), not in code

---

## More Questions

For more complete details (error codes, exact response formats, Python/Node sample code), see [`docs/API-REFERENCE.md`](../docs/API-REFERENCE.md).
