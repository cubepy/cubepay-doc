<?php

/**
 * 🔗 واسطِ CubePay برای «درگاه پرداخت سفارشی» میرزابات
 * ---------------------------------------------------------------
 * این یک فایل است و هر دو طرفِ ماجرا را انجام می‌دهد:
 *
 *   ۱) ربات به این فایل درخواست می‌دهد → اینجا از CubePay فاکتور می‌گیریم
 *      و لینکِ پرداخت را به ربات برمی‌گردانیم.
 *   ۲) CubePay بعد از پرداخت به همین فایل خبر می‌دهد → اینجا پرداخت را
 *      **مستقیماً از خودِ CubePay استعلام می‌کنیم** و تنها در صورت تایید،
 *      به ربات می‌گوییم سرویس را فعال کند.
 *
 * چرا استعلامِ دوباره: کال‌بک به یک آدرسِ عمومی می‌رسد و هر کسی می‌تواند
 * یک درخواستِ جعلی به آن بفرستد. پس کال‌بک فقط «بیدارباش» است و تصمیمِ
 * نهایی تنها بر اساس پاسخِ خودِ CubePay گرفته می‌شود.
 *
 * نصب:
 *   ۱. این فایل را روی هاستِ خودتان بگذارید، مثلاً:
 *      https://yourdomain.com/cubepay-gateway.php
 *   ۲. پنجره‌ی تنظیماتِ زیر را پر کنید.
 *   ۳. در ربات: مالی ← تنظیمات درگاه سفارشی
 *        - آدرس API  = همان آدرسِ بالا
 *        - توکن API  = همان چیزی که در BRIDGE_API_KEY گذاشتید
 *
 * نیازمندی: PHP 7.4+ با cURL. هیچ کتابخانه‌ای لازم نیست.
 */

declare(strict_types=1);

// ============================================================
//  ⚙️ تنظیمات — فقط این بخش را پر کنید
// ============================================================

/** آدرسِ رباتِ خودتان، بدون اسلشِ آخر. مثال: https://bot.example.com */
const BOT_BASE_URL = 'https://YOUR-BOT-DOMAIN.com';

/** توکنِ اصلیِ ربات — با دستور /token2 داخلِ ربات می‌گیرید. */
const BOT_TOKEN2 = 'PUT-YOUR-BOT-TOKEN2-HERE';

/** یک رشته‌ی تصادفیِ دلخواه؛ همین را در «توکن API» ربات هم وارد کنید. */
const BRIDGE_API_KEY = 'PUT-A-LONG-RANDOM-STRING-HERE';

/** توکنِ API حسابِ CubePay شما — از @cubepy_bot ← «🔗 پنل من». */
const CUBEPAY_TOKEN = 'PUT-YOUR-CUBEPAY-API-TOKEN-HERE';

/** آدرس‌های CubePay — معمولاً نیازی به تغییر ندارند. */
const CUBEPAY_PAY_BASE    = 'https://cubevps.ir/pay';
const CUBEPAY_SMSPAY_BASE = 'https://cubevps.ir/smspay';

/**
 * 🤖 نمایشِ شماره‌کارت داخلِ خودِ ربات (اختیاری)
 *
 * 📌 روشن/خاموشش را معمولاً از رباتِ CubePay تعیین می‌کنید — «🏪 فروشگاه من
 * → 💳 روش‌های پرداخت → 🤖 نمایش کارت در ربات». این فایل از همان پیروی می‌کند.
 *
 * false (پیش‌فرض) = از تنظیمِ حسابِ شما در رباتِ CubePay پیروی کن.
 * true            = همیشه روشن، حتی اگر در ربات خاموش باشد.
 *
 * برای این کار توکنِ تلگرامیِ رباتِ خودتان لازم است (همان که از @BotFather
 * گرفته‌اید) — چون این فایل باید مستقیم به مشتری پیام بدهد. اگر خالی بماند،
 * این قابلیت بی‌سروصدا خاموش می‌ماند و چیزی خراب نمی‌شود.
 *
 * ⚠️ فقط روی مسیرِ کارتی کار می‌کند: اگر روی حسابتان هم کارت و هم کریپتو
 * فعال باشد، لحظه‌ی ساختِ فاکتور هنوز کارتی اختصاص داده نشده (مشتری هنوز
 * انتخاب نکرده)، پس پیامی فرستاده نمی‌شود.
 */
const SHOW_CARD_IN_BOT    = false;
const TELEGRAM_BOT_TOKEN  = '';

/** آدرسِ API تلگرام — فقط برای تست عوض می‌شود. */
const TELEGRAM_API_BASE   = 'https://api.telegram.org';

/**
 * پوشه‌ی نگهداریِ سفارش‌ها (برای جلوگیری از فعال‌سازیِ تکراری و نگهداریِ
 * authority). اگر وجود نداشته باشد، خودش ساخته می‌شود.
 */
const STORAGE_DIR = __DIR__ . '/cubepay-orders';

// ============================================================
//  از اینجا به بعد نیازی به تغییر نیست
// ============================================================

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input') ?: '';
$in  = json_decode($raw, true);
$in  = is_array($in) ? $in : [];

$isCallback = isset($_GET['cb']) || isset($_GET['authority']) || isset($_GET['sig'])
    || isset($in['authority']) || isset($in['sig']) || isset($in['status']);

try {
    if ($isCallback) {
        handle_callback($in);
    } else {
        handle_create($in);
    }
} catch (Throwable $e) {
    cg_log('unhandled: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'internal error'], JSON_UNESCAPED_UNICODE);
}

// ------------------------------------------------------------
//  ۱) ساختِ فاکتور — ربات این را صدا می‌زند
// ------------------------------------------------------------
function handle_create(array $in): void
{
    $apiKey = (string) ($in['api_key'] ?? '');
    if (!hash_equals(BRIDGE_API_KEY, $apiKey)) {
        http_response_code(403);
        echo json_encode(['error' => 'invalid api_key'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $orderId = trim((string) ($in['order_id'] ?? ''));
    $amount  = (int) ($in['amount'] ?? 0); // ربات مبلغ را به تومان می‌فرستد

    if ($orderId === '' || $amount < 100) {
        http_response_code(422);
        echo json_encode(['error' => 'invalid order_id or amount'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $callbackUrl = cg_self_url() . '?cb=1';

    // روترِ یکپارچه: بسته به تنظیماتِ حسابتان در @cubepy_bot خودش تصمیم
    // می‌گیرد کارت‌به‌کارت باشد، کریپتو، یا صفحه‌ی «کارت یا کریپتو؟».
    // مبلغ اینجا تومان است — دقیقاً همان چیزی که ربات فرستاده.
    $res = cg_post(CUBEPAY_PAY_BASE . '/create-order.php', [
        'order_id'     => $orderId,
        'price_amount' => $amount,
        'callback_url' => $callbackUrl,
        // ربات است نه سایت: بعد از پرداخت، مرورگرِ مشتری به دامنه‌ی شما
        // هدایت نشود. نتیجه را خودِ ربات به مشتری می‌گوید.
        'redirect_after_payment' => false,
    ], CUBEPAY_TOKEN);

    if (empty($res['success']) || empty($res['pay_page_url'])) {
        cg_log("create failed for {$orderId}: " . json_encode($res, JSON_UNESCAPED_UNICODE));
        http_response_code(502);
        echo json_encode(['error' => $res['message'] ?? 'cubepay error'], JSON_UNESCAPED_UNICODE);
        return;
    }

    cg_store($orderId, [
        'order_id'  => $orderId,
        'amount'    => $amount,
        'authority' => $res['authority'] ?? null,
        'notified'  => false,
        'created_at' => date('c'),
    ]);

    cg_maybe_send_card((int) ($in['user_id'] ?? 0), $res);

    echo json_encode(['url' => $res['pay_page_url']], JSON_UNESCAPED_UNICODE);
}

// ------------------------------------------------------------
//  ۲) کال‌بکِ CubePay — بعد از پرداخت
// ------------------------------------------------------------
function handle_callback(array $in): void
{
    $orderId   = (string) ($in['order_id'] ?? ($_GET['order_id'] ?? ''));
    $authority = (string) ($in['authority'] ?? ($_GET['authority'] ?? ''));

    if ($orderId === '' && $authority === '') {
        http_response_code(400);
        echo json_encode(['error' => 'missing order_id/authority'], JSON_UNESCAPED_UNICODE);
        return;
    }

    $record = $orderId !== '' ? cg_load($orderId) : null;
    if ($record === null && $orderId !== '') {
        cg_log("callback for unknown order {$orderId}");
    }
    if ($authority === '' && is_array($record)) {
        $authority = (string) ($record['authority'] ?? '');
    }

    // 🔒 هرگز به خودِ کال‌بک اعتماد نکن — از CubePay بپرس.
    if (!cg_is_really_paid($orderId, $authority)) {
        echo json_encode(['ok' => false, 'reason' => 'not verified'], JSON_UNESCAPED_UNICODE);
        return;
    }

    // جلوگیری از فعال‌سازیِ دوباره (کال‌بک ممکن است چند بار برسد)
    if (is_array($record) && !empty($record['notified'])) {
        echo json_encode(['ok' => true, 'duplicate' => true], JSON_UNESCAPED_UNICODE);
        return;
    }

    $ok = cg_notify_bot($orderId);
    if ($ok && is_array($record)) {
        $record['notified'] = true;
        $record['paid_at'] = date('c');
        cg_store($orderId, $record);
    }

    echo json_encode(['ok' => $ok], JSON_UNESCAPED_UNICODE);
}

/**
 * آیا این سفارش واقعاً پرداخت شده؟ اول با order_id می‌پرسیم (مسیرِ یکپارچه
 * و کریپتو)، و اگر آن مسیر جواب نداد، با authority سراغِ verify-payment
 * می‌رویم (مسیرِ فقط-کارتیِ مستقیم).
 */
function cg_is_really_paid(string $orderId, string $authority): bool
{
    if ($orderId !== '') {
        $st = cg_get(CUBEPAY_PAY_BASE . '/check-order-status.php?order_id=' . urlencode($orderId), CUBEPAY_TOKEN);
        if (!empty($st['success'])) {
            $status = (string) ($st['status'] ?? '');
            // فقط این دو یعنی «پولْ قطعاً رسیده»
            return $status === 'verified' || $status === 'finished';
        }
    }

    if ($authority !== '') {
        $vr = cg_post(CUBEPAY_SMSPAY_BASE . '/api/verify-payment.php', ['authority' => $authority], CUBEPAY_TOKEN);
        // 409 یعنی «قبلاً تایید شده» — که برای ما هم یعنی پرداخت‌شده
        return ($vr['status'] ?? '') === 'verified';
    }

    return false;
}

/** به ربات بگو سرویس را فعال کند. */
function cg_notify_bot(string $orderId): bool
{
    $ch = curl_init(rtrim(BOT_BASE_URL, '/') . '/api/payment');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Token: ' . BOT_TOKEN2,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'order_id' => $orderId,
            'actions'  => 'custom_payment_verify',
        ], JSON_UNESCAPED_UNICODE),
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err !== '' || $code < 200 || $code >= 300) {
        cg_log("bot notify failed for {$orderId}: http={$code} err={$err} body=" . substr((string) $body, 0, 300));
        return false;
    }
    cg_log("bot notified for {$orderId}: " . substr((string) $body, 0, 200));
    return true;
}

/**
 * اگر فروشنده خواسته باشد، شماره‌کارت را مستقیم در تلگرام برای مشتری بفرست.
 * بی‌سروصدا رد می‌شود اگر: خاموش باشد، توکنِ ربات نداشته باشیم، کاربر معلوم
 * نباشد، یا پاسخِ CubePay اصلاً کارتی نداشته باشد (مسیرِ «کارت یا کریپتو؟»).
 */
function cg_maybe_send_card(int $userId, array $res): void
{
    // یا فروشنده در رباتِ CubePay روشنش کرده، یا این فایل روی true است.
    $wanted = SHOW_CARD_IN_BOT || !empty($res['show_card_in_bot']);
    if (!$wanted || TELEGRAM_BOT_TOKEN === '' || $userId <= 0) {
        return;
    }
    $number = (string) ($res['card']['number'] ?? '');
    if ($number === '') {
        return;
    }

    $holder  = (string) ($res['card']['holder'] ?? '');
    $minutes = (int) ($res['expires_in_minutes'] ?? 30);
    $toman   = (int) ($res['pay_amount_toman'] ?? 0);

    $text = "💳 <b>پرداخت کارت‌به‌کارت</b>\n"
        . "━━━━━━━━━━━━━━━\n\n"
        . "🔢 شماره کارت:\n<code>{$number}</code>\n"
        . ($holder !== '' ? "👤 به نام: {$holder}\n" : '')
        . ($toman > 0 ? "\n💰 مبلغ دقیق: <b>" . number_format($toman) . "</b> تومان\n" : "\n")
        . "⏳ مهلت پرداخت: {$minutes} دقیقه\n\n"
        . "⚠️ مبلغ باید رقم‌به‌رقم دقیق باشه — تاییدِ خودکار فقط با همین عدد انجام می‌شه.";

    $ch = curl_init(TELEGRAM_API_BASE . '/bot' . TELEGRAM_BOT_TOKEN . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode([
            'chat_id' => $userId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], JSON_UNESCAPED_UNICODE),
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // شکستش نباید جلوی پرداخت را بگیرد — فقط لاگ می‌شود.
    if ($code < 200 || $code >= 300) {
        cg_log("card message failed for user {$userId}: http={$code} " . substr((string) $body, 0, 200));
    }
}

// ------------------------------------------------------------
//  کمکی‌ها
// ------------------------------------------------------------

function cg_post(string $url, array $body, string $token): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err !== '') {
        cg_log("POST {$url} failed: {$err}");
        return [];
    }
    $decoded = json_decode((string) $resp, true);
    return is_array($decoded) ? $decoded : [];
}

function cg_get(string $url, string $token): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err !== '') {
        cg_log("GET {$url} failed: {$err}");
        return [];
    }
    $decoded = json_decode((string) $resp, true);
    return is_array($decoded) ? $decoded : [];
}

/** آدرسِ خودِ همین فایل (برای ساختِ callback_url). */
function cg_self_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $path   = (string) ($_SERVER['SCRIPT_NAME'] ?? '/cubepay-gateway.php');
    return $scheme . '://' . $host . $path;
}

function cg_storage_path(string $orderId): string
{
    if (!is_dir(STORAGE_DIR)) {
        @mkdir(STORAGE_DIR, 0700, true);
        @file_put_contents(STORAGE_DIR . '/.htaccess', "Require all denied\n");
    }
    return STORAGE_DIR . '/' . sha1($orderId) . '.json';
}

function cg_store(string $orderId, array $data): void
{
    @file_put_contents(cg_storage_path($orderId), json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function cg_load(string $orderId): ?array
{
    $f = cg_storage_path($orderId);
    if (!is_file($f)) {
        return null;
    }
    $d = json_decode((string) file_get_contents($f), true);
    return is_array($d) ? $d : null;
}

/** لاگِ ساده و چرخشی — برای وقتی چیزی کار نکرد. */
function cg_log(string $line): void
{
    $file = STORAGE_DIR . '/gateway.log';
    if (!is_dir(STORAGE_DIR)) {
        @mkdir(STORAGE_DIR, 0700, true);
    }
    if (is_file($file) && filesize($file) > 1048576) {
        @rename($file, $file . '.1');
    }
    @file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $line . "\n", FILE_APPEND | LOCK_EX);
}
