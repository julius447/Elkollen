# Elkollen — Definitive CSS Reference (v7.3.8)

Developer documentation for `assets/behorighetskollen.css` (~1408 lines). Written for
Chris, who is re-implementing this tool inside Bricks/WordPress as Fluent Snippets.

> **CODE IS TRUTH.** This document was written by reading the actual current CSS file and
> confirming class usage against `assets/behorighetskollen.js`. Where the older docs
> (`HANDOVER.md`, `CHECKLIST.md`, `README.md`) disagree with the file, the file wins. Known
> drift between those docs and the real CSS is listed in the final section. All line numbers
> below refer to `assets/behorighetskollen.css` as of v7.3.8.

UI strings are Swedish (customer-facing). This document describes *structure*, in English.

---

## 1. Overview & scoping

### 1.1 What this file styles

The stylesheet styles **one component**: the Elkollen behörighetskollen tool — a 4-slide
funnel (room grid → neutral job list → question/choice step → verdict), plus an in-tool lead
form and success state. It renders in two layouts:

- **Embedded / default** — `.ampy-bk` alone. The tool sits inside a page as a 60rem-max card.
- **Hero** — `.ampy-bk.ampy-bk--hero`. The tool *is* the page hero's right column: the card
  fills its column, gets a min-height contract, and the verdict reveal gains the full-height
  accent rule + interior wash. Everything hero-specific is scoped under `.ampy-bk--hero` so the
  embedded tool is never touched (CSS lines 968–1010, 1218–1287).

### 1.2 Scoping discipline

**Every rule is scoped under `.ampy-bk`.** There are no bare element selectors that escape the
wrapper. The only global rule in the file is `html { font-size: 62.5%; }` (line 29) — see §1.4,
this is the one thing that leaks and the one thing you must handle at port time.

Box-sizing is reset only inside the wrapper (lines 129–133):

```css
.ampy-bk *, .ampy-bk *::before, .ampy-bk *::after { box-sizing: border-box; }
```

### 1.3 What is NOT in this file (do not look for it here)

The **preview-page chrome** — the split-hero landing shell (`.hero__grid`, `.hero__head`,
`.hero__h`, `.hero__sub`, `.hero__tool`, `.hero__actions`, `.hero__btn`, `.hero__foot`,
`.hero__trust`) and the theme host `.elkollen-root[data-hero-theme]` — lives in
**`preview/hero.html`**, NOT in this stylesheet. It is scaffolding for previewing the tool in a
hero context; it is not part of the shipped component. Do not port it and do not document it as
part of the tool. This reference covers `assets/behorighetskollen.css` only.

### 1.4 The 10px rem base — and the CRITICAL Fluent Snippets caveat

Line 29:

```css
html { font-size: 62.5%; }
```

This sets the root font-size to **10px** (62.5% of the browser default 16px). Every `rem` value
in this file is therefore read on a **1rem = 10px** base: `--space-7: 1.6rem` = 16px,
`--fs-16: 1.6rem` = 16px, `min-height: 4.8rem` = 48px, and so on. This is a deliberate authoring
convenience — the numbers in the file are px÷10.

> ### ⚠ CRITICAL PORT CAVEAT
> **You cannot ship `html { font-size: 62.5% }` into a WordPress theme.** It is a global rule.
> Dropping it into a live theme re-bases *the entire site's* rem-sized typography to a 10px
> root and shrinks the whole theme. For the Fluent Snippet port you have two safe options:
>
> 1. **Convert every `rem` to `px`** at 1rem = 10px (recommended for a wrapper-scoped snippet).
>    `1.6rem → 16px`, `4.8rem → 48px`, `0.4rem → 4px`, `56rem → 560px`, etc. The token
>    comments in the file already give you the px value for the spacing and type scales.
> 2. **Re-base the tokens** so they no longer depend on the 10px root (e.g. redefine the
>    `--space-*` / `--fs-*` custom properties in px directly on `.ampy-bk`, then delete the
>    `html{62.5%}` rule).
>
> Do **not** keep `html{62.5%}` and hope the theme tolerates it. This is the single most
> important porting decision in the file.

---

## 2. The font decision (v7.3.8) — there is deliberately NO `@font-face` / `@import`

**As of v7.3.8 this stylesheet loads no font.** There is no `@import`, no `@font-face`. The
top-of-file comment block (lines 18–27) documents exactly why, and it is intentional — do not
"helpfully" re-add a font import.

**The history (why it's empty):**

1. An `@import` for Outfit static weights originally sat lower in the file, **after** a style
   rule. Per the CSS spec an `@import` that is not at the very top of the sheet is **invalid and
   silently discarded by every browser**. So in practice this stylesheet **never loaded a font** —
   the approved, pixel-signed rendering has always been the **host page's font stack**.
2. In v7.3.7 the import was "fixed" (moved to the top so it actually loaded). Activating those
   static Outfit weights **changed the approved rendering across the whole tool**. The owner
   **rejected** that change.
3. In v7.3.8 the import was **removed for good**. Fonts are declared as the host page's job.

The font *family* is still requested in the CSS via `font-family` (line 119):

```css
font-family: 'Outfit', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
```

…but nothing in this file *provides* Outfit. If the host page doesn't serve Outfit, the tool
falls back to `system-ui`. The current approved reference look is that fallback stack.

**For production (what Chris must do):** self-host **Outfit** as a woff2 (latin subset **including
å ä ö**) via the Fluent Snippets delivery contract, at the theme/Bricks level — not inside this
component's CSS. Typography is weight-led (see §3.4): 400 body, 500 interactive, 600 headings,
700 for the verdict headline, so the woff2 must carry those weights (or be the variable font).

> **Owner approval gate:** once real Outfit is served, the rendering **will look different** from
> the current system-font render. The owner must approve the Outfit result against the approved
> previews before launch. This is an explicit sign-off item, not a silent swap.

---

## 3. The token system

All tokens are CSS custom properties defined on `.ampy-bk` (lines 31–127). Components **never**
use raw values — only these semantic variables. Enumerated below with real values and line refs.

### 3.1 Colour tokens (lines 33–63)

| Token | Value | Line | Role |
|---|---|---|---|
| `--bg-primary` | `#ffffff` | 33 | Card/surface/input background (white) |
| `--bg-secondary` | `#f4f5fb` | 34 | Hover fills, info notice, chip rest bg |
| `--bg-surface` | `rgb(9, 11, 50)` | 35 | Ampy midnight (defined; used as the dark surface value) |
| `--text-primary` | `rgb(9, 11, 50)` | 37 | Primary text (midnight) |
| `--text-secondary` | `#5a5d7a` | 38 | Body/secondary text |
| `--text-tertiary` | `rgb(104, 107, 128)` | 41 | Muted: source line, footnote, placeholder, crumb separator. **Darkened from old `#8a8da5` (3.26:1) to clear AA 4.5:1** |
| `--text-inverse` | `#ffffff` | 42 | Text on solid/dark fills |
| `--text-info` | `rgb(10, 122, 191)` | 43 | Info-notice icon (blue) |
| `--text-success` | `rgb(15, 110, 86)` | 44 | ✓ row/tip icons (dark green, AA on white) |
| `--action-primary` | `rgb(0, 169, 145)` | 49 | Brand teal — **borders/rings/icons only** (3:1 graphics rule) |
| `--action-primary-text` | `rgb(0, 110, 94)` | 50 | Teal **text** links (darkened to AA 4.5:1) |
| `--action-primary-strong` | `rgb(0, 122, 105)` | 51 | Solid primary-CTA fill + selected room chip fill (AA labels) |
| `--focus-ring` | `0 0 0 3px rgba(0,169,145,0.25)` | 52 | The teal focus ring (box-shadow) |
| `--state-success` | `rgb(54, 178, 92)` | 57 | Leafy green — verdict "do it yourself" (warmer than teal, kept distinct) |
| `--state-warning` | `rgb(245, 175, 25)` | 58 | Amber |
| `--state-error` | `rgb(214, 64, 64)` | 59 | Error red |
| `--border-default` | `#e3e5ed` | 61 | Default input/button border |
| `--border-tertiary` | `#ebedf3` | 62 | Hairline dividers, card border, list rows |
| `--border-focus` | `rgb(0, 169, 145)` | 63 | Focus outline colour (teal) |

**Verdict ramp defaults** (lines 66–68, overridden per-verdict — see §4.5):

| Token | Default value | Line |
|---|---|---|
| `--verdict-accent` | `var(--state-success)` | 66 |
| `--verdict-badge-bg` | `rgb(116, 200, 138)` | 67 |
| `--verdict-badge-fg` | `rgb(4, 52, 44)` | 68 |

### 3.2 Spacing scale (lines 71–83) — 10px rem base

| Token | rem | px | Token | rem | px |
|---|---|---|---|---|---|
| `--space-1` | 0.4rem | 4 | `--space-8` | 1.8rem | 18 |
| `--space-2` | 0.6rem | 6 | `--space-9` | 2.0rem | 20 |
| `--space-3` | 0.8rem | 8 | `--space-10` | 2.2rem | 22 |
| `--space-4` | 1.0rem | 10 | `--space-11` | 2.4rem | 24 |
| `--space-5` | 1.2rem | 12 | `--space-12` | 2.8rem | 28 |
| `--space-6` | 1.4rem | 14 | `--space-13` | 3.2rem | 32 |
| `--space-7` | 1.6rem | 16 | | | |

Note the scale is **non-linear** (2px steps early, 4px steps late). Port the px column directly.

### 3.3 Radius & shadow (lines 86–93)

| Token | Value | px | Line |
|---|---|---|---|
| `--radius-sm` | 0.6rem | 6 | 86 |
| `--radius-md` | 1.0rem | 10 | 87 |
| `--radius-lg` | 1.4rem | 14 | 88 |
| `--radius-full` | 999px | (already px) | 89 |
| `--shadow-sm` | `0 1px 2px rgba(11,13,42,0.04)` | — | 91 |
| `--shadow-md` | `0 6px 18px rgba(11,13,42,0.06)` | — | 92 |
| `--shadow-lg` | `0 14px 32px rgba(11,13,42,0.10)` | — | 93 |

`--radius-full` and the shadow blur/offset are already in px and don't need rem conversion.

### 3.4 Type scale (lines 100–111) — rem on a 10px base

**One truthful token set for BOTH layouts.** The old `.ampy-bk--hero` block that re-defined
`--fs-*` (e.g. `--fs-12: 1.3rem`) is deleted — token names that lie are treated as a defect
class (comment lines 95–99). Hero/mobile differences are **explicit per-element overrides**,
never token remaps.

| Token | rem | px | Where used |
|---|---|---|---|
| `--fs-11` | 1.1rem | 11 | source line, mobile only |
| `--fs-12` | 1.2rem | 12 | kicker, verdict-src |
| `--fs-13` | 1.3rem | 13 | source line desktop, fine print, clarifier mobile |
| `--fs-14` | 1.4rem | 14 | meta: crumb, labels, clarifiers, caveat, info |
| `--fs-15` | 1.5rem | 15 | secondary body: tips, rows mobile, tabs, lead intro |
| `--fs-16` | 1.6rem | 16 | **primary body + ALL buttons + ALL inputs (iOS 16px floor)** |
| `--fs-17` | 1.7rem | 17 | summary desktop, tile labels desktop |
| `--fs-18` | 1.8rem | 18 | verdict board mobile |
| `--fs-20` | 2.0rem | 20 | card/question/lead headings mobile, success h2 |
| `--fs-22` | 2.2rem | 22 | card/question/lead headings desktop |
| `--fs-24` | 2.4rem | 24 | verdict headline mobile |
| `--fs-30` | 3.0rem | 30 | verdict headline desktop |

**Weight-led typography (Outfit):** 400 body · 500 interactive · 600 headings · 700 reserved for
the verdict headline/badge. There is no reliance on many sizes; hierarchy comes from weight.

### 3.5 Block frame tokens (lines 114–116)

| Token | Value | Note |
|---|---|---|
| `--block-max-width` | `60rem` (600px) | Calm line length; hero overrides to `none` |
| `--block-padding-y` | `var(--space-9)` (20px) | |
| `--block-padding-x` | `var(--space-10)` (22px) | |

### 3.6 The teal discipline (hard rule 5) — load-bearing

This is documented intent in the file (comment lines 46–48, 54–56, 802–810, 1289–1298). It is an
accessibility + brand rule, and it is the reason there are **three** teal tokens instead of one:

- **Solid light-teal fill (`--action-primary`) is reserved for the primary CTA only.** It is not
  used as decorative chrome.
- **Teal as a functional accent** (focus rings, search/affordance icons, hire/advice text link)
  is allowed because it marks interactivity — but as an *accent* it only meets the 3:1 graphics
  rule, not the 4.5:1 text rule.
- **Teal TEXT uses `--action-primary-text`** `rgb(0,110,94)` (darkened to clear AA 4.5:1). This is
  why text links use a different token than icons/borders.
- **The selected room chip uses `--action-primary-strong`** `rgb(0,122,105)` — the **v7.3.8/v7.3.7
  AA fix**. White 13px text on the light teal was ~2.97:1 (AA fail); white on strong teal is
  ~5.3:1. Also, solid light-teal fill is reserved for the primary CTA, so the chip may not use it
  (lines 802–810).
- **The solid primary CTA fill uses `--action-primary-strong`** (lines 660–664).
- **The green verdict ramp is a warmer, leafy green (`--state-success`), kept visually distinct
  from teal** so "you may do it yourself" (green) never reads as the teal "hire us" action.

---

## 4. Component vocabulary

Every class below is confirmed **rendered by `assets/behorighetskollen.js`** (verified by grep).
Two rendered classes have **no CSS rule** — see the note at the end of this section.

### 4.1 Block & breadcrumb

**`.ampy-bk__block`** (lines 138–165) — the white card surface. `max-width: var(--block-max-width)`
(60rem), hairline `1px solid var(--border-tertiary)`, `--radius-lg`, `--shadow-md`,
`display:flex; flex-direction:column`. `gap:0` (spacing is per-element via margin-bottom). Has a
240ms `ampy-bk-block` fade-in (lines 161–165). `scroll-margin-top` gives a clean scroll-sync
landing. JS sets `dataset.booted = 'true'` on the mount at boot (line 966 gates the noscript
fallback off it).

**`.ampy-bk__crumb` / `.ampy-bk__crumb-back`** (lines 184–217) — the universal "← Tillbaka" back
control on every slide. The **44px tap target lives on the button** (`min-height: 4.4rem`, line
206), not on the row (`min-height:0`, line 191). SVG chevron 1.6rem. On mobile the crumb is
pulled up into the card padding (negative top margin, line 195).

**`.ampy-bk__crumb-sep` / `.ampy-bk__crumb-job`** (lines 221–231) — on the verdict screen the crumb
carries the job title: `← Tillbaka · {job}`. `--crumb-job` is 600-weight and truncates with
`overflow:hidden; text-overflow:ellipsis; white-space:nowrap; min-width:0` (overflow guard).

### 4.2 Entry mode — room grid & job list (hero) and room chips (embedded)

**`.ampy-bk__entry-title`** (lines 1093–1104) — slide-1's only heading (`--fs-22`, 600). The old
eyebrow + search field are gone from the entry step. `text-wrap: balance`.

**`.ampy-bk__roomgrid` + `.ampy-bk__roomtile`** (lines 1107–1194) — the 6-tile picker
(5 rooms + "Alla eljobb"). Grid is `repeat(2, minmax(0,1fr))` with `grid-auto-rows: 1fr` — the
**equal-size guarantee** (every tile pixel-identical). Tile: flex column, centered, `min-height:
11.2rem` (9.2rem mobile). Hover lifts (`translateY(-1px)` + `--shadow-md`), active `scale(0.98)`.

- `.ampy-bk__roomtile-chip` — 4rem circle, `rgba(0,169,145,0.08)` tint, teal glyph. SVG
  `stroke-width: 1.8` normalized (2.0 renders heavy).
- `.ampy-bk__roomtile-label` — `--fs-17`, 600.
- `.ampy-bk__roomtile-sub` — single-line ellipsis at all widths (overflow guard). This is what
  guarantees identical tiles on desktop too.
- The old `--roomtile--all` dashed/grey override is deleted; "Alla eljobb" is styled like the rooms.

**`.ampy-bk__search`** (lines 717–771) — the embedded search field (label + input + absolute
icon). `.ampy-bk__search-input` has `-webkit-appearance:none` to strip Safari search chrome,
`font-size: var(--fs-16)` (**iOS 16px zoom floor — see §5.4**), `min-height: 4.8rem`. The icon is
absolutely positioned and centered to the input box via `top:50%; transform:translateY(-50%)`.

**`.ampy-bk__rooms` + `.ampy-bk__room`** (lines 774–830) — the room chip row (embedded picker).
Chips are `--radius-full` pills, `min-height: 4.4rem` (44px tap target), teal glyph. Selected
(`[aria-selected="true"]`, set by JS) fills with **`--action-primary-strong`** + `--text-inverse`
(the AA fix, lines 805–809). On mobile the chip row becomes a horizontal scroller
(`flex-wrap:nowrap; overflow-x:auto`) with hidden scrollbar and a right-edge **mask-image fade**
signalling more chips (lines 812–830).

**`.ampy-bk__joblist` + `.ampy-bk__job-row`** (lines 833–878) — the **neutral** job list (slide 2):
one flat list, hairline-divided rows, **no group headers, no colour dots, no verdict word** — a
row must be unreadable as an answer. Row: `min-height: 5.2rem`, `--fs-16`, 500. Teal row icon =
interactivity only. Hover tints bg + label goes `--action-primary-text`. Arrow pushed right with
`margin-left:auto`.

**`.ampy-bk__joblist-hint`** (lines 833–839) and **`.ampy-bk__source-line`** (lines 880–889) — the
list helper line and the muted source/attribution footer (top-bordered). In hero the source line
is pinned to the card floor (`margin-top:auto`, line 1200).

### 4.3 The drawer (hero room results)

**`.ampy-bk__drawer`** (lines 1016–1030) — the results list after picking a room. Desktop scrolls
**inside** the card (`max-height:60vh; overflow-y:auto`); mobile flows in the page
(`max-height:none; overflow:visible`, lines 1025–1030). Children stagger-fade via `ampy-bk-fade`.

**`.ampy-bk__drawer-title` / `.ampy-bk__list-subtitle`** (lines 1033–1050) — slide-2 header
(`--fs-22`, 600) + helper subtitle.

**`.ampy-bk__drawer-back`** (lines 1053–1077) — "Visa vanliga eljobb" back link inside the drawer.
`--action-primary-text` (teal text). **The mobile override (lines 1071–1077) is deliberately
placed AFTER the base rule** — see §5.5 (cascade order). `min-height` 4.4rem desktop / 4rem mobile.

### 4.4 Question mode (the choice step)

**`.ampy-bk__q-title`** (lines 239–247) — the question heading (`--fs-22`, 600). The old uppercase
`--q-kicker` is retired; the job name now lives in the crumb.

**`.ampy-bk__options` + `.ampy-bk__option`** (lines 249–309) — the answer buttons. Each option is a
full-width flex button, `min-height: 7.2rem` (6.4rem mobile), hairline border that goes teal on
hover with a `--bg-secondary` fill; active `scale(0.997)`; focus-visible = teal border +
`--focus-ring`.

- `.ampy-bk__option-title` — `--fs-16`, **600** ("the options ARE the decision").
- `.ampy-bk__option-clarifier` — `--fs-14`, 400, secondary.
- `.ampy-bk__option-arrow` — 1.8rem chevron.

**`.ampy-bk__info` / `-icon` / `-text`** (lines 312–335) — always-visible info notice
(`--bg-secondary` block, blue `--text-info` icon).

### 4.5 Verdict (the judgment board)

**`.ampy-bk__judgment`** (lines 340–410) — the verdict header: a left accent bar
(`.ampy-bk__judgment-accent`, 3px) + body holding the badge. 280ms cubic-bezier reveal.

**`.ampy-bk__badge`** (lines 368–392) — the verdict pill: icon + verdict word, `--fs-20`/700,
`--verdict-badge-bg`/`-fg`. On mobile it steps down to `--fs-15` to keep the longest label on one
line (lines 388–392).

**Per-verdict bindings** — there are **two binding mechanisms**, and it matters which layout you're in:

1. **`.ampy-bk__judgment--green|yellow|red`** (lines 395–410, added by JS as
   `ampy-bk__judgment--${verdictKey}`) rebind `--verdict-accent`, `--verdict-badge-bg`,
   `--verdict-badge-fg`. Used by the badge/accent.
2. **`.ampy-bk__block[data-verdict="green|red|yellow"]`** (lines 1237–1239, `dataset.verdict` set
   by JS at render) rebind `--verdict-accent` **plus** `--verdict-wash`, `--verdict-board-bg`,
   `--verdict-board-border` — used by the **hero** reveal (full-height rule + interior wash +
   bordered board).

| data-verdict | `--verdict-accent` | `--verdict-wash` | `--verdict-board-bg` | `--verdict-board-border` |
|---|---|---|---|---|
| green | `rgb(27,132,71)` | `rgba(54,178,92,0.08)` | `rgba(54,178,92,0.10)` | `rgba(27,132,71,0.45)` |
| red | `rgb(122,22,35)` | `rgba(214,64,64,0.07)` | `rgba(214,64,64,0.06)` | `rgba(150,40,50,0.4)` |
| yellow | `rgb(135,101,7)` | `rgba(245,175,25,0.09)` | `rgba(245,175,25,0.08)` | `rgba(135,101,7,0.4)` |

**Hero verdict reveal** (lines 1223–1287): `.ampy-bk--hero .ampy-bk__block[data-verdict]` gets
`position:relative; overflow:hidden`, a `::before` 6px full-height accent rule (`z-index:2`), and
a `::after` top-anchored wash gradient (20rem desktop / 12rem mobile) that fades to transparent —
the **card interior never darkens**. The 3px `--judgment-accent` sliver is hidden in hero
(replaced by the full-height rule, line 1233). In hero the badge becomes a **bordered board**
(`.ampy-bk--hero .ampy-bk__badge`, lines 1259–1286): tinted 1px hairline border
(`--verdict-board-border`), `--verdict-board-bg`, `--fs-22`/700 (`--fs-18` mobile), icon coloured
`--verdict-accent`.

**`.ampy-bk__verdict-src`** (lines 417–433) — minimal muted legal source ref under the red/yellow
board (green gets none). `--fs-12`, `--text-tertiary`, `padding: var(--space-2) 0` gives a ≥24px
tap target for the external link.

**Tabs** — `.ampy-bk__tabs` / `.ampy-bk__tab` / `.ampy-bk__tab-body` (lines 438–481). Thin
underline tabs (Förklaring / Tips / Konsekvenser). Tab weight is **500 in both states** — this
kills the selected-state width shift (comment line 456). The active underline colour is
`--verdict-accent` (JS also sets it inline per verdict, JS lines 508–513).

> ⚠ **CSS/JS attribute mismatch — verify before porting.** The CSS active-tab rule keys off
> **`[aria-selected="true"]`** (lines 465–468), but the JS renders the verdict tabs with
> **`aria-pressed`** (JS lines 496–503), *not* `aria-selected`, and deliberately does not use
> `role=tab` (JS comment lines 476–478). By selector matching, `.ampy-bk__tab[aria-selected="true"]`
> **does not match the rendered tabs**, so the accent underline from that specific rule does not
> fire through it. The tab indicator you see comes from the inline `--verdict-accent` + the
> `border-bottom` on the base `.ampy-bk__tab` and its per-verdict inline var. If you reproduce the
> markup in Bricks, keep the JS's `aria-pressed` contract and don't assume the `[aria-selected]`
> CSS rule is styling the active tab. (Note: `.ampy-bk__room[aria-selected="true"]`, §4.2, *is*
> correct — room chips really do use `aria-selected`.)

**Förklaring tab body** (lines 480–563):

- `.ampy-bk__tab-body` — indented (`padding-left`) to clear the full-height accent rule.
- `.ampy-bk__cta-zone` — `margin-top:auto` pins the CTA stack to the card floor (desktop hero);
  in the embedded/no-min-height layout it resolves to 0 (lines 483–487).
- `.ampy-bk__summary` — `--fs-17`/500, `text-wrap:pretty`.
- `.ampy-bk__row` + `.ampy-bk__row-icon` + `.ampy-bk__row-text` — the ✓/✗ contrast rows.
  `.ampy-bk__row--do` icon = `--text-success` (green); `.ampy-bk__row--dont` = `--text-tertiary`
  (muted). Icon centering: see §7.
- `.ampy-bk__caveat` (green only) — amber left-border notice (`3px solid rgb(186,117,23)`),
  square corners, `align-items:center` so the ⚠ centers when the text wraps.

**Tips tab** (lines 568–599): `.ampy-bk__tips` / `.ampy-bk__tip` / `.ampy-bk__tip-icon` /
`.ampy-bk__tip-text`. `--tip--do` icon green, `--tip--dont` muted. Icon centering: §7.

**Konsekvenser tab** (lines 604–611): `.ampy-bk__consequence-text` — `--fs-15`, 400, single block.

**CTA zone** (lines 619–703):

- `.ampy-bk__cta-note` — the green framing line ("Vill du dubbelkolla…").
- `.ampy-bk__cta-primary` — the top CTA; default is an **outline** button (hairline border, white
  bg). `min-height: 4.8rem` (5.2rem mobile).
- `.ampy-bk__cta-primary--solid` — the **solid teal** primary (used on red; the strongest
  conversion action). `background/border: var(--action-primary-strong)`, `--text-inverse`. This is
  the reserved solid-teal fill (§3.6).
- `.ampy-bk__cta-secondary` — the outline "Läs mer om…" button below. `line-height:1.3` matches the
  primary so the stacked buttons are equal height.
- The old green-only `--cta-row`/`--cta-link` treatment is deleted; every verdict ends with the
  same primary + secondary pair.

### 4.6 Lead form & success

**`.ampy-bk__lead-back`** (lines 1308–1322) — same calm back affordance as the crumb.

**`.ampy-bk__lead-title` / `.ampy-bk__lead-intro`** (lines 1323–1330) — heading (`--fs-22`/600) +
intro.

**`.ampy-bk__lead-grid`** (lines 1331–1335) — two-column grid (`1fr 1fr`, `column-gap:--space-5`,
`row-gap:--space-7`). Collapses to a **single column below 520px** (line 1335).

**`.ampy-bk__lead-field` / `.ampy-bk__lead-label`** (lines 1336–1339) — field wrapper + 600-weight
label.

**`.ampy-bk__lead-input`** (lines 1340–1348) — `min-height: 4.8rem`, `font-size: var(--fs-16)`
(**16px floor — MUST NOT drop below 16px; see §5.4**). Focus = teal border + `--focus-ring`.

**`.ampy-bk__lead-hp`** (line 1349) — honeypot field, positioned off-screen (`left:-9999px`,
1×1px, `opacity:0`). (JS renders the honeypot input with `name="webbplats"`.)

**`.ampy-bk__lead-error`** (lines 1350–1351) — `--state-error`, `[hidden]` toggles display.

**`.ampy-bk__lead-submit`** (lines 1352–1353) — the submit button; disabled state uses the strong
teal fill. `min-height: 4.8rem` (5.2rem mobile). **Desktop-only (≥768px)**: the whole
`.ampy-bk__lead` becomes a centered flex column and the submit gap opens to `--space-13` (32px) so
the action reads as its own step (lines 1355–1369). Mobile is untouched.

**`.ampy-bk__req`** (line 1203) — the required-field asterisk (small, black `#000`).

**`.ampy-bk__lead-fineprint`** (lines 1206–1216) — consent fine print (replaces the checkbox),
`--fs-13`, `--text-tertiary`; links use `--action-primary-text` underlined.

**`.ampy-bk__lead-success`** (lines 1370–1381) — centered success state: green tinted circle icon
(`.ampy-bk__lead-success-icon`, 5.6rem, `rgba(54,178,92,0.14)`, `--state-success`), `h2` `--fs-20`,
`p` `--fs-15`.

### 4.7 Utilities & no-JS fallback

- `.ampy-bk__sr` (lines 894–902) — visually-hidden screen-reader utility (clip-rect pattern).
- `.ampy-bk__empty` (lines 904–911) — dashed empty-state box.
- `.ampy-bk__loading` (lines 913–918) — centered loading text.
- `.ampy-bk__noscript` + `.ampy-bk__noscript-grid` (lines 920–966) — the crawlable / no-JS
  fallback (server-rendered by render.php). `.ampy-bk[data-booted="true"] .ampy-bk__noscript
  { display:none }` (line 966) hides it once JS boots (JS sets `dataset.booted='true'`). The
  noscript grid is `repeat(3,1fr)` → `repeat(2,1fr)` below **600px** (lines 952–954) — one of only
  two 600px breakpoints in the file.

### 4.8 Rendered-but-unstyled classes (note for Bricks copy)

Two classes are emitted by the JS but have **no CSS rule** in this file. They are layout/ARIA
containers and render fine unstyled, but note them so you don't hunt for missing CSS:

- **`.ampy-bk__swap`** — the `aria-live="polite"` region wrapper the JS swaps slide content into
  (JS line 946). No CSS rule (confirmed: `swap` appears nowhere in the CSS). Harmless.
- **`.ampy-bk__lead-form`** — the `<form>` element inside `.ampy-bk__lead` (JS line 692). The grid
  lives on the child `.ampy-bk__lead-grid`, not the form, so the form needs no rule.

---

## 5. Responsive

### 5.1 Every breakpoint actually used in this file

Verified by grepping all `@media` rules. **These are the only breakpoints in
`assets/behorighetskollen.css`:**

| Query | Count | Purpose |
|---|---|---|
| `max-width: 520px` | 1 (line 1335) | lead grid → single column |
| `max-width: 600px` | 1 (line 952) | noscript grid 3-col → 2-col |
| `max-width: 767px` | 15 blocks | **the** mobile breakpoint (type steps, tap targets, drawer flow, chip scroller, crumb pull-ups, verdict wash height, etc.) |
| `min-width: 768px` | 2 (lines 1081, 1362) | desktop-only: verdict rhythm; lead-form centering |
| `prefers-reduced-motion: reduce` | 2 (lines 171, 983) | see §6 |

> **Correction to the old assumptions:** there is **no `640px` breakpoint, no `768–1023` tablet
> range, and no `1024px` breakpoint** in this stylesheet. If a tablet/desktop-wide breakpoint is
> mentioned anywhere for Elkollen, it belongs to the **preview hero chrome (`preview/hero.html`)**,
> not to this component. The tool itself is a two-state responsive design: `≤767px` (mobile) and
> `≥768px` (desktop), with two narrow special-cases (520px lead grid, 600px noscript grid).

### 5.2 The hero card min-height contract

- **Desktop hero:** `.ampy-bk--hero .ampy-bk__block { min-height: 56rem; }` (line 992 — raised from
  52→56rem so slides S1–S4 share one height and the left hero column stays stable). Padding
  `--space-13` (32px).
- **Mobile hero (≤767px):** `min-height: 30rem` — the card is **content-sized** (only a tiny floor
  so an ultra-short slide isn't awkward). The owner wanted the internal caveat→CTA slack gone; with
  the scroll-anchor pinning the card top, a content-height card gives tight spacing (lines
  996–1005). On mobile the `--cta-zone` `margin-top` is reset to 0 (line 1008) so the CTA flows
  right after content instead of being floor-pinned.

### 5.3 The mobile overrides block (end of file)

Lines 1383–1408 hold the consolidated **v7 mobile overrides (≤767px)** — explicit per-element type
steps and tap-target bumps (never token remaps): `--entry-title`/`--drawer-title`/`--q-title`/
`--lead-title` → `--fs-20`; `--job-row` → `--fs-15`, 4.8rem; `--option` → 6.4rem; CTA buttons →
5.2rem min-height; `--source-line` → `--fs-11`. Rationale in the comment: ~90% of paid traffic is
mobile.

### 5.4 The 16px input floor — do NOT drop below 16px

`.ampy-bk__lead-input` (line 1344) and `.ampy-bk__search-input` (line 742) are pinned to
`font-size: var(--fs-16)` = **16px**. iOS Safari **auto-zooms the viewport** on focus for any input
whose font-size is < 16px. The embedded layout's body size is `--fs-15` (15px), which would trigger
the zoom, so the inputs are explicitly floored at 16px. **When porting: these two inputs must stay
≥16px** (i.e. `16px` after rem→px conversion). Never let a "make inputs smaller" tweak drop them.

### 5.5 Cascade-order discipline (a known past bug class)

The mobile override for `.ampy-bk__drawer-back` (lines 1071–1077) is deliberately placed **AFTER**
its base rule (lines 1053–1070). Both selectors are **equal specificity** (`.ampy-bk__drawer-back`
vs the same class inside a media query), so **source order decides the winner**. In earlier
versions the mobile override sat *before* the base rule, so its `min-height`/`margin` silently lost
the cascade and never applied — the **v7.3.7/v7.3.8 cascade fix** relocated it after. A comment at
lines 1028–1029 also flags this. **When porting, preserve source order** for equal-specificity
overrides; do not let a build step reorder or "optimize" the stylesheet, and don't hoist media
queries above their base rules.

### 5.6 Overflow guards

Recurring guards against horizontal overflow: `minmax(0, 1fr)` on grid tracks (roomgrid line 1109),
`min-width: 0` + `overflow:hidden` + `text-overflow:ellipsis` + `white-space:nowrap` on truncating
text (crumb-job lines 227–230, roomtile-sub lines 1181–1184), and `flex-wrap:nowrap` + hidden
scrollbar + mask fade on the mobile chip row (lines 812–830). Keep these intact.

---

## 6. Motion & accessibility

### 6.1 `prefers-reduced-motion: reduce` (two blocks)

**Block 1 (lines 171–179):** disables the entrance animations and the press/hover micro-transforms:

```css
.ampy-bk__block, .ampy-bk__judgment, .ampy-bk__drawer > * { animation: none !important; }
.ampy-bk__option:active,
.ampy-bk__roomtile:active,
.ampy-bk__roomtile:hover { transform: none !important; }
```

The transform overrides were **added in v7.3.7/v7.3.8** — the press/hover micro-transforms also
move content, so they are cut under reduced motion (comment line 176).

**Block 2 (lines 983–985):** `.ampy-bk--hero { transition: none; }` — kills the hero `min-height`
transition.

Animations that exist and are disabled/limited: `ampy-bk-block` (240ms card fade, lines 161–165),
`ampy-bk-fade` (200ms drawer child stagger, lines 167–170), `ampy-bk-judgment` (280ms verdict
reveal, lines 347–350).

### 6.2 Focus-visible rings

Interactive elements use `:focus-visible` (not `:focus`) with `--focus-ring` (the teal
`0 0 0 3px rgba(0,169,145,0.25)` box-shadow) or a 2px teal outline: crumb-back (line 216), options
(283), tabs (`inset` variant, 469–472), CTAs (653, 697), inputs (756, 1348), room chips (801),
roomtiles (1144), job rows (873), verdict-src (432), drawer-back (1070), lead-back (1322). The base
`.ampy-bk` also sets `-webkit-tap-highlight-color: transparent` (line 126) because the tool
provides its own `:active`/`:focus-visible` feedback.

---

## 7. Icon-centering contract (v7.3.8) — declare `display:inline-flex` in CSS

Three icon wrappers — **`.ampy-bk__row-icon`** (lines 509–520), **`.ampy-bk__caveat-icon`** (lines
546–554), **`.ampy-bk__tip-icon`** (lines 581–588) — now declare `display: inline-flex` +
`align-items:center` + `justify-content:center` **in the CSS**, as of v7.3.7/v7.3.8. Previously the
centering was applied only via a **JS inline style**; it is now in the stylesheet so any markup
reproduction (render.php growth, a **Bricks copy**) keeps the icon centered without the JS.

The centering technique: the wrapper's `height` is set to **one text line tall** (e.g.
`calc(var(--fs-16) * 1.5)` for rows, `calc(var(--fs-14) * 1.55)` for the caveat,
`calc(var(--fs-15) * 1.5)` for tips) and the glyph is flex-centered inside it — so the icon
optically aligns to the **first line** of the row text (robust vs a fixed `margin-top` nudge).

> **When you copy this markup into Bricks, keep `display:inline-flex` on these three wrappers.**
> If they collapse to the default `inline` box, the icons lose vertical centering.

---

## 8. Porting to Fluent Snippets

Concrete guidance for the WordPress/Bricks re-implementation. Ordered by risk.

1. **rem → px conversion (highest priority).** You **cannot** ship `html{font-size:62.5%}` into a
   live theme (§1.4) — it shrinks the whole site. Either convert every `rem` to `px` at
   **1rem = 10px** (`1.6rem→16px`, `4.8rem→48px`, `56rem→560px`, `0.4rem→4px`…) or re-base the
   `--space-*`/`--fs-*` tokens in px on `.ampy-bk` and delete the `html` rule. The token comments
   in §3.2/§3.4 already give you the px column. `--radius-full: 999px` and the shadow values are
   already px.
2. **Remove the font `@import` question entirely.** There is none in this file (v7.3.8, §2) — keep
   it that way. Self-host **Outfit woff2 (latin incl. å ä ö, weights 400/500/600/700)** at the
   theme/Bricks level, per the Fluent Snippets delivery contract, **not** inside this component's
   CSS. Then get **owner approval** of the Outfit render against the approved previews — it will
   look different from the current system-font fallback.
3. **Wrapper-scoping so theme CSS can't bleed in.** Everything is already scoped under `.ampy-bk`
   (and hero under `.ampy-bk--hero`). Keep the wrapper. Keep the scoped
   `box-sizing:border-box` reset (lines 129–133) so the theme's box model can't distort the tool.
   Do not let the snippet emit any bare element selector.
4. **Preserve the load-bearing rules:**
   - The **16px input floor** on `.ampy-bk__lead-input` and `.ampy-bk__search-input` (§5.4) — never
     below 16px, or iOS zooms on focus.
   - The **three teal tokens** and their discipline (§3.6): solid `--action-primary` fill = primary
     CTA only; teal text = `--action-primary-text`; selected chip + solid CTA + disabled submit =
     `--action-primary-strong`. Collapsing these to one teal reintroduces the AA failures.
   - The **cascade order** of equal-specificity overrides (§5.5) — keep source order; do not let a
     minifier/optimizer reorder rules or hoist media queries. The `.ampy-bk__drawer-back` mobile
     override must stay after its base rule.
   - The **`display:inline-flex`** on the three icon wrappers (§7) when you copy markup into Bricks.
   - The **overflow guards** (§5.6) and the **hero min-height contract** (§5.2) if you ship the hero
     layout.
5. **Attribute contracts to reproduce faithfully:** room chips use `aria-selected` (CSS depends on
   it); verdict tabs use `aria-pressed` (CSS `[aria-selected]` tab rule does **not** match them —
   §4.5); `data-verdict` on `.ampy-bk__block` drives the hero verdict ramp; `data-booted="true"`
   hides the noscript fallback.
6. **Do not re-add removed subsystems.** The **share** feature (button/popover/status/trust-row)
   is fully removed (CSS comment lines 705–709; no share classes render). The hero-scope `--fs-*`
   remap and the `--roomtile--all` dashed style are deleted. Don't reintroduce them from the old
   docs.

---

## Drift found vs the old docs

While verifying against the current code (`assets/behorighetskollen.css` + `.js`, `.php`, data
file), I confirmed the following drift in the pre-existing docs — **the code is truth**:

- **Version:** `README.md`/`CHECKLIST.md` predate 7.x; `CLAUDE.md` still reads "7.3.7" in places,
  while `ampy-behorighetskollen.php` (`Version:`/`AMPY_BK_VERSION`), the data file `"version"`, and
  the CSS header are all **7.3.8**.
- **Share feature removed:** old docs describe a share button / share image; the CSS block is gone
  (lines 705–709 note the removal) and no share class renders in the JS.
- **jobGroup / verdict-grouped list gone:** the slide-2 list is now a single **neutral** flat list
  (`.ampy-bk__joblist`, no group headers/colour dots); older grouped-engine references are stale.
- **CSS/JS tab-attribute mismatch (live in the code, not just the docs):** the CSS active-tab rule
  targets `[aria-selected="true"]` but the JS emits `aria-pressed` on the verdict tabs (§4.5) — the
  active underline comes from the base rule + inline `--verdict-accent`, not that selector.

---

*Verified against v7.3.8 (`assets/behorighetskollen.css`, ~1408 lines) by reading the full CSS and
grepping the rendered class names in `assets/behorighetskollen.js`. Line numbers are v7.3.8.*
