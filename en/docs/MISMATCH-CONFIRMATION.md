[🇮🇷 فارسی](../../docs/MISMATCH-CONFIRMATION.md) · 🇬🇧 English

# Confirming a payment mismatch without a buyer receipt

When CubePay has received the bank SMS and recorded an amount mismatch, the merchant can confirm it after checking the deposit in their bank account and establishing that it belongs to the same order. **A buyer-uploaded receipt is not required.**

## In the Telegram bot

1. Open the **«⚠️ واریز با مبلغ نامنطبق»** notification for the order.
2. Select **«🔎 بررسی و تأیید واریز همین سفارش»** (review and confirm this order's deposit).
3. Check the order number, invoice amount and deposited amount against the customer's order and your bank account.
4. Only when satisfied, select **«واریز و همین سفارش را بررسی کردم؛ تأیید»** (I checked the deposit and this order; confirm). Otherwise select Cancel.

## In the Telegram Mini App

Open **Home → «… واریز نیاز به بررسی شما داره» → the relevant order → «بررسی و تأیید واریز»**. Review the order number and both amounts in the confirmation dialog before confirming.

This is the Telegram Mini App route, not the web panel's invoice page or the Android app's delivery logs.

## Safeguards and limits

- A bank SMS and a recorded mismatch are required. This is not unrestricted manual approval of any invoice.
- The invoice must still be pending and unexpired. Ambiguous deposits, including several plausible invoices, remain blocked.
- A consumed deposit/SMS or an already-paid invoice cannot create another payment. Repeated confirmation must not pay the order again.
- Changes to the deposit or order evidence after the preview invalidate confirmation; reopen the review.
- Similar amounts do not prove the buyer's identity or ownership of a deposit. The merchant must establish the connection to this order; do not approve uncertain cases.

## Missing controls or old messages

Close and reopen the Mini App. For an older bot notification, select Review again to create a fresh final confirmation. No banner may simply mean there are no open mismatches; seeing a deposit in your bank account alone does not create one.

## Separate from late receipts

This change does not extend the payment deadline. The separate late-receipt route still requires a receipt and remains limited to one hour after original expiry for eligible invoices. Its rules are not removed by this change.

Confirmation records payment on the same invoice and queues the result for the store. It does not itself deliver the product: the store must verify the payment and fulfill the order only once.

[Late receipts](LATE-RECEIPTS.md) · [Online guide (Persian)](https://cubevps.ir/panel/help/panel.html#bank-mismatch-confirmation)
