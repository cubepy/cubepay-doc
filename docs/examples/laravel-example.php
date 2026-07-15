<?php

// در فایل config/services.php این بخش را اضافه کنید:
//
// 'cubepay' => [
//     'token' => env('CUBEPAY_API_TOKEN'),
//     'base_url' => 'https://cubevps.ir/smspay/api',
// ],

use Illuminate\Support\Facades\Http;

// ------------------------------------------------------------------
// ایجاد تراکنش (مثلاً داخل یک Controller)
// ------------------------------------------------------------------

$response = Http::withToken(config('services.cubepay.token'))
    ->post(config('services.cubepay.base_url') . '/create-payment.php', [
        'amount' => 200000,
        'order_id' => 'ORD123',
        'callback_url' => route('cubepay.callback'),
        'type' => 'card',
        'description' => 'شارژ کیف پول',
    ]);

if ($response->json('success')) {
    return redirect($response->json('payment_link'));
}

abort(422, $response->json('message'));

// ------------------------------------------------------------------
// روت و کنترلر کال‌بک (routes/web.php)
// ------------------------------------------------------------------
//
// Route::post('/cubepay/callback', [CubePayController::class, 'callback'])
//     ->name('cubepay.callback');

// ------------------------------------------------------------------
// نمونه‌ی متد callback در کنترلر
// ------------------------------------------------------------------

// public function callback(Request $request)
// {
//     $verify = Http::withToken(config('services.cubepay.token'))
//         ->post(config('services.cubepay.base_url') . '/verify-payment.php', [
//             'authority' => $request->input('authority'),
//         ]);
//
//     if ($verify->json('success')) {
//         // سفارش را به عنوان پرداخت‌شده علامت بزنید
//         Order::where('id', $verify->json('order_id'))->update(['status' => 'paid']);
//     }
//
//     return response()->json(['ok' => true]);
// }
