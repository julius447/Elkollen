# Elkollen — Implementation checklist for Chris

> Follow the steps top to bottom. Each box is one concrete action. Do not skip
> **Step 8 (the launch gate)** — the tool gives legal guidance and must not go
> public before a certified electrician (auktoriserad elinstallatör) has signed off.
>
> Deep technical docs are in `HANDOVER.md`; your Claude Code agent should read
> `CLAUDE.md` first (its rules + architecture brief). This file is for you, the human.
>
> **Language note:** the tool's UI is in Swedish (it serves Swedish homeowners).
> The text you type into Bricks below is the literal Swedish copy — use it as-is.
>
> **New since the prototype handover:** the verdict CTA now opens an in-tool **lead
> form** that emails leads to your WP admin (see **Step 4b** — do it before launch).

Estimated time: **30–45 min** for install + test. The sign-off gate is separate.

---

## Step 0 — What you have and what you need

**You have:** this plugin folder/ZIP (`ampy-behorighetskollen`). It is complete:
code, data, design, documentation. No build step, no npm dependencies.

**You need:**
- [ ] WordPress 6.0+ with the Bricks theme.
- [ ] PHP 7.4+ (check under Tools → Site Health).
- [ ] Admin access to WP + the Bricks editor.
- [ ] (Optional) A designer to produce 3 OG share images.

---

## Step 1 — Install the plugin

- [ ] If you have the folder but not a ZIP: zip the `ampy-behorighetskollen`
      folder so the ZIP contains the folder (not just its contents).
- [ ] WP admin → **Plugins → Add New → Upload Plugin** → choose the ZIP →
      **Install Now** → **Activate**.
- [ ] Check: under Plugins, "Elkollen (Ampy)" should show as active.

---

## Step 2 — (Only if you change anything) verify the data file

All content lives in `data/behorighetskollen-data.json`. You normally do **not**
need to touch it. If you do change something:

- [ ] Validate the JSON (paste into jsonlint.com or run `python3 -m json.tool`).
- [ ] Bump `meta.version` AND `AMPY_BK_VERSION` in `ampy-behorighetskollen.php`
      (these are cache-busting — otherwise visitors may get stale files).
- [ ] Run `php -l` on the three PHP files on staging (the build environment
      could not lint them; do it once on your server).

---

## Step 3 — Place the tool on a page in Bricks

Pick the layout that fits the page.

### Option A (recommended for the landing page) — split hero
Copy on the left, tool on the right. Reference design: `preview/hero.html`.

- [ ] Add a **Section** → inside it a **2-column Container** (left ~44%, right ~56%).
- [ ] **Left column** (native Bricks elements):
      - **H1**: **Får du fixa elen själv? Få svaret på 30 sekunder.**
      - Lead paragraph: **Elkollen ger dig ett tydligt besked, grönt eller rött, baserat på Elsäkerhetslagen. Hela svaret är gratis, ingen mejladress behövs.**
      - A short honesty line + 3 trust bullets (copy is in `preview/hero.html`).
      - Two CTA buttons that are the **exact same pair as the ampy.se homepage hero**:
        **"Kontakta oss"** (green->teal gradient, dark text, arrow-up-right icon) →
        `https://ampy.se/offert/` and **"010-265 79 79"** (cyan gradient, dark text,
        phone icon on the right) → `tel:+46102657979`. In Bricks, reuse the site's
        existing `green-button` / `blue-cta` + `bricks-button` button classes so they
        inherit the live homepage styling and stay 1:1 automatically (the prototype in
        `preview/hero.html` replicates those computed styles: 16px radius, soft shadow,
        Outfit 400, the site's clamp() padding, full-width stack at <=478px).
- [ ] **Right column**: a **Shortcode element** with `[elkollen layout="hero"]`
- [ ] Set the column container to **stack to 1 column below 768px**.
- [ ] Below the hero, add the **"Så funkar det"** section (3 cards, copy in
      `preview/hero.html`). The FAQ and final-CTA come from your existing site
      blocks — they are intentionally not part of this prototype anymore.

> Why the H1 is a Bricks element, not in the tool: Google should read the page
> H1 from the page, not from inside the widget. The tool never carries its own
> page heading.

### Option B (simpler / service pages) — centered, no split
- [ ] Add a **Heading (H2)**: **Koppla elen** + a **Text**: **Se direkt vilka eljobb du får göra själv.**
- [ ] Add a **Shortcode element**: `[elkollen]` (no `layout`).
- [ ] On a specific service page, preselect: `[elkollen jobb="golvvarme"]`.

---

## Step 4 — Preselect per service page (the SEO lever)

On each electrical service page that matches a job, add the shortcode with
`jobb="…"` so the tool opens straight into that verdict:

- [ ] Underfloor heating page: `[elkollen jobb="golvvarme"]`
- [ ] Outlet/switch page: `[elkollen jobb="byta-vagguttag"]`
- [ ] EV charger page: `[elkollen jobb="laddbox"]`
- [ ] …and so on. Job IDs are in `HANDOVER.md` §5 / in the data file's `jobs[]`.

---

## Step 4b — Lead form & email setup (REQUIRED — the form is live)

The verdict CTA "Få kostnadsfri rådgivning" opens an on-page form that POSTs to a
REST endpoint and **emails the lead to your WP admin address**. Set this up before
launch or leads will not arrive:

- [ ] **Admin email:** confirm WP admin email (Settings → General) is a monitored
      inbox — that's where leads land. (To send elsewhere, hook
      `ampy_bk_lead_received` or filter `wp_mail`.)
- [ ] **Real mail provider:** install/confirm an authenticated **SMTP / transactional**
      mail plugin (e.g. provider's SMTP). Raw PHP `mail()` will land leads in spam.
- [ ] **Caching:** exclude `/wp-json/ampy-bk/` from full-page cache (Cloudflare / WP
      Rocket / LiteSpeed). The form fetches a fresh nonce from
      `GET /wp-json/ampy-bk/v1/nonce`; if that GET is cached, submits can 403.
- [ ] **Verify on staging:** as a logged-out visitor, on a **cached** page, fill the
      form and submit → you get the "Tack!" success and an email arrives. Re-test
      after the page has been cached > 24 h.
- [ ] **(Recommended) durable storage:** have your agent hook
      `ampy_bk_lead_received` to store leads as a CPT or push to your CRM, so a mail
      hiccup never loses a lead. (On mail failure the payload is written to the PHP
      error log as a fallback.)
- [ ] **(Optional) abuse:** the endpoint has a built-in per-IP rate limit (15/10 min).
      Behind Cloudflare, prefer edge rate limiting too.

---

## Step 5 — OG share images (optional, but recommended)

For nice previews when someone shares a verdict on Facebook/Reddit:

- [ ] Ask the designer for three images at **1200×630 px**: `green.png`,
      `yellow.png`, `red.png`.
- [ ] Place them in `assets/og/` in the plugin folder (via FTP or file manager).
- [ ] (Optional) Per-job override: `assets/og/<job-id>.png`.
- [ ] Without images everything still works; only the share preview is generic.

---

## Step 6 — GDPR: self-host the fonts (recommended before launch)

The tool currently loads fonts from Google Fonts. For a Swedish site, self-hosting
is better (GDPR + performance). The tool **does not break** without this (there is
a system-font fallback), but please do it:

- [ ] Tell your Claude Code agent: "Self-host Plus Jakarta Sans + Outfit: download
      the woff2 files, place them in `assets/fonts/`, and replace the `@import`
      line in `assets/behorighetskollen.css` with local `@font-face` rules."
      (See `HANDOVER.md` §11.)

---

## Step 7 — Test matrix (do this before launch)

Test on **desktop** and **mobile (360 px wide)**:

**Entry mode (the start view):**
- [ ] Heading "Koppla elen" + lead appear above the tool.
- [ ] Search works (type "uttag" → the list filters).
- [ ] 5 room chips (Badrum, Kök, Vardagsrum, Utomhus, Elcentral). On mobile they
      must scroll sideways without being clipped by the card edge.
- [ ] The list is grouped: 🟢 Får du göra själv / 🟡 Det beror på / 🔴 Kräver elektriker.

**Green verdict (e.g. byta glödlampa / change a bulb):**
- [ ] Badge "Det här får du göra själv" (green).
- [ ] Source chip "Elsäkerhetsverket – Detta får du göra själv" → opens the right page.
- [ ] Explanation: one summary line + a ✓ row + a ✗ row + a yellow caveat note.
- [ ] Tips tab: the first three tips have ✓, the last one (the stop condition) has ✗.
- [ ] CTA: calm "Läs mer om …" (→ service page) + a discreet "Få kostnadsfri
      rådgivning" link (→ opens the in-tool lead form, see below).

**Red verdict (e.g. installera golvvärme / install underfloor heating):**
- [ ] Badge "Det här kräver elektriker" (red) on **one line**.
- [ ] A one-line consequence summary shows **above the tabs** by default.
- [ ] Source chip "Elsäkerhetslagen (2016:732) 27 §" → opens riksdagen.se.
- [ ] Explanation + Consequences tab.
- [ ] CTA: solid teal "Få kostnadsfri rådgivning" (opens the lead form) + outline
      "Läs mer om …" (→ service page).
- [ ] The trust row "Ampy är registrerat hos Elsäkerhetsverket, verifiera oss"
      → "verifiera oss" opens Ampy's entry in the register.

**The in-tool lead form (the advice CTA):**
- [ ] Clicking "Få kostnadsfri rådgivning" opens an on-page form (does NOT leave
      the page): Namn, E-post, Telefon, Postnummer + a GDPR consent checkbox.
- [ ] Submitting with name + e-post + consent shows a "Tack!" success state.
- [ ] On the live WP site, a lead e-mail reaches the admin address (the REST
      endpoint `ampy-bk/v1/lead` is enabled). Check spam if missing.
- [ ] "Tillbaka till beskedet" returns to the verdict.

**Analytics (optional but recommended):**
- [ ] Open the console and run `window.dataLayer` (or listen for the
      `elkollen:track` event) — confirm events fire: tool_view, job_selected,
      verdict_shown, cta_click, lead_form_open, lead_submitted, share_*. Wire to
      GA4/GTM if used.

**Conditional (e.g. byta vägguttag / replace an outlet):**
- [ ] Select the job → a question step with 2 answers (each with an explanatory subline).
- [ ] Select an answer → the right verdict (green for "replacing in the same spot",
      red for "moving/new").
- [ ] "Tillbaka" (Back) from the verdict goes to the **question step**, not all the
      way back to the start.

**General:**
- [ ] Nothing is clipped on mobile. The block has breathing room around it.
- [ ] No errors in the browser console (F12 → Console).

---

## Step 8 — LAUNCH GATE (must not be skipped) 🚦

The tool gives guidance people trust legally. Before going public:

- [ ] A **certified electrician** (auktoriserad elinstallatör) reads through the
      entire job matrix in the data file and confirms each verdict (green/yellow/red)
      matches current rules.
- [ ] Specific checks:
      - The **`fast-armatur`** job (is "replacing a fixed-mounted light fitting"
        with terminal wiring still a layperson job in your scope?).
      - The **penalty paragraph** in the Consequences text (48 § vs 49 § — see
        `_consequence_verify` in the data).
      - The **26 jobs' tips** (written within the layperson scope, reviewed by an
        independent safety reviewer, but to be signed off).
- [ ] Once signed off: fill in `meta.reviewed_by` + `meta.last_reviewed` and remove
      `meta._pending_verification` in the data file. Bump `version`.

---

## Step 9 — Go live

- [ ] Publish the page(s).
- [ ] Share a verdict link (`?jobb=golvvarme`) in a chat and check the OG preview
      looks good (if OG images were added).
- [ ] Add the page to your sitemap / internal linking.

---

## Uptime & troubleshooting (if something looks wrong)

| Symptom | Likely cause | Fix |
|---|---|---|
| The tool shows only a list of links, not the interactive UI | JS didn't load | Hard reload (Ctrl/Cmd+Shift+R). Check the JS file exists at the plugin URL. Check the console. |
| Old design/copy shows after a change | Cached CSS/JS | Bump `AMPY_BK_VERSION` in PHP + clear any WP cache/CDN. |
| "Behörighetskollen kunde inte laddas" (could not load) | Broken/missing data file | Validate `data/behorighetskollen-data.json` (jsonlint). |
| The font looks like a system font | Google Fonts blocked | Harmless (fallback). Self-host the fonts (Step 6) for a permanent fix. |
| The tool disappeared entirely | Plugin deactivated or shortcode removed | Activate the plugin / put `[elkollen]` back. |

**Rollback:** the plugin is standalone and has no database tables. Deactivate the
plugin → the tool disappears, the rest of the site is untouched. No side effects.

---

## Quick reference — where things live

- **All text/copy/rules/links:** `data/behorighetskollen-data.json`
- **Design:** `assets/behorighetskollen.css` (everything scoped to `.ampy-bk`)
- **Logic/interaction:** `assets/behorighetskollen.js`
- **Shortcode + enqueue + OG:** `ampy-behorighetskollen.php`
- **The heading (H2 + lead):** in Bricks, above the shortcode (not in the code)
- **Deep technical docs:** `HANDOVER.md`
