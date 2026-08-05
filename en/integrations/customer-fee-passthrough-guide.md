[🇮🇷 فارسی](../../integrations/customer-fee-passthrough-guide.md) · 🇬🇧 English

# 🧾 Passing the fee on to the customer — implementation guide for bot platforms

This guide is for **developers of bot/panel platforms** (Mirzabot, Seamless, Zitac, and any other platform offering CubePay to its users): how to let a merchant push the gateway fee — fully or partially — onto their end customer, **with zero changes on the CubePay side**.

The ready-made [Foxima files](../../integrations/faoxima-ready-files/) already implement this pattern and can be used as the reference example.

---

## 🧠 The idea in one line

One numeric setting in the bot's admin panel plus an on/off toggle. The number is interpreted like this:

| Entered value | Meaning | Example |
|---|---|---|
| `0` to `100` | a **percentage** added onto the invoice (decimals allowed) | `9.9` → a 100,000-toman invoice becomes 109,900 |
| above `100` | a **fixed amount in toman** | `5000` → a 100,000-toman invoice becomes 105,000 |

In practice this rule is unambiguous: nobody sets a fixed fee below 100 toman, and nobody wants a percentage above 100.

## ⚙️ Where to apply it

**Only when creating the order** — gross the amount up before calling `POST /pay/create-order.php` and send the result as `price_amount`:

```php
/** What the customer must pay (toman). $price = the order's base price. */
function cubepay_payable_amount(int $price, bool $feeEnabled, float $feeSetting): int
{
    if (!$feeEnabled || $feeSetting <= 0) {
        return $price;
    }
    return $feeSetting <= 100
        ? (int) ceil($price * (1 + $feeSetting / 100)) // percentage — round up
        : $price + (int) round($feeSetting);            // fixed toman amount
}
```

## ✅ Three rules that must not break

1. **Credit the wallet / deliver the service using the base price, not the invoice amount.** The extra amount only compensates the fee; crediting with the invoice amount would give the customer more credit than they bought.
2. **Callback validation needs no change.** The `sig` signature is built over the amount the gateway actually charged (the grossed-up invoice) and stays valid as before. Just don't *assume* the callback amount equals your base price.
3. **After saving the setting, echo its interpretation back to the admin with a worked example** ("9.9 → a 100,000-toman invoice becomes 109,900") so a mistaken entry is caught on the spot.

## 💡 The math note for passing a percentage fee on *fully*

CubePay's percentage fee is taken from the invoice's **gross** amount. So to pass a P% fee on completely, the setting must be `P ÷ (100 − P) × 100`, not P itself:

| Gateway fee | Setting for full pass-through |
|---|---|
| 9% (CubePay VIP — [live rates](https://cubevps.ir/fees.php)) | **9.9** |
| 3% (crypto) | **3.1** |

Show this hint in the setting's help text — the Foxima files do exactly that.

## 🚀 Why this pattern instead of a gateway-side setting?

Because the invoice amount always stays exactly what your code sent — no "magic" number is changed on the gateway side, your existing validation and crediting code is untouched, and the same single implementation works identically for **card-to-card, crypto, and VIP**.

---

Technical questions? [@cubepy_bot](https://t.me/cubepy_bot) · full API docs: [API-REFERENCE](../docs/API-REFERENCE.md) and [CUBEPAY-VIP-API-REFERENCE](../docs/CUBEPAY-VIP-API-REFERENCE.md)
