# Elkollen — Changelog

All UI text is in Swedish, sentence case. No em-dashes in the UI.

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
