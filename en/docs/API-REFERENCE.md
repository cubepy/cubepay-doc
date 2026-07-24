<div align="center"><img src="../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 📚 Full API Reference

This file has the complete technical details of the API (for those working directly with the API, not through an SDK). If you just want to connect quickly, see [`generic-integration-guide.md`](../integrations/generic-integration-guide.md) instead.

---

## 🚀 Quick Start (Direct API Calls)

To use the API you need an **API Token**. You get this token from the merchant management bot (`@cubepy_bot`) after registering and having your account approved.

The token must be sent in the header of every request like this:

```
Authorization: Bearer YOUR_API_TOKEN
```

⚠️ Important: unlike some gateways, the endpoints below end in `.php` — make sure you copy them exactly as shown in your merchant panel.

---

## 🛒 Create Payment

**Endpoint:**
```
POST https://cubevps.ir/smspay/api/create-payment.php
```

### 📋 Parameters

| Name | Type | Required | Description |
|---|---|---|---|
| `amount` | int | ✅ | Transaction amount in **Rial** (minimum 1000 Rial) |
| `order_id` | string | ✅ | Your unique order identifier |
| `callback_url` | string | ✅ | The address notified after a successful payment |
| `type` | string | ✅ | Currently only `card` |
| `customer_user_id` | string | ❌ | Your customer's identifier (e.g. their Telegram numeric ID) |
| `description` | string | ❌ | Order description |

### ✅ Sample Success Response

```json
{
  "success": true,
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "payment_link": "https://cubevps.ir/smspay/pay.php?authority=bdc9e0497c121d6187750d53798dae81",
  "pay_amount": 200720,
  "pay_amount_toman": 20072
}
```

📌 **Take `pay_amount_toman` seriously:** this is the exact amount payable, not necessarily the number you sent. To reliably match bank SMS messages, the system adds a small random amount to the total (e.g. 20,000 becomes 20,072). **Give the `payment_link` directly to the customer** — the payment page itself clearly shows this exact amount, so you don't need to calculate anything yourself.

### ❌ Sample Error Response

```json
{
  "success": false,
  "message": "Invalid amount (Rial, minimum 1000)."
}
```

---

## 🔍 Verify Payment

**Endpoint:**
```
POST https://cubevps.ir/smspay/api/verify-payment.php
```

### 📋 Parameters

| Name | Type | Required | Description |
|---|---|---|---|
| `authority` | string | ✅ | The code you received from the create-payment step |

### ✅ Sample Response (First Successful Verification)

```json
{
  "success": true,
  "message": "Payment verified.",
  "order_id": "ORD123",
  "amount": 200000,
  "status": "verified"
}
```

### 📌 Other Responses (Depending on Current Transaction State)

| Status | HTTP Status | Meaning |
|---|---|---|
| `verified` (duplicate) | `409` | This transaction has already been verified once — don't deliver the service again |
| `pending` | `402` | No payment recorded yet |
| `expired` / `failed` | `410` | The transaction's time limit expired, or it failed |
| Invalid authority | `404` | No such transaction found |

⚠️ Only the **first** successful `verify-payment` call returns `success: true`. This is intentional so that if it's called twice for any reason (user refresh, your own retry, etc.), you don't deliver the service/credit to the user twice.

---

## ⏳ Transaction Validity & Rules

- Every invoice is valid for **30 minutes**; after that it expires and can no longer be paid.
- If you call `create-payment` again with a duplicate `order_id` while the previous invoice is still **awaiting payment**, the same `authority`/`payment_link` is returned (no new invoice is created).
- Amounts everywhere (request and response) are in **Rial**, unless explicitly labeled "Toman" (like `pay_amount_toman`).

---

## 🔄 Data Sent to `callback_url`

As soon as a deposit is detected (from the bank SMS), this request is sent to your `callback_url`:

**As a POST (JSON body):**
```json
{
  "success": true,
  "status": "paid",
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "order_id": "ORD123",
  "amount": 200000
}
```

**And simultaneously as querystring parameters on the same URL** (for compatibility with backends that only read GET):
```
?authority=...&order_id=...&status=paid
```

📌 After receiving this callback, always call `verify-payment` to make sure (this callback is just a notification, not the final confirmation).

---

## 💻 Code Samples

### 🐘 PHP

```php
<?php
$accessToken = "YOUR_API_TOKEN";

$data = [
    "amount" => 200000,
    "order_id" => "ORD123",
    "callback_url" => "https://yourbot.example.com/callback.php",
    "type" => "card",
    "description" => "Wallet top-up",
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
    echo "Payment link: " . $result['payment_link'];
} else {
    echo "Error: " . $result['message'];
}
```

### 🐍 Python

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
    "description": "Wallet top-up",
}

response = requests.post(url, json=data, headers=headers)
print(response.json())
```

### 🟢 Node.js

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

### 💻 cURL

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

📎 More complete samples (Laravel, a ready-made PHP client, etc.) in [`examples/`](./examples/).

📎 For the full machine-readable spec, see the [`openapi.yaml`](./openapi.yaml) file, or try it directly:

- 🧪 [Open in Swagger Editor](https://editor.swagger.io/?url=https://raw.githubusercontent.com/cubepy/cubepay-doc/main/docs/openapi.yaml)
- 💻 [View in VS Code (web)](https://vscode.dev/github/cubepy/cubepay-doc/blob/main/docs/openapi.yaml)

---

## ⚠️ Common Codes & Messages

| Message | HTTP Status | Reason |
|---|---|---|
| Token not sent / invalid | `401` | The Authorization header is empty or the token is wrong |
| Your merchant account has not been approved yet... | `403` | An admin hasn't approved your request yet |
| Invalid amount | `422` | Less than 1000 Rial or not a number |
| Invalid order_id / callback_url | `422` | Wrong format or length (callback must be a valid, non-internal https URL) |
| Insufficient fee wallet balance | `402` | You need to top up your commission wallet |
| Concurrent invoice capacity full | `503` | Very rare; try again in a moment |

---

## 🔗 Links

🤖 Merchant management bot: [@cubepy_bot](https://t.me/cubepy_bot)
