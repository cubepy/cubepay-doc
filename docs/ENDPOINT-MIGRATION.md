🇮🇷 فارسی · [🇬🇧 English](../en/docs/ENDPOINT-MIGRATION.md)

# 🔀 راهنمای مهاجرت به روتر یکپارچه

> **اگر می‌خواهید CubePay VIP بگیرید و اتصال فعلی‌تان قدیمی است، اول این صفحه را انجام دهید — ۵ دقیقه کار است.**

## 🧭 الان روی کدام آدرس هستید؟

| | آدرس | مبلغ | چه توکنی را می‌فهمد |
|---|---|---|---|
| 🕰 قدیمی — کارتی | `/smspay/api/create-payment.php` | **ریال**، فیلد `amount` | فقط عادی |
| 🕰 قدیمی — کریپتو | `/crypto/api/create-crypto-payment.php` | تومان | فقط عادی |
| ✅ جدید — روتر یکپارچه | `/pay/create-order.php` | **تومان**، فیلد `price_amount` | **عادی و VIP هر دو** |

آدرس‌های قدیمی حذف نمی‌شوند و اتصال فعلی‌تان با توکن عادی همچنان کار می‌کند. ولی:

- توکن `vip_` فقط روی روتر یکپارچه معتبر است — روی آدرس قدیمی خطای راهنما می‌گیرید.
- روی روتر، **هر دو توکن روی یک آدرس** کار می‌کنند و می‌توانید هر لحظه بینشان جابه‌جا شوید ([راهنمای هر دو سیستم با هم](../integrations/using-both-systems-guide.md)).

## 🛠 مهاجرت — دقیقاً دو تغییر

### ۱) آدرس

```diff
- https://cubevps.ir/smspay/api/create-payment.php
+ https://cubevps.ir/pay/create-order.php
```

### ۲) واحد و نام فیلد مبلغ

```diff
- "amount": 500000        ← ریال (۵۰ هزار تومان)
+ "price_amount": 50000   ← تومان (همان ۵۰ هزار تومان)
```

⚠️ **این را جا نیندازید** — اگر آدرس را عوض کنید ولی همچنان ریال بفرستید، همه‌ی فاکتورهایتان ۱۰ برابرِ قیمت واقعی ساخته می‌شوند.

بقیه‌ی فیلدها (`order_id`، `callback_url`) همان قبلی‌اند.

## ✅ قبل و بعد — یک نمونه‌ی کامل

قبل (قدیمی):

```bash
curl -X POST https://cubevps.ir/smspay/api/create-payment.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount": 500000, "order_id": "ORD-1", "callback_url": "https://yoursite.com/cb.php"}'
```

بعد (روتر یکپارچه):

```bash
curl -X POST https://cubevps.ir/pay/create-order.php \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"price_amount": 50000, "order_id": "ORD-1", "callback_url": "https://yoursite.com/cb.php"}'
```

پاسخ همچنان `authority` دارد، پس کد `verify-payment.php` فعلی‌تان **بدون تغییر** کار می‌کند. (فقط فاکتورهای VIP به‌جای `authority`، فیلد `invoice_uid` می‌گیرند و وضعیتشان از `check-order-status.php` خوانده می‌شود — [جزئیات](./CUBEPAY-VIP-API-REFERENCE.md).)

## 🤖 از فایل‌های آماده استفاده می‌کنید؟

- **Foxima:** نسخه‌ی فعلی [فایل‌های آماده](../integrations/faoxima-ready-files/) از قبل روی روتر است — فقط فایل‌های اجباری را با نسخه‌ی جدید جایگزین کنید و تمام.
- **وردپرس / راهنمای عمومی / Mirzabot رسمی:** از اول روی روتر بوده‌اند — کاری ندارید.

## ❓ از کجا بفهمم مشکلم همین است؟

اگر با توکن `vip_` این پیام را می‌گیرید، دقیقاً همین‌جایید:

```
این یک توکن CubePay VIP است و روی این آدرس (API قدیمی کارت‌به‌کارت) کار نمی‌کند...
```

دو تغییر بالا را انجام دهید، حل می‌شود.

---

- 👑 مرجع کامل VIP: [`CUBEPAY-VIP-API-REFERENCE.md`](./CUBEPAY-VIP-API-REFERENCE.md)
- 🔀 هر دو سیستم با هم: [`using-both-systems-guide.md`](../integrations/using-both-systems-guide.md)
- 🤖 سوال دارید؟ از داخل ربات [@cubepy_bot](https://t.me/cubepy_bot) تیکت بزنید
