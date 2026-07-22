<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: 
* @name: Ampy - Elkollen - CSS
* @type: css
* @status: published
* @created_by: 13
* @created_at: 2026-06-11 08:35:13
* @updated_at: 2026-07-22 14:28:10
* @is_valid: 1
* @updated_by: 13
* @priority: 10
* @run_at: wp_head
* @load_as_file: yes
* @load_in_block_editor: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
/* ==========================================================================
   ELKOLLEN — Fluent Snippet #1 of 3  (type: "CSS")
   MERGED BUILD: Julius's v7.3.8 tool CSS (assets/behorighetskollen.css),
   FORMAT-ONLY ported — every rem value converted to px (1rem = 10px) so the tool
   renders identically without depending on a 62.5% root font-size. The self-hosted
   font @font-face block + the html{font-size:62.5%} anchor are the LIVE prefix
   (kept verbatim; the site's battery calculator still needs the 62.5% base).
   The dead v5.7.9 .elkollen-root PAGE CHROME is DROPPED (v7.3.8 render_mount no
   longer emits it). The tool is self-contained under .ampy-bk / .ampy-bk--hero.
   Do not hand-edit; edit the sources and re-run build-ek-css.mjs. No design changed.
   ========================================================================== */

/* ── Self-hosted variable fonts (no third-party requests) ────────────────────
   ampy.se ships these as variable woff2 in /wp-content/uploads/fonts/. One file
   per family covers the whole weight axis, so the old static latin/latin-ext
   pairs and the Google Fonts request are both unnecessary. Root-relative URLs
   resolve the same whether this snippet is inlined or served as a file. */
@font-face{font-family:'Outfit';src:url('/wp-content/uploads/fonts/Outfit-VariableFont_wght.woff2') format('woff2-variations'),url('/wp-content/uploads/fonts/Outfit-VariableFont_wght.woff2') format('woff2');font-weight:100 900;font-style:normal;font-display:swap;}
@font-face{font-family:'Plus Jakarta Sans';src:url('/wp-content/uploads/fonts/PlusJakartaSans-VariableFont_wght.woff2') format('woff2-variations'),url('/wp-content/uploads/fonts/PlusJakartaSans-VariableFont_wght.woff2') format('woff2');font-weight:200 800;font-style:normal;font-display:swap;}

/* ── Root rem base ───────────────────────────────────────────────────────────
   KEEP THIS LINE. This snippet (together with Elcentral's) is what supplies
   html{font-size:62.5%} on ampy.se. The battery calculator's CSS contains 138
   rem values and anchors nothing itself, so deleting this silently rescales that
   tool site-wide. The tool below is written in px and does not need it; the rest
   of the site does. Placed after the @import: a rule before an @import makes the
   browser discard the @import and the fonts fail silently. */
html{font-size:62.5%}


/* ============================================================================
   Elkollen v7 — the 4-slide funnel.
   Slide 1: heading + six identical room tiles. Slide 2: one neutral job list
   (no verdict grouping). Slide 3: the choice step (conditional jobs only;
   v7.2: the 10 direct-verdict jobs go straight from the list to the verdict).
   Slide 4: refined verdict (board + tabs + one CTA hierarchy for every
   verdict, the CTA stack pinned to the card floor).

   Typography is OUTFIT-ONLY (weight-led: 400 body, 500 interactive, 600
   headings, 700 reserved for the verdict headline). Plus Jakarta Sans is
   removed. NOTE for the dev: per the FluentSnippets delivery contract the
   Google Fonts @import below should be replaced with a self-hosted Outfit
   variable woff2 (latin subset incl. å ä ö) at WordPress implementation time.

   Rem base: html font-size 62.5% (= 10 px) — all rem values read on that base.
   Tokens: never raw values in components; only semantic CSS variables.
   ============================================================================ */
/* v7.3.8: NO @import here - fonts are deliberately the HOST PAGE's job.
   History: an @import for Outfit static weights sat below (after a style rule =
   invalid CSS, silently discarded by every browser), so this stylesheet has in
   practice never loaded a font. In v7.3.7 the import was "fixed" (moved first)
   and the activated static weights CHANGED the approved rendering across the
   whole tool - the owner rejected it and the import is now removed for good.
   The pixel-approved reference look is the host page's font stack. At WordPress
   time the theme/Bricks (or the FluentSnippets self-hosted woff2 per the
   delivery contract) provides Outfit - verify the result against the approved
   previews with the owner before launch. */


.ampy-bk {
  /* ---------- Color tokens ---------------------------------------------- */
  --bg-primary: #ffffff;
  --bg-secondary: #f4f5fb;
  --bg-surface: rgb(9, 11, 50);

  --text-primary: rgb(9, 11, 50);
  --text-secondary: #5a5d7a;
  /* Darkened to clear WCAG AA (4.5:1) for the informational text that uses it
     (source line, form footnote, placeholder, crumb separator). Was #8a8da5 (3.26:1). */
  --text-tertiary: rgb(104, 107, 128);
  --text-inverse: #ffffff;
  --text-info: rgb(10, 122, 191);
  --text-success: rgb(15, 110, 86);

  /* Brand teal. --action-primary is the accent for borders/rings/icons (3:1 graphics
     rule). For TEXT links use --action-primary-text and for the solid primary-CTA fill
     use --action-primary-strong, both darkened to pass AA (4.5:1) for their labels. */
  --action-primary: rgb(0, 169, 145);
  --action-primary-text: rgb(0, 110, 94);
  --action-primary-strong: rgb(0, 122, 105);
  --focus-ring: 0 0 0 3px rgba(0, 169, 145, 0.25);

  /* Leafier green, deliberately warmer than the teal --action-primary so the
     "green = you may do it yourself" verdict never reads as the teal "hire us"
     action color (the two must stay visually distinct). */
  --state-success: rgb(54, 178, 92);
  --state-warning: rgb(245, 175, 25);
  --state-error: rgb(214, 64, 64);

  --border-default: #e3e5ed;
  --border-tertiary: #ebedf3;
  --border-focus: rgb(0, 169, 145);

  /* ---------- Verdict ramps (per-verdict bindings) --------------------- */
  --verdict-accent: var(--state-success);
  --verdict-badge-bg: rgb(116, 200, 138);
  --verdict-badge-fg: rgb(4, 52, 44);

  /* ---------- Spacing scale (10 px rem-bas) ---------------------------- */
  --space-1: 4px;  /*  4 px */
  --space-2: 6px;  /*  6 px */
  --space-3: 8px;  /*  8 px */
  --space-4: 10px;  /* 10 px */
  --space-5: 12px;  /* 12 px */
  --space-6: 14px;  /* 14 px */
  --space-7: 16px;  /* 16 px */
  --space-8: 18px;  /* 18 px */
  --space-9: 20px;  /* 20 px */
  --space-10: 22px; /* 22 px */
  --space-11: 24px; /* 24 px */
  --space-12: 28px; /* 28 px */
  --space-13: 32px; /* 32 px */

  /* ---------- Radius / shadow ------------------------------------------ */
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-full: 999px;

  --shadow-sm: 0 1px 2px rgba(11, 13, 42, 0.04);
  --shadow-md: 0 6px 18px rgba(11, 13, 42, 0.06);
  --shadow-lg: 0 14px 32px rgba(11, 13, 42, 0.1);

  /* ---------- Type sizes (rem on a 10 px base) --------------------------
     v7: ONE truthful token set for BOTH layouts (the old .ampy-bk--hero
     block that redefined --fs-12 to 1.3rem etc. is deleted; token names
     that lie are a defect class). Mobile differences are explicit
     per-element overrides inside media queries, never token remaps. */
  --fs-11: 11px;  /* source line mobile only */
  --fs-12: 12px;  /* kicker, share-status */
  --fs-13: 13px;  /* source line desktop, fine print, clarifier mobile */
  --fs-14: 14px;  /* meta layer: crumb, labels, clarifiers, caveat, law box, info */
  --fs-15: 15px;  /* secondary body: tips, rows mobile, tabs, lead intro */
  --fs-16: 16px;  /* primary body + ALL buttons + ALL inputs (iOS 16px floor) */
  --fs-17: 17px;  /* summary desktop, tile labels desktop */
  --fs-18: 18px;  /* verdict board mobile (v7.2) */
  --fs-20: 20px;  /* card/question/lead headings mobile, success h2 */
  --fs-22: 22px;  /* card/question/lead headings desktop */
  --fs-24: 24px;  /* verdict headline mobile */
  --fs-30: 30px;  /* verdict headline desktop */

  /* ---------- Block frame ---------------------------------------------- */
  --block-max-width: 600px; /* v5.1 A6: 600 px @ 10 px base, calm line length */
  --block-padding-y: var(--space-9);
  --block-padding-x: var(--space-10);

  /* Base */
  font-family: 'Outfit', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  color: var(--text-primary);
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  /* The tool provides its own :active / :focus-visible feedback, so suppress the
     default grey/blue tap flash on iOS Safari / Android Chrome. */
  -webkit-tap-highlight-color: transparent;
}

.ampy-bk *,
.ampy-bk *::before,
.ampy-bk *::after {
  box-sizing: border-box;
}

/* ============================================================================
   BLOCK — white surface, hairline border, fixed on desktop, natural flow on mobile
   ============================================================================ */
.ampy-bk__block {
  max-width: var(--block-max-width);
  margin: var(--space-7) auto;
  padding: var(--block-padding-y) var(--block-padding-x);
  background: var(--bg-primary);
  border: 1px solid var(--border-tertiary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  gap: 0; /* hanteras per-element via margin-bottom enligt mockup */
  scroll-margin-top: var(--space-11); /* 2.4rem — clean landing for scroll-sync */
}

@media (max-width: 767px) {
  /* v5.1 A2: gutter around the block on mobile — the block must not sit
     edge-to-edge. Outer margin = spacing-lg (1.5rem ≈ 15 px). */
  .ampy-bk__block {
    padding: var(--space-9) var(--space-9);
    margin: var(--space-7) var(--space-7);
  }
}

.ampy-bk__block { animation: ampy-bk-block 240ms ease both; }
@keyframes ampy-bk-block {
  from { opacity: 0; transform: translateY(2px); }
  to   { opacity: 1; transform: translateY(0); }
}
/* Staggered fade for drawer results (was referenced but undefined). */
@keyframes ampy-bk-fade {
  from { opacity: 0; transform: translateY(4px); }
  to   { opacity: 1; transform: translateY(0); }
}
@media (prefers-reduced-motion: reduce) {
  .ampy-bk__block,
  .ampy-bk__judgment,
  .ampy-bk__drawer > * { animation: none !important; }
  /* v7.3.7: the press/hover micro-transforms also move content */
  .ampy-bk__option:active,
  .ampy-bk__roomtile:active,
  .ampy-bk__roomtile:hover { transform: none !important; }
}

/* ============================================================================
   BREADCRUMB — universal pattern (v7): ONLY "← Tillbaka" on every slide
   ============================================================================ */
.ampy-bk__crumb {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  /* v7.1 (M4): the container min-height is dropped (the 44px tap target lives on
     the button); the crumb sits tight and consistent across every slide. */
  min-height: 0;
  margin: 0 0 var(--space-6);
}
@media (max-width: 767px) {
  /* v7.1 (M4): pull the crumb up into the card's top padding on mobile. */
  .ampy-bk__crumb { margin: calc(var(--space-4) * -1) 0 var(--space-6); } /* v7.3 (M1): sits higher */
}

.ampy-bk__crumb-back {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  background: none;
  border: 0;
  padding: var(--space-2) 0;
  margin: 0;
  min-height: 44px; /* the real tap target lives on the button, not the row */
  font: inherit;
  font-size: var(--fs-14);
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: color 150ms ease;
}
.ampy-bk__crumb-back:hover { color: var(--action-primary-text); }
.ampy-bk__crumb-back:focus-visible { outline: 2px solid var(--border-focus); outline-offset: 2px; }
.ampy-bk__crumb-back svg { width: 16px; height: 16px; }

/* v7.1 (D5b): the crumb carries the job title on the verdict screen
   (← Tillbaka · {job}). The separator + job-name span. */
.ampy-bk__crumb-sep { color: var(--text-tertiary); font-size: var(--fs-14); }
.ampy-bk__crumb-job {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 600;
  color: var(--text-primary);
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* ============================================================================
   QUESTION MODE — mellansteget
   ============================================================================ */
/* v7.3.3: the job name now lives in the crumb (.ampy-bk__crumb-job), identical
   to the verdict step, so the choice and verdict screens read consistently.
   The old uppercase .ampy-bk__q-kicker is retired. */
.ampy-bk__q-title {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-22);
  font-weight: 600;
  line-height: 1.3;
  color: var(--text-primary);
  letter-spacing: -0.01em;
  margin: 0 0 var(--space-9);
}

.ampy-bk__options {
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
  list-style: none;
  padding: 0;
  margin: 0 0 var(--space-9);
}

.ampy-bk__option {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-5);
  text-align: left;
  padding: var(--space-7);
  font: inherit;
  background: var(--bg-primary);
  color: var(--text-primary);
  border: 1px solid var(--border-tertiary);
  border-radius: var(--radius-md);
  cursor: pointer;
  min-height: 72px;
  transition: border-color 150ms ease, background 150ms ease, transform 150ms ease;
}
.ampy-bk__option:hover {
  border-color: var(--action-primary);
  background: var(--bg-secondary);
}
.ampy-bk__option:active { transform: scale(0.997); }
.ampy-bk__option:focus-visible {
  outline: none;
  border-color: var(--border-focus);
  box-shadow: var(--focus-ring);
}

.ampy-bk__option-body { display: block; min-width: 0; flex: 1; }
.ampy-bk__option-title {
  display: block;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16);
  font-weight: 600; /* v7: the options ARE the decision */
  line-height: 1.35;
  color: var(--text-primary);
}
.ampy-bk__option-clarifier {
  display: block;
  margin-top: 3px;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 400;
  line-height: 1.45;
  color: var(--text-secondary);
}
.ampy-bk__option-arrow {
  flex-shrink: 0;
  width: 18px;
  height: 18px;
  color: var(--text-secondary);
}

/* Info notice — always visible (replaces v3.1's expansion) */
.ampy-bk__info {
  display: flex;
  align-items: flex-start;
  gap: var(--space-4);
  padding: var(--space-5) var(--space-6);
  background: var(--bg-secondary);
  border-radius: var(--radius-md);
  margin: 0;
}
.ampy-bk__info-icon {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  margin-top: 2px;
  color: var(--text-info);
}
.ampy-bk__info-text {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 400;
  line-height: 1.5;
  color: var(--text-secondary);
  margin: 0;
}

/* ============================================================================
   JUDGMENT — badge + cite chip linked by the left accent
   ============================================================================ */
.ampy-bk__judgment {
  display: flex;
  gap: var(--space-5);
  margin: 0 0 var(--space-10);
  animation: ampy-bk-judgment 280ms cubic-bezier(0.2, 0.6, 0.2, 1) both;
}

@keyframes ampy-bk-judgment {
  from { opacity: 0; transform: translateY(4px); }
  to   { opacity: 1; transform: translateY(0); }
}

.ampy-bk__judgment-accent {
  width: 3px;
  background: var(--verdict-accent);
  border-radius: 0;
  flex-shrink: 0;
}

.ampy-bk__judgment-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

/* Verdict badge: pill, icon + word, contrast color */
.ampy-bk__badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-7);
  background: var(--verdict-badge-bg);
  color: var(--verdict-badge-fg);
  border-radius: var(--radius-full);
  font-family: 'Outfit', system-ui, sans-serif;
  /* The verdict is the payoff: it must be the dominant heading on the screen
     (>= the entry/lead titles). */
  font-size: var(--fs-20);
  font-weight: 700;
  line-height: 1.2;
  margin: 0;
  max-width: 100%;
}
.ampy-bk__badge svg { width: 18px; height: 18px; flex-shrink: 0; }
/* On narrow screens the full badge label ("Det här kräver elektriker") must stay
   on one line, so step the badge down a size. */
@media (max-width: 767px) {
  /* Keep the longest verdict label ("Det här får du göra själv" /
     "Det här kräver elektriker") on ONE line within the narrow card. */
  .ampy-bk__badge { font-size: var(--fs-15); padding: var(--space-3) var(--space-5); }
}

/* Per-verdict bindings */
.ampy-bk__judgment--green {
  /* Leafier green, kept distinct from the teal --action-primary. */
  --verdict-accent: rgb(15, 110, 86);
  --verdict-badge-bg: rgb(116, 200, 138);
  --verdict-badge-fg: rgb(4, 52, 44);
}
.ampy-bk__judgment--yellow {
  --verdict-accent: rgb(135, 101, 7);
  --verdict-badge-bg: rgb(245, 201, 122);
  --verdict-badge-fg: rgb(61, 42, 0);
}
.ampy-bk__judgment--red {
  --verdict-accent: rgb(122, 22, 35);
  --verdict-badge-bg: rgb(240, 149, 149);
  --verdict-badge-fg: rgb(80, 19, 19);
}

/* ============================================================================
   VERDICT SOURCE REF (v7.1, M7) — a minimal muted legal reference under the
   red/yellow verdict board. Replaces the deleted merged law box; the
   consequence now lives only in the Konsekvenser tab. GREEN gets none.
   ============================================================================ */
.ampy-bk__verdict-src {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-2);
  padding: var(--space-2) 0; /* >=24px effective tap target for the external link */
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-12);
  font-weight: 400;
  color: var(--text-tertiary);
  text-decoration: none;
  transition: color 150ms ease;
}
.ampy-bk__verdict-src:hover { color: var(--text-primary); }
.ampy-bk__verdict-src:hover span:first-child { text-decoration: underline; text-underline-offset: 2px; }
.ampy-bk__verdict-src:focus-visible { outline: none; box-shadow: var(--focus-ring); border-radius: var(--radius-sm); }
.ampy-bk__verdict-src svg { width: 13px; height: 13px; opacity: 0.75; flex-shrink: 0; }

/* ============================================================================
   TABS — thin underline, two states
   ============================================================================ */
.ampy-bk__tabs {
  display: flex;
  gap: var(--space-11);
  border-bottom: 1px solid var(--border-tertiary);
  margin: 0 0 var(--space-8);
}
@media (max-width: 767px) {
  .ampy-bk__tabs { gap: var(--space-9); }
}

.ampy-bk__tab {
  background: none;
  border: 0;
  padding: 0 0 var(--space-4);
  margin: 0 0 -1px;
  font: inherit;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-15);
  /* v7: weight 500 in BOTH states — kills the selected-state width shift */
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: color 150ms ease, border-color 150ms ease;
  min-height: 44px;
}
.ampy-bk__tab:hover { color: var(--text-primary); }
.ampy-bk__tab[aria-selected="true"] {
  color: var(--text-primary);
  border-bottom-color: var(--verdict-accent, var(--action-primary));
}
.ampy-bk__tab:focus-visible {
  outline: none;
  box-shadow: 0 -2px 0 var(--border-focus) inset;
}

/* ============================================================================
   FÖRKLARING TAB — summary + ✓/✗ contrast rows + caveat notice (green)
   ============================================================================ */
/* v7.1 (M6): indent the tab panel inward, clearing the full-height accent rule.
   v7.2 (§7): margin-bottom 2rem = the guaranteed minimum gap before the pinned
   CTA zone (collapses with the tab content's own last margin, never stacks). */
.ampy-bk__tab-body { display: block; padding-left: var(--space-4); margin-bottom: var(--space-9); }
@media (max-width: 767px) { .ampy-bk__tab-body { padding-left: var(--space-5); margin-bottom: var(--space-6); } }

/* v7.2 (§7): the verdict CTA stack (note + primary + secondary) pins to the
   card floor on BOTH desktop and mobile — remaining slack sits inside the
   layout above it, never as dead space under the CTAs. In the embedded layout
   (no min-height) the auto margin simply resolves to 0. */
.ampy-bk__cta-zone { margin-top: auto; }

.ampy-bk__summary {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-17);
  font-weight: 500;
  line-height: 1.5;
  color: var(--text-primary);
  text-wrap: pretty;
  margin: 0 0 var(--space-7);
}

.ampy-bk__row {
  display: flex;
  align-items: flex-start;
  gap: var(--space-4);
  margin: 0 0 var(--space-4);
}
/* Bump the last row so there is real breathing room before the CTA (extra
   important on red, where there is no caveat notice acting as a visual buffer). */
.ampy-bk__row:last-of-type { margin-bottom: var(--space-11); }

.ampy-bk__row-icon {
  flex-shrink: 0;
  /* v7.3.7: declared here, not only via the JS inline style, so any markup
     reproduction (render.php growth, Bricks copy) keeps the centering. */
  display: inline-flex;
  width: 18px;
  /* Box = one text line tall, glyph centered, so the icon optically aligns to the
     first line of the row text (robust vs a fixed margin-top nudge). */
  height: calc(var(--fs-16) * 1.5);
  align-items: center;
  justify-content: center;
}
.ampy-bk__row-icon svg { width: 18px; height: 18px; }
.ampy-bk__row--do .ampy-bk__row-icon { color: var(--text-success); }
.ampy-bk__row--dont .ampy-bk__row-icon { color: var(--text-tertiary); }

.ampy-bk__row-text {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16);
  font-weight: 400;
  line-height: 1.5;
  color: var(--text-secondary);
  margin: 0;
}

/* Caveat notice — green only. Amber accent, square corners.
   v7.2 (§8): align-items center so the ⚠ icon centers against the text block
   when it wraps to two lines on mobile (desktop single-line reads the same). */
.ampy-bk__caveat {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  border-left: 3px solid rgb(186, 117, 23);
  border-radius: 0;
  padding: 2px 0 2px var(--space-5);
  margin: 0 0 var(--space-9);
}
.ampy-bk__caveat-icon {
  flex-shrink: 0;
  display: inline-flex; /* v7.3.7: not only via the JS inline style */
  width: 16px;
  height: calc(var(--fs-14) * 1.55); /* one line tall, glyph centered -> aligns to first line */
  align-items: center;
  justify-content: center;
  color: rgb(186, 117, 23);
}
.ampy-bk__caveat-icon svg { width: 16px; height: 16px; }
.ampy-bk__caveat-text {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 400;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0;
}

/* ============================================================================
   TIPS TAB — punktlista
   ============================================================================ */
.ampy-bk__tips {
  list-style: none;
  padding: 0;
  margin: 0 0 var(--space-11);
  display: flex;
  flex-direction: column;
  gap: var(--space-5);
}
.ampy-bk__tip {
  display: flex;
  align-items: flex-start;
  gap: var(--space-4);
}
.ampy-bk__tip-icon {
  flex-shrink: 0;
  display: inline-flex; /* v7.3.7: not only via the JS inline style */
  width: 18px;
  height: calc(var(--fs-15) * 1.5); /* one line tall, glyph centered -> aligns to first text line */
  align-items: center;
  justify-content: center;
}
.ampy-bk__tip-icon svg { width: 18px; height: 18px; }
/* ✓ = something you MAY do (green check). ✗ = the stop condition (muted). */
.ampy-bk__tip--do .ampy-bk__tip-icon { color: var(--text-success); }
.ampy-bk__tip--dont .ampy-bk__tip-icon { color: var(--text-tertiary); }
.ampy-bk__tip-text {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-15);
  font-weight: 400;
  line-height: 1.5;
  color: var(--text-secondary);
}

/* ============================================================================
   KONSEKVENSER TAB — text-block
   ============================================================================ */
.ampy-bk__consequence-text {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-15);
  font-weight: 400;
  line-height: 1.55;
  color: var(--text-secondary);
  margin: 0 0 var(--space-11);
}

/* ============================================================================
   CTA (v7, F3) — ONE hierarchy on every verdict: solid teal advice CTA on top,
   outline "Läs mer om ..." below. Green adds a framing line above the pair.
   ============================================================================ */
/* v7.2 (§7): breathing room around the green framing line ("Vill du
   dubbelkolla ..."), which sat cramped against the tab body and the CTA. */
.ampy-bk__cta-note {
  margin: var(--space-9) 0 var(--space-5);
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-13);
  font-weight: 400;
  line-height: 1.5;
  color: var(--text-secondary);
}

.ampy-bk__cta-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-3);
  width: 100%;
  padding: var(--space-5);
  margin: 0 0 var(--space-4);
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16);
  font-weight: 600;
  line-height: 1.3;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-default);
  background: var(--bg-primary);
  color: var(--text-primary);
  cursor: pointer;
  text-decoration: none;
  transition: border-color 150ms ease, background 150ms ease;
  min-height: 48px;
}
.ampy-bk__cta-primary:hover {
  border-color: var(--action-primary);
  background: var(--bg-secondary);
}
.ampy-bk__cta-primary:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring);
}
.ampy-bk__cta-primary svg { width: 16px; height: 16px; }

/* Red primary — solid teal */
.ampy-bk__cta-primary--solid {
  background: var(--action-primary-strong);
  color: var(--text-inverse);
  border-color: var(--action-primary-strong);
}
.ampy-bk__cta-primary--solid:hover {
  opacity: 0.92;
  background: var(--action-primary-strong);
  border-color: var(--action-primary-strong);
}

/* Secondary outline button (red) */
.ampy-bk__cta-secondary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-3);
  width: 100%;
  padding: var(--space-5);
  margin: 0;
  line-height: 1.3; /* match the primary so the stacked buttons are equal height */
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-15);
  font-weight: 500;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-default);
  background: transparent;
  color: var(--text-primary);
  cursor: pointer;
  text-decoration: none;
  transition: border-color 150ms ease, background 150ms ease;
  min-height: 48px;
}
.ampy-bk__cta-secondary:hover {
  border-color: var(--action-primary);
  background: var(--bg-secondary);
}
.ampy-bk__cta-secondary:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring);
}

/* v7 (F3): the green-only .ampy-bk__cta-row / .ampy-bk__cta-link treatment is
   deleted — every verdict now ends with the same primary + secondary pair. */

/* v7.3.7: the share subsystem (button, popover, status, trust-row) is REMOVED.
   The share button was deliberately dropped from the verdict in v7.1 and the
   whole block had been unreachable dead code since (verified: zero render
   paths). The matching dead JS (renderShareButton/generateShareImage) is
   removed in the same release. */

/* ============================================================================
   ENTRY MODE — the picker (approved, keeps the v4 structure)
   ============================================================================ */
/* v7: .ampy-bk__head / __head-title removed — no render path creates them. */

/* Search */
.ampy-bk__search {
  margin: 0 0 var(--space-7);
}
/* The field wraps only the icon + input, so the icon centers on the INPUT,
   not on the label+input block (which left it floating high). */
.ampy-bk__search-field {
  position: relative;
  display: block;
}
.ampy-bk__search-label {
  display: block;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-13);
  font-weight: 500;
  color: var(--text-secondary);
  margin: 0 0 var(--space-2);
}
.ampy-bk__search-input {
  width: 100%;
  font: inherit;
  /* Strip Safari/WebKit native search chrome (pill shape + clear-x) so the field
     keeps the custom radius/height/padding on iOS + desktop Safari. */
  -webkit-appearance: none;
  appearance: none;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16); /* 16px floor so iOS Safari doesn't auto-zoom on focus */
  font-weight: 400;
  /* Matches the lead-form inputs (one input component, one size). */
  padding: var(--space-4) var(--space-5) var(--space-4) calc(var(--space-5) + 16px + var(--space-3));
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  background: var(--bg-primary);
  color: var(--text-primary);
  transition: border-color 150ms ease, box-shadow 150ms ease;
  min-height: 48px;
}
.ampy-bk__search-input::placeholder { color: var(--text-tertiary); }
.ampy-bk__search-input::-webkit-search-cancel-button,
.ampy-bk__search-input::-webkit-search-decoration { -webkit-appearance: none; display: none; }
.ampy-bk__search-input:focus {
  outline: none;
  border-color: var(--border-focus);
  box-shadow: var(--focus-ring);
}
.ampy-bk__search-icon {
  position: absolute;
  left: var(--space-5);
  /* Center to the input box regardless of its height (robust at 4.4 and 4.8rem). */
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-tertiary);
  pointer-events: none;
  width: 16px;
  height: 16px;
}

/* Rumschips */
.ampy-bk__rooms {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  list-style: none;
  padding: 0;
  margin: 0 0 var(--space-7);
}
.ampy-bk__room {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-5);
  min-height: 44px; /* comfortable 44px tap target */
  background: var(--bg-secondary);
  color: var(--text-primary);
  border: 1px solid transparent;
  border-radius: var(--radius-full);
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-13);
  font-weight: 500;
  cursor: pointer;
  transition: background 150ms ease, border-color 150ms ease, color 150ms ease;
  white-space: nowrap;
}
.ampy-bk__room svg { width: 14px; height: 14px; color: var(--action-primary); }
.ampy-bk__room:hover { border-color: var(--action-primary); }
.ampy-bk__room:focus-visible { outline: none; box-shadow: var(--focus-ring); }
/* v7.3.7: selected fill uses the STRONG teal - white 13px text on the light
   teal was ~2.97:1 (WCAG AA fail), and solid light-teal fill is reserved for
   the primary CTA (hard rule 5). White on strong teal is ~5.3:1. */
.ampy-bk__room[aria-selected="true"] {
  background: var(--action-primary-strong);
  color: var(--text-inverse);
  border-color: var(--action-primary-strong);
}
.ampy-bk__room[aria-selected="true"] svg { color: var(--text-inverse); }

@media (max-width: 767px) {
  /* v5.1 A1: chips scroll WITHIN the card's content width — must not spill
     past the padding edge. Hidden scrollbar + soft right fade signaling
     that more chips exist off to the right. */
  .ampy-bk__rooms {
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x proximity;
    scrollbar-width: none;
    margin: 0 0 var(--space-7);
    padding: 0 var(--space-7) var(--space-2) 0; /* room on the right for the fade */
    mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 24px), transparent 100%);
    -webkit-mask-image: linear-gradient(to right, #000 0%, #000 calc(100% - 24px), transparent 100%);
  }
  .ampy-bk__rooms::-webkit-scrollbar { display: none; }
  .ampy-bk__room { scroll-snap-align: start; }
}

/* Job list (entry mode) */
.ampy-bk__joblist-hint {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-13);
  font-weight: 500;
  color: var(--text-secondary);
  margin: 0 0 var(--space-3);
}

/* v7 (F1): the NEUTRAL job list — one flat list, hairline-divided rows.
   No group headers, no colour dots, no verdict word anywhere: a row must be
   unreadable as an answer. Teal on every row icon = interactivity only. */
.ampy-bk__joblist {
  list-style: none;
  padding: 0;
  margin: 0;
}
.ampy-bk__joblist > li { border-bottom: 1px solid var(--border-tertiary); }
.ampy-bk__joblist > li:last-child { border-bottom: 0; }

.ampy-bk__job-row {
  width: 100%;
  display: flex;
  align-items: center;
  gap: var(--space-5);
  padding: var(--space-5) var(--space-3);
  min-height: 52px;
  background: none;
  color: var(--text-primary);
  border: 0;
  border-radius: var(--radius-sm);
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16);
  font-weight: 500;
  line-height: 1.4;
  cursor: pointer;
  text-align: left;
  transition: background 150ms ease, color 150ms ease;
}
.ampy-bk__job-row:hover { background: var(--bg-secondary); }
.ampy-bk__job-row:hover .ampy-bk__job-row-label { color: var(--action-primary-text); }
.ampy-bk__job-row:focus-visible { outline: none; box-shadow: var(--focus-ring); }
.ampy-bk__job-row-icon { width: 20px; height: 20px; color: var(--action-primary); flex-shrink: 0; }
.ampy-bk__job-row-icon svg { width: 20px; height: 20px; }
.ampy-bk__job-row-label { min-width: 0; }
.ampy-bk__job-row-arrow { margin-left: auto; width: 16px; height: 16px; color: var(--text-tertiary); flex-shrink: 0; }
.ampy-bk__job-row-arrow svg { width: 16px; height: 16px; }

.ampy-bk__source-line {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-13);
  font-weight: 400;
  line-height: 1.45;
  color: var(--text-tertiary);
  margin: var(--space-7) 0 0;
  padding-top: var(--space-5);
  border-top: 1px solid var(--border-tertiary);
}

/* ============================================================================
   UTILITIES
   ============================================================================ */
.ampy-bk__sr {
  position: absolute;
  width: 1px; height: 1px;
  padding: 0; margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.ampy-bk__empty {
  padding: var(--space-5);
  text-align: center;
  color: var(--text-secondary);
  border: 1px dashed var(--border-default);
  border-radius: var(--radius-md);
  font-size: var(--fs-13);
}

.ampy-bk__loading {
  padding: var(--space-13);
  text-align: center;
  color: var(--text-secondary);
  font-size: var(--fs-14);
}

/* Crawlable fallback */
/* Crawlable / no-JS fallback (render.php). Replaced by JS immediately at boot,
   but styled anyway so search engines and no-JS visitors get a clean view. */
.ampy-bk__noscript .ampy-bk__instrument {
  max-width: var(--block-max-width);
  margin: var(--space-7) auto;
  padding: 0 var(--space-7);
}
.ampy-bk__noscript .ampy-bk__tagline {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-16);
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 var(--space-5);
}
.ampy-bk__noscript .ampy-bk__source-line,
.ampy-bk__noscript .ampy-bk__disclaimer {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-12);
  line-height: 1.5;
  color: var(--text-tertiary);
  margin: var(--space-5) 0 0;
}

.ampy-bk__noscript-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: var(--space-3);
  list-style: none;
  padding: 0;
  margin: var(--space-5) 0 0;
}
@media (max-width: 600px) {
  .ampy-bk__noscript-grid { grid-template-columns: repeat(2, 1fr); }
}
.ampy-bk__noscript-grid a {
  display: block;
  padding: var(--space-3) var(--space-5);
  background: var(--bg-primary);
  border: 1px solid var(--border-tertiary);
  border-radius: var(--radius-md);
  color: var(--text-primary);
  text-decoration: none;
  font-weight: 500;
  font-size: var(--fs-13);
}
.ampy-bk[data-booted="true"] .ampy-bk__noscript { display: none; }

/* ============================================================================
   HERO LAYOUT (layout="hero") — split-hero landing variant.
   Everything is scoped under .ampy-bk--hero so the default/embedded tool is
   completely untouched. The two-column split itself lives in Bricks; this
   only styles what is INSIDE the right column (the tool).
   ============================================================================ */

/* Larger, hero-context type ramp (the embedded "instrument" scale reads thin
   when the tool is the page hero). One step up across the board. */
/* v7: the hero-scope --fs-* remap is DELETED (one truthful token set for both
   layouts); hero-specific sizes are explicit per-element rules. */
.ampy-bk--hero {
  display: block;
  transition: min-height 200ms ease;
}
@media (prefers-reduced-motion: reduce) {
  .ampy-bk--hero { transition: none; }
}

/* Fill the Bricks right column instead of centering at 60rem. */
.ampy-bk--hero .ampy-bk__block {
  max-width: none;
  margin: 0;
  /* v7.1 (§4A): raised 52->56rem so S1-S4 share one height, keeping the centered
     left column (§2B) stable and the chrome out of view. */
  min-height: 560px;
  padding: var(--space-13);     /* 3.2rem — hero breathing room */
}
@media (max-width: 767px) {
  .ampy-bk--hero .ampy-bk__block {
    /* v7.3: mobile card is CONTENT-SIZED (no min-height floor). Owner wanted the
       internal caveat->CTA slack gone; with the scroll-anchor pinning the card
       top, a content-height card gives tight spacing and no gap above OR below
       the CTA. A tiny floor only so an ultra-short slide isn't awkward. */
    min-height: 300px;
    /* v7: 16px side padding buys one extra word per line at 375px */
    padding: var(--space-7) var(--space-7) var(--space-9);
  }
  /* v7.3: on mobile the CTA flows right after the content (no floor-pin), so the
     caveat/tips -> CTA gap is tight; desktop keeps the pin. */
  .ampy-bk--hero .ampy-bk__block .ampy-bk__cta-zone { margin-top: 0; }
  .ampy-bk--hero .ampy-bk__block .ampy-bk__source-line { margin-top: var(--space-9); }
}

/* v7: quick-pick chips, the "Välj rum" dropdown, commonwrap and "Se alla" CSS
   are removed — no render path has created them since the room-grid entry. */

/* The drawer: grouped list. Desktop scrolls INSIDE the card (long rooms), mobile flows. */
.ampy-bk__drawer {
  margin-top: var(--space-6);
  max-height: 60vh;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch; /* momentum scroll on iOS */
  padding-right: var(--space-2);
}
.ampy-bk__drawer[hidden] { display: none; }
.ampy-bk__drawer > * { animation: ampy-bk-fade 200ms ease both; }
@media (max-width: 767px) {
  /* Mobile drawer flows in the page (the card min-height is 0 on phones). */
  .ampy-bk__drawer { max-height: none; overflow: visible; padding-right: 0; margin-top: 0; }
  /* NOTE: the mobile .ampy-bk__drawer-back override lives AFTER its base rule
     below (equal specificity - source order decides; v7.3.7 cascade fix). */
}

/* Slide-2 header: room title + helper subtitle above the neutral list */
.ampy-bk__drawer-title {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-22);
  font-weight: 600;
  line-height: 1.25;
  letter-spacing: -0.01em;
  color: var(--text-primary);
  margin: 0 0 var(--space-3); /* v7.3 (M2): a touch more air title -> subtitle */
}
.ampy-bk__drawer-title:focus { outline: none; }
.ampy-bk__list-subtitle {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 400;
  line-height: 1.5;
  color: var(--text-secondary);
  margin: 0 0 var(--space-5);
}

/* "Visa vanliga eljobb" back link inside the results drawer */
.ampy-bk__drawer-back {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  margin-bottom: var(--space-4);
  padding: var(--space-2) 0;
  min-height: 44px;
  background: none;
  border: 0;
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  font-weight: 600;
  color: var(--action-primary-text);
  cursor: pointer;
}
.ampy-bk__drawer-back svg { width: 16px; height: 16px; }
.ampy-bk__drawer-back:hover { text-decoration: underline; text-underline-offset: 3px; }
.ampy-bk__drawer-back:focus-visible { outline: 2px solid var(--border-focus); outline-offset: 2px; border-radius: var(--radius-sm); }
@media (max-width: 767px) {
  /* v7.3 (M1): pull the list block up on mobile — the back control tucked up
     into the card padding (40px control; the tap area is preserved by padding).
     v7.3.7: moved AFTER the base rule; it used to sit before it in the file, so
     min-height/margin silently lost the equal-specificity cascade. */
  .ampy-bk__drawer-back { min-height: 40px; margin: calc(var(--space-3) * -1) 0 var(--space-3); }
}

/* Verdict headline block rhythm in hero (16px to the tabs) */
.ampy-bk--hero .ampy-bk__judgment { margin-bottom: var(--space-7); }
@media (min-width: 768px) {
  /* v7.2 (§7): with the CTA stack pinned to the card floor, give the desktop
     verdict a touch more top rhythm — crumb->board and board->tabs each +4px. */
  .ampy-bk--hero .ampy-bk__block[data-verdict] .ampy-bk__crumb { margin-bottom: var(--space-6); }
  .ampy-bk--hero .ampy-bk__block[data-verdict] .ampy-bk__judgment { margin-bottom: var(--space-9); }
}

/* ============================================================================
   ROOM-GRID ENTRY (hero, Version-3) — shared, theme-agnostic
   ============================================================================ */
/* Slide-1 card heading (v7): the card's ONLY heading — the old eyebrow and
   the search field are gone (T1/T2). */
.ampy-bk__entry-title {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-22);
  font-weight: 600;
  line-height: 1.25;
  letter-spacing: -0.01em;
  text-wrap: balance;
  color: var(--text-primary);
  margin: 0 0 var(--space-9);
}
.ampy-bk__entry-title[hidden] { display: none; }
.ampy-bk__entry-title:focus { outline: none; }

/* The 6-tile grid: 5 rooms + "Alla jobb" (2x3 fills the 520px floor) */
.ampy-bk__roomgrid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  grid-auto-rows: 1fr; /* v7 (T3): every row identical — the equal-size guarantee */
  gap: var(--space-5);
  list-style: none;
  padding: 0;
  /* v7.1 (M2): a guaranteed tiles->source gap so the divider never touches the
     last tiles (the mobile fill would otherwise collapse the auto-margin to 0). */
  margin: 0 0 var(--space-9);
}
.ampy-bk__roomgrid[hidden] { display: none; }
.ampy-bk__roomgrid li { margin: 0; }

.ampy-bk__roomtile {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: var(--space-3);
  width: 100%;
  height: 100%;   /* fill the 1fr row */
  min-height: 112px;
  padding: var(--space-7);
  background: var(--bg-primary);
  border: 1px solid var(--border-default);
  border-radius: var(--radius-md);
  text-align: center;
  cursor: pointer;
  transition: border-color 150ms ease, transform 150ms ease, box-shadow 150ms ease;
}
.ampy-bk__roomtile:active { transform: scale(0.98); }
.ampy-bk__roomtile:hover {
  border-color: var(--action-primary);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}
.ampy-bk__roomtile:focus-visible { outline: none; box-shadow: var(--focus-ring); }
.ampy-bk__roomtile-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: var(--radius-full); /* v7: circle, calmer than the square */
  background: rgba(0, 169, 145, 0.08);
  color: var(--action-primary);
  flex-shrink: 0;
}
/* stroke-width normalized to 1.8 across all tile glyphs (2.0 renders heavy) */
.ampy-bk__roomtile-chip svg { width: 20px; height: 20px; stroke-width: 1.8; }
.ampy-bk__roomtile-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  min-width: 0;
  width: 100%;
}
.ampy-bk__roomtile-label {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-17);
  font-weight: 600;
  line-height: 1.25;
  letter-spacing: -0.005em;
  color: var(--text-primary);
}
/* Single-line ellipsis at ALL widths: with grid-auto-rows 1fr this is what
   guarantees six pixel-identical tiles on desktop too. */
.ampy-bk__roomtile-sub {
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-14);
  line-height: 1.35;
  color: var(--text-tertiary);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 100%;
}
/* v7 (T3): the .ampy-bk__roomtile--all dashed/grey overrides are DELETED —
   the "Alla eljobb" tile is styled exactly like the five rooms. */
@media (max-width: 767px) {
  /* Keep 2 columns on mobile (minmax(0,1fr) prevents overflow); tiles a touch shorter */
  .ampy-bk__roomgrid { gap: var(--space-4); }
  .ampy-bk__roomtile { min-height: 92px; padding: var(--space-6); }
  .ampy-bk__roomtile-chip { width: 36px; height: 36px; }
  .ampy-bk__roomtile-chip svg { width: 18px; height: 18px; }
}

/* Pin the source line to the card floor so the card never reads half-empty.
   v7.2 (§7): auto-pinned on BOTH desktop and mobile again — with the mobile svh
   fill gone (plain 50rem floor) the pin no longer shoves it far down, and the
   roomgrid's 2rem margin-bottom guarantees it never collides with the tiles. */
.ampy-bk--hero .ampy-bk__source-line { margin-top: auto; }

/* Required-field asterisk (small, black) */
.ampy-bk__req { color: #000; margin-left: 2px; }

/* Lead-form consent fine print (replaces the checkbox) */
.ampy-bk__lead-fineprint {
  margin: var(--space-4) 0 0;
  font-size: var(--fs-13);
  line-height: 1.5;
  color: var(--text-tertiary);
}
.ampy-bk__lead-fineprint a {
  color: var(--action-primary-text);
  text-decoration: underline;
  text-underline-offset: 2px;
}

/* ============================================================================
   THE VERDICT REVEAL (hero) — identical in BOTH themes; type + restraint.
   The white card interior NEVER darkens; the accent is a full-height rule +
   a faint top wash (<=9% alpha) + a magazine cover-line.
   ============================================================================ */
.ampy-bk--hero .ampy-bk__block[data-verdict] { position: relative; overflow: hidden; }
.ampy-bk--hero .ampy-bk__block[data-verdict]::before {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 6px;
  background: var(--verdict-accent);
  z-index: 2;
}
/* Retire the 3px judgment sliver — the full-height rule replaces it */
.ampy-bk--hero .ampy-bk__block[data-verdict] .ampy-bk__judgment-accent { display: none; }
/* v7.2 (§3): --verdict-board-border is a SOFTENED, semi-transparent take on the
   accent used only for the board's hairline (the full-strength --verdict-accent
   stays on the rule, icons and tab indicator). */
.ampy-bk__block[data-verdict="green"]  { --verdict-accent: rgb(27, 132, 71);  --verdict-wash: rgba(54, 178, 92, 0.08);  --verdict-board-bg: rgba(54, 178, 92, 0.10);  --verdict-board-border: rgba(27, 132, 71, 0.45); } /* leafy family, never reads as the teal CTA */
.ampy-bk__block[data-verdict="red"]    { --verdict-accent: rgb(122, 22, 35);  --verdict-wash: rgba(214, 64, 64, 0.07);  --verdict-board-bg: rgba(214, 64, 64, 0.06);  --verdict-board-border: rgba(150, 40, 50, 0.4); }
.ampy-bk__block[data-verdict="yellow"] { --verdict-accent: rgb(135, 101, 7);  --verdict-wash: rgba(245, 175, 25, 0.09);  --verdict-board-bg: rgba(245, 175, 25, 0.08);  --verdict-board-border: rgba(135, 101, 7, 0.4); }
/* Interior wash: top-anchored, fades to white (never dark) */
.ampy-bk--hero .ampy-bk__block[data-verdict]::after {
  content: '';
  position: absolute;
  left: 0; right: 0; top: 0;
  height: 200px;
  background: linear-gradient(180deg, var(--verdict-wash) 0%, transparent 100%);
  pointer-events: none;
  z-index: 0;
}
.ampy-bk--hero .ampy-bk__block[data-verdict] > * { position: relative; z-index: 1; }
@media (max-width: 767px) {
  .ampy-bk--hero .ampy-bk__block[data-verdict]::after { height: 120px; }
}
/* v7.1 (D5c): the verdict label returns to a bordered green/red "board" (owner:
   the old bordered treatment beat the giant plain headline). The icon is back.
   The drama still lives in the full-height accent rule + top wash.
   v7.2 (§3): softened 1px tinted hairline (was a 1.5px full-accent border that
   read black/heavy), one type step up, padding grown proportionally. */
.ampy-bk--hero .ampy-bk__badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-6) var(--space-8);
  border: 1px solid var(--verdict-board-border, var(--verdict-accent));
  border-radius: var(--radius-md);
  background: var(--verdict-board-bg);
  color: var(--text-primary);
  font-family: 'Outfit', system-ui, sans-serif;
  font-weight: 700;
  font-size: var(--fs-22);
  line-height: 1.2;
  letter-spacing: -0.01em;
  max-width: 100%;
  margin: 0;
}
.ampy-bk--hero .ampy-bk__badge svg {
  display: inline-flex;
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  color: var(--verdict-accent);
}
@media (max-width: 767px) {
  /* v7.2 (§3): +3px type on mobile too, padding grown in step. */
  .ampy-bk--hero .ampy-bk__badge { font-size: var(--fs-18); padding: var(--space-5) var(--space-7); }
}
/* The lead form sets no data-verdict, so the wash/rule never fire there. */

/* ============================================================================
   TEAL USAGE RULE (documented intent)
   - SOLID teal FILL (.ampy-bk__cta-primary--solid) is reserved for the primary
     conversion action: the "Få kostnadsfri rådgivning" CTA (strongest on RED).
   - Teal as a functional ACCENT (focus rings, the search/affordance icons, the
     hire/advice text link) is allowed because it marks interactivity.
   - Teal is NOT used as decorative chrome. The green verdict ramp is a separate,
     warmer leafy green (--state-success) so "you may do it yourself" never reads
     as the teal "hire us" action.
   ============================================================================ */

/* v7.1 (M7): the merged law box is deleted; red/yellow now show only a minimal
   source ref under the board (.ampy-bk__verdict-src) and the consequence lives
   in the Konsekvenser tab. */


/* ---------- IN-TOOL LEAD FORM ---------------------------------------------- */
/* Same calm treatment as the breadcrumb back, so the back affordance is
   identical at every step (entry -> verdict -> lead -> success). */
.ampy-bk__lead-back {
  display: inline-flex; align-items: center; gap: var(--space-3);
  margin-bottom: var(--space-4); padding: var(--space-2) 0; min-height: 44px;
  background: none; border: 0; cursor: pointer;
  font-family: 'Outfit', system-ui, sans-serif; font-size: var(--fs-14); font-weight: 500;
  color: var(--text-secondary);
  transition: color 150ms ease;
}
@media (max-width: 767px) {
  /* v7.1 (M8): match the M4 crumb pull so the back affordance is identical at every step. */
  .ampy-bk__lead-back { margin: calc(var(--space-3) * -1) 0 var(--space-4); }
}
.ampy-bk__lead-back svg { width: 16px; height: 16px; }
.ampy-bk__lead-back:hover { color: var(--action-primary-text); }
.ampy-bk__lead-back:focus-visible { outline: 2px solid var(--border-focus); outline-offset: 2px; border-radius: var(--radius-sm); }
.ampy-bk__lead-title {
  margin: 0 0 var(--space-3);
  font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-22); font-weight: 600; line-height: 1.25; letter-spacing: -0.01em; color: var(--text-primary);
}
.ampy-bk__lead-intro {
  margin: 0 0 var(--space-8); font-size: var(--fs-15); line-height: 1.5; color: var(--text-secondary);
}
.ampy-bk__lead-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  column-gap: var(--space-5); row-gap: var(--space-7);
}
@media (max-width: 520px) { .ampy-bk__lead-grid { grid-template-columns: 1fr; row-gap: var(--space-7); } }
.ampy-bk__lead-field { display: flex; flex-direction: column; gap: var(--space-3); }
.ampy-bk__lead-label {
  font-family: 'Outfit', system-ui, sans-serif; font-size: var(--fs-14); font-weight: 600; color: var(--text-primary);
}
.ampy-bk__lead-input {
  width: 100%; min-height: 48px; padding: var(--space-4) var(--space-5);
  /* 16px floor: iOS Safari auto-zooms on focus for inputs < 16px (the embedded
     layout's --fs-15 is 15px), so pin to 1.6rem to prevent the zoom jump. */
  font-family: 'Outfit', system-ui, sans-serif; font-size: var(--fs-16); color: var(--text-primary);
  background: var(--bg-primary); border: 1px solid var(--border-default); border-radius: var(--radius-md);
  transition: border-color 150ms ease, box-shadow 150ms ease;
}
.ampy-bk__lead-input:focus { outline: none; border-color: var(--border-focus); box-shadow: var(--focus-ring); }
.ampy-bk__lead-hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
.ampy-bk__lead-error { margin: var(--space-3) 0 0; font-size: var(--fs-14); font-weight: 500; color: var(--state-error); }
.ampy-bk__lead-error[hidden] { display: none; }
.ampy-bk__lead-submit { margin: var(--space-7) 0 0; min-height: 48px; }
.ampy-bk__lead-submit:disabled { background: var(--action-primary-strong); border-color: var(--action-primary-strong); color: var(--text-inverse); cursor: default; }

/* v7.3.4 — DESKTOP-ONLY lead-form spacing (mobile <=767px is untouched, it is
   already right). The hero card reserves 56rem; on desktop the lead content is
   shorter, so the submit button sat only 16px under the fields ("extremt nära")
   while ~114px of dead space pooled below the fine print. Fix: center the form
   group in the card so the leftover slack balances top+bottom instead of dumping
   at the bottom, and open the fields->submit gap to 32px so the action reads as
   its own step (2x the 16px inter-row gap). */
@media (min-width: 768px) {
  .ampy-bk--hero .ampy-bk__lead {
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .ampy-bk--hero .ampy-bk__lead .ampy-bk__lead-submit { margin-top: var(--space-13); }
}
.ampy-bk__lead-success { text-align: center; padding: var(--space-7) var(--space-4); }
.ampy-bk__lead-success-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 56px; height: 56px; margin-bottom: var(--space-5);
  border-radius: 50%; background: rgba(54,178,92,0.14); color: var(--state-success);
}
.ampy-bk__lead-success-icon svg { width: 28px; height: 28px; }
.ampy-bk__lead-success h2 {
  margin: 0 0 var(--space-3); font-family: 'Outfit', system-ui, sans-serif;
  font-size: var(--fs-20); font-weight: 600; letter-spacing: -0.005em; color: var(--text-primary);
}
.ampy-bk__lead-success p { margin: 0 0 var(--space-5); font-size: var(--fs-15); line-height: 1.5; color: var(--text-secondary); }

/* ============================================================================
   v7 MOBILE OVERRIDES (<=767px) — explicit per-element steps, never token
   remaps. 90% of paid traffic is mobile: tighter type, >=16px inputs, >=44px
   tap targets, 52px primary buttons.
   ============================================================================ */
@media (max-width: 767px) {
  .ampy-bk__block { scroll-margin-top: 12px; }
  /* v7.1 (M2): 16px heading->tiles on mobile (symmetric with the 20px tiles->source). */
  .ampy-bk__entry-title { font-size: var(--fs-20); margin: 0 0 var(--space-7); }
  .ampy-bk__drawer-title { font-size: var(--fs-20); }
  .ampy-bk__roomtile-label { font-size: var(--fs-16); }
  .ampy-bk__roomtile-sub { font-size: var(--fs-13); }
  .ampy-bk__job-row { font-size: var(--fs-15); min-height: 48px; }
  .ampy-bk__q-title { font-size: var(--fs-20); margin-bottom: var(--space-7); }
  .ampy-bk__options { margin-bottom: var(--space-7); }
  .ampy-bk__option { padding: var(--space-6) var(--space-7); min-height: 64px; }
  .ampy-bk__option-title { font-size: var(--fs-15); }
  .ampy-bk__option-clarifier { font-size: var(--fs-13); }
  .ampy-bk__summary { font-size: var(--fs-16); }
  .ampy-bk__row-text { font-size: var(--fs-15); }
  .ampy-bk__row-icon { height: calc(var(--fs-15) * 1.5); }
  .ampy-bk__cta-primary, .ampy-bk__cta-secondary { min-height: 52px; }
  .ampy-bk__lead-title { font-size: var(--fs-20); }
  .ampy-bk__lead-submit { min-height: 52px; }
  .ampy-bk__source-line { font-size: var(--fs-11); line-height: 1.45; }
}


/* ==========================================================================
   HERO CHROME — split-hero landing (preview/hero.html), 1:1.
   Ported from preview/hero.html inline <style>, rem->px (1rem = 10px) on EVERY
   rem incl. inside clamp()/calc(); vw/%/px/deg untouched. Preview-only rules
   excluded (html 62.5% anchor already above, body, .qa-bar*, .qa-theme*).
   --ampy-header-h forced to 0px (JS probes the real sticky-header height at
   runtime). Everything is scoped to .elkollen-root / .hero / .ampy-bk--hero,
   so it cannot leak into the rest of the site. Do not hand-edit.
   ========================================================================== */
    /* ====================================================================
       PAGE CHROME — this is what Bricks owns in production (the split grid +
       the marketing sections). The plugin only owns what is inside .ampy-bk.

       A/B HARNESS: the entire hero lives inside a single
       .elkollen-root[data-hero-theme="light|dark"]. Only rules scoped under
       that attribute change between arms. NOTHING inside .ampy-bk is themed —
       the white tool card is byte-identical in both arms (fair A/B).

       v7: hero copy (H1/sub/buttons/mobile foot) is hydrated from
       data/behorighetskollen-data.json meta.hero so an owner copy swap is a
       data-file edit only. The static strings below are the same defaults.
       ==================================================================== */
    /* Production: 0 fallback; the tool JS probes the real sticky-header height at runtime. */
    :root { --ampy-header-h: 0px; }
    .wrap { max-width: 1180px; margin: 0 auto; padding: 0 24px; }


    /* ====================================================================
       ELKOLLEN ROOT — the single fork point
       ==================================================================== */
    .elkollen-root { display: block; min-height: 100vh; }

    /* Chrome tokens per theme (H1/sub/trust read these) */
    .elkollen-root[data-hero-theme="light"] {
      --pg-bg: #f4f5fb;
      --pg-ink: rgb(9, 11, 50);
      --pg-ink-2: #5a5d7a;
      --pg-teal: rgb(0, 169, 145);
      --pg-teal-strong: rgb(0, 122, 105);
      background: var(--pg-bg);
      color: var(--pg-ink);
    }
    .elkollen-root[data-hero-theme="dark"] {
      --stage-ink-0: #06070f;
      --aurora-rgb: 0, 169, 145;
      --pg-bg: #06070f;
      --pg-ink: #f5f7ff;
      --pg-ink-2: rgba(233, 237, 250, .70);
      --pg-teal: rgb(0, 169, 145);
      --pg-teal-strong: rgb(0, 122, 105);
      background: var(--stage-ink-0);
      color: var(--pg-ink);
    }

    /* ---------- HERO base (shared structure; colours come from tokens) ---------- */
    .hero { position: relative; padding: clamp(48px, 6vw, 80px) 0; }
    .hero__grid {
      display: grid;
      grid-template-columns: minmax(380px, 44fr) minmax(460px, 56fr);
      /* v7 root-cause fix (H1c): auto 1fr — the row-spanning tool can no longer
         inflate the head row, so the foot sits right under the paragraph. */
      grid-template-rows: auto 1fr;
      grid-template-areas:
        "head tool"
        "foot tool";
      column-gap: 64px;
      row-gap: 0;
      align-items: start;
    }
    .hero__head { grid-area: head; overflow-wrap: break-word; }
    .hero__tool { grid-area: tool; }
    .hero__foot { grid-area: foot; align-self: start; }
    @media (min-width: 768px) {
      /* v7.3 (owner): the left column is PINNED, not re-centered. The top spacer
         is a FIXED 9.6rem (matching its home-view centered position on the 56rem
         card), so even when the right card grows taller between slides the left
         column stays exactly where it started. The bottom 1fr just absorbs slack. */
      .hero__grid {
        grid-template-columns: minmax(380px, 44fr) minmax(460px, 56fr);
        grid-template-rows: 96px auto auto 1fr;
        grid-template-areas:
          ".    tool"
          "head tool"
          "foot tool"
          ".    tool";
        column-gap: 64px;
        row-gap: 0;
        align-items: start;
      }
      .hero__head { position: static; }
      .hero__foot { margin-top: 26px; } /* v7.3 (D2): 2px tighter paragraph->bullets */
    }

    /* v7 (H1a): Outfit 600, one size step down, opened leading, wider measure */
    .hero__h1 {
      font-family: 'Outfit', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      /* v7.0.1 (owner H1a): capped so the 50-char H1 breaks on TWO lines in the
         desktop column (~25ch/line), not three. Mobile keeps the 3.4rem floor.
         v7.3.6 (owner): desktop cap 3.7->4.0rem (+3px) for better balance against
         the taller CTAs; mobile floor and the tablet override are untouched. */
      font-size: clamp(34px, 3.2vw, 40px);
      font-weight: 600;
      line-height: 1.18; /* v7.1 (D4): airier */
      letter-spacing: -.015em;
      color: var(--pg-ink);
      margin: 0 0 14px; /* v7.3 (D2): 2px tighter H1->paragraph */
      text-wrap: balance;
    }
    /* v7.2 (QC): tablet step-down — the left column narrows to ~272-330px in the
       768-1023 split, where the desktop H1 size wraps to 4+ lines. */
    @media (min-width: 768px) and (max-width: 1023px) {
      .hero__h1 { font-size: clamp(28px, 3.4vw, 34px); }
    }
    /* v7.3.2 (QC): the teal sentence ALWAYS gets its own line (block), so the two
       H1 colours never share a line at ANY width. Was mobile-only, which let the
       768-1023 tablet range mix "eljobbet själv? Kolla" on one line. */
    .hero__h1-key { color: var(--pg-teal-strong); display: block; margin-top: 2px; }
    .hero__sub {
      font-size: 18px;
      line-height: 1.6; /* v7.1 (D4) */
      color: var(--pg-ink-2);
      max-width: 46ch;
      margin: 0;
    }
    .hero__trust { list-style:none; padding:0; margin:0 0 28px; display:flex; flex-direction:column; gap:16px; } /* v7.1 (D4): larger gap between bullets */
    .hero__trust li { display:flex; align-items:flex-start; gap:12px; font-size:15px; line-height:1.45; font-weight:500; color: var(--pg-ink-2); }
    .hero__trust svg { width:18px; height:18px; min-width:18px; margin-top:2px; color: var(--pg-teal); flex-shrink:0; }
    /* v7.3.6 (owner): DESKTOP-ONLY +2px on the paragraph and the three trust
       bullets (icons follow) for better balance against the taller CTAs.
       >=1024px so mobile ("perfect") and the narrow 768-1023 tablet column keep
       their tuned sizes. */
    @media (min-width: 1024px) {
      .hero__sub { font-size: 20px; }
      .hero__trust li { font-size: 17px; }
      .hero__trust svg { width: 20px; height: 20px; min-width: 20px; }
    }

    /* v7.2 (§4): TRUE ampy.se 1:1 button recipe (recovered from the v5/v6
       hero's .ampy-btn): gradient fill, NO border, soft white glow shadow,
       Outfit 400, fluid type + padding, icon pushed to the edge via
       justify-content:space-between + 2rem gap. SAME buttons in both A/B arms;
       only the dark stage swaps the glow for a plain elevation shadow. */
    .hero__actions { display:flex; flex-wrap:wrap; gap:20px; align-items:center; }
    /* v7.3.5: Picasso recipe (radius 16px, Outfit 400, ink #0d0d0d, gradients, light
       glow, translateY(-1px)+saturate hover) with OWNER finesse: taller body (the old
       ~9px vertical read too thin) and space-between so the label sits left and the
       icon sits at the right edge. On desktop the two buttons are flex:1 -> equal
       width, filling the row. Icon is on the RIGHT for both (arrow / phone). */
    .hero__btn {
      box-sizing:border-box;
      display:inline-flex; align-items:center; justify-content:space-between;
      flex:1 1 0;                 /* desktop: two equal-width buttons fill the row */
      gap:16px;                 /* minimum; space-between opens the rest */
      padding:20px 24px;        /* taller, premium body (~56px tall) */
      border:0; border-radius:16px;
      box-shadow: 0 0 16px rgba(241,241,241,0.25);
      font-family:'Outfit', system-ui, sans-serif;
      font-size: clamp(14px, calc(0.21vw + 13.3px), 16px);
      font-weight:400; line-height:1; color:#0d0d0d;
      text-decoration:none; white-space:nowrap; cursor:pointer;
      transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
    }
    .hero__btn svg { width: clamp(16px, calc(0.21vw + 15.3px), 18px); height: clamp(16px, calc(0.21vw + 15.3px), 18px); flex:0 0 auto; color:#0d0d0d; }
    .hero__btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(94,177,191,0.28); filter: saturate(1.05); }
    .hero__btn:focus-visible { outline:3px solid #00a991; outline-offset:3px; }
    /* PRIMARY "Kontakta oss" — green->teal-blue 120deg, arrow-up-right */
    .hero__btn--primary { background-image: linear-gradient(120deg, #55ff9a 0%, #5eb1bf 100%); }
    /* SECONDARY phone — light-blue->teal-blue 141deg (Picasso), phone icon stroke #212121 */
    .hero__btn--secondary { background-image: linear-gradient(141deg, #b6f2ff 0%, #5eb1bf 100%); box-shadow: 0 0 16px rgba(241,241,241,0.23); }
    .hero__btn--secondary svg { stroke:#212121; color:#212121; }

    /* Mobile-only foot pieces (hidden on desktop) */
    .hero__foot-ask { display:none; }

    /* ---------- RESPONSIVE: stack the split ---------- */
    @media (max-width: 1023px) {
      .hero__grid { grid-template-columns: minmax(0,40fr) minmax(0,60fr); column-gap: 40px; }
    }
    @media (max-width: 767px) {
      /* Locked mobile order: H1+sub -> TOOL -> foot. minmax(0,1fr) (not 1fr)
         so long Swedish words can't force horizontal overflow. */
      .hero__grid {
        grid-template-columns: minmax(0, 1fr);
        grid-template-rows: none;
        grid-template-areas:
          "head"
          "tool"
          "foot";
        row-gap: 20px;
        column-gap: 0;
      }
      .hero__head { position: static; }
      .hero__foot { margin-top: 4px; }
      .hero { padding: 24px 0 40px; }
      .wrap { padding: 0 16px; }
      /* v7.2 (§6): two lines total. "Får du göra eljobbet själv?" must hold ONE
         line at 375px (343px content width): measured with real Outfit 600 at
         -.012em, 3.0rem = 346.8px (wraps) but 2.8rem = 323.7px (fits); the 7.4vw
         slope keeps the fit down to 320px (2.4rem floor = 277.5px vs 288px). */
      .hero__h1 { font-size: clamp(24px, 7.4vw, 28px); line-height: 1.15; letter-spacing: -.012em; margin: 0 0 12px; }
      /* v7.1 (M1): teal-sentence-own-line is now global (see base rule); nothing extra here. */
      .hero__sub { font-size: 16px; line-height: 1.5; max-width: none; }
      /* v7 (H1d): the 3 trust bullets hide on mobile — every claim in them
         already lives inside the tool. The compact foot: ask line + stacked
         full-width buttons + one trust line. */
      .hero__trust { display: none; }
      .hero__foot-ask {
        display: block;
        font-size: 15px; font-weight: 600; line-height: 1.4;
        color: var(--pg-ink); text-align: center; margin: 0 0 12px;
      }
      /* v7.3.5: full-width stacked, tall, label-left/icon-right (space-between from
         the base). column-REVERSE puts the phone number FIRST and "Kontakta oss"
         second under "Hellre prata med en elektriker direkt?" (owner order), while
         desktop keeps Kontakta-left / phone-right with no DOM reorder. */
      .hero__actions { flex-direction: column-reverse; align-items: stretch; gap: 12px; }
      .hero__btn { width: 100%; max-width: 100%; flex: 0 0 auto; padding: 22px 24px; }
    }

    /* ====================================================================
       LIGHT THEME — "Verdict-as-Hero" editorial. Gallery-white; the white
       card floats and the verdict reveal is the payoff.
       ==================================================================== */
    .elkollen-root[data-hero-theme="light"] .hero { background: transparent; }
    /* v7.2 (§4): no light-theme button seating — the base 1:1 ampy.se recipe
       (gradient + soft white glow, no border) applies untouched. */
    /* Card float (chrome-only: the block wrapper shadow; interior untouched) */
    .elkollen-root[data-hero-theme="light"] .ampy-bk--hero .ampy-bk__block {
      box-shadow: 0 2px 4px rgba(11,13,42,.04), 0 18px 40px rgba(11,13,42,.09);
    }

    /* ====================================================================
       DARK THEME — "Aurora Stage" premium near-black midnight.
       Dominant field = near-black #06070f; Ampy midnight only as a top glow
       that decays to black; one restrained teal aurora; grain + vignette.
       ==================================================================== */
    .elkollen-root[data-hero-theme="dark"] .hero {
      isolation: isolate;
      overflow: clip;
      background: radial-gradient(120% 90% at 50% -20%, #0c0f36 0%, #090b26 34%, #070813 60%, var(--stage-ink-0) 100%);
    }
    /* Aurora (behind the right column) */
    .elkollen-root[data-hero-theme="dark"] .hero::before {
      content: "";
      position: absolute;
      inset: -10% -5%;
      z-index: -2;
      pointer-events: none;
      background:
        radial-gradient(34% 42% at 72% 40%, rgba(var(--aurora-rgb),.20) 0%, rgba(var(--aurora-rgb),.09) 42%, rgba(var(--aurora-rgb),0) 72%),
        radial-gradient(26% 36% at 60% 84%, rgba(var(--aurora-rgb),.10) 0%, rgba(var(--aurora-rgb),0) 70%);
      filter: blur(18px);
      animation: elk-aurora 24s ease-in-out infinite alternate;
      will-change: transform, opacity;
    }
    @keyframes elk-aurora {
      0%   { transform: translate3d(-1.5%,-1%,0) scale(1.02); opacity: .85; }
      100% { transform: translate3d(2.5%,1.5%,0) scale(1.09); opacity: 1; }
    }
    @media (prefers-reduced-motion: reduce) {
      .elkollen-root[data-hero-theme="dark"] .hero::before { animation: none; }
    }
    /* Depth: vignette + grain to kill banding */
    .elkollen-root[data-hero-theme="dark"] .hero::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: -1;
      pointer-events: none;
      background:
        radial-gradient(140% 120% at 50% -5%, transparent 52%, rgba(0,0,0,.55) 100%),
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
    }
    /* Lit card — the teal-tinted lift = the "lit vitrine" (wrapper only) */
    .elkollen-root[data-hero-theme="dark"] .ampy-bk--hero .ampy-bk__block {
      border-color: rgba(255,255,255,.08);
      box-shadow:
        0 2px 6px rgba(0,0,0,.35),
        0 28px 64px -16px rgba(2,3,12,.72),
        0 0 0 1px rgba(255,255,255,.05),
        0 0 90px -24px rgba(var(--aurora-rgb),.38);
    }
    /* Keep the H1 keyword legible on near-black (brighter teal) */
    .elkollen-root[data-hero-theme="dark"] .hero__h1-key { color: var(--pg-teal); }
    /* v7.2 (§4): dark-theme seating — the SAME 1:1 buttons (no border, no
       coloured glow ring); only a soft elevation shadow lifts them off the
       near-black stage. Dark ink is kept (set on the base rule). */
    .elkollen-root[data-hero-theme="dark"] .hero__btn {
      box-shadow: 0 8px 24px rgba(0,0,0,.45);
    }
    /* Teal-tint disc behind each trust check (dark polish) */
    .elkollen-root[data-hero-theme="dark"] .hero__trust svg {
      box-sizing: content-box;
      padding: 4px;
      border-radius: 999px;
      background: rgba(var(--aurora-rgb),.12);
    }
    /* Mobile dark (~80% traffic): freeze motion + drop blur; static centre glow */
    @media (max-width: 767px) {
      .elkollen-root[data-hero-theme="dark"] .hero {
        background: radial-gradient(140% 70% at 50% -8%, #0c0f36 0%, #090b24 30%, var(--stage-ink-0) 70%);
      }
      .elkollen-root[data-hero-theme="dark"] .hero::before {
        filter: none;
        animation: none;
        inset: 0;
        background: radial-gradient(60% 30% at 50% 46%, rgba(var(--aurora-rgb),.16) 0%, rgba(var(--aurora-rgb),.06) 45%, rgba(var(--aurora-rgb),0) 75%);
      }
    }
  
