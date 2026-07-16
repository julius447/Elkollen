# Elkollen — implementation documentation (for Chris)

**Tool version: 7.3.8.** These four references describe the *current* code. They were
written directly from `assets/`, `includes/`, `data/` and the previews at v7.3.8 and
cross-checked against the code — where an older doc disagrees, **the code and these
docs win.**

Elkollen is a Swedish lead-magnet: a homeowner picks an electrical job and gets a
GREEN / RED verdict ("får du göra det själv?") with the legal source, then an in-tool
form requests a free consultation. **The lead form is the entire business point** —
treat that flow as load-bearing. It renders inside the **Bricks** theme; you are
implementing it as **Fluent Snippets**.

---

## Read in this order

1. **[handover-bricks-fluentsnippets.md](handover-bricks-fluentsnippets.md)** — START HERE.
   The master implementation guide: the three-snippet model, the delivery contract
   (rem→px, fonts), Bricks placement, wiring the lead flow, the launch gate, and the
   pre-launch checklist.
2. **[reference-html.md](reference-html.md)** — the markup: the two layouts (embedded vs
   split-hero), what Bricks owns vs what the tool owns, the server mount + no-JS
   fallback, and the runtime DOM the JS builds.
3. **[reference-css.md](reference-css.md)** — the styling: token system, component
   vocabulary, responsive breakpoints, the 10px rem base + the rem→px porting rule, and
   the v7.3.8 font decision.
4. **[reference-js.md](reference-js.md)** — the behaviour: state machine, engine, render
   modes, the scroll contract, the lead flow, analytics, and the JS↔PHP contract.

For the human deploy steps and the test matrix, the repo root also has `CHECKLIST.md`
and `HANDOVER.md` (older; use these `docs/` for anything technical).

---

## The three things you must not miss

1. **⚠️ The `elkollen-fluent-snippets/` folder is STALE (v5.7.9).** Its embedded data and
   its `_build/elkollen.php.template` predate the entire v7 redesign and the v7.3.7
   hardening. **Do not ship it.** Regenerate the three snippets from the current v7.3.8
   source, and reconcile the PHP template first (it is missing the nonce `Cache-Control:
   no-store`, the OG `is_singular` gate + data shape-guard, and still uses `tool_only`
   instead of `layout="hero"`). See the handover §2.
2. **The font is currently the system stack, on purpose.** v7.3.8 removed an invalid
   `@import`; the tool renders in the host page's font. When production self-hosts Outfit
   it **will look different** — the owner must approve that rendering against the approved
   previews before launch. This was a real problem once; do not let it slip.
3. **The launch gate 🚦** — the tool gives legal guidance. Do not go public until a
   certified electrician (auktoriserad elinstallatör) has signed off the job matrix
   (`meta._pending_verification`). See the handover §7.

---

## Known issues found during this documentation pass (owner-gated)

- **Active verdict tab has no active-state styling.** The JS marks the active tab
  (Förklaring / Tips / Konsekvenser) with `aria-pressed="true"`, but the CSS active rule
  (`assets/behorighetskollen.css:465`) targets `[aria-selected="true"]`, so the underline
  + darker colour never apply — all three tabs look inactive (the body content still
  switches correctly). One-line fix (retarget the selector to `[aria-pressed="true"]`),
  but it *activates an inert rule* and therefore **changes the rendering the owner has
  seen** — so it is held for owner sign-off rather than shipped silently. See
  reference-css.md.

## Owner / content items still open (not code)

- `byta-vagguttag` links to `/elservice/strombrytare/`; the `/elservice/vagguttag/` page
  currently 404s — create it or accept the shared page.
- `utomhusbelysning` job-level tips text contradicts its red fork — reconcile at the
  electrician sign-off.
- OG share PNGs are not yet in `assets/og/` (shares degrade to text-only cards).
- `meta.last_reviewed` is a placeholder (`2026-06-XX`) — set it at sign-off.
