<div align="center"><img src="../../../cubepay-logo.png" alt="CubePay" width="220"></div>

# 📦 Ready-Made CubePay Connection for Foxima Bots (File Install, Not Manual Editing)

This guide is for merchants who built their sales bot with **Foxima** code (or similar forks). Instead of editing the code line-by-line yourself, just replace these ready-made files — faster and less error-prone.

⚠️ **Before you start:** back up your bot's folder. These files **completely replace** your current ones; if you'd already made changes to these same 5 files, they'll be lost.

---

## 🔴 Part 1 — Functional Files (Required, CubePay Won't Work Without These)

| File | Replace at path |
|---|---|
| `business_logic_1.php` | `re/rx/function/business_logic_1.php` |
| `successful.php` | `payment/ZarinPay/successful.php` |

These two files are **the heart of the CubePay connection** — they handle invoice creation and payment confirmation. They contain no personal information (token, password); you enter the token separately inside the bot (explained below).

---

## 🔵 Part 2 — Cosmetic/Keyboard Files (Optional, Just to Remove the "ZarinPay" Name)

| File | Replace at path |
|---|---|
| `bootstrap_1.php` | `re/rx/admin/bootstrap_1.php` |
| `bootstrap_2.php` | `re/rx/admin/bootstrap_2.php` |
| `layouts_1.php` | `re/rx/keyboard/layouts_1.php` |
| `settings.php` | `re/rx/admin/settings.php` |
| `service_keyboard.php` | `panel/service_keyboard.php` |

These five files only change the **button text** (in the admin bot chat and the web panel) from "ZarinPay" to "CubePay" — they don't change any logic. If the "ZarinPay" name in your own admin panel doesn't matter to you, you can skip replacing these 5 files entirely — CubePay works fine without them too.

📌 **One thing unrelated to naming:** the `bootstrap_2.php` file has something else — the "Connection tutorial" button in the original Foxima version shows a message with a **personal contact link belonging to ZarinPay's original creator** (not you, not CubePay). If you don't want your customers seeing a random contact link, we recommend replacing this file even if you don't care about the "ZarinPay" name.

📌 **An SQL step is also needed** (if you're replacing Part 2): another instance of "ZarinPay" is stored in the database itself (not in a file), which affects both the customer's purchase button and a list in the web panel. Run this one line in phpMyAdmin (SQL tab):

```sql
UPDATE textbot SET text = '🟠 CubePay' WHERE id_text = 'zarinpey';
```

---

## ✅ Installation Steps

### Step 1 — Backup
Make a copy of your bot's folder.

### Step 2 — Replace the files
Depending on whether you want just Part 1 or all 5 files, replace them at exactly the paths listed above.

### Step 3 — Register with CubePay
If you're not a merchant yet, message [@cubepy_bot](https://t.me/cubepy_bot) and register (business name, phone number, terms acceptance). After admin approval, grab your API token from "🔗 My Panel".

### Step 4 — Enter the token into your own bot
In your own (Foxima) bot, go to:

```
Admin panel → Rial gateways → ZarinPay (or CubePay, if you replaced Part 2 too)
```

And configure:
- ✅ Enable: on
- 🔑 Token: the one you got from CubePay
- (Optional) min/max amount, cashback

### Step 5 — Set up your card and SMS webhook in CubePay
In `@cubepy_bot`:
- "💳 Manage Cards" → add your own card
- "📲 SMS Connection Guide" → set up the Forwarder app on your phone following the guide

⚠️ **Don't forget:** the phone running this app must always stay connected to the internet, or no transactions will be confirmed.

### Step 6 — Test
Try a small wallet top-up (e.g. 10,000 Toman) in your own bot. It should:
1. Open the payment link and show the exact amount (with a few extra Toman)
2. Get auto-confirmed within a few seconds after the card-to-card deposit
3. Have your bot automatically deliver the service/credit

---

## ❓ If the Files Don't Work

If you get errors after replacing them, your Foxima version probably differs from the one these files were built for (e.g. you'd already manually changed something else in these same files). In that case:
- Restore your backup
- Use the **[manual editing guide](../faoxima-integration-guide.md)** instead — change just those 2 specific lines in your own file, without replacing the whole file

If you're still stuck, send us the `error_log` next to the PHP files so we can look into it more precisely.
