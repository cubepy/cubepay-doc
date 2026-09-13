[🇮🇷 فارسی](../../docs/GROUP-ALERTS.md) · 🇬🇧 English

# 📢 Group alerts

Every confirmed payment is announced in a Telegram group as well, at the same time as your own private message.

**Who is it for?** Anyone with staff. Your employees see incoming payments **without having any access to your account** — not the wallet, not the cards, not the settings. Just the payment alert.

---

## Setup in 3 steps

1. In the bot, go to **⚙️ More settings ← 📢 Group alerts**. You get a 6-character code.
2. **Add [@cubepy_bot](https://t.me/cubepy_bot) to your group.**
3. In that group, send:

```
/connect YOUR-CODE
```

The bot confirms, and that's it.

> ⏳ Each code is valid for **15 minutes**. If it expires, get a fresh one from the same menu.

---

## Disconnecting

In that same group, send:

```
/disconnect
```

---

## A few notes

**The bot reads no messages from the group.** It only sends alerts. The only things it looks at are the `/connect` and `/disconnect` commands.

**The code must be sent by the account owner.** If anyone else in the group sends it, nothing is connected.

**If the group becomes unreachable** — the bot is removed, or the group is deleted — your own private message still arrives. The group alert is skipped silently and nothing stops.

**The alert text is exactly your private message** — nothing added, nothing removed. If you would rather your staff did not see details such as a low-wallet-balance warning, keep that in mind.

**One group at a time.** To move to a different group, send `/disconnect` in the old one first, then connect the new one.
