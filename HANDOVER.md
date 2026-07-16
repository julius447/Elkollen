# Elkollen — Technical handover (for the implementation agent)

> **Reader:** Chris's Claude Code implementation agent (and any developer who
> touches the code). This document explains *every* part of the tool:
> architecture, data contract, engine, view, WordPress/Bricks integration, the
> lead flow, known pitfalls, and how to extend it without breaking anything.
>
> **The human checklist is separate, in `CHECKLIST.md`** — that's what Chris
> follows step by step. This document is the reference the agent looks things up in.
>
> **Language:** developer docs and the guidance below are in English. The product
> UI strings live in `data/behorighetskollen-data.json` and are **Swedish** by
> design (the tool serves Swedish homeowners). Do not translate the UI copy.
> The inline comments inside `assets/*.css` and `assets/*.js` are still in
> Swedish; translate on request if needed (they are implementation notes, not
> handover documentation).

---

## 0. TL;DR (if you read only one thing)

- Current version **7.3.7** (see CHANGELOG.md). The prototype is feature-complete and pixel-reviewed;
  remaining work is Bricks/WordPress implementation + the launch gate.
- This is a **standalone WordPress plugin** exposing a shortcode `[elkollen]`
  (alias `[behorighetskollen]`); `[elkollen layout="hero"]` is the split-hero
  landing variant; `[elkollen jobb="<id>"]` preselects a verdict.
- **All copy, all rules, all links live in `data/behorighetskollen-data.json`.**
  Never edit text in PHP/JS/CSS. Edit the data file.
- **No build step, no npm deps, no external API.** Pure PHP + vanilla JS + CSS.
- The verdict CTA opens an **in-tool lead form** that POSTs to the REST endpoint
  (§9). There is a backend setup checklist (nonce/caching, SMTP) Chris must do.
- **Launch gate:** a certified electrician (auktoriserad elinstallatör) must sign
  off on the job matrix before public launch. See `meta._pending_verification`.
- **Bump `AMPY_BK_VERSION`** in `ampy-behorighetskollen.php` on every CSS/JS
  change (cache-busting). The agent should also read **`CLAUDE.md`** first.

---

## 1. What the tool does (product summary)

A homeowner selects an electrical job (e.g. "byta vägguttag" / replace a wall
outlet) and gets a categorical verdict:

- 🟢 **GREEN** — "Det här får du göra själv" (you may do this yourself)
- 🟡 **YELLOW** — "Det beror på" (it depends; used only as an intermediate — a
  conditional job asks a question and each answer resolves to green or red)
- 🔴 **RED** — "Det här kräver elektriker" (requires a certified electrician)

Each verdict shows the legal source, an explanation (✓ allowed / ✗ requires an
electrician), and either **Tips** (green) or **Consequences** (red), plus the CTAs:
"Läs mer om {jobb}" (the service page) and **"Få kostnadsfri rådgivning"** which
opens the in-tool lead form. (The share button was removed in 5.7.1; the share
code remains but is not surfaced — see §9.1.)

The tool drives three things at once: free value, SEO (internal links to the Ampy
service pages via `?jobb=`), and qualified leads (the in-tool form).

---

## 2. Non-negotiable (do not touch)

1. **The data file is the single source of truth.** No UI text is hardcoded in
   PHP/JS/CSS. To change a word, edit `data/behorighetskollen-data.json`.
2. **The verdict logic and the 26 jobs' outcomes** are verified against
   Elsäkerhetsverket (the Swedish Electrical Safety Authority, June 2026) and
   await the electrician's sign-off. Do not change a verdict without re-review.
3. **No em-dashes (—) in UI text.** Use periods/commas. (Em-dashes appear only in
   internal `_`-prefixed dev notes in the data, never in the UI.)
4. **Color discipline:** solid teal FILL (`--action-primary-strong`) is the primary
   CTA ("Få kostnadsfri rådgivning"), strongest on red; teal TEXT links use
   `--action-primary-text` (AA). The green verdict ramp (`--state-success`) is a
   warmer leafy green, kept visually distinct from teal.
5. **The page H1 + lead live OUTSIDE the tool** — native Bricks elements above/left
   of the shortcode. The tool's verdict badge is an `<h2>`, so the page keeps a
   single H1 (SEO + a11y).
6. **The QA bar** ("⌘ QA-genvägar (preview only)") exists in BOTH
   `preview/index.html` and `preview/hero.html` and must NEVER ship to production.
   Neither preview file ships — only the `.ampy-bk` mount from the shortcode
   reaches production (the QA bar + page chrome are prototype-only).

---

## 3. File tree and what each file does

```
ampy-behorighetskollen/
├── ampy-behorighetskollen.php      Plugin entry: shortcode, asset enqueue, OG meta
├── data/
│   └── behorighetskollen-data.json THE SINGLE SOURCE OF TRUTH — 26 jobs, rules, copy, links
├── assets/
│   ├── behorighetskollen.css       All design (tokens scoped to .ampy-bk)
│   ├── behorighetskollen.js        The entire tool (vanilla ES6, no build)
│   └── og/                         OG share images (designer drops PNGs here)
│       └── README.md
├── includes/
│   ├── render.php                  Server-rendered mount + crawlable fallback
│   └── lead-endpoint.php           REST: POST /lead + GET /nonce (ACTIVE — the lead form posts here)
├── preview/
│   ├── hero.html                   Landing-page (split-hero) prototype — Bricks reference (NOT production)
│   └── index.html                  Embedded-layout prototype (NOT production)
├── CLAUDE.md                       Brief for the Claude Code agent (rules + architecture + commands)
├── README.md                       Overview + reader-routing + 60-second handover
├── HANDOVER.md                     This document
├── CHECKLIST.md                    Human checklist for Chris
└── CHANGELOG.md                    Version history
```

---

## 4. Architecture — three layers + a PHP shell

```
DATA (truth)                ENGINE (logic)               VIEW (presentation)
──────────────              ──────────────               ─────────────────
behorighetskollen     ───▶  resolve(job, answer)   ───▶  ONE block, three modes:
-data.json                  jobGroup(job)                · entry  (room picker + search)
                            URL = state                  · question (intermediate step)
26 jobs, rules,             adaptive opening             · verdict (the answer)
tips, sources, rooms                                     Explanation/Tips/Consequences tabs
        ▲                                                         │
        │                                          PHP: shortcode + enqueue +
  edited after                                     render.php (crawlable fallback) +
  electrician sign-off                             dynamic OG meta per ?jobb=
```

**How it loads in WordPress (data flow):**
1. The page contains the shortcode `[elkollen]`.
2. `ampy_bk_shortcode()` runs: enqueues CSS+JS, and `wp_localize_script` injects
   the ENTIRE data file as `window.AmpyBK.data` (no extra HTTP round trip).
3. `render.php` prints the mount point `<div class="ampy-bk">` with a
   server-rendered **crawlable fallback** (all jobs as links) inside it.
4. JS boots on `DOMContentLoaded`, reads `window.AmpyBK.data`, removes the
   fallback, and renders the tool.

**Robustness (why uptime is high):**
- No external runtime API. The data is a static file bundled in the plugin.
- If JS doesn't run (old browser, JS disabled): the server-rendered fallback
  shows all jobs as real links → search engines + no-JS users still work.
- If the data file is missing/corrupt: PHP returns a clear error message instead
  of a crashed page.
- Fonts: loaded from Google Fonts via CSS but with a `system-ui` fallback → never
  breaks if Google Fonts is blocked (see §11 on GDPR/self-hosting).

---

## 5. The data contract (`data/behorighetskollen-data.json`)

### 5.1 `meta`
| Field | Purpose |
|---|---|
| `version` | Data version. Keep in sync with `AMPY_BK_VERSION` in PHP. |
| `product_name` | "Elkollen" — used in the OG title. |
| `page_heading` / `page_lead` | Reference text for the H2 + lead (placed in Bricks above the tool). `page_lead` is also used in the crawlable fallback. |
| `reviewed_by` / `last_reviewed` | Filled in by the electrician at sign-off. |
| `_pending_verification` | **Launch-gate flag.** Read it. |
| `disclaimer` | Persistent disclaimer. |
| `koppla_sakert_url` | External reference. |
| `verify_company_url` | Ampy's exact entry in Elsäkerhetsverket's company register ("verifiera oss" / verify us). |
| `ampy_offert_url` | `https://ampy.se/offert/` — all quote/expert CTAs. |
| `contact_url` | = the quote URL (green "Anlita expert?" / hire an expert). |
| `quick_picks` | 8 job IDs shown as "Vanliga eljobb" (common jobs) in the entry mode. |

### 5.2 `verdicts` (green / yellow / red)
| Field | Purpose |
|---|---|
| `label` | The verdict wording (badge). |
| `icon` | Icon key. |
| `token` | Design-system token. |
| `caveat` / `caveat_short` | The competence caveat (shown on green). |
| `consequence` | Text in the Consequences tab (red). |
| `_consequence_verify` | Dev note: the penalty paragraph 48§/49§ must be confirmed at sign-off. |
| `source` | `{ text, url }` — the **per-verdict source** (green → Elsäkerhetsverket, red → Elsäkerhetslagen §27). |

### 5.3 `rooms` (5 of them)
`{ id, label, icon, jobs: [job-id, ...] }`. Drives the room chips in entry mode.
Order in the UI = order in the array (Badrum/Bathroom first).

### 5.4 `jobs` (26 of them)
| Field | Required | Purpose |
|---|---|---|
| `id` | ✓ | URL slug (`?jobb=<id>`). **NOTE:** `byta-gloldlampa` is misspelled but is a stable ID — do NOT rename (it would break links). |
| `label` | ✓ | The job's name. |
| `icon` | ✓ | Icon key (see §7). |
| `service_page_url` | ✓ | Absolute Ampy URL ("Läs mer om…" / read more). |
| `type` | ✓ | `"fixed"` or `"conditional"`. |
| `default_verdict` | (fixed) | green/yellow/red. |
| `question` | (cond) | The question in the intermediate step. |
| `options[]` | (cond) | 2–3 answers, each: `label`, `clarifier`, `verdict`, `summary`, `do`, `dont` (+ optional `source` override). |
| `summary` | ✓ | **The principle** (one sentence). Must NEVER be the same as `do`. |
| `do` | ✓ | The exact allowed action (the ✓ row). |
| `dont` | ✓ | The boundary (the ✗ row). |
| `tips[]` | (green jobs) | `{ text, allowed }`. `allowed:true` → ✓ (something you may do), `allowed:false` → ✗ (the stop condition). |
| `rule_citation` | ✓ | Short legal reference (internal; the source chip shows `source.text`). |
| `why_text` | ✓ | Longer explanation (internal reference / OG fallback). |
| `source_quote` | – | Optional verbatim Elsäkerhetsverket quote. |
| `related_jobs[]` | ✓ | 2–3 job IDs (internal cross-linking). |

**Copywriting IA (critical — must not regress):** In the Explanation tab the
order, top to bottom, is: `summary` (principle, bold) → ✓ `do` (exact action) →
✗ `dont` (boundary) → (green) caveat note. **`summary` ≠ `do`** — otherwise it
reads as a duplicate. This was an explicit client fix; guard it.

---

## 6. The engine (`behorighetskollen.js`)

- `resolve(job, answerIndex)` — pure function: `fixed` → `default_verdict`;
  `conditional` without an answer → `ask`; with an answer → `options[i].verdict`.
- `jobGroup(job)` — classifies a job as green/depends/red for the grouping in the
  entry list.
- **URL = state:** `?jobb=<id>&svar=<index>`. Every verdict is a shareable,
  indexable link. `popstate` is handled (the browser Back button works).
- **Adaptive opening:** the mount attribute `data-preselect-job` (set by
  `jobb="…"` on the shortcode) makes the tool open straight into that verdict.
- **`backOne()`:** from a conditional verdict, "Tillbaka" (Back) goes to the
  question step; from a fixed verdict or the question step, to the picker.
- **Three render modes:** `renderEntryBlock` / `renderQuestionBlock` /
  `renderVerdictBlock`.
- **Tips ✓/✗:** `renderTips` reads `{text, allowed}` and shows a check or a cross.
- **Share:** `renderShareButton` — on a touch device, `navigator.share` (native
  sheet: Instagram/Messages/etc.); on desktop, a popover (Facebook/X/Reddit/
  Email/Copy link). It also builds a canvas-based 1200×630 share card attached to
  the native share. (See §9.1 for the touch-vs-desktop decision.)

---

## 7. Icons
All icons are inline SVGs in the `ICONS` object in JS. A job's `icon` field points
to a key there. New jobs need an existing or new key. Available job icons include:
bulb, lamp, pendant, ceiling, spotlight, switch, outlet, panel, rcd, stove,
appliance, outdoor, smart, heatpump, heat, balance, search, renovate, bath, cable,
splice, kitchen, sofa, charger, solar, inspect.

---

## 8. WordPress / Bricks integration

- **Shortcode:** `[elkollen]` (or `[behorighetskollen]`), optional `jobb="<id>"`
  to preselect a job (the SEO lever, per service page).
- **Enqueue:** CSS+JS load only on pages that contain the shortcode (no global weight).
- **Heading:** add the H2 "Koppla elen" + the lead text as separate Bricks
  elements ABOVE the shortcode element. (See CHECKLIST.md.)
- **OG meta:** `ampy_bk_dynamic_og()` sets og:title/description/image when the URL
  has `?jobb=`. Images: drop `green.png`/`yellow.png`/`red.png` (1200×630) in
  `assets/og/`, optional per-job `<id>.png`.

### 8.1 Hero layout (`layout="hero"`) — the split-hero landing page
The landing page uses a **split hero**: marketing copy on the left, the tool on
the right. Two rules:
- **Bricks owns the split.** Build a 2-column section: left = native Bricks
  elements (H1, lead `<p>`, honesty line, trust list, two CTA buttons); right =
  the shortcode `[elkollen layout="hero"]`. The plugin does NOT render the
  marketing copy or the H1 (SEO: one clean page H1 owned by Bricks). Stack to 1
  column < 768px. The two left-column CTAs are a 1:1 replica of the ampy.se
  homepage hero gradient buttons: "Kontakta oss" (green->teal gradient
  `linear-gradient(120deg,#55ff9a,#5eb1bf)`, dark text, arrow-up-right icon) →
  `/offert/` and "010-265 79 79" (cyan gradient `linear-gradient(120deg,#b6f2ff,
  #70becb)`, dark text, phone icon on the right) → `tel:+46102657979`. Both: 16px
  radius (the site's `--radius-m`), soft shadow `0 0 16px rgba(241,241,241,.23)`,
  Outfit 400, the site's clamp() padding, full-width stack at <=478px. In
  production, reuse Bricks' existing `green-button` / `blue-cta` + `bricks-button`
  classes so they stay 1:1 with the site automatically.
- **What `layout="hero"` changes inside the tool:** the *entry* state becomes
  compact, with a fixed top-to-bottom order:
  1. **Search** field.
  2. **"Välj rum" standing dropdown** (always visible, between search and the
     common list). Tapping it drops down the 5 rooms; selecting one filters the
     list into the results drawer and the toggle label shows the active room.
  3. **"Vanliga eljobb"**: 6 quick-pick chips (from `meta.quick_picks`) followed
     by a full-width **"Se alla N jobb"** row that opens the full grouped list.
  4. **Results drawer** (hidden until a search/room/see-all action). It carries a
     **"Visa vanliga eljobb"** back link to return to the compact view. Scroll is
     contained so the hero never grows tall.

  The type scale steps up one notch for hero readability. Question and verdict
  render in the **same right panel** (no page jump; an anti-collapse height floor
  smooths the swap). Everything is gated on `this.heroMode` / scoped under
  `.ampy-bk--hero`; the default centered embed is byte-for-byte unchanged.
- **`jobb="…"` still works with hero:** `[elkollen jobb="golvvarme" layout="hero"]`
  opens straight into that verdict in the hero panel.
- **Reference implementation:** `preview/hero.html` is the landing page reference
  (split hero + "Så funkar det"). The FAQ and final CTA are intentionally NOT in
  this prototype — they come from existing site blocks placed below. Use the file
  as the visual + copy source of truth when assembling the Bricks section.
- **"Så funkar det":** ampy.se-homepage style — a light section (`#eef2f9`),
  borderless columns (no cards), large gradient navy->teal line-icons, numbered
  inline titles ("1. Välj ditt jobb"), and a green accent on "det" in the heading.
  Tool-specific copy: Välj ditt jobb / Få ditt besked / Gör nästa steg. Desktop is a
  3-column row (left-aligned); mobile stacks centered.

---

## 9. The lead flow (read carefully)

**Current flow (since 5.6.0):** the verdict advice CTA ("Få kostnadsfri
rådgivning", `meta.cta_advice_label`) opens an **in-tool lead form** (render mode
`leadOpen` → `renderLeadBlock`). Fields: Namn, E-post, Telefon, Postnummer + a
required GDPR consent. It prefills the job + verdict context and POSTs JSON to
`/wp-json/ampy-bk/v1/lead`. All form copy lives in `data.meta.lead_form`.

- **`includes/lead-endpoint.php`** is **enabled** (the `require_once` is active). It
  validates a fresh nonce + honeypot + per-IP rate limit + format-checks
  (e-post via `is_email`, telefon, 5-digit postnummer) + consent, verifies the
  `job_id` against the data file, and emails the admin. `do_action
  ampy_bk_lead_received` is the hook to also persist as a CPT / forward to a CRM.
- **Nonce + full-page caching (IMPORTANT for Chris):** WordPress nonces are
  time-bound. To stop a stale nonce (baked into a cached page) from 403-ing
  anonymous submits, the JS fetches a FRESH nonce from `GET /wp-json/ampy-bk/v1/
  nonce` right before POSTing. Still **verify on staging** that a logged-out
  visitor on a *cached* page can submit > 24 h after the page was cached; if your
  cache also caches the REST GET, exclude `/wp-json/ampy-bk/` from caching.
- **Durability:** if `wp_mail` fails the payload is written to the PHP error log
  as a safety net, but email is the only sink by default — for a paid lead magnet,
  hook `ampy_bk_lead_received` to a CPT/CRM and confirm an authenticated SMTP /
  transactional provider on staging (raw `mail()` will hurt deliverability).
- **Rate limit:** 15 submits / 10 min per IP (transient). Behind Cloudflare,
  REMOTE_ADDR is the edge IP — prefer edge/WAF rate limiting; the `IP:` line in the
  lead email is the edge IP on such stacks (informational only, never for blocking).
- **Static preview behavior:** when there is no WordPress backend (`window.AmpyBK`
  absent), `submitLead()` resolves a simulated success so the prototype demos the
  full flow. In production it does a real `fetch` with the `X-WP-Nonce` header.
- The hero left-column "Kontakta oss" / phone buttons are separate top-level
  brand CTAs and still link to `/offert/` and `tel:+46102657979`.

### 9.0 Analytics (since 5.6.0)
A vendor-agnostic `track(event, props)` emits each funnel step twice: a
`window.dataLayer.push` (GA4 / GTM) and a DOM `CustomEvent('elkollen:track')`.
No-ops if nothing listens. Events: `tool_view`, `job_selected`, `question_shown`,
`question_answered`, `verdict_shown` (job + color), `cta_click`,
`lead_form_open`, `lead_submitted`, `verify_company_click`. Wire GA4/GTM or
subscribe to the CustomEvent server-side.

### 9.1 Share (currently not surfaced)
As of 5.7.1 the share button was removed from the verdict screen (per client) so the
verdict ends with the CTAs. The capability remains in the code (`renderShareButton`
+ `generateShareImage`, which builds a 1200x630 verdict card; touch -> native share
sheet, desktop -> a Facebook/X/Reddit/Email/Copy popover) but is no longer called.
To re-enable, append `this.renderShareButton(job, verdictKey)` in `renderCta` and
re-add the `share_opened`/`share_completed` tracking.

---

## 10. How to extend (common changes)

| You want to… | Do this (data file only) |
|---|---|
| Change a word/copy | Edit the field in `jobs[]`/`verdicts`/`meta`. Bump `version`. |
| Add a job | Add an object to `jobs[]` (all fields per §5.4), add the ID to the right `rooms[].jobs` and optionally `quick_picks`, pick an `icon` key. |
| Change a link | Edit `service_page_url`, `ampy_offert_url`, `source.url`, etc. |
| Reorder rooms | Reorder `rooms[]`. |
| Change heading/lead | `meta.page_heading` / `meta.page_lead` + update the Bricks elements. |

**After changing CSS/JS:** bump `AMPY_BK_VERSION` in PHP (cache-busting).

---

## 11. Known pitfalls / pre-launch notes

1. **Google Fonts (GDPR + uptime):** the CSS `@import`s Plus Jakarta Sans +
   Outfit from `fonts.googleapis.com`. For a Swedish/EU site, **self-hosting** the
   fonts is recommended (GDPR + performance + zero external dependency). The tool
   does not break without them (system-ui fallback exists), but fix this before
   launch. How: download the fonts, place them in `assets/fonts/`, and replace the
   `@import` line in the CSS with local `@font-face` rules.
2. **`AMPY_BK_VERSION`** must be bumped on every asset change, otherwise visitors
   may get stale CSS/JS from cache.
3. **PHP lint:** could not be run in the build environment (php was unavailable).
   Run `php -l` on the three PHP files on staging before production.
4. **Launch gate:** do NOT publish until the electrician has signed off. Set
   `meta.reviewed_by` + `meta.last_reviewed` and remove `_pending_verification`.
   Verify especially: the `fast-armatur` job (scope), the penalty paragraph
   (48 § vs 49 §), and the 26 jobs' tips.
5. **`byta-gloldlampa`** — misspelled ID but stable. Do not rename (breaks URLs).
6. **The QA bar** in both `preview/index.html` and `preview/hero.html` must never
   reach production (it exists only in the preview files, not in the plugin output).

---

## 12. Verification suite (test this after installation)

See `CHECKLIST.md` §7 for the full test matrix. The essentials:
- Entry mode: search + 5 room chips + grouped list (green/depends/red).
- Conditional (e.g. `byta-vagguttag`): question → answer → verdict. Back → the question.
- Green verdict: the Tips tab shows ✓ for three tips and ✗ for the stop tip.
- Red verdict: solid "Få kostnadsfri offert" + the §27 source + the trust row.
- Share: popover on desktop, native sheet on a touch device.
- Mobile 360 px: nothing clipped, chips scroll inside the block, badge on one line.
- All links: quote → /offert/, "Läs mer" → the right /elservice/ page,
  "verifiera oss" → Ampy's register entry.
