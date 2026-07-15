# 🌐 راهنمای اتصال CubePay به وردپرس / ووکامرس

این راهنما نحوه‌ی افزودن CubePay به‌عنوان یک روش پرداخت سفارشی در **ووکامرس** رو توضیح می‌ده.

> 📌 اگه به دنبال یک افزونه‌ی نصب‌شونده‌ی آماده (یک فایل zip که فقط Upload می‌کنید) هستید و اون رو ندارید، از همین کد پایین می‌تونید یک افزونه‌ی سفارشی بسازید — کافیه محتوای پایین رو در یک فایل جدید داخل `wp-content/plugins/cubepay-gateway/cubepay-gateway.php` قرار بدید و از پنل وردپرس فعالش کنید.

## پیش‌نیاز

- وردپرس + ووکامرس نصب و فعال
- توکن API از [@cubepy_bot](https://t.me/cubepy_bot)

## ساخت افزونه‌ی گیت‌وی پرداخت سفارشی

فایل `wp-content/plugins/cubepay-gateway/cubepay-gateway.php`:

```php
<?php
/**
 * Plugin Name: CubePay Gateway for WooCommerce
 * Description: پرداخت کارت‌به‌کارت با تایید خودکار از طریق CubePay
 * Version: 1.0.0
 */

add_action('plugins_loaded', 'cubepay_init_gateway_class');

function cubepay_init_gateway_class()
{
    class WC_Gateway_CubePay extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'cubepay';
            $this->icon = '';
            $this->has_fields = false;
            $this->method_title = 'CubePay (کارت به کارت)';
            $this->method_description = 'پرداخت کارت‌به‌کارت با تایید خودکار پیامکی';

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->api_token = $this->get_option('api_token');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_api_wc_gateway_cubepay', [$this, 'handle_callback']);
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => 'فعال‌سازی',
                    'type' => 'checkbox',
                    'label' => 'فعال کردن CubePay',
                    'default' => 'yes',
                ],
                'title' => [
                    'title' => 'عنوان نمایشی',
                    'type' => 'text',
                    'default' => 'پرداخت کارت به کارت',
                ],
                'api_token' => [
                    'title' => 'توکن API',
                    'type' => 'password',
                    'description' => 'توکن دریافتی از ربات @cubepy_bot',
                ],
            ];
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            $response = wp_remote_post('https://cubevps.ir/smspay/api/create-payment.php', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_token,
                ],
                'body' => json_encode([
                    'amount' => intval($order->get_total()) * 10, // تومان به ریال
                    'order_id' => (string) $order_id,
                    'callback_url' => WC()->api_request_url('WC_Gateway_CubePay'),
                    'type' => 'card',
                    'description' => 'سفارش #' . $order_id,
                ]),
                'timeout' => 15,
            ]);

            if (is_wp_error($response)) {
                wc_add_notice('خطا در اتصال به درگاه CubePay.', 'error');
                return;
            }

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (empty($body['success'])) {
                wc_add_notice('خطا: ' . ($body['message'] ?? 'نامشخص'), 'error');
                return;
            }

            $order->update_meta_data('_cubepay_authority', $body['authority']);
            $order->save();

            return [
                'result' => 'success',
                'redirect' => $body['payment_link'],
            ];
        }

        public function handle_callback()
        {
            $authority = sanitize_text_field($_REQUEST['authority'] ?? '');

            $response = wp_remote_post('https://cubevps.ir/smspay/api/verify-payment.php', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->api_token,
                ],
                'body' => json_encode(['authority' => $authority]),
                'timeout' => 15,
            ]);

            $body = json_decode(wp_remote_retrieve_body($response), true);

            if (!empty($body['success'])) {
                $order = wc_get_order($body['order_id']);
                if ($order && !$order->is_paid()) {
                    $order->payment_complete();
                }
            }

            wp_die('ok', '', ['response' => 200]);
        }
    }
}

add_filter('woocommerce_payment_gateways', function ($gateways) {
    $gateways[] = 'WC_Gateway_CubePay';
    return $gateways;
});
```

## فعال‌سازی

1. پوشه‌ی `cubepay-gateway` رو در `wp-content/plugins/` قرار بدید.
2. از پنل وردپرس → افزونه‌ها → **CubePay Gateway for WooCommerce** رو فعال کنید.
3. برید به **ووکامرس → تنظیمات → پرداخت‌ها → CubePay** و توکن API رو وارد کنید.

## نکات مهم

- مبلغ در ووکامرس معمولاً به **تومان** ذخیره می‌شه؛ کد بالا اون رو ضربدر ۱۰ می‌کنه تا به ریال تبدیل بشه — اگه فروشگاه شما مبلغ رو به ریال ذخیره می‌کنه، این ضرب رو حذف کنید.
- آدرس Callback به‌صورت خودکار از طریق `WC()->api_request_url()` ساخته می‌شه؛ نیازی به تنظیم دستی نیست.
- برای جزئیات کامل API، [docs/API-REFERENCE.md](../docs/API-REFERENCE.md) رو ببینید.

سوالی موند؟ [docs/FAQ.md](../docs/FAQ.md) یا [cube_sup](https://t.me/cube_sup).
