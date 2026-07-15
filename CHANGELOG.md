# تغییرات نسخه‌ها

این پروژه از [Semantic Versioning](https://semver.org/) پیروی می‌کند.

## [Unreleased]
### Changed
- بروزرسانی `docs/API-REFERENCE.md`، `docs/examples/CubePayClient.php` و `docs/openapi.yaml` با نسخه‌ی نهایی و کامل‌تر (شامل `handleCallback()` در SDK و مثال‌های embedded در OpenAPI)
- بازسازی کامل ساختار مستندات: تفکیک به پوشه‌های `docs/` و `integrations/`
- README.md به یک صفحه‌ی فهرست (Hub) کوتاه تبدیل شد
- افزودن بنر دمو در README

### Added
- `START-HERE.md` برای راهنمای شروع سریع کاربران تازه‌کار
- `docs/API-REFERENCE.md` (مرجع کامل فنی، جدا از README)
- `docs/FAQ.md` (سوالات متداول و Troubleshooting)
- `docs/openapi.yaml` (اسپک OpenAPI 3.0)
- `docs/examples/` شامل نمونه کد PHP، Python، Node.js، Laravel، cURL و کلاینت کامل PHP
- `integrations/generic-integration-guide.md`
- `integrations/wordpress-plugin-guide.md`
- `integrations/faoxima-integration-guide.md` (ویرایش دستی)
- `integrations/faoxima-ready-files/faoxima-ready-files-guide.md` (نصب با فایل آماده)
- CONTRIBUTING.md، SECURITY.md، CODE_OF_CONDUCT.md، LICENSE

## [1.0.0] - تاریخ انتشار اول
### Added
- مستندات کامل create-payment API
- مستندات کامل verify-payment API
- مستندات callback / webhook
- نمونه کد PHP، Python، Node.js، cURL
- راهنمای دریافت توکن از ربات @cubepy_bot
