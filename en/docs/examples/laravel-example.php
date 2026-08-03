<?php

// Add this block to config/services.php:
//
// 'cubepay' => [
//     'token' => env('CUBEPAY_API_TOKEN'),
//     'base_url' => 'https://cubevps.ir/smspay/api',
// ],

use Illuminate\Support\Facades\Http;

// ------------------------------------------------------------------
// Create a transaction (e.g. inside a Controller)
// ------------------------------------------------------------------

$response = Http::withToken(config('services.cubepay.token'))
    ->post(config('services.cubepay.base_url') . '/create-payment.php', [
        'amount' => 200000,
        'order_id' => 'ORD123',
        'callback_url' => route('cubepay.callback'),
        'type' => 'card',
        'description' => 'Wallet top-up',
    ]);

if ($response->json('success')) {
    return redirect($response->json('payment_link'));
}

abort(422, $response->json('message'));

// ------------------------------------------------------------------
// Callback route and controller (routes/web.php)
// ------------------------------------------------------------------
//
// Route::post('/cubepay/callback', [CubePayController::class, 'callback'])
//     ->name('cubepay.callback');

// ------------------------------------------------------------------
// Example callback method in the controller
// ------------------------------------------------------------------

// public function callback(Request $request)
// {
//     $verify = Http::withToken(config('services.cubepay.token'))
//         ->post(config('services.cubepay.base_url') . '/verify-payment.php', [
//             'authority' => $request->input('authority'),
//         ]);
//
//     if ($verify->json('success')) {
//         // Mark the order as paid
//         Order::where('id', $verify->json('order_id'))->update(['status' => 'paid']);
//     }
//
//     return response()->json(['ok' => true]);
// }
