# Elkollen — Master Implementation Handover (Bricks + Fluent Snippets)

**For:** Chris (implementation developer)
**Tool:** Elkollen (Ampy) — Swedish electrical lead-magnet
**Current version:** **7.3.8** (plugin header + `AMPY_BK_VERSION` + `data/…-data.json` `meta.version` all agree)
**Delivery format:** three **Fluent Snippets** (CSS · PHP/HTML · JS) into a live **Bricks** WordPress site — *not* a plugin, *not* a Bricks rebuild.
**Doc status:** written against the real code in this repo. Where existing docs disagree with code, this guide trusts code and says so.

> **Code is truth.** Before you trust any older doc in this repo, read the drift notes in §0. Several existing files (`README.md`, `CLAUDE.md`, `HANDOVER.md`, and the whole `elkollen-fluent-snippets/` folder) still carry old version numbers.

---

## 1. What Elkollen is

Elkollen is a free Swedish lead-magnet tool: a homeowner picks an electrical job (e.g. *byta vägguttag*, *golvvärme*, *laddbox*) and gets a **GREEN / YELLOW / RED** verdict answering *"får du göra det själv?"* — with the legal source (Elsäkerhetslagen 2016:732 / Elsäkerhetsverket), practical safety tips, and an **in-tool lead form** that offers a free consultation. It is simultaneously a lead magnet, an SEO surface (one tool serves **26 job URLs** via `?jobb=`), and a shareable link. Vanilla JS + CSS + PHP, no build step, no npm. **The business goal is qualified leads: the in-tool form is load-bearing — if the lead flow is not wired, the tool earns nothing.** Treat §6 as mandatory, not optional.

---

## 2. ⚠️ STALE-PORT WARNING — read before you touch `elkollen-fluent-snippets/`

**The pre-built `elkollen-fluent-snippets/` folder is OUT OF DATE. Do NOT paste it into production as-is.**

Hard evidence (verified in this repo):

| Artifact | Version it carries | Current source |
|---|---|---|
| `elkollen-fluent-snippets/elkollen.php` header + embedded JSON (`"version": "5.7.9"`) | **5.7.9** | **7.3.8** |
| Current plugin (`ampy-behorighetskollen.php`, `AMPY_BK_VERSION`) | — | **7.3.8** |

That is **many releases of drift** (the v7 funnel redesign, the v7.2 verdict/question reversion, the v7.3.7 hardening, the v7.3.8 font decision — none of it is in the port). Shipping the port would ship the wrong copy, the wrong job matrix, the wrong rendering, and PHP that is missing the v7.3.7 cache/OG/shape hardening.

**The three snippets must be REGENERATED from the current v7.3.8 source before shipping.**

### The regeneration path

The port is produced by a deterministic converter: `elkollen-fluent-snippets/_build/build.py`. It reads the **current** source and writes the three top-level files:

- **`elkollen.css`** ← `_build/chrome.css` (scoped landing shell) + `_build/hardening.css` (theme-defence) + `assets/behorighetskollen.css` (the tool), with the `@import` removed/hoisted and **every `rem` converted to `px` (1rem = 10px)** and the global `html{font-size:62.5%}` rule dropped. (§4 explains why.)
- **`elkollen.js`** ← `assets/behorighetskollen.js`, **byte-for-byte identical** (only a banner comment is prepended). The JS already reads the injected `window.AmpyBK` global, so nothing in it changes.
- **`elkollen.php`** ← `_build/elkollen.php.template` with `__ELKOLLEN_JSON__` replaced by the **verbatim current `data/behorighetskollen-data.json`** embedded as a PHP nowdoc (delimiter `ELKOLLEN_JSON`).

To regenerate:

```bash
cd elkollen-fluent-snippets/_build
python3 build.py
# It prints CSS/JS/PHP/preview stats and asserts "zero rem left in CSS".
```

`build.py` re-reads the live `assets/*.css`, `assets/*.js` and `data/*.json` each run, so re-running it **automatically pulls in the current CSS/JS/data** — that half of the staleness fixes itself.

### ‼️ But re-running build.py alone is NOT sufficient — the PHP template is itself stale

`build.py` only re-feeds **CSS, JS and the data JSON**. The **PHP chrome, shortcode surface, REST endpoints and OG logic** come from the hand-maintained `_build/elkollen.php.template`, and that template was frozen at the v5.7.9 era. Verified gaps in the current template vs. the current plugin code:

- **Missing** the v7.3.7 fresh-nonce `Cache-Control: no-store, max-age=0` header (present in `includes/lead-endpoint.php`). Without it, an edge cache can serve one stale nonce to every anonymous visitor → every lead 403s.
- **Missing** the v7.3.7 OG gate (`is_singular()` + `has_shortcode()` + `_bricks_page_content_2` check) and the `ampy_bk_get_data()` shape guard.
- **Different shortcode surface:** the template exposes `[elkollen tool_only="1"]`; the **current** plugin instead uses **`[elkollen layout="hero"]`** and has no `tool_only`. (See §5.)

**Action for Chris:** before regenerating, reconcile `_build/elkollen.php.template` with the *current* PHP —
`ampy-behorighetskollen.php` (enqueue/localize/OG), `includes/lead-endpoint.php` (nonce `no-store`, honeypot, rate limit, validation), and `includes/render.php` (mount markup) — and align the shortcode surface to `jobb` + `layout` (drop `tool_only`, add `layout="hero"`). Then run `build.py` and diff the three outputs against the current plugin behaviour. **Do not hand-edit the generated top-level files** — edit the sources/template and rebuild.

---

## 3. The three-snippet model

Everything ships as three Fluent Snippets. Load order and scope matter.

| # | Snippet | Fluent Snippets type | Run on | What it does |
|---|---|---|---|---|
| 1 | **CSS** | `CSS` (or a wrapped `<style>`) | Site-wide is safe (fully scoped) or landing-page-only | All styling. Scoped under `.elkollen-root` (page chrome) and `.ampy-bk` (the tool) so it can't bleed into the theme. |
| 2 | **PHP + HTML** | `PHP / Functions` | **Everywhere** | Registers the `[elkollen]` shortcode, embeds the data JSON, registers the REST routes on `rest_api_init`, prints the `.ampy-bk` mount, and injects `window.AmpyBK` (data + REST url + fresh nonce). Outputs nothing on its own. |
| 3 | **JS** | `JS` (load in **footer**) | Site-wide or landing-page-only | The whole tool (`class ElkollenApp`). Only acts on a page that contains the mount, so site-wide is harmless. |

**Load order (critical):** Snippet 2 (PHP) must inject `window.AmpyBK` into the page **before** Snippet 3 (JS) runs. The JS reads `window.AmpyBK.data` / `.restUrl` / `.restNonce` at boot. Because the PHP prints the global inline in the page body (via the shortcode) and the JS loads in the **footer**, order is naturally correct — keep the JS in the footer. If `window.AmpyBK` is absent (e.g. the static prototype), the JS falls back to simulating a successful lead submit, so a mis-wire fails *silently* — verify the real POST on staging (§6).

**"Run everywhere vs landing-page-only":** the **PHP must run Everywhere** (REST routes register globally; the shortcode must be resolvable wherever it's placed). The CSS and JS *can* be conditioned to the landing page, but site-wide is safe because both are scoped/inert off-page. Simplest correct setup: PHP = Everywhere, CSS = site-wide, JS = site-wide footer.

---

## 4. The Fluent Snippets delivery contract (format-only 1:1 rules)

These rules are what make the snippet a **true 1:1 clone** of the approved design without a plugin. They change **format only, never design**:

1. **`rem → px` (1rem = 10px).** The tool CSS is authored on a `10px` rem base — `assets/behorighetskollen.css` line 29 sets `html { font-size: 62.5%; }` and uses ~138 `rem` values. That global `html` rule, pasted into a live theme, would **shrink the entire theme's typography**. So `build.py` converts every `rem` to its exact px (`1.6rem → 16px`) and **removes the `html{62.5%}` rule**. Rendering is identical; the global side-effect is gone. `build.py` asserts zero `rem` survive.
2. **Wrapper-scoped CSS.** Page chrome scoped under `.elkollen-root`, the tool under `.ampy-bk`, plus a trailing **theme-hardening block** (`_build/hardening.css`) that re-asserts the browser defaults the design assumes (`box-sizing`, button `line-height`/`margin`, icon sizes) so a normal theme's `*{box-sizing:border-box}` / `button{…}` / `img,svg{max-width:100%}` resets can't shift the clone. The only thing it can't beat is a theme putting `!important` on those base resets — the blank/canvas page template (§5) is the belt to that suspenders.
3. **Self-host the font as woff2.** Production should self-host **Outfit** (weights 400/500/600, plus 700 for the verdict headline) as woff2 with a **latin subset that includes å ä ö**. No external Google Fonts request (GDPR).
4. **`@container`:** not used in the current CSS (verified: zero `@container` rules) — nothing to preserve here, but if you add container queries during the Bricks build, keep them scoped under the wrapper.

### ‼️ 4a. The v7.3.8 FONT DECISION — an owner sign-off gate, do not let this slip

This is a real, previously-burned issue. Read `assets/behorighetskollen.css` lines 9–27:

- The CSS declares `font-family: 'Outfit', system-ui, …` **but does NOT load Outfit** — as of **v7.3.8 there is deliberately NO `@import`** (fonts are "the host page's job"). History: an old `@import` sat *after* a style rule, which is **invalid CSS silently discarded by every browser**, so this stylesheet has in practice **never loaded Outfit**.
- **The pixel-approved reference look therefore renders in the SYSTEM font stack** (`system-ui` fallback), *not* Outfit. In v7.3.7 someone "fixed" the import (moved it first); the now-active Outfit static weights **changed the approved rendering across the whole tool**, and the **owner rejected it**. The import was removed for good.

**Consequence for Chris:** when production self-hosts Outfit (per rule 3 above), the tool **will look different** from the approved previews — that is exactly the change the owner rejected once already. **Do not silently self-host Outfit and ship.** Self-host it, render the result, and get the **owner to approve the Outfit rendering against the approved previews (`preview/hero.html` / `preview/index.html`) before launch.** If in doubt, the approved look = the host page's own font stack.

---

## 5. Bricks placement — two paths

The plugin/snippet only owns the `.ampy-bk` mount and (optionally) the landing chrome; **Bricks owns the page**. Two ways to place it:

**Path A — one shortcode prints the whole landing.** Drop a single `[elkollen]` on a **blank / canvas / full-width** Bricks page template (no inner `.container`). The (regenerated) snippet prints the entire split-hero: left marketing column (H1, lead text, 3 trust bullets, the two ampy.se gradient CTAs) **and** the interactive tool on the right. The landing has its own `max-width` and padding. Fastest to ship; least native-Bricks control.

> Note: in the **current plugin**, bare `[elkollen]` renders the *embedded* mount only (Bricks owns the chrome). The "prints the whole hero chrome" behaviour is a property of the **Fluent Snippets port template**, which builds its own chrome. Decide this at regeneration time (§2) and document which behaviour the shipped `elkollen.php` has.

**Path B — native Bricks hero + tool in the right column (recommended for control).** Build the hero **left column** with native Bricks elements — the H1, the lead `<p>`, the 3 trust bullets, and the two ampy.se CTAs ("Kontakta oss" gradient button + the phone button `010-265 79 79`) — and put **`[elkollen layout="hero"]`** in the **right column**. `preview/hero.html` is the visual + copy reference for this rebuild. (The `preview/*.html` files are prototypes and **do not ship** — production renders via the snippet/`render.php`.)

### Exact shortcodes (current plugin surface: `jobb` + `layout` only)

| Shortcode | Renders |
|---|---|
| `[elkollen]` | Default embedded mount (Path A: the port template makes this the full landing). |
| `[elkollen layout="hero"]` | The compact split-hero tool for the **right column** (Path B). Allowlisted value: only `hero`. |
| `[elkollen jobb="golvvarme"]` | Preselect a job — **the SEO lever**. One tool serves 26 job URLs; `?jobb=<id>` in the URL does the same. Use one Bricks page id per job. |
| `[behorighetskollen …]` | **Legacy alias** of `[elkollen]` — identical output, same attributes. |

`jobb` is validated against real job ids server-side (unknown ids fall back to the entry screen). **Never rename the job id `byta-gloldlampa`** (misspelled but stable — renaming breaks shared `?jobb=` URLs and OG links).

---

## 6. Wiring the lead flow — REQUIRED before launch

The verdict's primary CTA **"Få kostnadsfri rådgivning"** opens an in-tool form (Namn, E-post, Telefon, Postnummer, GDPR consent — all required) that POSTs JSON to the REST endpoint. Verified route strings and field names from `includes/lead-endpoint.php`:

- **Endpoints (namespace `ampy-bk/v1`):**
  - `POST /wp-json/ampy-bk/v1/lead` — submit a lead (`WP_REST_Server::CREATABLE`).
  - `GET /wp-json/ampy-bk/v1/nonce` — returns a **fresh** `wp_rest` nonce (`READABLE`, `permission_callback → __return_true`).
- **Fresh-nonce pattern & WHY:** `submitLead()` first GETs `/nonce` for a fresh nonce, then POSTs `/lead` with it in the **`X-WP-Nonce`** header (nonce action = `'wp_rest'`). This is so a **stale nonce baked into a full-page-cached HTML page never 403s an anonymous submit.** The `/nonce` response sets **`Cache-Control: no-store, max-age=0`** (v7.3.7) — WP only sends nocache headers on REST for logged-in users, so without this an edge/full-page cache could serve one stale nonce to every anonymous visitor after its 12–24h lifetime and break every lead.
- **Payload fields (POST /lead):** `job_id`, `verdict`, `namn`, `kontakt` (e-post), `telefon`, `postnummer`, `meddelande` (optional), `samtycke` (boolean, required), `webbplats` (honeypot, optional). Server-side validation: `is_email(kontakt)`, telefon regex `^[\d\s\+\-\(\)]{6,}$`, postnummer `^\d{5}$`, `verdict ∈ {green,yellow,red}`, `job_id` must exist in the data, `samtycke` must be true.
- **The durable sink (wire this to your CRM):** on every accepted lead the endpoint fires
  ```php
  do_action( 'ampy_bk_lead_received', $payload );
  // $payload keys: job_id, verdict, namn, kontakt, telefon, postnummer, meddelande
  ```
  Add a listener (a 4th tiny PHP snippet, or in the PHP snippet) to `wp_insert_post()` a CPT or forward to the CRM/webhook. **By default the ONLY sink is an email to `get_option('admin_email')`** — a `wp_mail()` from a shared host often lands in spam, so **use real SMTP** (FluentSMTP / Postmark / SES). On `wp_mail` failure the lead is written to the PHP `error_log` as a safety net, but do not rely on that.
- **Honeypot + rate limit:** the hidden `webbplats` field must stay empty (any value → fake-success, silently dropped). Per-IP rate limit = **15 requests / 10 min** via transient keyed on `REMOTE_ADDR`. **Behind Cloudflare/CDN, `REMOTE_ADDR` is the edge IP** — prefer enforcing rate limiting at the edge/WAF (or read the real client IP from a trusted forwarded header) so you don't throttle many users sharing one edge IP.
- **‼️ STAGING CHECK (do this before launch):** a **logged-out** visitor on a **cached** page must be able to submit successfully. **Exclude `/wp-json/ampy-bk/` from full-page cache** (WP Rocket / LiteSpeed / Cloudflare "cache everything" rules) — if the `/nonce` GET is itself cached, the fresh-nonce defence is defeated. Test: logged out, on a cached page, submit a green and a red job with all four fields → confirm the admin email arrives (and the CRM listener fires, if wired).

---

## 7. The LAUNCH GATE 🚦

**Elkollen gives legal guidance. It must NOT go public until the gate clears.**

- **Electrician sign-off (blocking):** a **certified electrician (auktoriserad elinstallatör)** must sign off the 26-job matrix + per-verdict legal sources + per-job tips. Tracked in `meta._pending_verification` (`reviewed_by` = `TBD…`). Also awaiting re-sign as part of the same gate: the **5 authored two-option scenario questions** (`meta._authored_questions_note`, `[GAP]`) and the **18 option-level green-tip arrays** (`meta._green_tips_note`, `[GAP]`). **Do not set `reviewed_by` or remove these flags yourself** — that is the electrician's act.
  - *Drift note:* older docs describe the `48 §` straffparagraf as unverified. Per the current data (`_pending_verification`), the **48 § penalty citation is owner-verified (2026-07-16)** and the red-verdict source ref is the verified 27 §. There is **no separate `meta._consequence_verify` key** in the current data — treat the matrix sign-off (above) as the single legal gate.
- **Open owner/content items:**
  - **`byta-vagguttag` links to the wrong service page:** its `service_page_url` is `https://ampy.se/elservice/strombrytare/` (the `/vagguttag/` page 404s). Owner to confirm the correct target or ship the strömbrytare page first. (`utomhusbelysning` points at `/elservice/utomhusbelysning/` — confirm that page exists too.)
  - **`utomhusbelysning`** verdict/tips vs its red fork — owner to confirm wording.
  - **OG images not dropped:** `assets/og/` contains only `README.md` — **no `green.png` / `yellow.png` / `red.png` (1200×630)**. Add them (and any per-job `<id>.png` overrides) or shared previews fall back to none.
  - **`meta.last_reviewed` is a placeholder** (`2026-06-XX`) — set the real date at sign-off.

---

## 8. Version & cache-busting discipline

- **`AMPY_BK_VERSION` must equal `meta.version`** in the data file. Both are **7.3.8** today.
- **Bump BOTH on any CSS/JS change.** In the plugin this is `define('AMPY_BK_VERSION', …)` used as the enqueue `?v=`. **In the Fluent Snippets world there is no `wp_register_script` version arg** — the equivalent is the cache-bust query on however you enqueue/print the assets (or bump the snippet and clear WP/CDN cache). Keep the string in sync with `meta.version` in the embedded JSON. After any CSS/JS edit: edit source → re-run `build.py` → bump the version → clear WP cache/CDN.

---

## 9. Pre-launch checklist

**Regenerate & reconcile**
- [ ] Confirm the shipped snippets are **v7.3.8**, not the stale **v5.7.9** in `elkollen-fluent-snippets/` (§2).
- [ ] Reconcile `_build/elkollen.php.template` with current `ampy-behorighetskollen.php` + `includes/lead-endpoint.php` + `includes/render.php` (nonce `no-store`, OG `is_singular` gate, shape guard, shortcode surface = `jobb`+`layout`).
- [ ] Run `python3 _build/build.py`; confirm "zero rem left in CSS" and diff outputs against current plugin behaviour.
- [ ] Do **not** hand-edit generated top-level files.

**Snippets & placement**
- [ ] Snippet 1 CSS installed (scoped); Snippet 2 PHP = **Everywhere**; Snippet 3 JS = **footer**.
- [ ] Confirm PHP injects `window.AmpyBK` before JS runs (JS in footer).
- [ ] Page uses a blank/canvas/full-width template; placed via Path A or Path B (§5).
- [ ] SEO deep-links use `[elkollen jobb="…"]` / `?jobb=…`; `byta-gloldlampa` id untouched.

**Font (owner gate)**
- [ ] Outfit self-hosted as woff2 (400/500/600/700, latin incl. å ä ö), no Google Fonts request.
- [ ] **Owner has approved the Outfit rendering against the approved previews** (§4a) — or the host font stack is used deliberately.

**Lead flow**
- [ ] Real SMTP configured; admin email correct; test email received.
- [ ] `do_action('ampy_bk_lead_received', …)` listener wired to CRM/CPT (durable sink).
- [ ] `/wp-json/ampy-bk/` excluded from full-page cache.
- [ ] **Logged-out visitor on a cached page can submit** (green + red job, all four fields).
- [ ] Rate-limit strategy set for Cloudflare/edge (REMOTE_ADDR note).

**Launch gate**
- [ ] Auktoriserad elinstallatör signed off the 26-job matrix + questions + green tips; `reviewed_by` set by them; `_pending_verification` cleared by them.
- [ ] `byta-vagguttag` (and `utomhusbelysning`) service-page URLs confirmed / pages exist.
- [ ] OG PNGs (`green/yellow/red.png` 1200×630, + any per-job) dropped in `assets/og/`.
- [ ] `meta.last_reviewed` set to the real date.

**Version**
- [ ] `AMPY_BK_VERSION` == `meta.version` == 7.3.8 (bump both + clear cache on any CSS/JS change).

---

### Drift log (existing docs vs. code — trust code)
- `README.md` says **"Version 5.7.9"** — stale; current is 7.3.8.
- `CLAUDE.md` and `HANDOVER.md` say **7.3.7** — stale by one; code is 7.3.8.
- `elkollen-fluent-snippets/` (README + built files + embedded JSON) is the **v5.7.9 port** — must be regenerated (§2).
- `elkollen-fluent-snippets/README.md` documents `[elkollen tool_only="1"]` and a Google-Fonts `@import`; the **current** plugin uses `[elkollen layout="hero"]` (no `tool_only`) and has **no `@import`** (§4a). Reconcile at regeneration.
