<?php

if (!function_exists('faoxima_dedup_error_log')) {
    function faoxima_dedup_error_log($key, $message, $ttl = 3600) {
        $cacheDir = sys_get_temp_dir() . '/faoxima_log_dedup';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0700, true);
        }
        $cacheFile = $cacheDir . '/' . md5($key);
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            return false;
        }
        @touch($cacheFile);
        error_log($message);
        return true;
    }
}

/**
 * 🔌 آزادسازیِ موقتِ اتصال‌های دیتابیس در طولِ تماسِ HTTP با پنل.
 *
 * چرا: هر درخواستِ پرداخت دو اتصال باز می‌کند (mysqli + PDO)، بعد ۵ تا ۱۰
 * ثانیه منتظرِ پاسخِ پنل می‌مانَد و در تمامِ این مدت هر دو اتصال باز ولی
 * بلااستفاده‌اند. با چند خریدِ هم‌زمان، سقفِ max_user_connections پر می‌شود
 * و درخواست‌های بعدی خطای ۱۲۰۳ می‌گیرند — سرویس در پنل ساخته می‌شود ولی
 * ثبت و اطلاع‌رسانی‌اش انجام نمی‌شود.
 *
 * اینجا درست قبل از curl_exec اتصال‌ها بسته و بلافاصله بعدش دوباره باز
 * می‌شوند. مدتِ نگه‌داشتن از ~۱۰ ثانیه به ~۰.۱ ثانیه می‌رسد.
 *
 * ⚠️ CurlRequest فقط برای APIهای پنل استفاده می‌شود (مرزبان، مرزنشین،
 * هیدیفای، ...). تماس‌های تلگرام از مسیرِ دیگری می‌روند، پس این کار
 * روی آن‌ها اثری ندارد.
 *
 * برای خاموش‌کردن، در config.php بنویسید:
 *   define('BOT_KEEP_DB_DURING_PANEL_CALL', true);
 */
if (!function_exists('faoxima_db_release_for_panel_call')) {
    function faoxima_db_release_for_panel_call(): array
    {
        $state = ['released' => false, 'config' => null];

        if (defined('BOT_KEEP_DB_DURING_PANEL_CALL') && BOT_KEEP_DB_DURING_PANEL_CALL === true) {
            return $state;
        }

        $configFile = __DIR__ . '/config.php';
        if (!is_file($configFile)) {
            return $state; // ساختارِ استاندارد نیست — دست نزن
        }

        try {
            // 🚨 اگر تراکنشی باز است، بستنِ اتصال آن را بی‌صدا rollback
            // می‌کند. در چنین حالتی هرگز اتصال را رها نمی‌کنیم.
            if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO && $GLOBALS['pdo']->inTransaction()) {
                return $state;
            }

            if (isset($GLOBALS['connect']) && $GLOBALS['connect'] instanceof mysqli) {
                @$GLOBALS['connect']->close();
                $GLOBALS['connect'] = null;
                $state['released'] = true;
            }
            if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
                // PDO با از بین رفتنِ آخرین ارجاع بسته می‌شود. در این نقطه
                // پشته‌ی فراخوانی فقط `global $pdo` دارد (که ارجاع به همین
                // متغیر است)، پس refcount واقعاً صفر می‌شود.
                $GLOBALS['pdo'] = null;
                $state['released'] = true;
            }

            if ($state['released']) {
                $state['config'] = $configFile;
            }
        } catch (Throwable $e) {
            // آزادسازی یک بهینه‌سازی است، نه یک ضرورت — اگر نشد، بی‌خیال
            $state = ['released' => false, 'config' => null];
        }

        return $state;
    }
}

if (!function_exists('faoxima_db_restore_after_panel_call')) {
    function faoxima_db_restore_after_panel_call(array $state): void
    {
        if (empty($state['released']) || empty($state['config'])) {
            return;
        }

        // سه تلاش با مکثِ فزاینده — دیتابیس ممکن است همان لحظه شلوغ باشد
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                // ⚠️ include داخلِ تابع، متغیرها را در scope همین تابع
                // می‌سازد نه global — پس بعدش دستی به $GLOBALS می‌دهیم.
                $connect = null;
                $pdo = null;
                include $state['config'];

                if (isset($pdo) && $pdo instanceof PDO) {
                    $GLOBALS['pdo'] = $pdo;
                    if (isset($connect) && $connect instanceof mysqli) {
                        $GLOBALS['connect'] = $connect;
                    }
                    return;
                }
            } catch (Throwable $e) {
                // تلاشِ بعدی
            }
            usleep(200000 * $attempt); // ۰.۲ ، ۰.۴ ثانیه
        }

        faoxima_dedup_error_log(
            'db_reconnect_failed',
            'faoxima: اتصال دیتابیس بعد از تماس با پنل برقرار نشد — این درخواست ناتمام می‌ماند.',
            300
        );
    }
}

class CurlRequest {
    private $url;
    private $headers = [];
    private $timeout = null;
    private $authToken = null;
    private $api_key = null;
    private $cookie = null;
    public function __construct($url) {
        $this->url = $url;
    }

    public function setTimeout($seconds) {
        $this->timeout = $seconds;
    }

    public function setHeaders(array $headers) {
        $this->headers = array_merge($this->headers, $headers);
    }

    public function setBearerToken($token) {
        $this->authToken = $token;
    }

    public function api_key($token) {
        $this->api_key = $token;
    }

    public function setCookie($cookieStr) {
        $this->cookie = $cookieStr;
    }

    private function prepareHeaders() {
        $headers = $this->headers;

        if ($this->authToken) {
            $headers[] = "Authorization: Bearer {$this->authToken}";
        }
        if ($this->api_key) {
            $headers[] = "X-API-Key: {$this->api_key}";
        }

        return $headers;
    }

    private function execute($method, $data = null) {
        $this->timeout = !$this->timeout ? 8 : $this->timeout;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 60);
        curl_setopt($ch, CURLOPT_TCP_KEEPINTVL, 30);


        $verifyTls = defined('BOT_CURL_VERIFY_TLS') && BOT_CURL_VERIFY_TLS === true;
        if ($verifyTls) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if (function_exists('faoxima_apply_curl_proxy')) {
            faoxima_apply_curl_proxy($ch, 'panel');
        }

        $finalHeaders = $this->prepareHeaders();
        if (!empty($finalHeaders)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $finalHeaders);
        }
        if ($this->cookie) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
        }
        if ($data) {
            if (is_array($data)) {
                $data = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // اتصال‌های دیتابیس در طولِ این تماس لازم نیستند — رهاشان می‌کنیم
        // تا سقفِ max_user_connections را بی‌دلیل اشغال نکنند.
        $dbState = faoxima_db_release_for_panel_call();
        $response = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlErrMsg = $curlErrNo ? curl_error($ch) : '';
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        faoxima_db_restore_after_panel_call($dbState);
        if ($curlErrNo) {
            $error = $curlErrMsg;
            $host = parse_url($this->url, PHP_URL_HOST);
            $port = parse_url($this->url, PHP_URL_PORT);
            $dedupKey = 'curlerr|' . ($host ?: $this->url) . ':' . ($port ?: '') . '|' . $error;
            faoxima_dedup_error_log(
                $dedupKey,
                sprintf('CurlRequest error calling %s: %s (HTTP code: %s)', $this->url, $error, var_export($httpCode, true))
            );
            curl_close($ch);
            return [
                'status' => $httpCode,
                'body' => $response,
                'error' => $error,
            ];
        }
        curl_close($ch);


        $rxLogAll = defined('BOT_CURL_LOG_ALL_HTTP_ERRORS') && BOT_CURL_LOG_ALL_HTTP_ERRORS === true;
        if ($httpCode === 0 || $httpCode >= 500 || ($rxLogAll && $httpCode >= 400)) {
            $host = parse_url($this->url, PHP_URL_HOST);
            $port = parse_url($this->url, PHP_URL_PORT);
            $dedupKey = 'curlhttp|' . ($host ?: $this->url) . ':' . ($port ?: '') . '|' . $httpCode;
            faoxima_dedup_error_log(
                $dedupKey,
                sprintf('CurlRequest call to %s returned HTTP code %s', $this->url, var_export($httpCode, true))
            );
        }

        $upstreamDownCodes = [502, 503, 504, 520, 521, 522, 523, 524, 525, 526, 527];
        if (in_array((int)$httpCode, $upstreamDownCodes, true)) {
            return [
                'status' => $httpCode,
                'body'   => $response,
                'error'  => sprintf('Panel temporarily unavailable (HTTP %d).', (int)$httpCode),
            ];
        }

        return [
            'status' => $httpCode,
            'body' => $response
        ];
    }

    public function get() {
        return $this->execute("GET");
    }

    public function post($data) {
        return $this->execute("POST", $data);
    }

    public function put($data) {
        return $this->execute("PUT", $data);
    }

    public function delete($data = null) {
        return $this->execute("DELETE", $data);
    }
    public function PATCH($data = null){
        return $this->execute('PATCH',$data);
    }
}

