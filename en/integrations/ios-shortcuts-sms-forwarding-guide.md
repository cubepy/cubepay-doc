[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 📱 Forwarding bank SMS from an iPhone (iOS Shortcuts)

On iPhone a **ready-made shortcut** lets you auto-forward your bank's deposit SMS to CubePay, so invoices confirm within seconds — no third-party app.

> ⚠️ **This method has two parts, both required:**
> **1) the ready-made shortcut** (sends the SMS) + **2) an Automation** (runs it automatically).
> **Nothing works without the Automation** — the shortcut never runs on its own.

> 📱 The phone running this must **stay powered on and connected to the internet** (the bank SMS arrives on that phone).

---

## Step 1 — Add the ready-made shortcut

1. On your iPhone, open **[Add the CubePay shortcut](https://www.icloud.com/shortcuts/a22472a5fe254bb6a4c39414d490f2c0)** → **Add Shortcut**.
2. It lands in your list. Press and hold it (or tap ⋯) → **Edit**.
3. In the **Get Contents of URL** action, the URL ends with `PASTE_YOUR_CODE_HERE`. Delete just that and put your **own connection code** in its place (copy it from your account panel, "📲 Deposit confirmation method"). The final URL must read:
   `https://cubevps.ir/smspay/webhook/sms.php?secret=YOUR_CODE`
4. Tap **Done** (top-right).

## Step 2 — Create the Automation ⚠️ (nothing works without it)

1. **Shortcuts** app → **Automation** tab → **+** → **Create Personal Automation**.
2. Pick the trigger (**its name depends on your iOS version**):
   - **Newer iOS:** **When I receive a message** — it has a **Sender** filter that is **required**; tap it and enter your **bank's SMS sender number** (the one your deposit texts come from), or it won't turn on.
   - **Older iOS:** **When I Get a Message Containing...** (filters are optional, you may leave them empty).
3. Set the action to **Run** this **CubePay shortcut**.
4. Turn **Ask Before Running** **off** and save.

## Test

Create an invoice and pay a small amount; it should confirm automatically within seconds. For troubleshooting, use the **"🧪 Test SMS connection"** button in the bot.

---

## 🆘 Only if the ready-made shortcut won't open — build it by hand

If the ready link won't open, build the shortcut yourself. In the same Step 2 Automation, instead of picking the ready shortcut:

1. **Add Action** → **Get Contents of URL**, URL: `https://cubevps.ir/smspay/webhook/sms.php`
2. Tap **Show More** → **Method: POST** and **Request Body: Form**.
3. Add **two fields** (type **Text** each time):
   - `secret` = your own connection code
   - `text` = the **Shortcut Input** variable (don't type it — pick it from the bar above the keyboard so it becomes a **blue** token)
4. Turn **Ask Before Running** off and save.

> 🚫 Two common mistakes: (1) typing `Shortcut Input` by hand instead of picking the variable (the token must be **blue**). (2) putting `secret` as a separate Text action — it must be **inside the form fields** of the request.

If it still won't work, try the **[SMS Forwarder](https://apps.apple.com/us/app/sms-forwarder-forward-sms/id6693285061)** app: set the forward destination to **URL** and enter your webhook URL (`...sms.php?secret=...`).
