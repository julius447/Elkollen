# Elkollen — Changelog

All UI text is in Swedish, sentence case. No em-dashes in the UI.

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
