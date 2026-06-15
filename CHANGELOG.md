# Elkollen — Changelog

All UI text is in Swedish, sentence case. No em-dashes in the UI.

## 5.7.4 — Sticky hero copy (desktop)
- The hero copy column is now `position: sticky` (top: 2.4rem) on desktop (>=768px)
  so it stays pinned in view while the tool panel changes height / the user scrolls,
  instead of appearing to move. Static (normal flow) on mobile.

## 5.7.3 — "Så funkar det" 1:1 + final pixel pass
- **"Så funkar det" is now a true 1:1 of ampy.se's `.our-process`** (values pulled
  from the live site): light section, gradient-text heading
  `linear-gradient(90deg,#1e1e1e 30%,#1d2f52,#33995c)` weight 400, large gradient
  line-icons `#1B1D4A -> #5EB1BF` (thin 1.25 stroke), Outfit titles (400) + thin
  weight-200 body, ampy's clamp() type tokens, ~25rem left-aligned columns
  (centered on mobile).
- **Hero copy column is top-anchored** (`align-items: start`) so it no longer jumps
  vertically as the tool panel changes height between slides.
- **Removed the color dots** from the quick-pick chips.
- **Final multi-agent pixel audit applied:** even trust-bullet rhythm (removed a
  min-height that doubled the gap on mobile); RED's two stacked CTAs equal height
  (added line-height to the secondary); hero CTA 44px tap target at all widths;
  step columns to the ~25rem spec; room-panel + job-row use the shared shadow /
  focus-ring tokens; mobile verdict badge restored to fs-18 (dominant, still one
  line); drawer on the 14px rhythm; lead-form disabled-state contrast (AA) and
  consent-label pointer; fixed an undefined `--text-muted` fallback.
- Version -> 5.7.3.

## 5.7.2 — Mobile overflow fix + chip/centering polish
- **Mobile horizontal-overflow fixed (critical).** The hero grid used
  `grid-template-columns: 1fr`, whose min-content floor let the longest Swedish
  word / nowrap chip force the column wider than the screen, clipping content.
  Changed to `minmax(0, 1fr)`; quick-pick chips wrap (`white-space: normal`)
  below 768px; verdict tabs use a tighter gap on mobile; hero copy gets
  `overflow-wrap: break-word`. Result: zero horizontal overflow on entry,
  question, verdict, lead and "Så funkar det" at mobile widths.
- **Removed the color dots** next to the quick-pick chips (client).
- **Hero columns vertically centered** (`align-items: center`) so the left copy
  sits with equal space above and below the tool panel on desktop.
- Version -> 5.7.2.

## 5.7.1 — Second client polish round
- **"Så funkar det" redesigned** to match the ampy.se homepage style: light section
  (was navy), borderless columns (no cards), large gradient navy->teal line-icons,
  numbered inline titles ("1. Välj ditt jobb"), heading accent on "det". My 3-step
  copy kept. Desktop row left-aligned, mobile stacked centered.
- **Search icon alignment fixed:** icon + input now wrapped in `.ampy-bk__search-field`
  so the magnifier centers on the input (it was centering on label+input and floating
  high). Desktop + mobile.
- **Share button removed from the verdict** (all colors, both breakpoints) — the
  verdict now ends with the CTAs.
- **Lead form:** removed (the share-row); Telefon + Postnummer already required.
- **Internal link copy:** per-job `cta_short` so "Felsökning av el" -> "Läs mer om
  felsökning" (was "...om el") and "Dra ny kabel" -> "...om att dra ny kabel". All 26
  audited.
- **Mobile badge:** stepped down (fs-16) so "Det här kräver elektriker" stays on one
  line instead of wrapping.
- **Hero copy:** trust bullet 1 shortened to "Byggt på Elsäkerhetslagen och
  Elsäkerhetsverket"; trust-bullet icon/text spacing equalized.
- Version -> 5.7.1.

## 5.7.0 — Pixel-perfect pass (client feedback + multi-agent audit)
Eight client fixes + a 6-dimension UX/UI audit (typography, spacing, color/contrast,
mobile, components, flows). Highlights:
- **Accessibility / contrast (WCAG AA):** new darkened text tokens
  `--action-primary-text` (links) and `--action-primary-strong` (the solid primary
  CTA fill) so labels clear 4.5:1; `--text-tertiary` darkened (source line, footnote,
  placeholder, separator now legible); tokenized `--focus-ring`; hero CTAs got a
  focus-visible ring.
- **CTA buttons:** corrected radius to **8px** (the real ampy.se cascade value);
  on mobile they are full-width, 44px tall, label+icon centered (not split).
- **Lead form:** Telefon + Postnummer now required (client); intro reworded to
  "Ampys behöriga elektriker hör av sig..." (no job suffix); submit height matches
  the 48px inputs; submit/foot spacing fixed.
- **Hierarchy / rhythm:** verdict badge is now the dominant heading (fs-20/700);
  hero `--fs-14`/`--fs-15` split back to distinct steps; even 14px rhythm between the
  stacked entry blocks; search input unified to the 48px input size; search icon
  centered height-independently.
- **Consistency:** unified the go-back affordance (breadcrumb / lead / success) to one
  calm style; back links + breadcrumb to 44px; cite-chip padding on the token scale;
  quick-pick dot spacing via flex gap; removed dead trust-row CSS; consent checkbox
  bound to its own class.
- **Client copy/structure:** removed the hero honesty line; removed the RED
  registration trust line (share kept, right-aligned); long quick-pick (DCL) shows a
  shorter one-line chip label.
- **Trust bullets:** consistent icon size/gap, optically aligned to the first line.
- Yellow verdict documented as supported-but-unused (forward-ready).
- Version -> 5.7.0; data -> 5.7.

## 5.6.0 — Lead capture, analytics, CTA 1:1 redo, UX/UI pass
Built from a multi-agent audit (UX, UI, CRO, copy). Highlights:
- **In-tool lead form (NEW).** The verdict advice CTA now opens an on-page form
  (Namn, E-post, Telefon, Postnummer, GDPR consent) instead of linking out to
  `/offert/`. It prefills the job + verdict context and POSTs to the REST endpoint
  (`includes/lead-endpoint.php`, now re-enabled; `telefon` field added). In the
  static preview with no WordPress backend it gracefully simulates success.
  Copy lives in `data.meta.lead_form` (source of truth).
- **Funnel analytics (NEW).** Vendor-agnostic `track()` pushes to
  `window.dataLayer` (GA4/GTM) and dispatches an `elkollen:track` CustomEvent.
  Events: tool_view, job_selected, question_shown, question_answered,
  verdict_shown, cta_click, lead_form_open, lead_submitted, share_opened,
  share_completed (with channel), verify_company_click.
- **CTA buttons = true 1:1 with ampy.se hero gradient buttons.** "Kontakta oss"
  green->teal gradient `linear-gradient(120deg,#55ff9a,#5eb1bf)` + arrow-up-right
  icon; "010-265 79 79" cyan gradient + phone icon. 16px radius, soft shadow,
  Outfit 400, dark text, the site's clamp() padding, full-width <=478px.
- **Standardized advice CTA label** to "Få kostnadsfri rådgivning" everywhere
  (`data.meta.cta_advice_label`); removed the "Få offert / Anlita expert?" variants.
- **Copy:** new H1 ("Ta reda på det innan du börjar", dropped "30 sekunder"),
  removed the misleading "grönt eller rött" binary (the tool has a real yellow
  path), step 3 reworded ("Följ" not "Kör med"), fixed two user-facing em-dashes
  (question info-note, RED trust line), "Kopiera länken" (was "URL:en").
- **UX:** focus moves to the new view's heading after each transition; verdict
  badge is now `<h2>` (single page H1); RED surfaces a one-line consequence by
  default; tabs are honest plain buttons (aria-pressed) not a fake ARIA tablist;
  quick-pick chips carry a green/amber/red verdict dot; `quick_picks` reordered to
  surface laddbox/golvvärme + one green example; drawer stays scroll-capped on
  mobile; room chips bumped to 44px tap target.
- **UI:** warmer leafy `--state-success` (distinct from teal action color);
  documented teal-usage rule (solid-fill = primary CTA only); "Se alla N jobb"
  demoted to neutral text + teal arrow; "Så funkar det" on a navy band with flat
  white cards + flat teal-stroke icons (gradient/glow removed, subtler hover);
  H1 top-aligned with the tool panel.
- Version -> 5.6.0; data version -> 5.6.

## 5.5.1 — CTA 1:1 with ampy.se + font fallback fix (preview only)
- **CTA buttons made a true 1:1 replica of the ampy.se homepage hero buttons.**
  Pulled the live site's computed styles (via its actual Bricks stylesheets):
  "Kontakta oss" = solid yellow `#ffd64f` + dark text `#363636`; "010-265 79 79"
  = solid light cyan `#b8f2ff` + dark text, phone icon on the right. Both: 8px
  radius, Outfit 400, the site's exact clamp() padding/font tokens, and full-width
  stacking at <=478px. Hrefs corrected to `/offert/` and `tel:+46102657979`.
  (Previous version approximated these as a teal-fill + white-outline pair.)
- **Bug fix:** `.sec-head h2` and `.step h3` in `preview/hero.html` declared
  `'Plus Jakarta Sans'` with no fallback, so they rendered as serif whenever the
  webfont was not applied. Added `, system-ui, sans-serif`.
- Preview/landing-chrome only; plugin assets unchanged, so `AMPY_BK_VERSION`
  stays 5.5.0.

## 5.5.0 — Hero refinement (entry interaction + landing trim)
- **Hero entry restructured** (`renderHeroEntry`): the old "Se alla N jobb · Välj
  rum" disclosure row is gone. In its place:
  - **"Välj rum" is now a standing dropdown header** that always sits between the
    search field and "Vanliga eljobb". Tapping it drops down the 5 rooms; picking
    one filters the list and the toggle label reflects the active room.
  - **"Se alla N jobb" is its own tappable row** at the end of the "Vanliga eljobb"
    list (full-width box, not a text link).
  - The results drawer now carries a **"Visa vanliga eljobb" back link** to return
    to the compact common-jobs view.
  - New `chevron` icon added (the old disclosure code referenced a missing key and
    silently fell back to the info icon).
- **`preview/hero.html` landing trimmed** to match the production block plan:
  - Removed the "Koppla elen" eyebrow.
  - Trust bullet shortened to "Registrerat elinstallationsföretag" (dropped the
    "verifiera oss hos Elsäkerhetsverket" tail).
  - Micro-CTA replaced with a 1:1 replica of ampy.se's two homepage CTAs:
    **"Kontakta oss"** (filled teal pill) + **"010-265 79 79"** (outline, `tel:`).
  - Removed the trust band, the FAQ section and the final CTA section, so
    **"Så funkar det" follows immediately after the tool**. FAQ + final CTA are
    expected to come from existing site blocks below.
  - **"Så funkar det" redesigned** in the Ampy card style (gradient icon badges,
    step number chips, connector line on desktop). Three steps, tool-specific copy:
    Välj ditt jobb / Få ditt besked / Gör nästa steg.
- Version bumped to 5.5.0 (cache-busting; CSS+JS changed). Data file unchanged.

## 5.4.0 — Split-hero landing layout
- **New `layout="hero"` shortcode mode** for the landing page split hero (copy
  left, tool right). Bricks owns the split; the plugin's entry state becomes
  compact (search + 6 quick-pick chips + "Se alla N jobb"/"Välj rum" disclosure
  with an in-panel drawer for the full grouped list). Larger hero type scale.
  Verdict/question render in the same right panel; anti-collapse height floor.
- **Untouched:** the default centered/embedded layout (all hero code gated on
  `this.heroMode` / scoped under `.ampy-bk--hero`), verdict logic, data contract,
  honesty moat, solid-teal-on-red CTA, Swedish UI.
- **New reference:** `preview/hero.html` — full landing page (split hero + trust
  band + "så funkar det" + FAQ + final CTA). Trust-band numbers are placeholders.
- Version bumped to 5.4.0 (cache-busting; CSS+JS changed).

## 5.3.0 — Handover release
- **Copy revision:** `summary` ≠ `do` on all jobs (end of the duplicate in the
  Explanation tab). Clarifiers rewritten from jargon to plain language. Zero
  em-dashes in UI text.
- **Tips ✓/✗:** tips now carry `{ text, allowed }`. The stop condition (what you
  may NOT do) renders with ✗, the rest with ✓.
- **Service library expanded to 26 jobs:** + laddbox (EV charger),
  solcellsbatterier (solar/battery), elbesiktning (conditional), koksrenovering
  (kitchen reno), badrumsrenovering (bathroom reno). ~95% coverage of Ampy's
  electrical service / pillar pages. All `service_page_url`s are absolute
  ampy.se URLs.
- **Share:** native share sheet (touch devices) + a popover with Facebook/X/
  Reddit/Email/Copy link (desktop). Reddit icon replaced with a clean snoo.
- **Source per verdict:** green → Elsäkerhetsverket, red → Elsäkerhetslagen §27.
- **"Verifiera oss"** points to Ampy's exact entry in Elsäkerhetsverket's register.
- **Heading:** "Koppla elen" + lead "Se direkt vilka eljobb du får göra själv.",
  lives outside the tool (Bricks elements), font Outfit.
- **Quote:** all quote/expert CTAs → `https://ampy.se/offert/`.
- **Plugin polish:** name → "Elkollen (Ampy)", `AMPY_BK_VERSION` → 5.3.0
  (cache-busting), shortcode alias `[elkollen]`, OG title without em-dash + correct
  brand, lead endpoint disabled by default (UI links to /offert/), crawlable
  fallback uses the new lead text.
- **Documentation:** translated to English — HANDOVER.md (agent), CHECKLIST.md
  (Chris), README.md, CHANGELOG.md.

## Earlier iterations (summary)
- v5: craft pass — composed layout, de-blobbed explanation, always-visible info
  note in the question step, flattened quote.
- v4: 10 px rem base, type roles, responsive (fixed height desktop / flow mobile),
  one action color (solid teal only on red).
- v3.1: rename to Elkollen, two tabs per verdict, mobile fixes, 60rem width.
- v3: block doctrine (fixed height, swap zone).
- v2: instrument, adaptive opening, search picker.
- v1: data contract, verdict engine, 21 jobs, crawlable HTML, lead endpoint.

## Launch gate (open)
A certified electrician must sign off on: the job matrix, the per-verdict sources,
the penalty paragraph (48 § vs 49 §), the `fast-armatur` scope, and the 26 jobs'
tips. See `meta._pending_verification` in the data file.
