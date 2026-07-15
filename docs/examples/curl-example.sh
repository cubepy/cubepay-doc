#!/usr/bin/env bash
# نمونه‌ی cURL برای CubePay API
# مستندات کامل: ../API-REFERENCE.md

API_TOKEN="YOUR_API_TOKEN"
BASE_URL="https://cubevps.ir/smspay/api"

echo "== ایجاد تراکنش =="
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/create-payment.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d '{
    "amount": 200000,
    "order_id": "ORD123",
    "callback_url": "https://yourbot.example.com/callback",
    "type": "card",
    "description": "شارژ کیف پول"
  }')

echo "$CREATE_RESPONSE"

# استخراج authority از پاسخ (نیاز به jq دارد)
AUTHORITY=$(echo "$CREATE_RESPONSE" | grep -o '"authority":"[^"]*"' | cut -d'"' -f4)

echo ""
echo "== تایید تراکنش (بعد از پرداخت واقعی) =="
curl -s -X POST "$BASE_URL/verify-payment.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{\"authority\": \"$AUTHORITY\"}"
