# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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
├── CHANGELOG.en.md                            ← English changelog (kept separately, see below)
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
│   ├── faoxima-integration-guide.md           ← manual edit guide for the Foxima bot codebase
│   ├── faoxima-ready-files/                   ← drop-in PHP files that replace Foxima's own files
│   └── mirzabot-ready-files/                  ← CubePay is now merged upstream into Mirzabot's official repo (mahdiMGF2/mirzabot PR #75); this dir just holds a short pointer doc, no more drop-in files
└── en/                                         ← English mirror (see "Localization" below)
```

## Localization: fa is canonical, en is a partial, hand-maintained mirror

- **Persian (root-level files) is the source of truth.** New features/fixes are written in Persian first.
- `en/` mirrors most — but not all — root docs (`README.md`, `START-HERE.md`, `CONTRIBUTING.md`, `SECURITY.md`, `CODE_OF_CONDUCT.md`, `CHANGELOG.md`, `docs/API-REFERENCE.md`, `docs/CRYPTO-API-REFERENCE.md`, `docs/FAQ.md`, and the Foxima/generic/WordPress integration guides).
- `en/` currently has **no** English counterpart for: `docs/openapi.yaml`, `docs/examples/`, `integrations/ios-shortcuts-sms-forwarding-guide.md`, and `integrations/mirzabot-ready-files/`.
- English pages are translations maintained **by hand** and lag behind the Persian originals — e.g. at the time of writing, `CHANGELOG.md` (fa) is at version `2.0.1` while `CHANGELOG.en.md` is at `1.13.1`, and content further back in the two changelogs also diverges in numbering. When translating an update, don't assume version numbers line up 1:1 between the two files — check the actual entry content, not just the version tag.
- Every doc that has a translation links to its counterpart at the top (`🇮🇷 فارسی · [🇬🇧 English](...)`). When adding a new doc that should be bilingual, add this header to both sides and place the English file at the mirrored path under `en/`.
- The English crypto reference lives only at `en/docs/CRYPTO-API-REFERENCE.md` — there used to be a second, stale, unlinked copy at `docs/CRYPTO-API-REFERENCE.en.md` (missing the "Retries on Failure" section); it was removed since nothing linked to it. Don't recreate a root-level `.en.md` copy — keep English translations under `en/` only.

## Content conventions

- Amounts in the card-payment API are in **Rial** everywhere except fields explicitly suffixed `_toman` (e.g. `pay_amount_toman`). Don't blur this distinction when editing examples or tables.
- Endpoints are `.php` paths on `cubevps.ir` (legacy per-flow endpoints under `/smspay/api/` and `/crypto/api/`, plus a newer unified entry point `POST /pay/create-order.php` that auto-selects card vs. crypto vs. a chooser page based on what the merchant enabled). Keep new endpoint documentation consistent with this naming style.
- Docs are written in an informal, emoji-headed Persian tone (`## 🚀 شروع سریع`, `## 📋 پارامترها`, etc.) with liberal use of tables for parameters/responses and fenced code blocks for requests/responses. Match this style rather than switching to plain prose when adding sections.
- `README.md` contains a Mermaid flowchart of the payment flow — update it if the flow described changes, don't let prose and diagram drift apart.
- The `integrations/faoxima-ready-files/` guide documents a "replace these exact files at these exact paths in a specific third-party bot codebase" pattern. It separates **required/functional** files from **cosmetic/optional** ones (button text only) and calls out any accompanying SQL statement needed. Preserve that required-vs-cosmetic split when updating it or adding a new platform that still needs this pattern. `integrations/mirzabot-ready-files/` used to follow the same pattern but CubePay is now merged upstream into Mirzabot directly (mahdiMGF2/mirzabot PR #75) — that directory is just a short pointer/status doc now, not a file-replacement guide.
- Cross-doc linking is relative (`../docs/API-REFERENCE.md#anchor`, `./integrations/...`); anchors reference Persian heading text (with emoji) since headings themselves are in Persian — check anchors still match after renaming a Persian heading.

## Contribution flow (from CONTRIBUTING.md / SECURITY.md)

- Contributions are docs-only fixes/improvements: typos, grammar, new-language code examples, clarifying ambiguous text, or reporting errors *in the documentation* — not in the live CubePay service itself.
- Actual CubePay service bugs go to `@cubepy_bot` on Telegram, not this repo.
- Security vulnerabilities in the API/infrastructure go through private report to `@cubepy_bot`, never as a public issue — see `SECURITY.md` for what counts as a vulnerability vs. a normal validation error.
- For large changes, open an issue first to coordinate before submitting a PR.
