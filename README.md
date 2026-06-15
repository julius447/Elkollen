# Elkollen (Ampy) — WordPress plugin

> A free tool where a homeowner picks an electrical job and instantly gets a
> **GREEN / YELLOW / RED verdict** ("får du fixa elen själv?") with the legal
> source, practical tips, and a one-tap path to a free consultation.
> Lead magnet + SEO engine + shareable. Renders inside **Bricks** via a shortcode.

**Version 5.7.9** · PHP + vanilla JS + CSS · **no build step, no npm dependencies.**

> **Language:** all developer docs are in **English**. The product UI copy (in
> `data/behorighetskollen-data.json`) is in **Swedish** by design — it serves
> Swedish homeowners. **Do not translate the UI strings.**

---

## Start here — which reader are you?

| You are… | Read |
|---|---|
| **Chris — installing & launching the tool** | This README, then **`CHECKLIST.md`** (step-by-step). |
| **Chris's Claude Code agent** | **`CLAUDE.md`** (rules + architecture + commands), then `HANDOVER.md` for depth. |
| **Anyone wanting the full technical picture** | **`HANDOVER.md`**. |
| **What changed and when** | `CHANGELOG.md`. |

This is the **handover step**: taking the finished prototype and implementing it in
Bricks/WordPress. The design and front-end are complete and reviewed; what remains is
WordPress integration, a little backend setup, and the launch gate.

---

## The 60-second version

1. **Install:** WP → Plugins → Add New → Upload the ZIP → Activate ("Elkollen (Ampy)").
2. **Place it in Bricks.** Two layouts:
   - **Landing page (split hero):** Bricks builds the left column (H1, lead text,
     3 trust bullets, the two gradient CTAs); the right column is a **Shortcode**
     element with `[elkollen layout="hero"]`. Visual reference: `preview/hero.html`.
   - **Service pages (embedded):** a **Shortcode** element with `[elkollen]`, or
     `[elkollen jobb="golvvarme"]` to open straight into a specific verdict.
3. **Wire the lead form:** set the WP admin email (leads are emailed there), confirm
   a real SMTP/transactional mail provider, and exclude `/wp-json/ampy-bk/` from
   full-page caching. (Details in `CHECKLIST.md` Step 8 / `HANDOVER.md` §9.)
4. **Do NOT launch** until a **certified electrician (auktoriserad elinstallatör)**
   has signed off on the job matrix (`meta._pending_verification` in the data file).

Full, ordered steps with test matrix: **`CHECKLIST.md`**.

---

## What's in the box

```
ampy-behorighetskollen/
├── ampy-behorighetskollen.php        Plugin shell: [elkollen] shortcode, asset enqueue, OG meta
├── data/behorighetskollen-data.json  SINGLE SOURCE OF TRUTH — 26 jobs, rules, all copy, all links
├── assets/behorighetskollen.css      All design (scoped to .ampy-bk; 10px rem base)
├── assets/behorighetskollen.js       The whole tool (vanilla ES6)
├── assets/og/                        OG share images (drop green.png / yellow.png / red.png here)
├── includes/render.php               Server-rendered mount + crawlable no-JS fallback
├── includes/lead-endpoint.php        REST endpoint: the lead form posts here (ACTIVE)
├── preview/hero.html                 Landing-page (split-hero) prototype — Bricks reference
├── preview/index.html                Embedded-layout prototype
├── CLAUDE.md                         Brief for the Claude Code agent
├── README.md                         This file
├── HANDOVER.md                       Full technical reference
├── CHECKLIST.md                      Step-by-step install → test → launch
└── CHANGELOG.md                      Version history
```

> The two `preview/*.html` files are prototypes for judging the design and for
> rebuilding the page chrome in Bricks. **They never ship** (they carry a
> "preview only" QA bar). Production renders via the shortcode → `render.php`.

---

## How it works (one paragraph)

The shortcode prints a single mount (`.ampy-bk`) plus a crawlable no-JS fallback.
The JS reads `behorighetskollen-data.json` and renders three states in that mount:
**entry** (search + room dropdown + popular-job chips + a "see all 26" drawer),
**question** (for conditional jobs), and **verdict** (GREEN/YELLOW/RED badge + the
legal source + an explanation/tips/consequences tab + the CTA). The verdict CTA
opens an in-tool **lead form** that emails the lead to Ampy. URL state (`?jobb=…`)
makes every job its own shareable, SEO-friendly URL.

---

## The rules that must never be broken

1. **All copy / rules / links live in `data/behorighetskollen-data.json`.** Never
   edit user-facing text in PHP/JS/CSS.
2. **UI text is Swedish; no em-dashes (—).**
3. **Bump `AMPY_BK_VERSION`** (in the PHP file) on every CSS/JS change — it busts
   the browser cache. Keep it in sync with `meta.version` in the data file.
4. **Launch requires the electrician's sign-off** on the job matrix.

---

## Local preview (no WordPress needed)

```bash
cd ampy-behorighetskollen
python3 -m http.server 5176
# Landing page:  http://localhost:5176/preview/hero.html
# Embedded:      http://localhost:5176/preview/index.html
```

The preview loads the exact same CSS/JS/JSON the plugin uses in WordPress. Useful
QA links: `?jobb=byta-vagguttag` (conditional question), `?jobb=golvvarme` (red),
`?jobb=byta-armatur-dcl` (green). In the preview the lead form simulates a
successful submit (there is no WordPress backend locally).

---

## Before launch — the short list

- [ ] Electrician signs off the matrix; fill `meta.reviewed_by` + `meta.last_reviewed`,
      remove `meta._pending_verification` (and resolve the 48 §/49 § note).
- [ ] Set WP admin email; confirm authenticated SMTP / transactional mail; consider a
      CRM/CPT sink via the `ampy_bk_lead_received` hook.
- [ ] Exclude `/wp-json/ampy-bk/` from full-page caching; verify a logged-out submit
      works on a cached page.
- [ ] Run `php -l` on the three PHP files on staging.
- [ ] Add OG images to `assets/og/` (optional but recommended).
- [ ] Self-host the Google Fonts (GDPR) — see `CHECKLIST.md` Step 6.
- [ ] Walk the test matrix in `CHECKLIST.md` Step 7 on desktop + mobile.

**Rollback is trivial:** the plugin has no database tables. Deactivate it and the
tool disappears; the rest of the site is untouched.
