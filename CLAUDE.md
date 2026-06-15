# CLAUDE.md — Elkollen (Ampy)

This file is the working brief for a Claude Code agent helping Chris implement and
maintain **Elkollen**, a WordPress plugin. Read it fully before editing anything.
For depth, follow the pointers to `HANDOVER.md`. For the human install steps, see
`CHECKLIST.md`. Version history is in `CHANGELOG.md`.

Current version: **5.7.9** (the prototype is feature-complete and pixel-reviewed;
the remaining work is WordPress/Bricks implementation + the launch gate).

---

## What this is

A free Swedish lead-magnet tool: a homeowner picks an electrical job and gets a
**GREEN / YELLOW / RED** verdict ("får du göra det själv?") with the legal source
(Elsäkerhetslagen / Elsäkerhetsverket), practical tips, and an in-tool lead form
that requests a free consultation. It is a lead magnet, an SEO surface (one tool
serves 26 job URLs via `?jobb=`), and shareable. It renders inside the **Bricks**
theme via a shortcode. Vanilla JS + CSS + PHP. **No build step, no npm.**

The business goal is qualified leads (the lead form), so the lead flow is
load-bearing — treat it with care.

---

## HARD RULES (do not break these)

1. **UI copy is Swedish; docs/comments are English.** The tool serves Swedish
   homeowners. Never translate UI strings to English. Never write developer docs
   in Swedish.
2. **No em-dashes (—) in any UI text.** Use commas or periods. (Internal
   `_verify`/`_status` data notes may contain them; they are not UI.)
3. **`data/behorighetskollen-data.json` is the single source of truth** for ALL
   copy, rules, links, and labels. Never hardcode user-facing text in PHP/JS/CSS —
   edit the data file. (A few structural strings live in JS with a `data.meta`
   fallback; prefer the data file.)
4. **Bump `AMPY_BK_VERSION`** in `ampy-behorighetskollen.php` on EVERY change to
   `assets/*.css` or `assets/*.js` (cache-busting). Keep it in sync with
   `meta.version` in the data file.
5. **Teal usage:** solid teal FILL (`--action-primary-strong`) is reserved for the
   primary CTA; teal TEXT uses `--action-primary-text` (AA contrast). The green
   verdict ramp (`--state-success`) is a warmer leafy green kept visually distinct
   from teal. Don't blur these.
6. **LAUNCH GATE:** the tool gives legal guidance. It must NOT go public until a
   certified electrician (auktoriserad elinstallatör) has signed off on the job
   matrix. See `meta._pending_verification` and `meta._consequence_verify` (48 § vs
   49 § straffparagraf is explicitly unverified). Do not remove these flags or set
   `reviewed_by` yourself.
7. **Never rename the job id `byta-gloldlampa`** (misspelled but stable; renaming
   breaks shared `?jobb=` URLs and OG links).
8. **Commit/push only when asked.** This repo deploys its prototype to GitHub Pages
   from `main`.

---

## Architecture (5 layers)

```
data/behorighetskollen-data.json   Content + rules + copy + links (source of truth)
assets/behorighetskollen.css       All styling, scoped under .ampy-bk (10px rem base: html{62.5%})
assets/behorighetskollen.js        The whole tool, vanilla ES6, class ElkollenApp
includes/render.php                Server-rendered .ampy-bk mount + crawlable no-JS fallback
includes/lead-endpoint.php         REST: POST /ampy-bk/v1/lead + GET /ampy-bk/v1/nonce (ACTIVE)
ampy-behorighetskollen.php         Plugin shell: enqueue, [elkollen] shortcode, OG meta, localize
```

- **Two render contexts:** the default *embedded* layout (service pages), and
  `layout="hero"` — a compact split-hero variant for the landing page. Hero styles
  are scoped under `.ampy-bk--hero`; the embedded layout is untouched by them.
- **Three render modes inside the tool** (one mount, `replaceChildren`): `entry`
  (search + "Välj rum" dropdown + quick-pick chips + "Se alla N jobb" drawer),
  `question` (conditional jobs), `verdict` (badge + legal source + tabs + CTA).
  Plus a **lead-form** overlay (`this.leadOpen`) opened from the verdict CTA.
- **Engine** (`resolve(job, answerIndex)` + `jobGroup(job)`): `fixed` jobs return
  their `default_verdict`; `conditional` jobs ask a question then return the chosen
  option's verdict. Pure functions; see `HANDOVER.md` §6.
- **State** = `{jobId, answerIndex}` in the URL (`?jobb=&svar=`); `leadOpen` is
  transient (not in the URL). `render()` moves focus to the new view's
  `[data-focus-target]` heading for a11y.

Read `HANDOVER.md` §4–§6 before touching the engine, data contract, or render flow.

---

## The lead flow (load-bearing — read `HANDOVER.md` §9)

The verdict's primary CTA ("Få kostnadsfri rådgivning") opens an in-tool form
(Namn, E-post, Telefon, Postnummer, GDPR consent — all required) that POSTs JSON
to `POST /wp-json/ampy-bk/v1/lead`. Key facts:

- **Fresh-nonce pattern:** `submitLead()` first GETs `/ampy-bk/v1/nonce` for a fresh
  nonce before POSTing, so a stale nonce baked into a full-page-cached page doesn't
  403 anonymous submits. **Chris must still verify on staging** that a logged-out
  visitor on a cached page can submit (exclude `/wp-json/ampy-bk/` from cache if the
  cache also caches REST GETs).
- **Endpoint protections:** fresh-nonce check, honeypot (`webbplats`), per-IP rate
  limit (15/10 min via transient — behind Cloudflare prefer edge rate limiting),
  server-side validation (`is_email`, telefon pattern, 5-digit postnummer), GDPR
  consent. Emails the admin (`get_option('admin_email')`); on `wp_mail` failure the
  payload is `error_log`-ed so a lead is never silently lost.
- **Durable sink:** `do_action('ampy_bk_lead_received', $payload)` is the hook to
  persist to a CPT / forward to a CRM. Email is the only sink by default.
- **Static prototype:** with no `window.AmpyBK` (no WordPress), `submitLead()`
  simulates success so the prototype demos the full flow.

---

## Analytics

Vendor-agnostic `track(event, props)` pushes to `window.dataLayer` (GA4/GTM) AND
dispatches a DOM `CustomEvent('elkollen:track')`. No-ops if nothing listens. Events:
`tool_view`, `job_selected`, `question_shown`, `question_answered`, `verdict_shown`
(job+color), `cta_click`, `lead_form_open`, `lead_submitted`, `verify_company_click`.
Impression events fire once per distinct view (no double-count on lead open/close or
Back/Forward).

---

## Cross-device constraints (don't regress)

- **Inputs are pinned to 16px** (`.ampy-bk__search-input`, `.ampy-bk__lead-input`)
  so iOS Safari doesn't auto-zoom on focus. Don't drop them below 16px.
- **Mobile hero grid uses `minmax(0, 1fr)`** (NOT `1fr`) so long Swedish words /
  nowrap chips can't force horizontal overflow.
- **Hero copy is `position: sticky` on desktop** (≥768px) so it doesn't jump as the
  tool panel changes height.
- **`prefers-reduced-motion`** disables block/judgment/drawer animations.
- Verdict badge is an `<h2>` (the page H1 is owned by Bricks — keep one H1).

---

## Bricks / WordPress integration (this is Chris's main task)

- **Bricks owns the page chrome**, the plugin owns only the `.ampy-bk` mount.
  Shortcodes: `[elkollen]` (embedded), `[elkollen layout="hero"]` (split-hero
  right column), `[elkollen jobb="golvvarme"]` (preselect a verdict — the SEO lever).
  `[behorighetskollen ...]` is a legacy alias of the same.
- **`preview/hero.html` is the visual + copy reference** for the landing page Chris
  rebuilds in Bricks: left column = native Bricks elements (H1, lead `<p>`, 3 trust
  bullets, the two ampy.se gradient CTAs "Kontakta oss"/phone); right column = the
  `[elkollen layout="hero"]` shortcode. `preview/index.html` shows the embedded
  layout. **Neither preview file ships** — they are prototypes (they include a
  prototype-only QA bar). Production renders via `render.php`.
- See `CHECKLIST.md` Step 3 for the exact Bricks build, and `HANDOVER.md` §8 for the
  integration contract.

---

## Commands

```bash
# Lint before finishing (no build step):
node --check assets/behorighetskollen.js          # JS syntax
python3 -m json.tool data/behorighetskollen-data.json > /dev/null   # JSON valid
php -l includes/lead-endpoint.php                 # PHP (run on staging if no local PHP)
php -l includes/render.php
php -l ampy-behorighetskollen.php

# Local preview (no WordPress needed):
cd ampy-behorighetskollen && python3 -m http.server 5176
# then open http://localhost:5176/preview/hero.html  (or /preview/index.html)
```

---

## Before you finish any change

1. Edit copy in the **data file**, not in code.
2. **Bump `AMPY_BK_VERSION`** + `meta.version` if you touched CSS/JS.
3. **Lint** (JS + JSON + PHP per above).
4. Run the **verification suite** in `HANDOVER.md` §12 (green/red/conditional
   verdicts, the lead form submit, mobile no-overflow, no console errors).
5. Keep UI Swedish, docs English, no em-dashes.

---

## Pointers

- **Deep technical reference:** `HANDOVER.md` (architecture, data contract, engine,
  integration, pitfalls, verification).
- **Human install/launch steps:** `CHECKLIST.md`.
- **Version history:** `CHANGELOG.md`.
- **Pre-launch must-dos that need staging (not code):** nonce-on-cached-page check,
  authenticated SMTP / CRM sink, `php -l`, the electrician sign-off. See
  `CHECKLIST.md` Step 8 and `HANDOVER.md` §9 / §11.
