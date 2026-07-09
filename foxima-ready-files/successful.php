<?php

session_start();

$rawBody = file_get_contents('php://input');
$decodedBody = json_decode($rawBody, true);
$callbackPayload = [];
if (is_array($decodedBody)) {
    $callbackPayload = array_merge($callbackPayload, $decodedBody);
}
if (!empty($_POST)) {
    $callbackPayload = array_merge($callbackPayload, $_POST);
}
if (!empty($_GET)) {
    $callbackPayload = array_merge($callbackPayload, $_GET);
}

$normalizeValue = static function ($value) {
    if ($value === null) {
        return null;
    }

    if (is_scalar($value)) {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    return null;
};

$sessionAuthority = $normalizeValue($_SESSION['authority'] ?? null);
$sessionOrderId = $normalizeValue($_SESSION['order_id'] ?? null);
$callbackAuthority = $normalizeValue($callbackPayload['authority'] ?? null);
$callbackOrderId = $normalizeValue($callbackPayload['order_id'] ?? null);

$authority = $sessionAuthority ?: $callbackAuthority;
$invoiceId = $sessionOrderId ?: $callbackOrderId;

// صفحه‌ی این فایل همیشه یک صفحه‌ی HTML قشنگه — چه کاربر مستقیم از پرداخت
// ریدایرکت بشه (GET با authority/order_id تو querystring) و چه از طریق
// session قدیمی. هیچ‌وقت JSON خام به مرورگر کاربر نشون داده نمی‌شه.
$page = [
    'state' => 'error', // error | success | already
    'title' => 'خطا در بررسی پرداخت',
    'text'  => 'پارامترهای لازم برای بررسی این تراکنش یافت نشد.',
];

if ($authority === null || $invoiceId === null) {
    http_response_code(400);
    renderResultPage($page, null, null);
    exit;
}

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/config.php';
require_once $projectRoot . '/jdf.php';
require_once $projectRoot . '/botapi.php';
require_once $projectRoot . '/Marzban.php';
require_once $projectRoot . '/function.php';
require_once $projectRoot . '/panels.php';
require_once $projectRoot . '/keyboard.php';

$ManagePanel = new ManagePanel();

$textbotlang = languagechange($projectRoot . '/text.json');

$paymentReport = select('Payment_report', '*', 'id_order', $invoiceId, 'select');
if (!is_array($paymentReport)) {
    http_response_code(404);
    $page['text'] = 'این تراکنش در سیستم ثبت نشده است.';
    renderResultPage($page, $invoiceId, null);
    exit;
}

$price = (int) ($paymentReport['price'] ?? 0);

try {
    $payload = json_encode(['authority' => $authority], JSON_UNESCAPED_UNICODE);

    $token = getPaySettingValue('token_zarinpey');
    if (empty($token) || $token === '0') {
        throw new Exception('توکن زرین پی تنظیم نشده است.');
    }

    $ch = curl_init('https://cubevps.ir/smspay/api/verify-payment.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('خطا در اتصال: ' . $error);
    }

    curl_close($ch);

    $result = json_decode($response, true);

    // «این تراکنش قبلاً تایید شده است» یعنی موفقیت واقعی رخ داده (کیف پول
    // قبلاً شارژ شده) — این یک خطای واقعی نیست، فقط یعنی این صفحه دوباره
    // (مثلاً با رفرش یا برگشت مرورگر) باز شده. باید همچنان صفحه‌ی موفق
    // نشون داده بشه، نه خطا.
    $alreadyVerified = is_array($result)
        && empty($result['success'])
        && (($result['status'] ?? '') === 'verified');

    if (!is_array($result) || (empty($result['success']) && !$alreadyVerified)) {
        $message = is_array($result) ? ($result['message'] ?? 'پرداخت انجام نشد') : 'پاسخ نامعتبر از درگاه دریافت شد.';
        throw new Exception($message);
    }

    $setting = select('setting', '*');
    $paymentreports = select('topicid', 'idreport', 'report', 'paymentreport', 'select')['idreport'] ?? null;

    if ($paymentReport['payment_Status'] !== 'paid') {
        $atomic = $pdo->prepare(
            "UPDATE Payment_report SET payment_Status = 'paid' "
            . "WHERE id_order = :id AND payment_Status <> 'paid'"
        );
        $atomic->bindValue(':id', $paymentReport['id_order'], PDO::PARAM_STR);
        $atomic->execute();

        if ($atomic->rowCount() >= 1) {
            DirectPayment($paymentReport['id_order']);
            update('user', 'Processing_value', '0', 'id', $paymentReport['id_user']);
            update('user', 'Processing_value_one', '0', 'id', $paymentReport['id_user']);
            update('user', 'Processing_value_tow', '0', 'id', $paymentReport['id_user']);
            update('Payment_report', 'payment_Status', 'paid', 'id_order', $paymentReport['id_order']);

            if (!empty($setting['Channel_Report'])) {
                $priceFormatted = number_format($price);
                $userInfo = select('user', '*', 'id', $paymentReport['id_user'], 'select');
                $username = $userInfo['username'] ?? '—';
                $transactionId = $result['data']['transaction']['payment_id'] ?? '';

                $reportLines = [
                    '💵 پرداخت جدید',
                    '',
                    "آیدی عددی کاربر : {$paymentReport['id_user']}",
                    "نام کاربری کاربر : @{$username}",
                    "مبلغ تراکنش : {$priceFormatted} تومان",
                ];

                if (!empty($transactionId)) {
                    $reportLines[] = "شناسه تراکنش : {$transactionId}";
                }

                $reportLines[] = 'روش پرداخت : زرین پی';

                $telegramPayload = [
                    'chat_id' => $setting['Channel_Report'],
                    'text' => implode("\n", $reportLines),
                    'parse_mode' => 'HTML',
                ];

                if (!empty($paymentreports)) {
                    $telegramPayload['message_thread_id'] = $paymentreports;
                }

                telegram('sendmessage', $telegramPayload);
            }
        }
    }

    $page = [
        'state' => $alreadyVerified ? 'already' : 'success',
        'title' => 'پرداخت موفق!',
        'text'  => 'از انجام تراکنش متشکریم.',
    ];
} catch (Exception $e) {
    $page = [
        'state' => 'error',
        'title' => 'پرداخت تایید نشد',
        'text'  => $e->getMessage(),
    ];
}

session_unset();
session_destroy();

renderResultPage($page, $invoiceId, $price);
exit;

/**
 * صفحه‌ی نتیجه (موفق / قبلاً تایید‌شده / خطا) با ظاهر یکسان با pay.php.
 */
function renderResultPage(array $page, ?string $invoiceId, ?int $price): void
{
    $botUsername = htmlspecialchars((string) ($GLOBALS['usernamebot'] ?? ''), ENT_QUOTES, 'UTF-8');
    $isSuccess = in_array($page['state'], ['success', 'already'], true);
    $icon = $isSuccess ? '✓' : '✕';
    $accent = $isSuccess ? '#22c55e' : '#ef4444';
    $title = htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8');
    $text = htmlspecialchars($page['text'], ENT_QUOTES, 'UTF-8');
    $orderLine = $invoiceId !== null
        ? '<div class="row"><span>شماره تراکنش</span><b class="ltr">' . htmlspecialchars($invoiceId, ENT_QUOTES, 'UTF-8') . '</b></div>'
        : '';
    $priceLine = ($price !== null && $price > 0)
        ? '<div class="row"><span>مبلغ</span><b>' . number_format($price) . ' تومان</b></div>'
        : '';
    $dateLine = '<div class="row"><span>زمان</span><b>' . (function_exists('jdate') ? jdate('Y/m/d H:i') : date('Y-m-d H:i')) . '</b></div>';
    ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?= $title ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/vazir-font/27.2.0/font-face.css" rel="stylesheet" type="text/css">
<style>
  :root{
    --bg:#0b0d12; --card:#151822; --border:#232735; --text:#eef0f4; --dim:#8b93a7; --accent:<?= $accent ?>;
  }
  *{box-sizing:border-box}
  body{
    margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
    background:var(--bg); color:var(--text); font-family:"Vazir",Tahoma,sans-serif; padding:20px;
  }
  .card{
    width:100%; max-width:380px; background:var(--card); border:1px solid var(--border);
    border-radius:20px; padding:28px 24px; text-align:center;
    box-shadow:0 20px 50px rgba(0,0,0,.35);
  }
  .badge{
    width:72px; height:72px; border-radius:50%; margin:0 auto 18px; display:flex;
    align-items:center; justify-content:center; font-size:34px; font-weight:bold;
    background:color-mix(in srgb, var(--accent) 16%, transparent); color:var(--accent);
    border:2px solid var(--accent);
    animation:pop .4s ease;
  }
  @keyframes pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}
  h1{font-size:19px; margin:0 0 8px}
  p.sub{color:var(--dim); font-size:13.5px; margin:0 0 20px; line-height:1.9}
  .box{background:#0f1218; border:1px solid var(--border); border-radius:14px; padding:14px 16px; margin-bottom:20px}
  .row{display:flex; justify-content:space-between; align-items:center; font-size:13px; padding:6px 0; color:var(--dim)}
  .row b{color:var(--text); font-weight:600}
  .row b.ltr{direction:ltr; font-family:monospace; font-size:12px}
  .row + .row{border-top:1px dashed var(--border)}
  a.btn{
    display:block; text-decoration:none; background:#3b82f6; color:#fff; font-weight:bold;
    padding:13px; border-radius:12px; font-size:15px;
  }
  a.btn:active{background:#2563eb}
</style>
</head>
<body>
  <div class="card">
    <div class="badge"><?= $icon ?></div>
    <h1><?= $title ?></h1>
    <p class="sub"><?= $text ?></p>
    <?php if ($orderLine !== '' || $priceLine !== ''): ?>
    <div class="box">
      <?= $orderLine ?>
      <?= $priceLine ?>
      <?= $dateLine ?>
    </div>
    <?php endif; ?>
    <?php if ($botUsername !== ''): ?>
      <a class="btn" href="https://t.me/<?= $botUsername ?>">بازگشت به ربات</a>
    <?php endif; ?>
  </div>
</body>
</html>
    <?php
}
