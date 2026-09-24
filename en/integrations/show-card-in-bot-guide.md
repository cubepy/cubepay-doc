[🇮🇷 فارسی](../../integrations/show-card-in-bot-guide.md) · 🇬🇧 English

# 💳 Showing the card number inside the bot itself — implementation guide for bot platforms

This guide is for **developers of bot/panel platforms**: how to show the amount and card number directly inside the merchant's own bot chat, instead of sending the customer to CubePay's web payment page (`pay_page_url`) — **with zero changes on the CubePay side**, because this data is already in the invoice-creation response; it just hasn't been used until now.

---

## 🧠 The idea in one line

The merchant has an on/off setting in the management bot (`@cubepy_bot`): "show card number inside the bot". When it's on, the invoice-creation response includes `show_card_in_bot: true` plus the `card` object itself — meaning you no longer need to send the user to an external web page; you can show the amount and card right inside your own bot message.

## ⚙️ Where is it enabled?

By the merchant themselves, from inside `@cubepy_bot`: "⚙️ More settings → 💳 Payment methods → 🤖 Turn on showing card in bot". This is an account-level setting, not a request parameter — you just read the API response; turning it on/off is the merchant's own call.

## 📥 Relevant fields in the invoice-creation response

Whether you use the recommended endpoint (`POST /pay/create-order.php`) or the older card-only one (`POST /smspay/api/create-payment.php`), these fields are present in the response:

```jsonc
{
  "success": true,
  "method": "card",
  "authority": "bdc9e0497c121d6187750d53798dae81",
  "pay_page_url": "https://cubevps.ir/pay.php?authority=...",   // fallback if show_card_in_bot=false
  "pay_amount_toman": 216928,        // use exactly this — not your own requested amount
  "show_card_in_bot": true,          // the merchant's own account setting — their call
  "card": {
    "number": "6104XXXXXXXX1234",
    "holder": "Merchant Card Holder Name",
    "sheba": null                    // if null, don't show a Sheba line/button at all
  },
  "expires_at": "2026-09-24T13:45:00+03:30",
  "expires_in_minutes": 15
}
```

## ✅ Rules that must not break

1. **Always show exactly `pay_amount_toman` from this response, never your own requested amount.** This number includes a few extra toman for the "uniqueness offset" that makes automatic bank-SMS matching possible — showing your own rounded amount breaks automatic confirmation.
2. **Never cache the `card` object from a previous invoice.** If the merchant has card rotation on, each invoice may get a different card — always show the `card` from *this* invoice's own response.
3. **Don't hardcode `show_card_in_bot`.** Always read it from the API response; if it's `false`/`null`, fall back to sending `pay_page_url` (the web payment page link) instead — this is the merchant's decision, not yours.
4. **Payment verification doesn't change at all.** Regardless of whether you showed the card in-bot or opened the web page, use the same `authority` for `POST /smspay/api/verify-payment.php` or callback polling — the bank-SMS detection/confirmation mechanism is completely independent of how you display it.
5. **Sheba only for instant transfers.** If `card.sheba` has a value, always pair it with a short warning ("only use Sheba if your transfer is instant") — a regular Paya/Satna transfer can arrive late and let the invoice expire.

## 💬 Sample message template (Telegram, HTML)

```
💳 <b>Payment</b>
Amount: <b>{pay_amount_toman}</b> toman

Card number: <code>{card.number}</code>
Holder: {card.holder}

⚠️ Deposit exactly this amount — automatic confirmation only works with this exact figure.
⏳ This invoice is valid for {expires_in_minutes} more minutes.
```

(Optional but recommended: get an "I understand" acknowledgment from the user before showing this message — the same pattern CubePay's own web payment page now uses, to cut down on wrong-amount deposits.)

## 🚀 Why this way, and not a separate endpoint?

Because no new endpoint is needed — the data is already in the same invoice-creation response. All you do is add one condition: instead of always redirecting to `pay_page_url`, check `show_card_in_bot` and, when it's true, render that same data inside your own message.

---

Technical question? [@cubepy_bot](https://t.me/cubepy_bot) · Full API docs: [docs/API-REFERENCE.md](../../docs/API-REFERENCE.md)
