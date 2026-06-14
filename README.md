# Elkollen (Ampy) — WordPress plugin

> Free tool where a homeowner picks an electrical job and gets a
> **GREEN / YELLOW / RED verdict** with a legal source, tips, and a path to a
> quote. Lead magnet + SEO engine + shareable content. Renders in Bricks via a
> shortcode.

**Version 5.3.0** · pure PHP + vanilla JS + CSS · no build step · no npm deps.

> **Note on language:** all developer documentation is in English. The product
> UI copy (in `data/behorighetskollen-data.json`) is in **Swedish** by design —
> the tool serves Swedish homeowners. Do not translate the UI strings.

---

## Start here — which reader are you?

| You are… | Read |
|---|---|
| **Chris (installing the tool)** | **`CHECKLIST.md`** — step by step, install → test → launch. |
| **Claude Code implementation agent** | **`HANDOVER.md`** — full technical reference: architecture, data contract, engine, integration, pitfalls. |
| **Quick overview** | This file. |

---

## The 30-second version

1. Install the plugin (WP → Plugins → Upload ZIP → Activate).
2. In Bricks: add an **H2** ("Koppla elen") + a **text** ("Se direkt vilka eljobb
   du får göra själv.") + a **Shortcode element** `[elkollen]`.
3. Per service page: `[elkollen jobb="golvvarme"]` opens straight into that verdict.
4. **Do not launch** until a certified electrician (auktoriserad elinstallatör)
   has signed off on the matrix (see `meta._pending_verification` in the data file).

---

## File structure

```
ampy-behorighetskollen/
├── ampy-behorighetskollen.php       Shortcode, asset enqueue, OG meta
├── data/behorighetskollen-data.json THE SINGLE SOURCE OF TRUTH (26 jobs, rules, copy, links)
├── assets/behorighetskollen.css     All design (scoped to .ampy-bk)
├── assets/behorighetskollen.js      The entire tool (vanilla ES6)
├── assets/og/                       OG share images (designer drops PNGs here)
├── includes/render.php              Server-rendered mount + crawlable fallback
├── includes/lead-endpoint.php       REST endpoint (disabled by default — see HANDOVER §9)
├── preview/index.html               Local preview (NOT for production)
├── README.md                        This file
├── HANDOVER.md                      Technical reference (for the agent)
├── CHECKLIST.md                     Human checklist (for Chris)
└── CHANGELOG.md                     Version history
```

---

## The three rules that must never be broken

1. **All copy/rules/links live in `data/behorighetskollen-data.json`.** Never edit
   user-facing text in PHP/JS/CSS.
2. **Launch requires the electrician's sign-off** on the job matrix.
3. **Bump `AMPY_BK_VERSION`** (in the PHP file) on every CSS/JS change —
   cache-busting.

---

## Local preview (without WordPress)

```bash
cd ampy-behorighetskollen
python3 -m http.server 5176
# open http://localhost:5176/preview/
```

`preview/index.html` loads the same CSS/JS/JSON the plugin uses in WP. The QA bar
at the top is preview-only and is not part of the production output.
