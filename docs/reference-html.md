# Elkollen — HTML / Markup Reference (v7.3.8)

**Audience:** Chris (developer re-implementing Elkollen in Bricks / WordPress as Fluent Snippets).
**Rule of this document:** **CODE IS TRUTH.** Every snippet below was transcribed from the actual files
in this repo at plugin version **7.3.8** (`AMPY_BK_VERSION = '7.3.8'`, assets cache-busted with `?v=738`).
Where the brief that commissioned this doc referenced something the code does not actually contain, that is
called out explicitly under **Drift & corrections** at the end — do not trust older docs over the files.

Source files this document is built from:
- `ampy-behorighetskollen.php` — plugin header, shortcode, asset enqueue, dynamic OG meta.
- `includes/render.php` — the server-rendered mount + crawlable no-JS fallback.
- `preview/hero.html` — the HERO split-layout prototype (never ships; carries a QA bar).
- `preview/index.html` — the EMBEDDED prototype (never ships; carries a QA bar).
- `assets/behorighetskollen.js` — builds the entire runtime DOM (entry / question / verdict / lead).
- `assets/behorighetskollen.css` — the `[data-booted]` backstop and verdict tokens referenced here.
- `elkollen-fluent-snippets/elkollen.php` + `preview.html` — the FluentSnippets edition (porting target).

---

## 1. The two render contexts

Elkollen ships one tool that renders in **two page contexts**, selected by the shortcode `layout` attribute.
In **both** contexts the plugin only owns the markup **inside `.ampy-bk`**. Everything around it (page heading,
hero copy, CTAs, marketing sections) is native Bricks / page markup that the plugin never emits.

### (a) EMBEDDED — `[elkollen]` — service pages (`preview/index.html`)

Default, centered/embedded behaviour. Used on service pages. In Bricks you place a Heading + Paragraph
**above** the shortcode; the tool never carries its own page heading.

The page-chrome (Bricks-owned) that sits above the mount in the prototype:

```html
<header class="ampy-bk__page-head">
  <h2 class="ampy-bk__page-heading">Koppla elen</h2>
  <p class="ampy-bk__page-lead">Se direkt vilka eljobb du får göra själv.</p>
</header>
```

Note this is an **`<h2>`** — see §4 (one H1 per page). Below it, the shortcode outputs the mount (see §2).
The embedded mount has **no** `data-layout` attribute (empty = embedded).

### (b) HERO split-layout — `[elkollen layout="hero"]` — landing page (`preview/hero.html`)

A two-column split. The **LEFT column is native Bricks / page markup**; the **RIGHT column is the tool mount**.
Bricks owns: the H1, the lead paragraph, the 3 trust bullets, the two CTAs ("Kontakta oss" / phone), and the
mobile foot ("ask" line). The plugin owns only the `.ampy-bk--hero` mount in the right column.

**Layout grid (chrome CSS, Bricks-owned).** The prototype builds the split with CSS grid areas. On desktop
(≥768px, v7.3) the left column is **pinned, not re-centered** — a fixed `9.6rem` top spacer holds the head row in
place while the right-hand tool card grows/shrinks between slides:

```css
.hero__grid {
  display: grid;
  grid-template-columns: minmax(38rem, 44fr) minmax(46rem, 56fr);
  grid-template-areas: "head tool" "foot tool";
  column-gap: 6.4rem; row-gap: 0; align-items: start;
}
@media (min-width: 768px) {
  .hero__grid {
    grid-template-rows: 9.6rem auto auto 1fr;   /* fixed spacer pins the left column */
    grid-template-areas: ".    tool" "head tool" "foot tool" ".    tool";
  }
}
```

**Exact LEFT-column hero markup** (transcribe 1:1 into Bricks native elements). This is the canonical structure
from `preview/hero.html`. The `id="…"` hooks exist only so the prototype can hydrate copy from the data file
(`meta.hero`); in Bricks you simply type the strings into the elements — the ids are optional.

```html
<div class="elkollen-root" data-hero-theme="light">
  <header class="hero">
    <div class="wrap hero__grid">

      <!-- HEAD: H1 + sub (Bricks-owned) -->
      <div class="hero__head">
        <h1 class="hero__h1" id="hero-h1">Får du göra eljobbet själv?
          <span class="hero__h1-key">Kolla innan du kopplar.</span></h1>
        <p class="hero__sub" id="hero-sub">Välj installationen du funderar på och få ett tydligt besked direkt!</p>
      </div>

      <!-- TOOL: plugin-owned mount (hero layout), theme-agnostic -->
      <div class="hero__tool">
        <div class="ampy-bk ampy-bk--hero" data-layout="hero" data-data-url="../data/behorighetskollen-data.json">
          <!-- no-JS fallback lives here; see §2 -->
        </div>
      </div>

      <!-- FOOT (Bricks-owned): desktop = 3 trust bullets + 2 buttons;
           mobile = ask line + stacked buttons -->
      <div class="hero__foot">
        <ul class="hero__trust">
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
              Byggt på Elsäkerhetslagen och Elsäkerhetsverket</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>
              Registrerat elinstallationsföretag</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
              <span id="hero-trust-3">Få ett tydligt besked på några sekunder</span></li>
        </ul>
        <p class="hero__foot-ask" id="hero-foot-ask">Hellre prata med en elektriker direkt?</p>
        <div class="hero__actions">
          <a class="hero__btn hero__btn--primary" id="hero-btn-primary" href="https://ampy.se/offert/">
            <span id="hero-btn-primary-label">Kontakta oss</span>
            <svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path
              d="M4.66406 11.3333L11.3307 4.66663M11.3307 4.66663H4.66406M11.3307 4.66663V11.3333"
              stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          <a class="hero__btn hero__btn--secondary" id="hero-btn-secondary" href="tel:+46102657979">
            <span id="hero-btn-secondary-label">010-265 79 79</span>
            <svg viewBox="0 0 19 20" fill="none" aria-hidden="true"><path d="M11.9146 4.16668C12.6878 …Z"
              stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        </div>
      </div>

    </div>
  </header>
</div>
```

**CTA hrefs (verified, hard-code these in Bricks):**
- Primary "Kontakta oss" → `https://ampy.se/offert/`
- Secondary phone "010-265 79 79" → `tel:+46102657979`

**Button recipe (v7.3.5 + v7.3.6).** `.hero__btn` is `display:inline-flex` with
`justify-content:space-between` so the **label sits left and the icon sits at the right edge**; on desktop the two
buttons are `flex:1 1 0` (equal width, filling the row). Radius `16px`, Outfit 400, ink `#0d0d0d`, gradient fill,
no border, soft glow shadow, `translateY(-1px)` on hover. The icon is on the RIGHT for both. Primary =
`linear-gradient(120deg, #55ff9a 0%, #5eb1bf 100%)`; secondary phone =
`linear-gradient(141deg, #b6f2ff 0%, #5eb1bf 100%)` with stroke `#212121` on its icon. **v7.3.6** adds a
desktop-only (`≥1024px`) +2px bump to the H1 cap (3.7→4.0rem), the sub paragraph, and the trust bullets, to
balance against the taller CTAs. **v7.3.5** introduced the space-between edge-icon seating.

**Mobile order — `column-reverse` (phone first).** On `≤767px` the trust bullets hide, and the actions stack
full-width. The DOM order stays "Kontakta oss" then phone, but CSS reverses it so the **phone number renders
FIRST** and "Kontakta oss" second, under the "Hellre prata med en elektriker direkt?" ask line:

```css
@media (max-width: 767px) {
  .hero__trust { display: none; }
  .hero__foot-ask { display: block; }                 /* hidden on desktop */
  .hero__actions { flex-direction: column-reverse; align-items: stretch; gap: 1.2rem; }
  .hero__btn { width: 100%; max-width: 100%; flex: 0 0 auto; }
}
```

So: **desktop** = Kontakta-left / phone-right, no DOM reorder; **mobile** = phone on top, then Kontakta, under
the ask line.

> The `data-hero-theme="light|dark"` attribute on `.elkollen-root` in the prototype is an **A/B harness only**.
> Nothing inside `.ampy-bk` is themed (the white tool card is byte-identical in both arms). In production pick
> one arm; the theme toggle button and the two hydration `<script>`s at the bottom of `hero.html` never ship.

---

## 2. The server mount (`render.php`)

`ampy_bk_render_mount( $preselect, $data, $layout )` emits the mount element plus a crawlable no-JS fallback.
The exact element and its **verified** data-attributes:

```php
<div class="ampy-bk<?php echo $hero ? ' ampy-bk--hero' : ''; ?>"
     data-base-path="<?php echo esc_attr( AMPY_BK_URL ); ?>"
     data-data-url="<?php echo esc_url( AMPY_BK_URL . 'data/behorighetskollen-data.json' ); ?>"
     <?php if ( $hero ) : ?>data-layout="hero"<?php endif; ?>
     <?php if ( $preselect ) : ?>data-preselect-job="<?php echo esc_attr( $preselect ); ?>"<?php endif; ?>>
```

Rendered attribute names (this is the contract the JS reads — do not rename):

| Attribute | Emitted when | Read by JS? | Purpose |
|---|---|---|---|
| `class="ampy-bk"` | always | yes (`querySelectorAll('.ampy-bk')`) | boot selector + mount root |
| `class="ampy-bk--hero"` | `layout="hero"` only | via CSS only | hero surface styling |
| `data-layout="hero"` | `layout="hero"` only | **yes** (`mount.dataset.layout`) | switches JS to hero entry (rooms grid + drawer) |
| `data-data-url` | always | **yes** (`mount.dataset.dataUrl`) | fallback fetch URL when data isn't injected via `window.AmpyBK` |
| `data-preselect-job="<id>"` | when a valid `jobb` att is passed | **yes** (`mount.dataset.preselectJob`) | deep-links straight to a job (writes `?jobb=` on boot) |
| `data-base-path` | always | **no** (emitted, unused by JS) | present for completeness; JS ignores it |
| `data-booted="true"` | set by JS at runtime | set by JS | flips the CSS backstop that hides the fallback |

Note: `ampy-bk--hero` **and** `data-layout="hero"` are applied **together** (both gated on the same `$hero`
flag). The JS keys behaviour off `data-layout`; the CSS keys surface off the class.

**Crawlable no-JS fallback.** Inside the mount, before JS boots, the server renders the full job list as real
`<a href="?jobb=…">` links so crawlers and no-JS users get real HTML in the same instrument container:

```php
<div class="ampy-bk__noscript">
    <div class="ampy-bk__instrument">
        <p class="ampy-bk__tagline"><?php echo esc_html( $lead ); ?></p>
        <ul class="ampy-bk__noscript-grid" role="list">
            <?php foreach ( $data['jobs'] as $job ) : ?>
                <li><a href="?jobb=<?php echo esc_attr( $job['id'] ); ?>"><?php echo esc_html( $job['label'] ); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <p class="ampy-bk__source-line"><?php echo esc_html( $source ); ?></p>
    </div>
</div>
```

The fallback is a **flat link list** — the no-JS view never "asks" anything; each link resolves to the JS
question/verdict step once scripts run. `$lead` defaults to `meta.page_lead` ("Se direkt vilka eljobb du får
göra själv."); `$source` defaults to `meta.source_line`. (The embedded `preview/index.html` fallback additionally
renders a `<p class="ampy-bk__disclaimer">` line; `render.php` itself does not — see Drift.)

**How JS removes the fallback (two mechanisms):**

1. **JS removal.** On first `render()` the app removes the node and stamps the mount:
   ```js
   const noscript = this.mount.querySelector('.ampy-bk__noscript');
   if (noscript) noscript.remove();
   this.mount.dataset.booted = 'true';
   ```
2. **CSS backstop** (`assets/behorighetskollen.css:966`) — hides the fallback the instant `data-booted` is set,
   even before the node is torn out, so there is no flash:
   ```css
   .ampy-bk[data-booted="true"] .ampy-bk__noscript { display: none; }
   ```

---

## 3. The runtime DOM (built by JS)

`assets/behorighetskollen.js` is a vanilla-JS SPA (no framework). All markup below is **generated at runtime**
via the `el(tag, attrs, children)` helper — Chris does **not** author it, but should know the tree because the
CSS in `behorighetskollen.css` targets exactly these class names. Every view is a single `.ampy-bk__block`
swapped into the mount via `mount.replaceChildren(block)`.

The engine (`resolve(job, answerIndex)`) returns one of four kinds → four DOM shapes:
`fixed` job → verdict; `conditional` job with no answer → question (`ask`); with an answer → verdict;
lead form is an overlay state on top of a verdict.

### 3.1 Entry — EMBEDDED (`renderEntryBlock`)

```
div.ampy-bk__block[role=region][aria-label="Elkollen"]
├─ div.ampy-bk__search
│  ├─ label.ampy-bk__search-label[for=ampy-bk-search]  "Sök eljobb"
│  └─ div.ampy-bk__search-field
│     ├─ span.ampy-bk__search-icon (svg)
│     └─ input.ampy-bk__search-input#ampy-bk-search[type=search][placeholder="T.ex. vägguttag, badrum, spis…"]
├─ ul.ampy-bk__rooms[role=tablist]
│  └─ li > button.ampy-bk__room[role=tab][aria-selected][data-room=<id>]   (× rooms)
│        ├─ span (room icon svg)
│        └─ span (room label)
├─ div.ampy-bk__swap[role=region][aria-live=polite]         ← job list swaps in here
│  └─ (p.ampy-bk__joblist-hint "Vanliga eljobb")?  +  ul.ampy-bk__joblist
└─ p.ampy-bk__source-line
```

### 3.2 Entry — HERO (`renderHeroEntry`) — slides 1 + 2

Slide 1 = a card heading + six identical **room tiles** (5 rooms + "Alla eljobb"). Slide 2 = an in-panel
**drawer** with a neutral job list. The grid/heading hide and the drawer shows when a tile is tapped.

```
div.ampy-bk__block[role=region][aria-label="Elkollen"]
├─ h2.ampy-bk__entry-title[data-focus-target][tabindex=-1]   "Var i hemmet gäller det?"
├─ ul.ampy-bk__roomgrid[role=list]
│  └─ li > button.ampy-bk__roomtile[data-room=<id>]           (× 5 rooms + 1 "Alla eljobb")
│        ├─ span.ampy-bk__roomtile-chip (icon svg)
│        └─ span.ampy-bk__roomtile-body
│           ├─ span.ampy-bk__roomtile-label
│           └─ span.ampy-bk__roomtile-sub?
├─ div.ampy-bk__drawer[role=region][aria-live=polite][hidden]   ← slide 2
│  ├─ button.ampy-bk__drawer-back
│  ├─ p.ampy-bk__drawer-title[data-focus-target][tabindex=-1]
│  ├─ p.ampy-bk__list-subtitle
│  └─ ul.ampy-bk__joblist  →  li > button.ampy-bk__job-row …
└─ p.ampy-bk__source-line
```

The "Alla eljobb" tile uses the same `.ampy-bk__roomtile` anatomy with the `grid` icon and sorts jobs
Swedish-alphabetically (`localeCompare(…, 'sv')`).

### 3.3 The neutral job row (`renderJobRow`)

**One flat, neutral list — no verdict signal leaks before the choice step** (v7 F1: no colour dots, no group
headers). Teal marks interactivity only; the arrow is a **chevron** (drill-in navigation), not the CTA arrow:

```
li > button.ampy-bk__job-row[aria-label="Välj jobb: <label>"]
     ├─ span.ampy-bk__job-row-icon (job icon svg)
     ├─ span.ampy-bk__job-row-label
     └─ span.ampy-bk__job-row-arrow (chevronRight svg)
```

### 3.4 Question (`renderQuestionBlock`)

```
div.ampy-bk__block[role=region][aria-labelledby=ampy-bk-q]
├─ div.ampy-bk__crumb                                   ← back-crumb, carries the JOB NAME
│  ├─ button.ampy-bk__crumb-back  (arrowLeft + "Tillbaka")
│  ├─ span.ampy-bk__crumb-sep "·"
│  └─ span.ampy-bk__crumb-job  <job.chip_label || job.label>
├─ p.ampy-bk__q-title#ampy-bk-q[data-focus-target]      <job.question>
├─ ul.ampy-bk__options[role=list]
│  └─ li > button.ampy-bk__option
│        ├─ span.ampy-bk__option-body
│        │  ├─ span.ampy-bk__option-title
│        │  └─ span.ampy-bk__option-clarifier?
│        └─ span.ampy-bk__option-arrow (arrowRight svg)
└─ div.ampy-bk__info[role=note]
   ├─ span.ampy-bk__info-icon (info svg)
   └─ p.ampy-bk__info-text
```

The crumb carrying the job name is shared verbatim with the verdict step (v7.3.3), so the two screens read
consistently. `[data-focus-target]` is on the question title.

### 3.5 Verdict (`renderVerdictBlock`)

```
div.ampy-bk__block[role=region][aria-labelledby=ampy-bk-v][data-verdict=green|yellow|red]
├─ div.ampy-bk__crumb (back + job name)
├─ div.ampy-bk__judgment.ampy-bk__judgment--<verdictKey>
│  ├─ div.ampy-bk__judgment-accent[aria-hidden]        ← full-height accent rule
│  └─ div.ampy-bk__judgment-body
│     ├─ h2.ampy-bk__badge#ampy-bk-v[data-focus-target]   (icon + verdict label)  ← see §4
│     └─ a.ampy-bk__verdict-src[target=_blank][rel=noopener noreferrer]?   (red/yellow only)
├─ div.ampy-bk__tabs?                                  ← only when 2 tabs exist
│  └─ button.ampy-bk__tab[aria-pressed][data-tab=explain|consequence|tips]
├─ div.ampy-bk__tab-body#ampy-bk-tab-body[role=region] ← re-rendered on tab change
│  ├─ (Förklaring) p.ampy-bk__summary
│  │              + div.ampy-bk__row.ampy-bk__row--do   (check icon + text)
│  │              + div.ampy-bk__row.ampy-bk__row--dont (x icon + text)
│  │              + div.ampy-bk__caveat[role=note]?     (green only)
│  ├─ (Tips)        ul.ampy-bk__tips → li.ampy-bk__tip--do | .ampy-bk__tip--dont
│  └─ (Konsekvenser) p.ampy-bk__consequence-text
└─ div.ampy-bk__cta-zone                               ← margin-top:auto pins to card floor
   ├─ button.ampy-bk__cta-primary.ampy-bk__cta-primary--solid   "Boka kostnadsfri rådgivning" (opens lead form)
   └─ a.ampy-bk__cta-secondary?                                  "Läs mer om …" (job.service_page_url)
```

Key contracts: `block.dataset.verdict = verdictKey` drives the §5 verdict-reveal wash + accent rule (CSS reads
`.ampy-bk__block[data-verdict="green|red|yellow"]`). The tabs are **toggle buttons with `aria-pressed`**, not an
ARIA `tablist` (the roving-tabindex/arrow-key contract is deliberately not implemented, so `role=tab` would
mislead). The primary CTA is a `<button>` (opens the in-tool lead form, no outbound jump); the secondary is an
`<a href>` to the service page, and is **skipped entirely** if `service_page_url` is missing (no dead href).

### 3.6 Lead form (`renderLeadBlock`)

```
div.ampy-bk__block.ampy-bk__lead[role=region][aria-labelledby=ampy-bk-lead-h]
├─ button.ampy-bk__lead-back  (arrowLeft + "Tillbaka till beskedet")
├─ h2.ampy-bk__lead-title#ampy-bk-lead-h[data-focus-target]  "Boka kostnadsfri rådgivning"
├─ p.ampy-bk__lead-intro
└─ form.ampy-bk__lead-form[novalidate]
   ├─ div.ampy-bk__lead-grid   (2×2 on desktop)
   │  ├─ div.ampy-bk__lead-field → label.ampy-bk__lead-label(+span.ampy-bk__req "*") + input.ampy-bk__lead-input
   │  │     fields, in order: namn → telefon → postnummer → epost   (all required)
   │  ├─ … (telefon)   [type=tel  inputmode=tel  autocomplete=tel]
   │  ├─ … (postnummer)[type=text inputmode=numeric autocomplete=postal-code]
   │  └─ … (epost)     [type=email inputmode=email autocomplete=email]
   ├─ input.ampy-bk__lead-hp[name=webbplats][tabindex=-1][autocomplete=off][aria-hidden=true]   ← HONEYPOT
   ├─ p.ampy-bk__lead-error#ampy-bk-lf-error[role=alert][hidden]
   ├─ button.ampy-bk__cta-primary.ampy-bk__cta-primary--solid.ampy-bk__lead-submit[type=submit]
   └─ p.ampy-bk__lead-fineprint  (…"integritetspolicy" as an inline <a>)
```

Field id pattern: `ampy-bk-lf-<name>`. Autocomplete tokens (`name`/`tel`/`postal-code`/`email`) drive the
one-tap contact autofill chip and satisfy WCAG 1.3.5. On success the form is replaced by
`div.ampy-bk__lead-success[data-focus-target]` (check icon + personalised `<h2>` + body + "Kolla ett annat
eljobb" button routing back to slide 1). The POST payload keys sent to the REST endpoint:
`{ job_id, verdict, meddelande, namn, kontakt (=email), telefon, postnummer, samtycke, webbplats }`.

### 3.7 Attribute contracts (verified)

| Attribute | Where | Meaning |
|---|---|---|
| `[data-focus-target]` | question title, verdict badge, lead title, drawer title, entry heading, success block | a11y focus lands here on each view swap: `target.focus({preventScroll:true})`, `tabindex="-1"` set dynamically |
| `[data-verdict="green\|yellow\|red"]` | verdict `.ampy-bk__block` | drives the verdict-reveal wash + accent rule in CSS |
| `[data-booted="true"]` | the mount | set by JS on first render; hides the no-JS fallback via CSS backstop |
| `[data-layout="hero"]` | the mount | switches JS to hero entry (rooms + drawer); read as `mount.dataset.layout` |
| `[data-preselect-job="<id>"]` | the mount | deep-links to a job on boot |
| `[data-room="<id>"]` | room tab / room tile buttons | identifies the room for selection state |
| `[data-tab="explain\|consequence\|tips"]` | verdict tab buttons | identifies the active segment |

> **`[data-visible]` does not exist** anywhere in the codebase (JS, CSS, PHP, previews) — see Drift. View
> visibility is handled by `[hidden]` on the drawer and by `replaceChildren()` swaps, not a `data-visible` flag.

---

## 4. Accessibility structure

- **One H1 per page.** The verdict badge is an **`<h2>`** (`h2.ampy-bk__badge`), the hero card heading is an
  `<h2>` (`.ampy-bk__entry-title`), the embedded page heading is an `<h2>` (`.ampy-bk__page-heading`), the lead
  title and success title are `<h2>`. The page **H1 belongs to Bricks / the hero left column** (`.hero__h1`).
  Keep exactly one `<h1>` on the page — do not let the tool emit one.
- **Focus management.** On every view swap, `render()` focuses `block.querySelector('[data-focus-target]:not([hidden])')`
  with `preventScroll:true`, then defers `_syncScroll()` by 60ms. The very first paint (boot) is skipped so the
  tool never steals focus/scroll on page load.
- **Roles / labels.** Views are `[role=region]` with `aria-labelledby` pointing at the heading id
  (`ampy-bk-q`, `ampy-bk-v`, `ampy-bk-lead-h`) or `aria-label="Elkollen"`. The swap/drawer regions use
  `aria-live="polite"`. The error box is `[role=alert][hidden]` toggled on validation. Rooms use `role=tablist`
  / `role=tab` / `aria-selected`; verdict tabs use `aria-pressed` (NOT `role=tab`, deliberately). All decorative
  SVGs carry `aria-hidden="true"`.
- **Honeypot.** `input[name="webbplats"]` — visually hidden, `tabindex="-1"`, `autocomplete="off"`,
  `aria-hidden="true"`. If it has a value on submit, the submit silently returns (bot). Real humans never see it.
- **Validation a11y.** Invalid fields get `aria-invalid="true"`; the first invalid field also gets
  `aria-describedby` pointing at the error box and receives focus; editing a field clears its invalid state.

---

## 5. The `<head>` / meta the page needs

**What the prototypes set (never ships as-is):**

`preview/hero.html`:
```html
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Får du göra eljobbet själv? | Elkollen, Ampy</title>
<meta name="description" content="Välj eljobbet du funderar på och få ett tydligt besked direkt, byggt på Elsäkerhetslagen." />
<link rel="stylesheet" href="../assets/behorighetskollen.css?v=738" />
```

`preview/index.html` additionally preconnects/loads Google Fonts (Outfit / Plus Jakarta Sans / JetBrains Mono)
and has its own `<title>`/description. In production **Bricks supplies the fonts and the base title/description**;
do not copy the prototype font `<link>`s verbatim if the theme already self-hosts Outfit.

**Preview-only, must NOT ship:**
- The **QA bar** (`.qa-bar` sticky header with jump links + the "Tema: light/dark" toggle button in hero.html).
- The two hydration `<script>`s at the bottom of hero.html (theme toggle + `meta.hero` copy hydration).
- `html { font-size: 62.5%; }` reset and the inline page-chrome `<style>` block (Bricks owns page chrome).

**The `--ampy-header-h` hook.** The prototype sets `:root { --ampy-header-h: 44px; }` to account for its sticky
QA bar. In production **Bricks sets `--ampy-header-h` to the real sticky-header height (0 if none)**. As of
**v7.3.7** the JS resolves this via a hidden **probe element** (`height:var(--ampy-header-h,0px)` measured with
`getBoundingClientRect()`), so it may be **any CSS length** now — `px`, `rem`, or `calc()` all resolve
correctly (the old `parseFloat` path silently broke on `rem`/`calc`). The tool uses it only to anchor the
card-top on the two scroll cases (lead-form open, and the deep-list rescue).

**Dynamic OG / Twitter meta (production, `ampy_bk_dynamic_og`).** Emitted by the plugin into `wp_head` only when
`?jobb=<id>` is present in the URL AND the page actually carries the tool. **As of v7.3.8 (gate added v7.3.7)**
the OG code is gated to tool pages: it early-returns unless `is_singular()` and the post contains the `[elkollen]`
/ `[behorighetskollen]` shortcode — or, Bricks-aware, the tool id appears in `_bricks_page_content_2` postmeta
(Bricks stores layout there, not in `post_content`). An `ampy_bk_og_enabled` filter is the escape hatch. Without
this gate, any URL site-wide with `?jobb=` appended emitted misleading Elkollen share previews. When it fires it
outputs (per matched job):

```html
<meta property="og:title"   content="Får jag göra <label-lowercase> själv? | Elkollen" />
<meta property="og:description" content="<job.summary or why_text, tags stripped>" />
<meta property="og:image"   content="…/assets/og/<id>.png  OR  …/assets/og/<verdict>.png" />
<meta name="twitter:image"  content="… (same) …" />
<meta name="twitter:card"   content="summary_large_image" />
<meta name="twitter:title"  content="… (same as og:title) …" />
<meta name="twitter:description" content="… (same as og:description) …" />
```

Image resolution: per-job override `assets/og/<id>.png` → per-verdict fallback `assets/og/<verdict>.png`
(green/yellow/red, 1200×630) → none. `mb_strtolower` is guarded for mbstring-less hosts (v7.3.7).

---

## 6. Porting to Fluent Snippets

There are **two valid drop-in paths**. Pick per page.

### Path A — one shortcode prints the WHOLE landing (FluentSnippets edition)

In the FluentSnippets edition (`elkollen-fluent-snippets/elkollen.php`), the PHP snippet's `[elkollen]`
shortcode prints **hero chrome + the tool mount together**, so Chris drops **one shortcode on a blank page** and
gets the full split-hero landing. `elkollen_shortcode()`:

- `[elkollen]` → full landing: `<div class="elkollen-root"><header class="hero"><div class="wrap hero__grid">`
  with a **LEFT `<div class="hero__copy">`** (H1 + sub + `ul.hero__trust` + `div.hero__cta-row` with two
  `a.ampy-btn` CTAs) and a **RIGHT `<div class="hero__tool">`** holding the `.ampy-bk--hero` mount, then a
  `<script>` injecting `window.AmpyBK` (`data` + `restUrl` + `restNonce`).
- `[elkollen tool_only="1"]` → just `<div class="elkollen-root">` + the mount + inject (no marketing chrome),
  for service-page embeds.
- `[elkollen jobb="laddbox"]` → preselect a job (adds `data-preselect-job`).

The mount markup is identical to §2 (`ampy-bk ampy-bk--hero`, no-JS fallback inside). The injected
`window.AmpyBK.data` means the JS uses the injected payload and never fetches the JSON (the `data-data-url`
fallback path is not exercised).

**Reference render:** `elkollen-fluent-snippets/preview.html` is the standalone render of exactly this output —
use it as the visual truth for Path A.

### Path B — native Bricks left column + shortcode right column

The approach the canonical `preview/hero.html` documents (§1b): build the LEFT column as **native Bricks
elements** (Heading = H1, Paragraph = sub, a list for the 3 trust bullets, two Button elements for the CTAs with
the hrefs `https://ampy.se/offert/` and `tel:+46102657979`), and drop `[elkollen layout="hero"]` in the RIGHT
column to render only the tool mount. This keeps the marketing copy editable in Bricks and lets the plugin own
only `.ampy-bk`. Use Path B when the page is built in Bricks and you want native, translatable chrome; use
Path A when you want a single-shortcode blank-page drop-in.

**Both paths share:** the same `assets/behorighetskollen.js` + `.css`, the same `.ampy-bk` mount contract, the
same REST lead endpoint (`ampy-bk/v1/lead`), and the same data file as single source of truth.

---

## Drift & corrections (found while writing — CODE IS TRUTH)

1. **Version.** The plugin is **7.3.8** (`ampy-behorighetskollen.php`, `AMPY_BK_VERSION`), assets `?v=738`.
   The repo's `CLAUDE.md` header still says the current version is **7.3.7** — stale by one point release.
   Older docs previously said 5.7.9; ignore those. This doc is authored against **7.3.8**.

2. **`[data-visible]` does not exist.** The commissioning brief listed `[data-visible]` as an attribute
   contract. Grep across `assets/`, `includes/`, `preview/`, and the plugin file finds **zero** occurrences.
   Visibility is `[hidden]` on the drawer plus `replaceChildren()` swaps. Documented as non-existent above.

3. **`data-base-path` is emitted but unused.** `render.php` prints `data-base-path="<AMPY_BK_URL>"`, but the JS
   never reads `mount.dataset.basePath`. It is inert. Only `data-data-url`, `data-layout`, and
   `data-preselect-job` are actually consumed by the JS. Do not build behaviour on `data-base-path`.

4. **FluentSnippets hero markup diverges from the canonical prototype.** `preview/hero.html` (the canonical
   Path-B chrome) uses `.hero__head` / `.hero__foot` / `.hero__actions` / `.hero__btn--primary|--secondary`
   with the v7.3.5 space-between edge-icon buttons and the copy "Får du göra eljobbet själv? Kolla innan du
   kopplar." The FluentSnippets `elkollen.php` (Path A) instead emits an **older** structure —
   `.hero__copy` / `.hero__cta-row` / `.ampy-btn.ampy-btn--primary|--phone` — with different copy ("Får du fixa
   elen själv? Få svaret innan du börjar." and a "Helt gratis. Inget mejl…" trust bullet). Same hrefs
   (`/offert/`, `tel:+46102657979`), different class names, copy, and button recipe. If Path A must be 1:1 with
   the current canonical hero, its chrome needs re-syncing to the `hero__head`/`hero__foot` structure. Flagged
   for owner/Chris decision; not changed here.

5. **`disclaimer` line placement.** `preview/index.html` renders an extra `<p class="ampy-bk__disclaimer">` in
   the no-JS fallback; `includes/render.php` does **not** (v7 collapsed to a single `meta.source_line`). The
   FluentSnippets edition renders the disclaimer only when `meta.disclaimer` is set. Minor, expected drift
   between the embedded prototype and the canonical server render — the server render is authoritative.
