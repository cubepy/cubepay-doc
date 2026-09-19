[🇮🇷 فارسی](../../integrations/ios-shortcuts-sms-forwarding-guide.md) · 🇬🇧 English

# 📱 Forwarding bank SMS from an iPhone

A **ready-made shortcut** auto-forwards your bank's deposit SMS to CubePay, so invoices confirm within seconds — no third-party app.

> ⚠️ **Two steps, both required:** first **add the shortcut**, then create an **Automation** that runs it automatically. **Nothing works without the Automation.**
>
> 📱 The phone running this must **stay powered on and connected to the internet** (the bank SMS arrives on that phone).

---

## Step 1 — Add and prepare the shortcut

1. On your iPhone, open **[Add the CubePay shortcut](https://www.icloud.com/shortcuts/a22472a5fe254bb6a4c39414d490f2c0)** → **Add Shortcut**. You now have a shortcut named **CubePay** under **My Shortcuts**.
2. Press and hold it (or tap ⋯) → **Edit**. In the **Get Contents of URL** action, the URL ends with `PASTE_YOUR_CODE_HERE` — delete just that and put your **own connection code** in its place (copy it from the bot, "📲 Deposit confirmation method"). The final URL must read:
   `https://cubevps.ir/smspay/webhook/sms.php?secret=YOUR_CODE`
3. Tap **Done** (top-right).

## Step 2 — Create the Automation and connect your shortcut to it

1. **Shortcuts** app → **Automation** tab → **+** → **Create Personal Automation**.
2. Set the trigger to a message (**its name depends on your iOS version**):
   - **Newer iOS:** **When I receive a message** — it has a **required** **Sender** filter; tap it and enter your **bank's SMS sender number** (the one your deposit texts come from).
   - **Older iOS:** **When I Get a Message Containing...** — you can put the bank as the **From** sender, or leave it empty.
3. **Final step (the most important):** when it asks you to add an action, add **Run Shortcut** and pick **the CubePay shortcut you added in Step 1** from **My Shortcuts**. 👈 **This is where your shortcut connects to the Automation** — without it, the Automation does nothing.
4. Turn **Ask Before Running** **off** and save.

## Test

Create an invoice and pay a small amount; it should confirm automatically within seconds. For troubleshooting, use the **"🧪 Test SMS connection"** button in the bot.
