"""
CubePay — نمونه‌ی کامل پایتون برای ربات تلگرام

این فایل کلِ مسیر را نشان می‌دهد: ساخت فاکتور، نمایش پرداخت به مشتری،
و تایید نهایی وقتی کال‌بک رسید.

نیاز: pip install requests
"""

import hashlib
import hmac
import requests

API_TOKEN = "YOUR_API_TOKEN"          # از @cubepy_bot ← «🔗 پنل من»
CALLBACK_URL = "https://yoursite.example.com/cubepay/callback"

HEADERS = {
    "Content-Type": "application/json",
    "Authorization": f"Bearer {API_TOKEN}",
}


# ---------------------------------------------------------------- ۱) ساخت فاکتور
def create_order(order_id: str, amount_toman: int) -> dict:
    """
    روتر یکپارچه: بسته به تنظیمات حساب شما در ربات
    («🏪 فروشگاه من ← 💳 روش‌های پرداخت») خودش تصمیم می‌گیرد
    فاکتور کارتی بسازد، کریپتویی بسازد، یا صفحه‌ی انتخاب نشان بدهد.

    ⚠️ مبلغ اینجا «تومان» است، نه ریال.
    """
    payload = {
        "order_id": order_id,
        "price_amount": amount_toman,
        "callback_url": CALLBACK_URL,
        # چون فروش از طریق ربات است، بعد از پرداخت مرورگر مشتری را
        # به جایی ریدایرکت نکن؛ نتیجه را خودِ ربات اعلام می‌کند.
        "redirect_after_payment": False,
    }
    r = requests.post(
        "https://cubevps.ir/pay/create-order.php",
        json=payload, headers=HEADERS, timeout=30,
    )
    return r.json()


# ------------------------------------------------- ۲) نمایش پرداخت به مشتری
def build_payment_message(result: dict) -> tuple:
    """
    خروجی: (متن پیام, لینک پرداخت)

    اگر مسیر کارتی باشد، پاسخ خودِ شماره کارت و مبلغ دقیق را هم
    برمی‌گرداند؛ پس می‌توانید مشتری را اصلاً از تلگرام بیرون نبرید.
    """
    pay_url = result["pay_page_url"]
    card = result.get("card") or {}

    # فیلد show_card_in_bot همان تنظیمِ خودتان در ربات است
    # («🤖 نمایش کارت در ربات»). می‌توانید پیرویش کنید یا نادیده بگیرید.
    if result.get("show_card_in_bot") and card.get("number"):
        amount = f"{int(result['pay_amount_toman']):,}"
        holder = card.get("holder") or "-"
        text = (
            "💳 <b>پرداخت کارت‌به‌کارت</b>\n\n"
            f"🔢 شماره کارت:\n<code>{card['number']}</code>\n"
            f"👤 به نام: {holder}\n\n"
            f"💰 مبلغ دقیق: <b>{amount}</b> تومان\n"
            f"⏳ مهلت پرداخت: {result['expires_in_minutes']} دقیقه\n\n"
            "⚠️ مبلغ باید رقم‌به‌رقم دقیق باشد — تایید خودکار فقط با همین عدد انجام می‌شود."
        )
        return text, pay_url

    return "برای پرداخت روی دکمه‌ی زیر بزنید 👇", pay_url


# ------------------------------------------------------------- ۳) تایید نهایی
def verify_payment(authority: str) -> bool:
    """
    کال‌بک فقط یک «خبر» است، نه سند پرداخت — هر کسی می‌تواند به آدرس
    شما درخواست جعلی بفرستد. پس همیشه مستقیم از CubePay بپرسید.

    فقط اولین بار True برمی‌گرداند؛ دفعات بعد 409 می‌گیرید تا اگر
    کال‌بک دوباره آمد، سرویس را دو بار تحویل ندهید.
    """
    r = requests.post(
        "https://cubevps.ir/smspay/api/verify-payment.php",
        json={"authority": authority}, headers=HEADERS, timeout=30,
    )
    return r.status_code == 200 and bool(r.json().get("success"))


def verify_crypto_signature(order_id: str, status: str, amount: str, sig: str) -> bool:
    """کال‌بک کریپتو authority ندارد؛ به‌جایش امضا را بررسی کنید."""
    expected = hmac.new(
        API_TOKEN.encode(),
        f"{order_id}|{status}|{amount}".encode(),
        hashlib.sha256,
    ).hexdigest()
    return hmac.compare_digest(expected, sig or "")


# --------------------------------------------------------------------- نمونه
if __name__ == "__main__":
    res = create_order(order_id="ORD-123", amount_toman=50_000)

    if not res.get("success"):
        print("خطا:", res.get("message"))
        raise SystemExit(1)

    print("روش:", res["method"])          # card / crypto / choice
    message, url = build_payment_message(res)
    print(message)
    print("لینک پرداخت:", url)

    # این را در هندلرِ callback_url خودتان صدا بزنید، نه اینجا:
    # if verify_payment(authority_from_callback):
    #     deliver_service()
