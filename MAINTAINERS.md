# Maintainers' guide

Conventions, structure, and hard-won context for anyone working on this repository's contents.

## What this repository is

This is a **documentation-only repository** for CubePay, a hosted payment API service (card-to-card / SMS-detected bank transfer plus USDT/TRX/TON crypto payments). There is no application source code, no package manifest, and no build/lint/test tooling here — the "product" of this repo is the Markdown documentation itself, plus a handful of ready-made PHP snippets that merchants drop into their own bots.

The actual API runs at `https://cubevps.ir` (endpoints like `/smspay/api/create-payment.php`, `/crypto/api/create-crypto-payment.php`, `/pay/create-order.php`). This repo does not contain that service's implementation — never assume you can run or test it locally. Changes here are purely to documentation, examples, and integration snippets consumed by third-party merchants/bot operators.

## Working in this repo — commands

There is no build, lint, or test suite. The only meaningful verification is manual:
- Check that Markdown renders correctly (tables, fenced code blocks, the `mermaid` diagram in `README.md`).
- Check that relative links between docs still resolve after a file move/rename (e.g. `git grep -n "\.md)" -- '*.md'` to spot links, since there's no link checker configured).
- If editing `docs/openapi.yaml`, keep it valid OpenAPI 3.0.3 (no local linter is set up — validate by eye or with an external tool if needed).
- If editing PHP snippets under `docs/examples/` or `integrations/*/*.php`, they are illustrative/copy-paste snippets, not a runnable app — there's nothing to `composer install` or execute in this repo.

## Repository structure

```
cubepay-doc/
├── README.md, START-HERE.md, CONTRIBUTING.md, SECURITY.md,
│   CODE_OF_CONDUCT.md, CHANGELOG.md          ← Persian (fa) originals — canonical
├── docs/
│   ├── API-REFERENCE.md                       ← card-to-card API (create-payment / verify-payment / callback)
│   ├── CRYPTO-API-REFERENCE.md                ← crypto + unified router API
│   ├── FAQ.md
│   ├── openapi.yaml                           ← OpenAPI 3.0.3 spec covering both APIs
│   └── examples/                              ← one runnable snippet per language/framework
│       (CubePayClient.php, php-example.php, python-example.py, node-example.js,
│        laravel-example.php, curl-example.sh)
├── integrations/                              ← how to wire CubePay into specific platforms
│   ├── generic-integration-guide.md           ← platform-agnostic, direct API integration
│   ├── wordpress-plugin-guide.md
│   ├── ios-shortcuts-sms-forwarding-guide.md
│   ├── android-sms-forwarder-guide.md          ← CubePay's own dedicated Android SMS-forwarding app. The APK is a release asset on THIS repo under the fixed tag `android-latest` (permanent URL `releases/download/android-latest/CubePay.apk`; the app's private source repo republishes it there on every release). `.github/workflows/update-apk-checksum.yml` re-syncs the SHA-256 printed in both language versions of the guide daily — don't hand-edit those hashes.
│   ├── using-both-systems-guide.md             ← running the normal and VIP paths side by side (one endpoint, two tokens)
│   ├── faoxima-guide.md                       ← CubePay is native in Foxima from v1.0.0 (Mmd-Amir/Faoxima); the guide is now just "enter your API token". The old drop-in PHP files and manual-edit guide were removed — they live in git history only
│   └── mirzabot-ready-files/                  ← CubePay is now merged upstream into Mirzabot's official repo (mahdiMGF2/mirzabot PR #75); this dir just holds a short pointer doc, no more drop-in files
└── en/                                         ← English mirror (see "Localization" below)
```

## Localization: fa is canonical, en is a partial, hand-maintained mirror

- **Persian (root-level files) is the source of truth.** New features/fixes are written in Persian first.
- `en/` mirrors nearly all root docs: `README.md`, `START-HERE.md`, `CONTRIBUTING.md`, `SECURITY.md`, `CODE_OF_CONDUCT.md`, `CHANGELOG.md`, `docs/API-REFERENCE.md`, `docs/CRYPTO-API-REFERENCE.md`, `docs/CUBEPAY-VIP-API-REFERENCE.md`, `docs/FAQ.md`, `docs/openapi.yaml`, `docs/examples/`, and the Foxima/generic/WordPress/Android-SMS/iOS-Shortcuts/using-both-systems integration guides.
- `en/` still has **no** English counterpart for: `docs/MANAGED-SETTLEMENT-ARCHITECTURE.md` (internal design rationale — the English VIP reference links to the Persian original and says so) and `integrations/mirzabot-ready-files/`.
- **There is exactly one English changelog: `en/CHANGELOG.md`.** A second, diverging copy used to sit at the repo root (`CHANGELOG.en.md`); it was consolidated into `en/CHANGELOG.md` and the leftover stub has since been deleted. Add English changelog entries only to `en/CHANGELOG.md`, and don't recreate a root-level one.
- English pages are translations maintained **by hand** and can lag behind the Persian originals. The two changelogs also diverge in numbering — the Persian `2.0.1` and the English `1.13.1` describe the same fix, for example. When translating an update, match entries by content, not by version tag.
- Every doc that has a translation links to its counterpart at the top (`🇮🇷 فارسی · [🇬🇧 English](...)`). When adding a new doc that should be bilingual, add this header to both sides and place the English file at the mirrored path under `en/`.
- The English crypto reference lives only at `en/docs/CRYPTO-API-REFERENCE.md` — there used to be a second, stale, unlinked copy at `docs/CRYPTO-API-REFERENCE.en.md` (missing the "Retries on Failure" section); it was removed since nothing linked to it. Don't recreate a root-level `.en.md` copy — keep English translations under `en/` only, and keep `en/`-internal links pointing at `en/` counterparts rather than back at the Persian root files.

## Content conventions

- Amounts in the card-payment API are in **Rial** everywhere except fields explicitly suffixed `_toman` (e.g. `pay_amount_toman`). Don't blur this distinction when editing examples or tables.
- Endpoints are `.php` paths on `cubevps.ir` (legacy per-flow endpoints under `/smspay/api/` and `/crypto/api/`, plus a newer unified entry point `POST /pay/create-order.php` that auto-selects card vs. crypto vs. a chooser page based on what the merchant enabled). Keep new endpoint documentation consistent with this naming style.
- Docs are written in an informal, emoji-headed Persian tone (`## 🚀 شروع سریع`, `## 📋 پارامترها`, etc.) with liberal use of tables for parameters/responses and fenced code blocks for requests/responses. Match this style rather than switching to plain prose when adding sections.
- `README.md` contains a Mermaid flowchart of the payment flow — update it if the flow described changes, don't let prose and diagram drift apart.
- Foxima integration is no longer a drop-in-files pattern. As of Foxima v1.0.0 the gateway is native (`cubepayCreatePayment()` in `re/rx/function/database_helpers_2.php`, settings under "💎 مالی و گزارشات" → the "🟦 کیوب‌پی" row), so `integrations/faoxima-guide.md` documents only token entry. The retired drop-in files — and the hard-won delivery-resilience fixes they carried for busy shared hosts — remain in git history if a similar pattern is ever needed for another platform.
- Cross-doc linking is relative (`../docs/API-REFERENCE.md#anchor`, `./integrations/...`); anchors reference Persian heading text (with emoji) since headings themselves are in Persian — check anchors still match after renaming a Persian heading.

## Contribution flow (from CONTRIBUTING.md / SECURITY.md)

- Contributions are docs-only fixes/improvements: typos, grammar, new-language code examples, clarifying ambiguous text, or reporting errors *in the documentation* — not in the live CubePay service itself.
- Actual CubePay service bugs go to `@cubepy_bot` on Telegram, not this repo.
- Security vulnerabilities in the API/infrastructure go through private report to `@cubepy_bot`, never as a public issue — see `SECURITY.md` for what counts as a vulnerability vs. a normal validation error.
- For large changes, open an issue first to coordinate before submitting a PR.
