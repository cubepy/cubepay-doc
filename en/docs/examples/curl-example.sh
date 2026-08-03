#!/usr/bin/env bash
# cURL sample for the CubePay API
# Full documentation: ../API-REFERENCE.md

API_TOKEN="YOUR_API_TOKEN"
BASE_URL="https://cubevps.ir/smspay/api"

echo "== Create a transaction =="
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/create-payment.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d '{
    "amount": 200000,
    "order_id": "ORD123",
    "callback_url": "https://yourbot.example.com/callback",
    "type": "card",
    "description": "Wallet top-up"
  }')

echo "$CREATE_RESPONSE"

# Extract the authority from the response
AUTHORITY=$(echo "$CREATE_RESPONSE" | grep -o '"authority":"[^"]*"' | cut -d'"' -f4)

echo ""
echo "== Verify the transaction (after the customer actually pays) =="
curl -s -X POST "$BASE_URL/verify-payment.php" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $API_TOKEN" \
  -d "{\"authority\": \"$AUTHORITY\"}"
