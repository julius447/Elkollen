# Elkollen — JavaScript Reference (`assets/behorighetskollen.js`)

Definitive developer reference for the Elkollen front-end, written **from the current
code**, not the older `HANDOVER.md`/`README.md` (those contain drift — see the last
section). Target: a developer (Chris) re-implementing the tool in Bricks/WordPress via
Fluent Snippets.

- **File:** `assets/behorighetskollen.js` (~1226 lines)
- **Plugin version constant:** `AMPY_BK_VERSION = '7.3.8'` (`ampy-behorighetskollen.php:26`)
- **In-code comment tags:** most guard comments in the JS are labelled `v7.3.7`; the
  package version is `7.3.8`. Treat the two as the same release wave — the version
  constant bumped to 7.3.8 without re-tagging every inline comment. **CODE IS TRUTH:**
  the guards described below all exist in the shipped file regardless of comment tag.

Line numbers below refer to the current `assets/behorighetskollen.js`.

---

## 1. Overview

Plain **vanilla ES6**. No framework, no build step, no dependencies. The whole file is a
single **IIFE** (`(function () { 'use strict'; … })();`, lines 9–1226) that defines one
`class ElkollenApp` (line 130) plus a small set of module-private helpers (`ICONS`, `el`,
`icon`, `resolve`, `track`) and a `boot()` entry point.

### The `window.AmpyBK` global

At the bottom (lines 1219–1221) the IIFE publishes exactly two things onto a shared
global:

```js
window.AmpyBK = window.AmpyBK || {};   // preserve anything WordPress already injected
window.AmpyBK.resolve = resolve;       // pure engine, exposed for tests/debug
window.AmpyBK.boot = boot;             // manual boot hook
```

Note the `|| {}` — it **does not clobber** a `window.AmpyBK` that WordPress already
populated via `wp_localize_script`. That injected object is the runtime config
(`data`, `restUrl`, `restNonce`) and is read separately at boot.

### Two boot paths (`boot`, lines 1177–1205)

`boot(mount)` is called once per `.ampy-bk` element on `DOMContentLoaded` (lines
1223–1225), and can also be called manually via `window.AmpyBK.boot`.

1. **WordPress / injected-data path (preferred).** If `window.AmpyBK.data` exists
   (localized by PHP), the app constructs immediately with that object — no network
   round-trip:
   ```js
   const injected = (window.AmpyBK && window.AmpyBK.data) || null;
   if (injected) { maybeApplyPreselect(mount); new ElkollenApp(mount, injected).render(); return; }
   ```
2. **Static-fetch fallback (prototype / no WordPress).** Otherwise it fetches the JSON
   from `mount.dataset.dataUrl` (the `data-data-url` attribute emitted by
   `render.php:30`), falling back to a relative `'../data/behorighetskollen-data.json'`:
   ```js
   const dataUrl = mount.dataset.dataUrl || '../data/behorighetskollen-data.json';
   fetch(dataUrl, { credentials: 'same-origin' }) …
   ```
   On failure it logs `[Elkollen] Could not load data:` and renders a Swedish error card
   (`Kunde inte ladda eljobben just nu.`).

### The v7.3.8 double-boot guard (`dataset.booting`)

`render()` sets `mount.dataset.booted = 'true'` (line 241), but on the async fetch path
that flag isn't set until the fetch resolves. Two `boot()` calls while the fetch is
pending would both pass a `booted`-only guard and create **two** app instances (doubled
`popstate` listeners + doubled analytics). The `booting` flag closes that window
(lines 1183–1184):

```js
if (!mount || mount.dataset.booted === 'true' || mount.dataset.booting === 'true') return;
mount.dataset.booting = 'true';
```

`booting` is deleted again on fetch failure (line 1202) so a retry boot is still
possible. `maybeApplyPreselect()` (lines 1207–1217) reads `mount.dataset.preselectJob`
(the `data-preselect-job` attribute) and, if the URL has no `?jobb=`, `replaceState`s one
in — this is the per-service-page SEO lever (same tool, deep-linked to one job).

---

## 2. The `ICONS` map + the `el()` helper

### `ICONS` (lines 13–70) and `icon()` (line 73)

`ICONS` is a flat object of **inline SVG strings**, stroke-based, all `24×24` viewBox.
Two classes of key: UI glyphs (`check`, `alert`, `ban`, `info`, `arrowLeft`,
`arrowRight`, `chevron`, `chevronRight`, `external`, `search`, `x`) and job glyphs
(`bulb`, `lamp`, `pendant`, `ceiling`, `spotlight`, `switch`, `outlet`, `panel`, `rcd`,
`stove`, `appliance`, `outdoor`, `lantern`, `smart`, `heatpump`, `heat`, `balance`,
`renovate`, `bath`, `cable`, `splice`, `kitchen`, `sofa`, `felsok`, `charger`, `solar`,
`inspect`, `grid`).

- **Cleanup (tagged v7.3.7, shipped in 7.3.8):** the social/share icon set (`facebook`,
  `xtwitter`, `reddit`, `mail`, `link`, `share`) was **removed** together with the dead
  share subsystem (comment, lines 64–68).
- **De-duplicated `x` key:** there used to be **two** `x` keys — an early `stroke-2.2`
  variant near the top and a later `stroke-2` one. In JS object literals the **last**
  key wins, so only the `stroke-2` version ever rendered. The early duplicate was
  removed; the surviving `x` (lines 65–69) is the live "don't"-row cross.
- `ICONS.search_icon = ICONS.search` (line 71) is an alias.
- `icon(name)` (line 73): `ICONS[name] || ICONS.info` — **any unknown name silently
  falls back to the `info` glyph**, so a typo in the data file's `icon` field degrades to
  an info circle rather than a blank. (Separately, the job-row renderer maps a job
  `icon:"search"` to the `felsok` glyph — line 1136.)

### `el(tag, attrs, children)` (lines 76–93) — the DOM builder & the XSS contract

Tiny hyperscript-style factory. `attrs` handling:

| Key form | Behaviour |
|---|---|
| `class` | sets `node.className` |
| `html` | **`node.innerHTML = value`** |
| `on…` + function | `addEventListener(name.slice(2), fn)` (e.g. `onclick`) |
| `data` object | each pair → `node.dataset[k]` |
| `value === true` | boolean attribute (`setAttribute(k, '')`) |
| other non-null, non-false | `setAttribute(k, value)` |

`children` (string or array): **strings become `document.createTextNode`** (line 89);
element nodes are appended as-is; `null`/`false` are skipped.

**The XSS-safety contract (critical — preserve it verbatim when porting):**

- `html:` is the **only** path that writes `innerHTML`, and in this codebase it is
  **only ever fed values from the static `ICONS` table** (hand-authored SVG constants) —
  grep every `html:` call site and they resolve to `icon(...)` (or, in the fineprint,
  hand-split literal strings). No user input, no data-file field, ever reaches `html:`.
- **All dynamic/text content — job labels, questions, option text, the submitter's name,
  server error messages — goes through the `children` path and therefore through
  `textContent`/`createTextNode`**, which HTML-escapes inherently. Example: the success
  title interpolates the user's first name via `.replace('{namn}', firstName)` and passes
  the result as a text child (lines 782–789) — safe by construction because `el()` never
  routes it through `innerHTML`. If you re-implement `el()` in a snippet, **do not** add
  an `innerHTML` fast-path for arbitrary content; that is the whole safety model.

---

## 3. The engine — `resolve(job, answerIndex)` (lines 96–108)

Pure function, no side effects, returns a small result object:

```js
function resolve(job, answerIndex) {
  if (!job) return { kind: 'unknown' };
  if (job.type === 'fixed') return { kind: 'verdict', verdictKey: job.default_verdict };
  if (job.type === 'conditional') {
    if (!Array.isArray(job.options) || !job.options.length) return { kind: 'unknown' }; // guard A
    if (answerIndex == null || !job.options[answerIndex]) return { kind: 'ask' };
    return { kind: 'verdict', verdictKey: job.options[answerIndex].verdict };
  }
  return { kind: 'unknown' };
}
```

Result `kind` is one of `unknown` (→ entry screen), `ask` (→ question screen), or
`verdict` (→ verdict screen).

**Guards (tagged v7.3.7, shipped 7.3.8):**

- **Guard A (line 103):** a `conditional` job whose `options` array was lost/emptied
  (the data file is a non-dev editing surface) degrades to `unknown` instead of letting
  `renderQuestionBlock` iterate a missing array and throw.
- **`render()`-level verdict guard (lines 249–251):** after `resolve`, if the result is a
  `verdict` but the `verdictKey` is **not** present in `data.verdicts` (a typo like
  `"gren"`), it is downgraded to `unknown`:
  ```js
  if (result.kind === 'verdict' && !(this.data.verdicts && this.data.verdicts[result.verdictKey])) {
    result.kind = 'unknown';
  }
  ```
  This matters because `navigate()` **already pushed the URL** before `render()` runs
  (see §4). A throw here would freeze the old view against a desynced URL and leave every
  subsequent tap on that job dead. Degrading to the entry screen keeps the tool alive.

**`jobGroup()` is GONE.** The old verdict-grouped job list (green/yellow/red headers,
colour dots) was removed by design (comment, lines 110–112). **Job lists are neutral** —
a row must be unreadable as an answer; the verdict is only ever revealed on the verdict
slide, after a committed choice. There is no `jobGroup` function in the file.

---

## 4. State & routing

**State shape:** `this.state = { jobId, answerIndex }` — and **only** those two fields
live in the URL. `leadOpen`, `activeTab`, `activeRoom`, `heroExpanded`, `searchQuery` are
**transient** view state on the instance, deliberately *not* in the URL.

### `readUrl()` (lines 152–161)

Reads `?jobb=` and `?svar=` from `window.location.search`. `jobId` is validated against
`this.jobsById` (unknown id → `null`); `answerIndex` is `parseInt(…, 10)` and kept only if
`Number.isInteger`.

### `writeUrl(push = true)` (lines 163–172)

Starts from the **existing** `URLSearchParams`, deletes only `jobb`/`svar`, then re-sets
them from state. **Foreign params are preserved** (utm_*, fbclid, etc.). Rebuilds
`pathname + '?' + qs + hash` and calls `history.pushState` (or `replaceState` when
`push === false`). **`writeUrl` runs BEFORE `render()`** inside `navigate()` (lines
211–212) — which is exactly why the render-time verdict guard in §3 exists: the URL is
already committed by the time rendering can fail.

### `popstate` (`bindHistory`, lines 174–181)

On Back/Forward: re-reads the URL into `state`, resets `activeTab = 'explain'`, and
**forces `leadOpen = false`** so Back/Forward never leaves the lead form stuck open, then
re-renders.

### Navigation methods

- **`navigate(next, push = true)` (lines 191–213):** merges `next` into `state`, fires
  `job_selected` when the job changes and `question_answered` (enriched with the chosen
  option's verdict) when an answer is supplied, resets `activeTab`, forces
  `leadOpen = false`, then `writeUrl(push)` **then** `render()`.
- **`backOne()` (lines 229–236):** context-aware back — from an answered conditional job
  it clears just `answerIndex` (back to the question); otherwise clears both fields (back
  to entry).
- **`openLead()` (lines 215–222):** only opens if the current state resolves to a
  `verdict`; sets `leadOpen = true`, fires `lead_form_open`, re-renders. **Does not touch
  the URL** (transient).
- **`closeLead()` (lines 224–227):** `leadOpen = false`, re-render. Also not in the URL.

---

## 5. Render modes (`render()`, lines 238–303)

`render()` removes the crawlable `.ampy-bk__noscript` fallback (once), marks
`dataset.booted`, resolves state → result, then builds exactly one `block` and swaps it
into the mount via **`replaceChildren`**.

Mode selection (lines 253–267):

| Condition | Renderer | Impression event |
|---|---|---|
| `leadOpen && verdict` | `renderLeadBlock(job, verdictKey)` (675) | — (keeps `_lastShownKey`) |
| `!job \|\| kind==='unknown'` | `renderEntryBlock()` (882) | — (resets `_lastShownKey = null`) |
| `kind==='ask'` | `renderQuestionBlock(job)` (374) | `question_shown` |
| else (`verdict`) | `renderVerdictBlock(job, verdictKey)` (422) | `verdict_shown` |

- **`renderEntryBlock()` (882):** default embedded entry — search field +
  room tablist + swap area + source line. When `this.heroMode` is set (mount
  `data-layout="hero"`) it delegates to **`renderHeroEntry()` (966)** — the two-slide
  hero funnel: slide 1 = six identical room tiles (5 rooms + "Alla eljobb"), slide 2 =
  a neutral in-panel job-list drawer (`updateDrawer`, lines 1037–1083). Drawer swaps are
  in place (no full re-render).
- **`renderQuestionBlock(job)` (374):** back crumb carrying the **job name**
  (`job.chip_label || job.label`, line 380), the question, the option list, and an info
  note (`meta.entry.info_scenario`).
- **`renderVerdictBlock(job, verdictKey)` (422):** the verdict **board** (accent rule +
  badge + source link for red/yellow), the **tab bar** (`Förklaring` + a conditional
  `Konsekvenser`/`Tips`, lines 484–517), the tab body (`renderTabBody`, 540), and the CTA
  zone (`renderCta`, 634).
- **Lead overlay:** `renderLeadBlock` (see §7) replaces the whole card while `leadOpen`.

**Hero anti-jump min-height hold (lines 272–277):** in hero mode, before the swap it
captures the previous child's `offsetHeight`, pins `mount.style.minHeight` to it, calls
`replaceChildren(block)`, then releases the min-height on the next `requestAnimationFrame`
so the panel settles (CSS transition) to new content instead of snapping. Default mode
just `replaceChildren(block)` (line 279).

**Focus contract (lines 288–301):** skipped on the very first paint (`this._booted`
guard, so page load never steals focus/scroll). On every later swap it finds
`[data-focus-target]:not([hidden])` in the new block, sets `tabindex="-1"`, and
`focus({ preventScroll: true })` (fallback to plain `focus()`) — focus never fights the
programmatic scroll and no mobile keyboard pops. Then it schedules `_syncScroll()` via
`setTimeout(…, 60)` (deliberately **not** `rAF` — rAF never fires in occluded/background
tabs, which would silently skip the sync).

---

## 6. The scroll contract — `_syncScroll()` (lines 315–344)

**v7.3.6 STAY-PUT rule:** a content swap must **not** move the viewport — the tool swaps
in place, so the user is never nudged down and forced to scroll back up between taps.
Exactly **two** cases scroll, both to the card-top anchor:

1. **Lead-form opening** (`this.leadOpen`) — the one intended movement; the form takes
   over the view.
2. **Rescue** — the card top has scrolled *above* the viewport (deep inside a long list
   like "Alla eljobb"); without a nudge the shorter new content would strand the user
   below the card. Note the rescue only scrolls **up to** the card (`top < anchor - 2`),
   never down to it.

**The v7.3.8 `--ampy-header-h` probe (lines 322–330).** The Bricks sticky header height
is a CSS custom property. Reading it with `parseFloat` on the raw value was wrong:
`parseFloat('8rem') === 8` (px, ~10× too high) and `parseFloat('calc(...)') === NaN`.
The fix appends a hidden probe element whose `height` is `var(--ampy-header-h, 0px)` and
reads its resolved **`getBoundingClientRect().height`** in px (any unit/calc resolved by
the browser), then removes it. Falls back to `0` on any failure (and is `0` in the static
prototype, which has no such header):

```js
const probe = document.createElement('div');
probe.style.cssText = 'position:absolute;visibility:hidden;pointer-events:none;height:var(--ampy-header-h,0px);';
document.body.appendChild(probe);
headerH = probe.getBoundingClientRect().height || 0;
probe.remove();
```

`anchor = headerH + (isMobile ? 28 : 12)` px. The final move is
`window.scrollTo(0, Math.max(0, window.scrollY + top - anchor))`.

**Instant, not smooth (line 342):** deliberately `window.scrollTo(0, …)` (instant).
Smooth scrolling is rAF-driven and silently stalls in occluded/background tabs and can be
preempted by the view swap; an instant anchor is reliable on every device and reads as a
crisp slide change.

---

## 7. The lead flow (load-bearing — port carefully)

### The form (`renderLeadBlock`, lines 675–823)

Copy comes from `data.meta.lead_form`. Four **required** fields, built by the `field(…)`
factory (lines 693–708), in this order (lines 710–713):

| Field `name` | Label | `type` | `inputmode` | `autocomplete` |
|---|---|---|---|---|
| `namn` | Namn | text | — | name |
| `telefon` | Telefon | tel | tel | tel |
| `postnummer` | Postnummer | text | numeric | postal-code |
| `epost` | E-post | email | email | email |

Plus a **honeypot** input `name="webbplats"` (visually hidden, `tabindex="-1"`,
`aria-hidden`, lines 721–722) and a hidden `meddelande` value (added in the payload, not a
visible field). The form is `novalidate` — validation is done in JS.

**Client validation (lines 748–767):** presence-check only (`value.trim()` truthy) for
all four fields. If the honeypot has any value, submit is a silent no-op (`return`, line
751). Failures set `aria-invalid="true"`, wire `aria-describedby` to the error box, show
`f.error_required` (default `Fyll i alla fält så ringer vi upp dig.`), focus the first bad
field, and abort. Editing a field clears its invalid state (lines 744–746).

### `submitLead(job, verdictKey, fields)` (lines 836–873)

**Exact POST payload shape** (line 838 + the `fields` object assembled at lines 770–776):

```json
{
  "job_id":     "<job.id>",
  "verdict":    "<green|yellow|red>",
  "meddelande": "",
  "namn":       "<name>",
  "kontakt":    "<email>",     // NB: the email field posts as `kontakt`
  "telefon":    "<phone>",
  "postnummer": "<postal code>",
  "samtycke":   true,
  "webbplats":  "<honeypot, normally empty>"
}
```

Note the field-name remap: the visible `epost` input is sent as **`kontakt`** (matching
the PHP arg). `samtycke` (consent) is hard-set `true` — consent is implicit by submitting
(stated in the fineprint with an inline `integritetspolicy` link).

**The FRESH-NONCE pattern (lines 850–872).** Rather than trust the nonce baked into a
possibly full-page-cached HTML page, `submitLead` first does an uncached **GET** for a
fresh nonce, then **POST**s the lead:

```js
const nonceUrl = cfg.restUrl.replace(/\/lead\/?$/, '/nonce'); // derive /nonce from /lead
const freshNonce = this._fetchT(nonceUrl, {}, 8000)
  .then(r => (r.ok ? r.json() : null))
  .then(j => (j && j.nonce) || cfg.restNonce || '')   // fall back to the localized nonce
  .catch(() => cfg.restNonce || '');
return freshNonce.then(nonce => this._fetchT(cfg.restUrl, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
  body: JSON.stringify(payload)
}, 15000)).then(r => { … });
```

**v7.3.8 fixes in this path:**

- **No `X-WP-Nonce` header on the nonce GET.** The GET route is public
  (`permission_callback => __return_true`). WP core validates *any* present nonce header
  for cookie-authenticated requests, so sending a stale nonce made the refresh itself 403
  for logged-in sessions — the exact case the refresh exists for. The GET therefore sends
  no nonce header (`this._fetchT(nonceUrl, {}, 8000)`).
- **Hard timeouts via `_fetchT` (lines 828–834): 8 s** on the nonce GET, **15 s** on the
  lead POST. `_fetchT` wraps `fetch` in an `AbortController` (guarded — if
  `AbortController` is undefined it degrades to a plain `fetch`) with a `setTimeout(…abort)`
  cleared in `.finally`. On flaky mobile radio a request can hang at TCP level without
  erroring; abort turns the hang into a catchable rejection so the submit button never
  sticks on "Skickar…".
- **Surfacing the server's specific Swedish 400 message.** On a non-`ok` response,
  `submitLead` parses the JSON body and, if it has a `message`, attaches it as
  `err.serverMessage` and throws (lines 860–870). The form's `.catch` (lines 810–818)
  prefers `err.serverMessage` over the generic text, so a fixable input error (e.g. `Ange
  ett giltigt postnummer (5 siffror).`) shows precisely instead of reading as an outage.

**Static-prototype path (lines 840–841):** if there is no `window.AmpyBK` or no
`cfg.restUrl`, `submitLead` returns `new Promise(res => setTimeout(res, 600))` — a
simulated success so the prototype demonstrates the full success state with no backend.

**Success state + re-engagement loop (lines 777–809).** On resolve it fires
`lead_submitted`, personalises the title with the submitter's first name
(`(f.success_title || 'Tack {namn}! …').replace('{namn}', firstName)`, or the no-name
variant), and `replaceChildren`s a success card with a check icon, body copy, and a
**"Kolla ett annat eljobb"** button that resets `activeRoom`/`heroExpanded` and
`navigate({ jobId: null, answerIndex: null })` back to slide 1. The scroll/focus contract
is applied to the success swap too.

### The REST route (verified against `includes/lead-endpoint.php`)

- **Namespace / routes:** `ampy-bk/v1` → `POST /lead` (callback `ampy_bk_handle_lead`) and
  `GET /nonce` (returns `{ nonce }` with `Cache-Control: no-store, max-age=0`).
- **Nonce action string: `wp_rest`** on both sides — JS sends the header `X-WP-Nonce`;
  PHP `permission_callback` does `wp_verify_nonce($request->get_header('X-WP-Nonce'),
  'wp_rest')` (lead-endpoint.php:39–42), and `/nonce` mints `wp_create_nonce('wp_rest')`
  (line 26).
- **Server-side arg names** (lead-endpoint.php:43–53): `job_id`*, `verdict`*, `namn`*,
  `kontakt`* (e-post), `telefon`*, `postnummer`*, `meddelande`, `samtycke`* (boolean),
  `webbplats` (honeypot). (`*` = `required`.) These **exactly** match the JS payload keys
  above — this is the seam that must not drift.
- **Server behaviour:** honeypot → fake `{ok:true}`; per-IP transient rate limit (15 /
  10 min → 429); sanitize + validate (email, phone regex `^[\d\s\+\-\(\)]{6,}$`, postnummer
  `^\d{5}$`, verdict ∈ {green,yellow,red}); job_id must exist in the data file; `wp_mail`
  to `admin_email`; `do_action('ampy_bk_lead_received', …)` for a CRM/CPT sink; on mail
  failure logs a structured `AMPY_BK LEAD (mail failed)` line and returns 500 with a
  phone-number fallback message. The specific 400/500 messages are what the JS surfaces
  via `err.serverMessage`.

---

## 8. Analytics — `track(event, props)` (lines 118–127)

Dual sink, both `try/catch`-wrapped (never throws into the UI):

```js
function track(event, props) {
  const payload = Object.assign({ event: 'elkollen_' + event, elkollen_action: event }, props || {});
  try { window.dataLayer = window.dataLayer || []; window.dataLayer.push(payload); } catch (e) {}
  try { window.dispatchEvent(new CustomEvent('elkollen:track', { detail: payload })); } catch (e) {}
}
```

- **Sink 1:** `window.dataLayer.push` (GA4 / GTM). The pushed `event` field is
  **prefixed** — `event: 'elkollen_' + event` — and `elkollen_action` carries the bare
  name.
- **Sink 2:** a DOM `CustomEvent('elkollen:track')` with the same payload in `detail`, so
  any tool (Plausible, a server-side proxy) can subscribe. No-ops cleanly if nothing
  listens.

**Actual events emitted** (grep of `track('…`):

| Event (bare name) | Where | Key props |
|---|---|---|
| `tool_view` | constructor, 149 | `layout`, `deep_link` |
| `room_selected` | 986 | `room_id` |
| `see_all` | 1005 | — |
| `list_view` | 1079 | `source` (room id or `'all'`) |
| `job_selected` | 194 | `job_id` |
| `question_answered` | 202 | `job_id`, `answer_index`, `option_verdict` |
| `question_shown` | 263 | `job_id`, `question_type` |
| `verdict_shown` | 266 | `job_id`, `verdict` |
| `cta_click` | 645, 652 | `job_id`, `verdict`, `cta` (`advice`\|`read_more`) |
| `lead_form_open` | 220 | `job_id`, `verdict` |
| `lead_submitted` | 778 | `job_id`, `verdict` |

**Impression dedup (`_trackShown`, lines 185–189).** `question_shown` and `verdict_shown`
go through `_trackShown(event, props, key)`, which stores `_lastShownKey` and no-ops if the
key is unchanged — so opening/closing the lead form or Back/Forward to an unchanged view
doesn't inflate impressions. Keys: `'q:' + job.id` and `'v:' + job.id + ':' + verdictKey`.
`_lastShownKey` is reset to `null` when leaving to the entry screen (line 259) and
deliberately left intact when the lead form opens/closes over a verdict (line 255).

---

## 9. The JS ⇄ PHP contract (the seam Chris must not break)

### `window.AmpyBK` (localized by `ampy-behorighetskollen.php:102–106`)

```php
wp_localize_script( 'ampy-bk', 'AmpyBK', array(
  'data'      => $data,                                          // the whole data JSON
  'restUrl'   => esc_url_raw( rest_url( 'ampy-bk/v1/lead' ) ),   // absolute POST URL
  'restNonce' => wp_create_nonce( 'wp_rest' ),                   // fallback nonce
) );
```

| Global field | Produced by | Consumed by |
|---|---|---|
| `window.AmpyBK.data` | `wp_localize_script` | `boot()` (line 1186) — the injected-data path |
| `window.AmpyBK.restUrl` | `rest_url('ampy-bk/v1/lead')` | `submitLead` — POST target + nonce-URL base |
| `window.AmpyBK.restNonce` | `wp_create_nonce('wp_rest')` | `submitLead` — fallback if the fresh GET fails |

### POST field ↔ PHP arg map

| JS payload key | PHP arg (lead-endpoint.php) | Notes |
|---|---|---|
| `job_id` | `job_id` (required) | validated against data-file job ids |
| `verdict` | `verdict` (required) | must be green/yellow/red |
| `namn` | `namn` (required) | |
| `kontakt` | `kontakt` (required) | the **email** (JS remaps `epost`→`kontakt`) |
| `telefon` | `telefon` (required) | |
| `postnummer` | `postnummer` (required) | server strips non-digits, needs 5 |
| `meddelande` | `meddelande` (optional) | JS always sends `''` |
| `samtycke` | `samtycke` (required, boolean) | JS hard-sets `true` |
| `webbplats` | `webbplats` (optional) | honeypot |

### Nonce

- **Action string `'wp_rest'`** on both sides.
- **Header `X-WP-Nonce`** on the POST only (not on the nonce GET).
- **Nonce-URL derivation regex (JS, line 850):**
  `cfg.restUrl.replace(/\/lead\/?$/, '/nonce')` — turns `…/ampy-bk/v1/lead` into
  `…/ampy-bk/v1/nonce`. If you change the route names, keep both sides in sync or this
  regex silently produces a bad URL and the code falls back to the localized nonce.

---

## 10. Porting to Fluent Snippets

**The JS is byte-identical between the plugin and the snippet.** It already reads
`window.AmpyBK.data` (injected-data path) and only falls back to fetching
`data-data-url` when that's absent. So the snippet ships the **same** `behorighetskollen.js`
(and CSS) unchanged.

**The single hard requirement:** the PHP snippet must inject a `window.AmpyBK` object —
with at least `data`, and (for live leads) `restUrl` + `restNonce` — **before** the JS
runs, exactly as `wp_localize_script` does in the plugin (fields per §9). Without `data`,
the JS falls back to the fetch path and needs a valid `data-data-url` on the mount; without
`restUrl`, the lead form silently runs the simulated-success prototype path and **no lead
is ever POSTed**. Also register the two REST routes (or an equivalent endpoint) so the
`/nonce` GET and `/lead` POST resolve, using nonce action `'wp_rest'`.

**Browser-compat floor:**

- `replaceChildren` (used for every view swap) → **Safari 14+ / Chromium 86+ / Firefox
  78+**. This is the effective minimum.
- `AbortController` is **guarded** (`_fetchT` degrades to plain `fetch` where it's
  undefined) — so it doesn't lower the floor, but on very old engines the lead POST won't
  time out.
- Otherwise ES6 (`class`, arrow functions, template literals, `Object.fromEntries`,
  `URLSearchParams`, `CustomEvent`, `String.localeCompare(…, 'sv')`) — all comfortably
  within the Safari 14 / Chromium 86 floor.

---

## Appendix — quick function index

| Symbol | Lines | Role |
|---|---|---|
| IIFE | 9–1226 | module scope |
| `ICONS` / `icon()` | 13–73 | inline-SVG table + safe lookup |
| `el()` | 76–93 | DOM builder (textContent vs `html:`→innerHTML) |
| `resolve()` | 96–108 | pure engine |
| `track()` | 118–127 | dual-sink analytics |
| `ElkollenApp` ctor | 130–150 | state init, `bindHistory`, `tool_view` |
| `readUrl` / `writeUrl` | 152–172 | URL ⇄ state |
| `bindHistory` (popstate) | 174–181 | Back/Forward |
| `_trackShown` | 185–189 | impression dedup |
| `navigate` / `openLead` / `closeLead` / `backOne` | 191–236 | transitions |
| `render` | 238–303 | mode select + swap + focus/scroll |
| `_syncScroll` | 315–344 | STAY-PUT scroll contract |
| `renderCrumb` | 349–369 | back control + job title |
| `renderQuestionBlock` | 374–417 | question slide |
| `renderVerdictBlock` / `renderTabBody` / `renderExplain` / `renderTips` / `renderConsequence` | 422–629 | verdict slide |
| `renderCta` | 634–668 | advice CTA + read-more |
| `renderLeadBlock` | 675–823 | lead form + success |
| `_fetchT` | 828–834 | fetch + AbortController timeout |
| `submitLead` | 836–873 | fresh-nonce POST |
| `renderEntryBlock` / `renderHeroEntry` | 882–1087 | entry + hero funnel |
| `renderSwap` / `renderJobList` / `renderJobRow` | 1089–1143 | neutral job lists |
| `_shortLabel` / `_shortCitation` / `_resolveSource` | 1145–1172 | text/source helpers |
| `boot` / `maybeApplyPreselect` | 1177–1217 | entry points |
| global exports | 1219–1221 | `window.AmpyBK.{resolve,boot}` |
