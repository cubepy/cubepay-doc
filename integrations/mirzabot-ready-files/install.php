<?php
/**
 * CubePay ↔ Mirzabot — نصب‌کننده‌ی نیمه‌خودکار (وب یا CLI)
 * ============================================================
 *
 * کار «قدم ۲ (جایگزینی فایل‌ها)» + «قدم ۵ (ثبت توکن و فعال‌سازی)» از راهنمای
 * mirzabot-ready-files-guide.md رو خودکار می‌کنه: فایل‌های آماده رو از همین
 * پوشه (بعد از اکسترکت زیپ)، با بک‌آپ، تو مسیرهای درست ریشه‌ی ربات کپی
 * می‌کنه، و توکن + وضعیت فعال/غیرفعال رو مستقیم تو دیتابیس خودِ ربات (جدول
 * PaySetting — همون‌جایی که خودِ table.php این مقادیر رو می‌سازه) می‌نویسه.
 * هیچ درخواستی به سرورهای بیرونی نمی‌زنه؛ فقط روی همین هاست کار می‌کنه.
 *
 * نحوه‌ی اجرا:
 *   - هاست اشتراکی/cPanel: زیپ رو اکسترکت کنید، این فایل رو کنار بقیه‌ی
 *     فایل‌ها آپلود کنید و از مرورگر بازش کنید.
 *   - سرور با SSH/VPS: از همون‌جا بزنید `php install.php` (کاملاً تعاملی).
 *
 * ⚠️ امنیت — قبل از آپلود حتماً:
 *   1) مقدار INSTALL_PASSWORD زیر رو عوض کنید.
 *   2) بلافاصله بعد از نصب موفق، این فایل رو از روی سرور پاک کنید.
 */

declare(strict_types=1);

// ─── ۱) قبل از آپلود این خط رو عوض کنید ────────────────────────────────────
const INSTALL_PASSWORD = 'CHANGE_ME_BEFORE_UPLOAD';
// ────────────────────────────────────────────────────────────────────────────

const LOCK_FILE = __DIR__ . '/.cubepay_install_done';

// فایل جدیدیه که قبلاً تو ریپوی Mirzabot وجود نداره — پوشه‌ی مقصدش رو اگه لازم بود می‌سازیم.
const NEW_FILES = [
    'cubepay.php' => 'payment/cubepay.php',
];

const REQUIRED_FILES = [
    'function.php' => 'function.php',
    'index.php'    => 'index.php',
    'admin.php'    => 'admin.php',
    'keyboard.php' => 'keyboard.php',
    'table.php'    => 'table.php',
    'fa.php'       => 'lang/fa.php',
];

const COSMETIC_FILES = [
    'payment.php'        => 'panel/payment.php',
    'user.php'           => 'panel/user.php',
    'payment_expire.php' => 'cronbot/payment_expire.php',
];

function is_cli(): bool
{
    return PHP_SAPI === 'cli';
}

/**
 * @param array{
 *   bot_root: string, install_cosmetic: bool, token: string, enable: bool,
 *   db_host: string, db_name: string, db_user: string, db_pass: string
 * } $cfg
 * @return string[]
 */
function run_install(array $cfg): array
{
    $log = [];
    $botRoot = rtrim($cfg['bot_root'], '/');

    if (!is_dir($botRoot)) {
        throw new RuntimeException("مسیر ریشه‌ی ربات پیدا نشد: $botRoot");
    }

    foreach (NEW_FILES as $source => $relativeTarget) {
        $sourcePath = __DIR__ . '/' . $source;
        $targetPath = $botRoot . '/' . $relativeTarget;

        if (!is_file($sourcePath)) {
            throw new RuntimeException("فایل منبع کنار install.php پیدا نشد: $source (زیپ رو کامل اکسترکت کردید؟)");
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new RuntimeException("ساخت پوشه‌ی $targetDir ناموفق بود.");
        }

        if (is_file($targetPath)) {
            $backupPath = $targetPath . '.bak-' . date('Ymd-His');
            copy($targetPath, $backupPath);
            $log[] = "🗂  بک‌آپ گرفته شد: $relativeTarget → " . basename($backupPath);
        }

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException("کپی $source روی $relativeTarget ناموفق بود.");
        }
        $log[] = "✅ فایل جدید نوشته شد: $relativeTarget";
    }

    $targets = REQUIRED_FILES;
    if ($cfg['install_cosmetic']) {
        $targets += COSMETIC_FILES;
    }

    foreach ($targets as $source => $relativeTarget) {
        $sourcePath = __DIR__ . '/' . $source;
        $targetPath = $botRoot . '/' . $relativeTarget;

        if (!is_file($sourcePath)) {
            throw new RuntimeException("فایل منبع کنار install.php پیدا نشد: $source (زیپ رو کامل اکسترکت کردید؟)");
        }

        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            throw new RuntimeException("پوشه‌ی مقصد وجود نداره: $targetDir (احتمالاً ساختار Mirzabot شما فرق داره — راهنمای اصلی رو ببینید)");
        }

        if (is_file($targetPath)) {
            $backupPath = $targetPath . '.bak-' . date('Ymd-His');
            if (!copy($targetPath, $backupPath)) {
                throw new RuntimeException("گرفتن بک‌آپ از $relativeTarget ناموفق بود.");
            }
            $log[] = "🗂  بک‌آپ گرفته شد: $relativeTarget → " . basename($backupPath);
        }

        if (!copy($sourcePath, $targetPath)) {
            throw new RuntimeException("کپی $source روی $relativeTarget ناموفق بود (دسترسی نوشتن رو چک کنید).");
        }
        $log[] = "✅ جایگزین شد: $relativeTarget";
    }

    $pdo = new PDO(
        "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4",
        $cfg['db_user'],
        $cfg['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    ensure_pay_setting_table($pdo);

    upsert_pay_setting($pdo, 'merchant_token_cubepay', $cfg['token']);
    $log[] = '✅ توکن API تو PaySetting.merchant_token_cubepay ثبت شد.';

    upsert_pay_setting($pdo, 'statuscubepay', $cfg['enable'] ? 'oncubepay' : 'offcubepay');
    $log[] = '✅ وضعیت درگاه: ' . ($cfg['enable'] ? 'فعال' : 'غیرفعال') . ' (PaySetting.statuscubepay)';

    $log[] = 'ℹ️ حداقل/حداکثر مبلغ و درصد کش‌بک رو در صورت نیاز از داخل ربات (پنل مدیریت ← 💰 بخش مالی ← کیوب‌پی ← تنظیمات) تنظیم کنید.';

    file_put_contents(LOCK_FILE, date('c') . " — installed by CubePay installer\n");
    $log[] = '🔒 فایل قفل (.cubepay_install_done) ساخته شد — اجرای دوباره تا وقتی این فایل هست مسدوده.';

    return $log;
}

/** table.php معمولاً این جدول رو خودش می‌سازه؛ اینجا فقط برای اطمینان (اگه هنوز اجرا نشده) چک می‌کنیم. */
function ensure_pay_setting_table(PDO $pdo): void
{
    $exists = $pdo->query("SHOW TABLES LIKE 'PaySetting'")->fetchColumn();
    if ($exists === false) {
        throw new RuntimeException('جدول PaySetting پیدا نشد. یه‌بار قبل از این نصب، پنل ادمین یا ربات رو باز کنید تا table.php این جدول رو بسازه، بعد دوباره اینجا رو اجرا کنید.');
    }
}

function upsert_pay_setting(PDO $pdo, string $name, string $value): void
{
    $update = $pdo->prepare('UPDATE PaySetting SET ValuePay = :value WHERE NamePay = :name');
    $update->execute([':value' => $value, ':name' => $name]);

    if ($update->rowCount() === 0) {
        $exists = $pdo->prepare('SELECT 1 FROM PaySetting WHERE NamePay = :name');
        $exists->execute([':name' => $name]);
        if ($exists->fetchColumn() === false) {
            $insert = $pdo->prepare('INSERT INTO PaySetting (NamePay, ValuePay) VALUES (:name, :value)');
            $insert->execute([':name' => $name, ':value' => $value]);
        }
    }
}

// ============================================================
//  حالت CLI (اجرا با `php install.php` — مناسب سرورهای VPS با SSH)
// ============================================================
if (is_cli()) {
    function cli_prompt(string $label, ?string $default = null, bool $secret = false): string
    {
        $suffix = $default !== null && $default !== '' ? " [$default]" : '';
        fwrite(STDOUT, "$label$suffix: ");
        if ($secret && stripos(PHP_OS, 'WIN') === false) {
            system('stty -echo');
        }
        $line = trim((string) fgets(STDIN));
        if ($secret && stripos(PHP_OS, 'WIN') === false) {
            system('stty echo');
            fwrite(STDOUT, "\n");
        }
        return $line !== '' ? $line : (string) $default;
    }

    fwrite(STDOUT, "=== نصب‌کننده‌ی CubePay برای Mirzabot ===\n\n");

    if (INSTALL_PASSWORD === 'CHANGE_ME_BEFORE_UPLOAD') {
        fwrite(STDERR, "❌ اول مقدار INSTALL_PASSWORD رو تو install.php عوض کنید.\n");
        exit(1);
    }
    if (is_file(LOCK_FILE)) {
        fwrite(STDERR, "❌ این نصب قبلاً انجام شده (.cubepay_install_done وجود داره). برای نصب دوباره این فایل رو پاک کنید.\n");
        exit(1);
    }

    $password = cli_prompt('رمز نصب (همون INSTALL_PASSWORD)', null, true);
    if (!hash_equals(INSTALL_PASSWORD, $password)) {
        fwrite(STDERR, "❌ رمز اشتباهه.\n");
        exit(1);
    }

    $botRoot = cli_prompt('مسیر کامل ریشه‌ی ربات Mirzabot روی سرور', '/home/USER/public_html/bot');
    $installCosmetic = strtolower(cli_prompt('فایل‌های ظاهری پنل وب (بخش ۲) هم نصب بشه؟ (y/n)', 'y')) === 'y';
    $token = cli_prompt('توکن API از @cubepy_bot');
    $enable = strtolower(cli_prompt('همین الان درگاه فعال بشه؟ (y/n)', 'y')) === 'y';
    $dbHost = cli_prompt('DB Host', 'localhost');
    $dbName = cli_prompt('DB Name');
    $dbUser = cli_prompt('DB User');
    $dbPass = cli_prompt('DB Password', null, true);

    try {
        $log = run_install([
            'bot_root'         => $botRoot,
            'install_cosmetic' => $installCosmetic,
            'token'            => $token,
            'enable'           => $enable,
            'db_host'          => $dbHost,
            'db_name'          => $dbName,
            'db_user'          => $dbUser,
            'db_pass'          => $dbPass,
        ]);
        fwrite(STDOUT, "\n" . implode("\n", $log) . "\n\n");
        fwrite(STDOUT, "🎉 نصب کامل شد. حالا این فایل رو پاک کنید: rm " . __FILE__ . "\n");
    } catch (Throwable $e) {
        fwrite(STDERR, "\n❌ خطا: " . $e->getMessage() . "\n");
        exit(1);
    }
    exit(0);
}

// ============================================================
//  حالت وب (مرورگر — مناسب هاست اشتراکی/cPanel بدون SSH)
// ============================================================

if (isset($_GET['selfdestruct']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (hash_equals(INSTALL_PASSWORD, (string) ($_POST['password'] ?? ''))) {
        @unlink(LOCK_FILE);
        @unlink(__FILE__);
        echo '✅ فایل نصب حذف شد.';
    } else {
        http_response_code(403);
        echo '❌ رمز اشتباهه.';
    }
    exit;
}

$error = null;
$log = null;

if (INSTALL_PASSWORD === 'CHANGE_ME_BEFORE_UPLOAD') {
    $error = 'اول مقدار INSTALL_PASSWORD رو داخل خود فایل install.php (روی سرور) عوض کنید، بعد صفحه رو رفرش کنید.';
} elseif (is_file(LOCK_FILE)) {
    $error = 'این نصب قبلاً یک‌بار انجام شده (فایل .cubepay_install_done کنار install.php هست). برای نصب دوباره اول اون فایل رو پاک کنید.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(INSTALL_PASSWORD, (string) ($_POST['password'] ?? ''))) {
        $error = 'رمز نصب اشتباهه.';
    } else {
        try {
            $log = run_install([
                'bot_root'         => (string) ($_POST['bot_root'] ?? ''),
                'install_cosmetic' => isset($_POST['install_cosmetic']),
                'token'            => (string) ($_POST['token'] ?? ''),
                'enable'           => isset($_POST['enable']),
                'db_host'          => (string) ($_POST['db_host'] ?? 'localhost'),
                'db_name'          => (string) ($_POST['db_name'] ?? ''),
                'db_user'          => (string) ($_POST['db_user'] ?? ''),
                'db_pass'          => (string) ($_POST['db_pass'] ?? ''),
            ]);
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>نصب CubePay روی Mirzabot</title>
<style>
  body{font-family:Tahoma,sans-serif;background:#0b0d12;color:#eef0f4;max-width:640px;margin:40px auto;padding:0 16px}
  h1{font-size:20px}
  label{display:block;margin:14px 0 4px;font-size:14px;color:#c7cbd6}
  input[type=text],input[type=password]{width:100%;padding:9px;border-radius:8px;border:1px solid #2b3040;background:#151822;color:#eef0f4;box-sizing:border-box}
  .row{display:flex;align-items:center;gap:8px;margin-top:14px}
  button{margin-top:22px;padding:11px 18px;border:0;border-radius:10px;background:#3b82f6;color:#fff;font-weight:bold;cursor:pointer}
  .ok{background:#16211a;border:1px solid #2f5c3f;padding:14px;border-radius:10px;margin-top:16px;line-height:2}
  .err{background:#241416;border:1px solid #6a2b30;padding:14px;border-radius:10px;margin-top:16px}
  .warn{background:#2a2210;border:1px solid #6a5a2b;padding:14px;border-radius:10px;margin-top:16px}
  code{background:#1c202b;padding:2px 6px;border-radius:6px}
</style>
</head>
<body>
<h1>📦 نصب CubePay روی ربات Mirzabot</h1>

<?php if ($error): ?>
  <div class="err">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($log): ?>
  <div class="ok">
    <?= implode('<br>', array_map('htmlspecialchars', $log)) ?>
  </div>
  <div class="warn">
    ⚠️ نصب تموم شد. همین الان این فایل (<code>install.php</code>) رو از روی سرور پاک کنید —
    تا وقتی هست، هرکی رمز رو بدونه می‌تونه دوباره اجراش کنه.
    <form method="post" action="?selfdestruct=1" style="margin-top:10px" onsubmit="return confirm('این فایل نصب برای همیشه پاک بشه؟');">
      <input type="password" name="password" placeholder="رمز نصب رو دوباره وارد کنید" required>
      <button type="submit">حذف خودکار همین فایل</button>
    </form>
  </div>
<?php else: ?>
  <form method="post">
    <label>رمز نصب (INSTALL_PASSWORD)</label>
    <input type="password" name="password" required>

    <label>مسیر کامل ریشه‌ی ربات روی هاست (همون پوشه‌ای که <code>config.php</code> ربات توشه)</label>
    <input type="text" name="bot_root" placeholder="/home/USERNAME/public_html/bot" required>

    <div class="row">
      <input type="checkbox" id="install_cosmetic" name="install_cosmetic" checked>
      <label for="install_cosmetic" style="margin:0">فایل‌های ظاهری پنل وب (بخش ۲) هم نصب بشه</label>
    </div>

    <label>توکن API (از @cubepy_bot → 🔗 پنل من)</label>
    <input type="text" name="token" required>

    <div class="row">
      <input type="checkbox" id="enable" name="enable" checked>
      <label for="enable" style="margin:0">همین الان درگاه فعال بشه</label>
    </div>

    <hr style="border-color:#2b3040;margin:22px 0">
    <p style="color:#8b93a7;font-size:13px">
      ⚠️ قبل از این مرحله حتماً یک‌بار پنل ادمین یا خود ربات رو باز کرده باشید تا
      <code>table.php</code> جدول <code>PaySetting</code> رو بسازه.
      اطلاعات دیتابیس رو از همون <code>config.php</code> ربات‌تون بردارید:
    </p>

    <label>DB Host</label>
    <input type="text" name="db_host" value="localhost" required>

    <label>DB Name</label>
    <input type="text" name="db_name" required>

    <label>DB User</label>
    <input type="text" name="db_user" required>

    <label>DB Password</label>
    <input type="password" name="db_pass">

    <button type="submit">شروع نصب</button>
  </form>
<?php endif; ?>

</body>
</html>
