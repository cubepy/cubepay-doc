<?php
// A minimal, class-free sample — for a quick look at how the API works.
// For the complete, reusable version see CubePayClient.php

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
