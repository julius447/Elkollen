/* ============================================================================
   Elkollen v7 — the 4-slide funnel (rooms -> neutral list -> choice -> verdict)
   Pixel-faithful rendering against the approved design references (the middle
   step and the verdict screen). Function, data and flow are locked since earlier versions.
     1. DATA   — behorighetskollen-data.json (single source of truth)
     2. ENGINE — pure resolve(job, answerIndex) → verdict
     3. VIEW   — entry / question / verdict, all in one block with a fixed frame
   ============================================================================ */
(function () {
  'use strict';

  /* ---------- Icon library (inline SVG, stroke-based) ------------------- */
  const ICONS = {
    check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
    x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    alert: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>',
    ban: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    arrowLeft: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
    arrowRight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
    chevron: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>',
    /* v7: drill-in chevron for the neutral job list (navigation, not action) */
    chevronRight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 6 15 12 9 18"/></svg>',
    external: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M19 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h6"/></svg>',
    share: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/><line x1="15.4" y1="6.5" x2="8.6" y2="10.5"/></svg>',
    search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',

    /* Job icons */
    bulb: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7c.8.6 1 1 1 1.8V18h6v-1.5c0-.8.2-1.2 1-1.8A7 7 0 0 0 12 2Z"/></svg>',
    lamp: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2v3"/><path d="M5 10 12 5l7 5-3 6H8l-3-6Z"/><path d="M12 16v6"/><path d="M9 22h6"/></svg>',
    pendant: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3h18"/><path d="M12 3v6"/><path d="M7 15a5 5 0 1 0 10 0c0-3-2-5-5-6-3 1-5 3-5 6Z"/><path d="M10 21h4"/></svg>',
    ceiling: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4h18"/><circle cx="12" cy="13" r="6"/><circle cx="12" cy="13" r="2.2"/><path d="M12 4v3"/></svg>',
    spotlight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="5"/><path d="M7 14l-3 7"/><path d="M17 14l3 7"/><path d="M9 14l1 7"/><path d="M15 14l-1 7"/></svg>',
    switch: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><rect x="9" y="7" width="6" height="10" rx="1"/><line x1="12" y1="9" x2="12" y2="13"/></svg>',
    outlet: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="11" r="1.2"/><circle cx="15" cy="11" r="1.2"/><path d="M9 16h6"/></svg>',
    panel: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>',
    rcd: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="9" r="2.4"/><rect x="10" y="14" width="4" height="4" rx="0.8"/><line x1="12" y1="6.6" x2="12" y2="3.5"/></svg>',
    stove: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="9" r="2"/><circle cx="15.5" cy="9" r="2"/><circle cx="8.5" cy="16" r="2"/><circle cx="15.5" cy="16" r="2"/></svg>',
    appliance: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><circle cx="12" cy="14" r="5"/><circle cx="12" cy="14" r="1.6"/><line x1="7" y1="6" x2="9" y2="6"/></svg>',
    outdoor: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22V12"/><path d="M8 12l4-9 4 9"/><path d="M5 22h14"/><circle cx="12" cy="9" r="2"/></svg>',
    smart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.55a11 11 0 0 1 14 0"/><path d="M8.5 16.05a6 6 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1.2"/><path d="M2 8.82a15 15 0 0 1 20 0"/></svg>',
    heatpump: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="10" rx="2"/><path d="M6 19c1-1 2-1 3 0s2 1 3 0 2-1 3 0 2 1 3 0"/><line x1="6" y1="9" x2="18" y2="9"/><line x1="6" y1="12" x2="18" y2="12"/></svg>',
    heat: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14c2-2 4-2 6 0s4 2 6 0 4-2 4-2"/><path d="M4 18c2-2 4-2 6 0s4 2 6 0 4-2 4-2"/><path d="M12 4v6"/><path d="M9 7l3-3 3 3"/></svg>',
    balance: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M3 21h18"/><path d="M6 7l-3 6h6Z"/><path d="M18 7l-3 6h6Z"/><path d="M3 7h18"/></svg>',
    renovate: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4l6 6-8 8H6v-6Z"/><path d="M3 21h7"/></svg>',
    bath: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12h20v5a3 3 0 0 1-3 3H5a3 3 0 0 1-3-3v-5Z"/><path d="M6 12V5a2 2 0 0 1 4 0"/><line x1="4" y1="20" x2="4" y2="22"/><line x1="20" y1="20" x2="20" y2="22"/></svg>',
    cable: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4v6a4 4 0 0 0 4 4h0a4 4 0 0 1 4 4v2"/><path d="M20 20v-6a4 4 0 0 0-4-4h0a4 4 0 0 1-4-4V4"/><rect x="2" y="2" width="4" height="4"/><rect x="18" y="18" width="4" height="4"/></svg>',
    splice: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12h6"/><path d="M15 12h6"/><rect x="9" y="9" width="6" height="6" rx="1"/><path d="M9 9l-1.5-1.5"/><path d="M15 15l1.5 1.5"/></svg>',
    kitchen: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 21V8h16v13"/><path d="M4 13h16"/><circle cx="8" cy="10.5" r="0.6"/><circle cx="12" cy="10.5" r="0.6"/><path d="M9 17h3v3H9z"/><path d="M15 16v3"/></svg>',
    /* v7: redrawn sofa on the 24-grid (the old glyph read weak at tile size) */
    sofa: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11V8a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v3"/><path d="M2 13a2 2 0 0 1 4 0v1h12v-1a2 2 0 0 1 4 0v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/><path d="M5 19v2"/><path d="M19 19v2"/></svg>',
    felsok: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
    charger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M13 7l-4 6h3l-1 4 4-6h-3z"/></svg>',
    solar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="13" width="18" height="8" rx="1"/><line x1="7" y1="13" x2="6" y2="21"/><line x1="12" y1="13" x2="12" y2="21"/><line x1="17" y1="13" x2="18" y2="21"/><line x1="3" y1="17" x2="21" y2="17"/><circle cx="12" cy="6" r="2.5"/><line x1="12" y1="1" x2="12" y2="2.2"/><line x1="6.3" y1="6" x2="5.1" y2="6"/><line x1="18.9" y1="6" x2="17.7" y2="6"/></svg>',
    inspect: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="M9 13l2 2 4-4"/></svg>',
    grid: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',

    /* Share / social icons */
    facebook: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
    x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    xtwitter: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24h-6.66l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
    reddit: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm5.01 9.21c.026.18.04.365.04.55 0 2.8-3.262 5.07-7.286 5.07-4.024 0-7.286-2.27-7.286-5.07 0-.19.014-.378.042-.562a1.412 1.412 0 0 1-.65-1.187 1.42 1.42 0 0 1 2.408-1.018 7.157 7.157 0 0 1 3.864-1.21l.737-3.468a.3.3 0 0 1 .357-.23l2.434.518a1.01 1.01 0 1 1-.117.562l-2.155-.458-.66 3.11a7.15 7.15 0 0 1 3.806 1.205 1.42 1.42 0 1 1 1.715 2.205zM8.5 11.75a1.06 1.06 0 1 0 2.12 0 1.06 1.06 0 0 0-2.12 0zm5.69 2.92c-.74.74-2.27.8-2.69.8-.42 0-1.95-.06-2.69-.8a.27.27 0 0 1 .38-.38c.46.46 1.45.63 2.31.63.86 0 1.85-.17 2.31-.63a.27.27 0 1 1 .38.38zm-.13-1.86a1.06 1.06 0 1 1 0-2.12 1.06 1.06 0 0 1 0 2.12z"/></svg>',
    mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>',
    link: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>'
  };
  ICONS.search_icon = ICONS.search;
  // Alias: data uses 'search' for the felsökning (troubleshooting) job
  const icon = (name) => ICONS[name] || ICONS.info;

  /* ---------- Element helper -------------------------------------------- */
  const el = (tag, attrs, children) => {
    const node = document.createElement(tag);
    if (attrs) for (const k in attrs) {
      if (k === 'class') node.className = attrs[k];
      else if (k === 'html') node.innerHTML = attrs[k];
      else if (k.startsWith('on') && typeof attrs[k] === 'function') node.addEventListener(k.slice(2), attrs[k]);
      else if (k === 'data') for (const dk in attrs[k]) node.dataset[dk] = attrs[k][dk];
      else if (attrs[k] === true) node.setAttribute(k, '');
      else if (attrs[k] !== false && attrs[k] != null) node.setAttribute(k, attrs[k]);
    }
    if (children) {
      (Array.isArray(children) ? children : [children]).forEach(c => {
        if (c == null || c === false) return;
        node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
      });
    }
    return node;
  };

  /* ---------- Engine: pure resolve --------------------------------------- */
  function resolve(job, answerIndex) {
    if (!job) return { kind: 'unknown' };
    if (job.type === 'fixed') return { kind: 'verdict', verdictKey: job.default_verdict };
    if (job.type === 'conditional') {
      if (answerIndex == null || !job.options[answerIndex]) return { kind: 'ask' };
      return { kind: 'verdict', verdictKey: job.options[answerIndex].verdict };
    }
    return { kind: 'unknown' };
  }

  /* v7: jobGroup() + the verdict-grouped list are GONE by design. Job lists are
     neutral (F1): no green/yellow/red headers, no colour dots. The verdict is
     only ever revealed on slide 4, after a committed choice. */

  /* ---------- Analytics: funnel event emitter ---------------------------- */
  // Vendor-agnostic. Pushes to window.dataLayer (GA4/GTM) AND dispatches a
  // DOM CustomEvent ('elkollen:track') so any analytics tool (Plausible, a
  // server-side proxy, etc.) can subscribe. No-ops cleanly if nothing listens.
  function track(event, props) {
    const payload = Object.assign({ event: 'elkollen_' + event, elkollen_action: event }, props || {});
    try {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push(payload);
    } catch (e) { /* no-op */ }
    try {
      window.dispatchEvent(new CustomEvent('elkollen:track', { detail: payload }));
    } catch (e) { /* no-op */ }
  }

  /* ---------- App -------------------------------------------------------- */
  class ElkollenApp {
    constructor(mount, data) {
      this.mount = mount;
      this.data  = data;
      this.jobsById = Object.fromEntries(data.jobs.map(j => [j.id, j]));
      this.state = this.readUrl();
      this.activeTab = 'explain';
      this.activeRoom = null;
      this.searchQuery = '';
      this.swapNode = null;
      // v5.4: hero layout (split-hero landing). Compact entry, larger type,
      // anti-jump panel. Gated entirely on this flag; default mode is untouched.
      this.layout = mount.dataset.layout || '';
      this.heroMode = this.layout === 'hero';
      this.heroExpanded = false;
      this.leadOpen = false;
      this._booted = false;
      this._lastShownKey = null;
      this.bindHistory();
      track('tool_view', { layout: this.layout || 'default', deep_link: this.state.jobId || null });
    }

    readUrl() {
      const p = new URLSearchParams(window.location.search);
      const jobId = p.get('jobb');
      const ansRaw = p.get('svar');
      const ans = ansRaw === null ? null : parseInt(ansRaw, 10);
      return {
        jobId: jobId && this.jobsById && this.jobsById[jobId] ? jobId : null,
        answerIndex: Number.isInteger(ans) ? ans : null
      };
    }

    writeUrl(push = true) {
      const p = new URLSearchParams(window.location.search);
      p.delete('jobb'); p.delete('svar');
      if (this.state.jobId) p.set('jobb', this.state.jobId);
      if (this.state.answerIndex != null) p.set('svar', String(this.state.answerIndex));
      const qs = p.toString();
      const url = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
      if (push) history.pushState(this.state, '', url);
      else history.replaceState(this.state, '', url);
    }

    bindHistory() {
      window.addEventListener('popstate', () => {
        this.state = this.readUrl();
        this.activeTab = 'explain';
        this.leadOpen = false; // Back/Forward must not leave the lead form stuck open
        this.render();
      });
    }

    /* Fire an impression event only when the distinct view changes, so opening/
       closing the lead form and Back/Forward to an unchanged view don't inflate it. */
    _trackShown(event, props, key) {
      if (key === this._lastShownKey) return;
      this._lastShownKey = key;
      track(event, props);
    }

    navigate(next, push = true) {
      if (next && next.jobId && next.jobId !== this.state.jobId) {
        track('job_selected', { job_id: next.jobId });
      }
      if (next && Object.prototype.hasOwnProperty.call(next, 'answerIndex') && next.answerIndex != null) {
        // Enrich with the chosen option's verdict. (v7.2: the guess-fork
        // enrichment is gone with the guess jobs themselves — every remaining
        // question is a genuine scenario fork.)
        const ansJobId = next.jobId || this.state.jobId;
        const ansJob = ansJobId ? this.jobsById[ansJobId] : null;
        const ansOpt = (ansJob && ansJob.options) ? ansJob.options[next.answerIndex] : null;
        track('question_answered', {
          job_id: ansJobId,
          answer_index: next.answerIndex,
          option_verdict: ansOpt ? ansOpt.verdict : null
        });
      }
      this.state = { ...this.state, ...next };
      this.activeTab = 'explain';
      this.leadOpen = false; // leaving any verdict closes the lead form
      this.writeUrl(push);
      this.render();
    }

    openLead() {
      const job = this.state.jobId ? this.jobsById[this.state.jobId] : null;
      const result = resolve(job, this.state.answerIndex);
      if (!job || result.kind !== 'verdict') return;
      this.leadOpen = true;
      track('lead_form_open', { job_id: job.id, verdict: result.verdictKey });
      this.render();
    }

    closeLead() {
      this.leadOpen = false;
      this.render();
    }

    backOne() {
      const job = this.state.jobId ? this.jobsById[this.state.jobId] : null;
      if (job && job.type === 'conditional' && this.state.answerIndex != null) {
        this.navigate({ answerIndex: null });
      } else {
        this.navigate({ jobId: null, answerIndex: null });
      }
    }

    render() {
      const noscript = this.mount.querySelector('.ampy-bk__noscript');
      if (noscript) noscript.remove();
      this.mount.dataset.booted = 'true';

      const job = this.state.jobId ? this.jobsById[this.state.jobId] : null;
      const result = resolve(job, this.state.answerIndex);

      let block;
      if (this.leadOpen && job && result.kind === 'verdict') {
        block = this.renderLeadBlock(job, result.verdictKey);
        // do not touch _lastShownKey: closing the form returns to the same verdict
        // without re-firing verdict_shown.
      } else if (!job || result.kind === 'unknown') {
        block = this.renderEntryBlock();
        this._lastShownKey = null; // leaving the verdict/question resets the impression
      } else if (result.kind === 'ask') {
        block = this.renderQuestionBlock(job);
        this._trackShown('question_shown', { job_id: job.id, question_type: job.choice_type || 'scenario' }, 'q:' + job.id);
      } else {
        block = this.renderVerdictBlock(job, result.verdictKey);
        this._trackShown('verdict_shown', { job_id: job.id, verdict: result.verdictKey }, 'v:' + job.id + ':' + result.verdictKey);
      }

      // Hero mode: anti-jump. Hold the previous height across the swap, then
      // release so the panel settles to the new content (with a CSS transition)
      // instead of snapping. Default mode keeps its natural flow.
      if (this.heroMode) {
        const prev = this.mount.firstElementChild;
        const h = prev ? prev.offsetHeight : 0;
        if (h) this.mount.style.minHeight = h + 'px';
        this.mount.replaceChildren(block);
        requestAnimationFrame(() => { this.mount.style.minHeight = ''; });
      } else {
        this.mount.replaceChildren(block);
      }

      // v7 scroll/focus contract (M2), applied on EVERY view swap:
      // 1) focus the new view's [data-focus-target] with preventScroll:true so
      //    focus never fights the programmatic scroll (and no keyboard pops);
      // 2) then _syncScroll() anchors the tool-card top near the viewport top.
      // Skip the very first paint (booting) so we never steal focus or scroll
      // on page load.
      if (this._booted) {
        const target = block.querySelector('[data-focus-target]:not([hidden])');
        if (target) {
          target.setAttribute('tabindex', '-1');
          try { target.focus({ preventScroll: true }); } catch (e) { target.focus(); }
        }
        // Deferred sync: let the browser settle layout + scroll clamping after
        // replaceChildren (the page height changes between slides) BEFORE
        // measuring; a same-tick measurement is stale and the sync no-ops.
        // setTimeout (not rAF): rAF never fires in occluded/background tabs,
        // which would silently skip the sync there.
        clearTimeout(this._syncT);
        this._syncT = setTimeout(() => this._syncScroll(), 60);
      }
      this._booted = true;
    }

    /* v7 (M2): one scroll rule for every transition. The TOOL-CARD TOP is the
       anchor; a tolerance band prevents jarring micro-scrolls when the card is
       already near the top (desktop no-op most of the time). --ampy-header-h is
       the sticky-header hook for the Bricks page; 0 in the static prototype. */
    _syncScroll() {
      const block = this.mount.firstElementChild;
      if (!block) return;
      let headerH = 0;
      try {
        headerH = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--ampy-header-h')) || 0;
      } catch (e) { /* no-op */ }
      // v7.2 (§9): mobile lands the card top ~28px under the header so the eye
      // catches a sliver of page background + the card's rounded top on every
      // transition (including the lead form); the tight band prevents
      // hunt-scroll. Desktop keeps a wide band -> a no-op while the centered
      // column is stable.
      const isMobile = window.matchMedia('(max-width: 767px)').matches;
      const anchor = headerH + (isMobile ? 28 : 12); // px: target position for the card top
      const top = block.getBoundingClientRect().top;
      if (top < anchor - 2 || top > anchor + (isMobile ? 6 : 48)) {
        // INSTANT scroll, deliberately not 'smooth': smooth scrolling is
        // rAF-driven and silently stalls in occluded/background tabs, and it
        // can be preempted by the view swap. An instant anchor is reliable on
        // every device and reads as a crisp slide change.
        window.scrollTo(0, Math.max(0, window.scrollY + top - anchor));
      }
    }

    /* ===================================================================
       BREADCRUMB — universal pattern
       =================================================================== */
    renderCrumb(titleText) {
      // v7.1 (D5b): the crumb is the back control; on the verdict screen it also
      // carries the job title (the caps kicker + "Ditt val" subline are removed).
      // Called with no argument elsewhere -> back-only.
      const row = el('div', { class: 'ampy-bk__crumb' }, [
        el('button', {
          class: 'ampy-bk__crumb-back',
          type: 'button',
          'aria-label': 'Ett steg tillbaka',
          onclick: () => this.backOne()
        }, [
          el('span', { html: icon('arrowLeft'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          'Tillbaka'
        ])
      ]);
      if (titleText) {
        row.appendChild(el('span', { class: 'ampy-bk__crumb-sep', 'aria-hidden': 'true' }, '·'));
        row.appendChild(el('span', { class: 'ampy-bk__crumb-job' }, titleText));
      }
      return row;
    }

    /* ===================================================================
       QUESTION MODE — mellansteget (v5 mockup)
       =================================================================== */
    renderQuestionBlock(job) {
      const block = el('div', { class: 'ampy-bk__block', role: 'region', 'aria-labelledby': 'ampy-bk-q' });

      block.appendChild(this.renderCrumb());

      // v7: neutral kicker carries the job name (deliberately never
      // verdict-tinted on the choice step, so nothing leaks).
      block.appendChild(el('p', { class: 'ampy-bk__q-kicker' }, job.label.toUpperCase()));

      // The question
      block.appendChild(el('p', { class: 'ampy-bk__q-title', id: 'ampy-bk-q', 'data-focus-target': '' }, job.question));

      // Svarsalternativ (title + clarifier subline)
      const options = el('ul', { class: 'ampy-bk__options', role: 'list' });
      job.options.forEach((opt, idx) => {
        const li = el('li');
        const body = el('span', { class: 'ampy-bk__option-body' }, [
          el('span', { class: 'ampy-bk__option-title' }, opt.label),
          opt.clarifier ? el('span', { class: 'ampy-bk__option-clarifier' }, opt.clarifier) : null
        ]);
        const btn = el('button', {
          class: 'ampy-bk__option',
          type: 'button',
          onclick: () => this.navigate({ jobId: job.id, answerIndex: idx })
        }, [
          body,
          el('span', { class: 'ampy-bk__option-arrow', html: icon('arrowRight'), 'aria-hidden': 'true', style: 'display:inline-flex' })
        ]);
        li.appendChild(btn);
        options.appendChild(li);
      });
      block.appendChild(options);

      // Informationsnotis — alltid synlig; texten bor i datafilen. v7.2: every
      // remaining question is a scenario fork (the guess forks are gone), so
      // the info line is always the scenario one.
      const entryStrings = (this.data.meta && this.data.meta.entry) || {};
      const infoText = entryStrings.info_scenario || 'Lagen skiljer på att byta något befintligt och att installera nytt. Ditt svar avgör vilken regel som gäller.';
      block.appendChild(el('div', { class: 'ampy-bk__info', role: 'note' }, [
        el('span', { class: 'ampy-bk__info-icon', html: icon('info'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
        el('p', { class: 'ampy-bk__info-text' }, infoText)
      ]));

      return block;
    }

    /* ===================================================================
       VERDICT MODE — the verdict screen (v5 mockup)
       =================================================================== */
    renderVerdictBlock(job, verdictKey) {
      const v = this.data.verdicts[verdictKey];
      const block = el('div', { class: 'ampy-bk__block', role: 'region', 'aria-labelledby': 'ampy-bk-v' });
      // Drives the §5 verdict-reveal (full-height accent rule + interior wash).
      block.dataset.verdict = verdictKey;

      // v7.1 (D5b): the crumb now carries the job title (chip_label where authored
      // gives a tighter mobile crumb, else the full label).
      block.appendChild(this.renderCrumb(job.chip_label || job.label));

      // The chosen option, when there is one. v7.2: the 10 direct-verdict jobs
      // are type:fixed again (no question, no options) and land here with
      // answerIndex == null -> chosenOpt null -> every render path below falls
      // back to job-level content. Used to surface option-level green tips (§3F).
      const chosenOpt = (this.state.answerIndex != null && job.options && job.options[this.state.answerIndex])
        ? job.options[this.state.answerIndex] : null;

      // Judgment — full-height accent rule + the boxed verdict board (D5c). The
      // caps kicker and the "Ditt val" subline are gone (D5a); the job name is
      // in the crumb.
      const judgment = el('div', { class: `ampy-bk__judgment ampy-bk__judgment--${verdictKey}` });
      judgment.appendChild(el('div', { class: 'ampy-bk__judgment-accent', 'aria-hidden': 'true' }));
      const jbody = el('div', { class: 'ampy-bk__judgment-body' });
      const badgeIcon = verdictKey === 'green' ? 'check' : (verdictKey === 'red' ? 'ban' : 'alert');
      // h2, not h1: the page H1 is owned by Bricks/hero. data-focus-target so
      // focus lands here after the verdict renders (announced once, no aria-live
      // shouting the heading on every paint). The icon returns inside the board.
      jbody.appendChild(el('h2', {
        class: 'ampy-bk__badge',
        id: 'ampy-bk-v',
        'data-focus-target': ''
      }, [
        el('span', { html: icon(badgeIcon), 'aria-hidden': 'true', style: 'display:inline-flex' }),
        el('span', {}, v.label)
      ]));
      // v7.1 (M7): red/yellow get a minimal source ref directly under the board;
      // the merged law box is gone and the consequence lives only in the
      // Konsekvenser tab. GREEN renders no source element (the Förklaring tab +
      // the amber caveat carry the sourcing).
      if (verdictKey !== 'green') {
        const source = this._resolveSource(job, verdictKey);
        jbody.appendChild(el('a', {
          class: 'ampy-bk__verdict-src',
          href: source.url,
          target: '_blank',
          rel: 'noopener noreferrer'
        }, [
          el('span', {}, source.text),
          el('span', { html: icon('external'), 'aria-hidden': 'true', style: 'display:inline-flex' })
        ]));
      }
      judgment.appendChild(jbody);
      block.appendChild(judgment);

      // Two segments. Rendered as plain toggle buttons (aria-pressed), NOT an
      // ARIA tablist: we do not implement the roving-tabindex/arrow-key contract,
      // so claiming role=tab would mislead screen-reader users.
      // v7: the Tips tab is HIDDEN when the job has no tips array (otherwise
      // green verdicts on tip-less jobs render an empty tab). With only one
      // segment left there is nothing to switch, so the tab bar is skipped.
      // v7.1 (§3F): prefer the chosen option's tips so every green verdict shows
      // a correct Tips tab (falls back to job-level tips for back-compat).
      const hasTips = !!((chosenOpt && chosenOpt.tips && chosenOpt.tips.length) ||
                         (job.tips && job.tips.length));
      const secondTab = (verdictKey === 'red' || verdictKey === 'yellow')
        ? { key: 'consequence', label: 'Konsekvenser' }
        : (hasTips ? { key: 'tips', label: 'Tips' } : null);
      const tabDefs = [{ key: 'explain', label: 'Förklaring' }];
      if (secondTab) tabDefs.push(secondTab);

      if (tabDefs.length > 1) {
        const tabs = el('div', { class: 'ampy-bk__tabs' });
        tabDefs.forEach(def => {
          const tabBtn = el('button', {
            class: 'ampy-bk__tab',
            type: 'button',
            'aria-pressed': String(this.activeTab === def.key),
            data: { tab: def.key },
            onclick: () => {
              this.activeTab = def.key;
              tabs.querySelectorAll('.ampy-bk__tab').forEach(b => {
                b.setAttribute('aria-pressed', String(b.dataset.tab === def.key));
              });
              this.renderTabBody();
            }
          }, def.label);
          // Apply verdict accent to the tab indicator via inline CSS variable
          tabBtn.style.setProperty('--verdict-accent',
            verdictKey === 'green' ? 'rgb(15, 110, 86)' :
            verdictKey === 'red' ? 'rgb(122, 22, 35)' :
            'rgb(135, 101, 7)'
          );
          tabs.appendChild(tabBtn);
        });
        block.appendChild(tabs);
      }

      // Tab body — re-rendered on tab change
      this.tabBodyNode = el('div', {
        class: 'ampy-bk__tab-body',
        id: 'ampy-bk-tab-body',
        role: 'region'
      });
      this.currentJob = job;
      this.currentVerdict = verdictKey;
      this.renderTabBody();
      block.appendChild(this.tabBodyNode);

      // CTA-zonen. v7.2 (§7): wrapped in a container with margin-top:auto (CSS)
      // so the CTA stack pins to the card floor on both desktop and mobile;
      // slack sits INSIDE the layout above it, never trailing below.
      const ctaZone = el('div', { class: 'ampy-bk__cta-zone' });
      ctaZone.appendChild(this.renderCta(job, verdictKey));
      block.appendChild(ctaZone);

      return block;
    }

    renderTabBody() {
      if (!this.tabBodyNode) return;
      const job = this.currentJob;
      const verdictKey = this.currentVerdict;
      const v = this.data.verdicts[verdictKey];

      if (this.activeTab === 'explain') {
        this.tabBodyNode.replaceChildren(this.renderExplain(job, verdictKey, v));
      } else if (this.activeTab === 'tips') {
        this.tabBodyNode.replaceChildren(this.renderTips(v));
      } else if (this.activeTab === 'consequence') {
        this.tabBodyNode.replaceChildren(this.renderConsequence(job, v));
      }
    }

    /** Förklaring tab: summary + ✓/✗ contrast rows + caveat (green) */
    renderExplain(job, verdictKey, v) {
      const wrap = document.createDocumentFragment();

      // Summary: option-specifik > job-level
      const optIdx = this.state.answerIndex;
      const opt = (optIdx != null && job.options && job.options[optIdx]) ? job.options[optIdx] : null;
      const summary = (opt && opt.summary) || job.summary || (verdictKey === 'green' ? v.caveat_short : job.why_text);
      // v7.2: the guess-fork bold "reaction" lead-in is gone with the guess jobs.
      wrap.appendChild(el('p', { class: 'ampy-bk__summary' }, summary));

      // ✓ DO-rad
      const doText = (opt && opt.do) || job.do;
      if (doText) {
        wrap.appendChild(el('div', { class: 'ampy-bk__row ampy-bk__row--do' }, [
          el('span', { class: 'ampy-bk__row-icon', html: icon('check'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          el('p', { class: 'ampy-bk__row-text' }, doText)
        ]));
      }

      // ✗ DONT-rad
      const dontText = (opt && opt.dont) || job.dont;
      if (dontText) {
        wrap.appendChild(el('div', { class: 'ampy-bk__row ampy-bk__row--dont' }, [
          el('span', { class: 'ampy-bk__row-icon', html: icon('x'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          el('p', { class: 'ampy-bk__row-text' }, dontText)
        ]));
      }

      // Caveat notice — green only. v7: options flagged _kind:"planning" swap
      // to the planning caveat (a "break the current first" safety caveat is
      // nonsense over a pure planning verdict).
      if (verdictKey === 'green') {
        const caveatText = (opt && opt._kind === 'planning' && v.caveat_planning)
          ? v.caveat_planning
          : v.caveat_short;
        if (caveatText) {
          wrap.appendChild(el('div', { class: 'ampy-bk__caveat', role: 'note' }, [
            el('span', { class: 'ampy-bk__caveat-icon', html: icon('alert'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
            el('p', { class: 'ampy-bk__caveat-text' }, caveatText)
          ]));
        }
      }

      return wrap;
    }

    renderTips(v) {
      // v5.3: jobb-specifika tips med per-tips ✓/✗.
      //   { text, allowed }: allowed=true → ✓ (something you MAY do),
      //   allowed=false → ✗ (the stop condition, the boundary to an electrician).
      // Backwards-compat: plain strings are interpreted as allowed=true.
      const job = this.currentJob;
      // v7.1 (§3F): option tips first, then job-level tips, then verdict tips.
      const opt = (this.state.answerIndex != null && job && job.options && job.options[this.state.answerIndex])
        ? job.options[this.state.answerIndex] : null;
      const raw = (opt && opt.tips && opt.tips.length) ? opt.tips
                : ((job && job.tips && job.tips.length) ? job.tips : (v.tips || []));
      const tips = raw.map(t => (typeof t === 'string') ? { text: t, allowed: true } : t);

      const list = el('ul', { class: 'ampy-bk__tips', role: 'list' });
      tips.forEach(t => {
        const allowed = t.allowed !== false;
        const li = el('li', { class: allowed ? 'ampy-bk__tip ampy-bk__tip--do' : 'ampy-bk__tip ampy-bk__tip--dont' }, [
          el('span', { class: 'ampy-bk__tip-icon', html: icon(allowed ? 'check' : 'x'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          el('span', { class: 'ampy-bk__tip-text' }, t.text)
        ]);
        list.appendChild(li);
      });
      return list;
    }

    renderConsequence(job, v) {
      return el('p', { class: 'ampy-bk__consequence-text' }, job.consequence || v.consequence);
    }

    /* ===================================================================
       CTA — green (calm) / red (solid teal primary)
       =================================================================== */
    renderCta(job, verdictKey) {
      const wrap = document.createDocumentFragment();
      // Per-job override when the auto-shortened label would be illogical
      // (e.g. "Felsökning av el" -> "el"); else strip the leading verb.
      const shortJob = job.cta_short || this._shortLabel(job.label);
      // One standardized advice CTA label everywhere (data file = source of truth).
      const adviceLabel = this.data.meta.cta_advice_label || 'Boka kostnadsfri rådgivning';

      // The advice CTA opens the in-tool lead form (no outbound jump to /offert/).
      const adviceBtn = (cls) => el('button', {
        class: cls, type: 'button',
        onclick: () => { track('cta_click', { job_id: job.id, verdict: verdictKey, cta: 'advice' }); this.openLead(); }
      }, [
        adviceLabel,
        el('span', { html: icon('arrowRight'), 'aria-hidden': 'true', style: 'display:inline-flex' })
      ]);
      const readMore = (cls) => el('a', {
        class: cls, href: job.service_page_url,
        onclick: () => track('cta_click', { job_id: job.id, verdict: verdictKey, cta: 'read_more' })
      }, [
        `Läs mer om ${shortJob}`,
        el('span', { html: icon('arrowRight'), 'aria-hidden': 'true', style: 'display:inline-flex' })
      ]);

      // v7 (F3): ONE CTA hierarchy for every verdict — solid teal advice CTA
      // on top + outline "Läs mer om ..." below. GREEN gets a framing line
      // above the pair (a safety-net double-check, never contradicting the
      // verdict); RED gets no extra line (the law box already does the push).
      if (verdictKey === 'green' && this.data.meta.cta_green_note) {
        wrap.appendChild(el('p', { class: 'ampy-bk__cta-note' }, this.data.meta.cta_green_note));
      }
      wrap.appendChild(adviceBtn('ampy-bk__cta-primary ampy-bk__cta-primary--solid'));
      wrap.appendChild(readMore('ampy-bk__cta-secondary'));

      return wrap;
    }

    /* ===================================================================
       LEAD FORM — in-tool capture (no outbound jump). Posts to the REST
       endpoint when available (WordPress); in the static prototype it
       gracefully simulates success. Copy lives in data.meta.lead_form.
       =================================================================== */
    renderLeadBlock(job, verdictKey) {
      const f = (this.data.meta && this.data.meta.lead_form) || {};
      const block = el('div', { class: 'ampy-bk__block ampy-bk__lead', role: 'region', 'aria-labelledby': 'ampy-bk-lead-h' });

      // Back to the verdict
      block.appendChild(el('button', {
        class: 'ampy-bk__lead-back', type: 'button', onclick: () => this.closeLead()
      }, [
        el('span', { html: icon('arrowLeft'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
        f.back || 'Tillbaka till beskedet'
      ]));

      block.appendChild(el('h2', { class: 'ampy-bk__lead-title', id: 'ampy-bk-lead-h', 'data-focus-target': '' },
        f.title || 'Boka kostnadsfri rådgivning'));
      block.appendChild(el('p', { class: 'ampy-bk__lead-intro' },
        f.intro || 'Ampys behöriga elektriker hör av sig på telefon för din rådgivning!'));

      const form = el('form', { class: 'ampy-bk__lead-form', novalidate: 'true' });
      const field = (name, label, type, required, inputmode, autocomplete) => {
        const id = 'ampy-bk-lf-' + name;
        const wrapF = el('div', { class: 'ampy-bk__lead-field' });
        // All four fields are required now; each label carries a small black asterisk.
        wrapF.appendChild(el('label', { class: 'ampy-bk__lead-label', for: id }, [
          label,
          required ? el('span', { class: 'ampy-bk__req', 'aria-hidden': 'true' }, '*') : null
        ]));
        // Field-specific autocomplete tokens drive the one-tap contact autofill
        // chip on iOS/Android and satisfy WCAG 1.3.5 (Identify Input Purpose).
        const input = el('input', Object.assign({
          class: 'ampy-bk__lead-input', id: id, name: name, type: type, autocomplete: autocomplete || 'on'
        }, required ? { required: 'true' } : {}, inputmode ? { inputmode: inputmode } : {}));
        wrapF.appendChild(input);
        return { wrapF, input };
      };
      // Order: Namn -> Telefon -> Postnummer -> E-post (telefon is the money field).
      const namn  = field('namn', 'Namn', 'text', true, null, 'name');
      const tel   = field('telefon', 'Telefon', 'tel', true, 'tel', 'tel');
      const post  = field('postnummer', 'Postnummer', 'text', true, 'numeric', 'postal-code');
      const epost = field('epost', 'E-post', 'email', true, 'email', 'email');

      const grid = el('div', { class: 'ampy-bk__lead-grid' });
      grid.appendChild(namn.wrapF); grid.appendChild(tel.wrapF);
      grid.appendChild(post.wrapF); grid.appendChild(epost.wrapF);
      form.appendChild(grid);

      // honeypot (hidden from humans)
      const honey = el('input', { type: 'text', name: 'webbplats', class: 'ampy-bk__lead-hp', tabindex: '-1', autocomplete: 'off', 'aria-hidden': 'true' });
      form.appendChild(honey);

      const errorId = 'ampy-bk-lf-error';
      const errorBox = el('p', { class: 'ampy-bk__lead-error', id: errorId, role: 'alert', hidden: true });
      form.appendChild(errorBox);

      const submit = el('button', { class: 'ampy-bk__cta-primary ampy-bk__cta-primary--solid ampy-bk__lead-submit', type: 'submit' },
        f.submit || 'Boka kostnadsfri rådgivning');
      form.appendChild(submit);

      // Consent is implicit by submitting; state it in fine print under the button,
      // with the word "integritetspolicy" as an inline link to the privacy page.
      const fineText = f.fineprint || 'Genom att trycka på "Boka kostnadsfri rådgivning" samtycker jag till att Ampy behandlar mina personuppgifter enligt vår integritetspolicy.';
      const fpParts = fineText.split('integritetspolicy');
      form.appendChild(el('p', { class: 'ampy-bk__lead-fineprint' }, [
        fpParts[0],
        el('a', { href: this.data.meta.privacy_url || 'https://ampy.se/integritetspolicy/', target: '_blank', rel: 'noopener noreferrer' },
          'integritetspolicy'),
        fpParts.slice(1).join('integritetspolicy')
      ]));

      // Clear the invalid state on a field as soon as the user edits it.
      [namn, tel, post, epost].forEach(({ input }) => {
        input.addEventListener('input', () => { input.removeAttribute('aria-invalid'); input.removeAttribute('aria-describedby'); });
      });

      form.addEventListener('submit', (e) => {
        e.preventDefault();
        errorBox.hidden = true;
        if (honey.value) return; // bot
        const checks = [
          [namn.input, !!namn.input.value.trim()],
          [tel.input, !!tel.input.value.trim()],
          [post.input, !!post.input.value.trim()],
          [epost.input, !!epost.input.value.trim()]
        ];
        checks.forEach(([el2]) => el2.removeAttribute('aria-invalid'));
        const failed = checks.filter(([, ok]) => !ok).map(([el2]) => el2);
        if (failed.length) {
          failed.forEach(el2 => el2.setAttribute('aria-invalid', 'true'));
          failed[0].setAttribute('aria-describedby', errorId);
          errorBox.textContent = f.error_required || 'Fyll i alla fält så ringer vi upp dig.';
          errorBox.hidden = false;
          try { failed[0].focus(); } catch (e2) {}
          return;
        }
        submit.disabled = true;
        submit.textContent = f.submitting || 'Skickar…';
        this.submitLead(job, verdictKey, {
          namn: namn.input.value.trim(),
          kontakt: epost.input.value.trim(),
          telefon: tel.input.value.trim(),
          postnummer: post.input.value.trim(),
          samtycke: true,
          webbplats: honey.value
        }).then(() => {
          track('lead_submitted', { job_id: job.id, verdict: verdictKey });
          // Personalise the title with the submitter's first name (first whitespace
          // token). el() uses textContent, so the name is inherently HTML-escaped.
          const rawName = namn.input.value.trim();
          const firstName = rawName ? rawName.split(/\s+/)[0] : '';
          const successTitle = firstName
            ? (f.success_title || 'Tack {namn}! Vi hör av oss.').replace('{namn}', firstName)
            : (f.success_title_noname || 'Tack! Vi hör av oss.');
          block.replaceChildren(
            el('div', { class: 'ampy-bk__lead-success', 'data-focus-target': '' }, [
              el('span', { class: 'ampy-bk__lead-success-icon', html: icon('check'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
              el('h2', {}, successTitle),
              el('p', {}, f.success_body || 'En behörig elektriker ringer upp dig på telefon, oftast inom en arbetsdag. Passa på att kolla ett annat eljobb medan du väntar.'),
              // Re-engagement loop: route back to slide 1 (the room grid),
              // not the verdict and not a stale room list.
              el('button', { class: 'ampy-bk__lead-back', type: 'button', style: 'margin:0', onclick: () => {
                this.activeRoom = null;
                this.heroExpanded = false;
                this.navigate({ jobId: null, answerIndex: null });
              } }, [
                el('span', { html: icon('arrowLeft'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
                f.success_back || 'Kolla ett annat eljobb'
              ])
            ])
          );
          // v7 scroll contract also applies to the success swap.
          const t = block.querySelector('[data-focus-target]');
          if (t) {
            t.setAttribute('tabindex', '-1');
            try { t.focus({ preventScroll: true }); } catch (e2) { try { t.focus(); } catch (e3) {} }
          }
          this._syncScroll();
        }).catch(() => {
          submit.disabled = false;
          submit.textContent = f.submit || 'Boka kostnadsfri rådgivning';
          errorBox.textContent = f.error_send || 'Något gick fel. Ring oss på 010-265 79 79 så hjälper vi dig.';
          errorBox.hidden = false;
        });
      });

      block.appendChild(form);
      return block;
    }

    submitLead(job, verdictKey, fields) {
      const cfg = (typeof window !== 'undefined' && window.AmpyBK) ? window.AmpyBK : null;
      const payload = Object.assign({ job_id: job.id, verdict: verdictKey, meddelande: '' }, fields);
      // No WP endpoint (static prototype) → simulate a successful submit.
      if (!cfg || !cfg.restUrl) {
        return new Promise((resolve2) => setTimeout(resolve2, 600));
      }
      // Fetch a FRESH nonce right before POST. The nonce baked into the page can be
      // stale if the page is served from full-page cache; this uncached GET avoids
      // a 403 on submit. Falls back to the localized nonce if the GET fails.
      const nonceUrl = cfg.restUrl.replace(/\/lead\/?$/, '/nonce');
      const freshNonce = fetch(nonceUrl, { headers: { 'X-WP-Nonce': cfg.restNonce || '' } })
        .then(r => (r.ok ? r.json() : null))
        .then(j => (j && j.nonce) || cfg.restNonce || '')
        .catch(() => cfg.restNonce || '');
      return freshNonce.then(nonce => fetch(cfg.restUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
        body: JSON.stringify(payload)
      })).then(r => { if (!r.ok) throw new Error('bad status'); return r.json(); });
    }

    renderShareButton(job, verdictKey) {
      const status = el('span', { class: 'ampy-bk__share-status', role: 'status', 'aria-live': 'polite' });
      const shareUrl = window.location.origin + window.location.pathname +
        '?jobb=' + encodeURIComponent(job.id) +
        (this.state.answerIndex != null ? '&svar=' + this.state.answerIndex : '');
      const shareTitle = `Elkollen: ${job.label}`;
      const shareText = `${job.label}. ${this.data.verdicts[verdictKey].label}.`;

      const flash = (msg, color = '--text-success') => {
        status.textContent = msg;
        status.style.color = `var(${color})`;
        status.dataset.visible = 'true';
        clearTimeout(this._flashT);
        this._flashT = setTimeout(() => { status.dataset.visible = 'false'; }, 2400);
      };

      // Anchor that holds the button + popover (relative positioning context).
      const anchor = el('span', { class: 'ampy-bk__share-anchor' });

      // The popover menu (desktop / no native share). Built lazily on first open.
      const menu = el('div', { class: 'ampy-bk__share-menu', role: 'menu', 'aria-label': 'Dela resultatet', hidden: true });
      const enc = encodeURIComponent;
      const shareTargets = [
        { key: 'facebook', label: 'Facebook', icon: 'facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${enc(shareUrl)}` },
        { key: 'x',        label: 'X',        icon: 'xtwitter', href: `https://twitter.com/intent/tweet?url=${enc(shareUrl)}&text=${enc(shareText)}` },
        { key: 'reddit',   label: 'Reddit',   icon: 'reddit',   href: `https://www.reddit.com/submit?url=${enc(shareUrl)}&title=${enc(shareText)}` },
        { key: 'mail',     label: 'E-post',   icon: 'mail',     href: `mailto:?subject=${enc(shareTitle)}&body=${enc(shareText + ' ' + shareUrl)}` }
      ];
      shareTargets.forEach(t => {
        menu.appendChild(el('a', {
          class: 'ampy-bk__share-item',
          role: 'menuitem',
          href: t.href,
          target: '_blank',
          rel: 'noopener noreferrer',
          onclick: () => { track('share_completed', { job_id: job.id, verdict: verdictKey, channel: t.key }); closeMenu(); }
        }, [
          el('span', { class: 'ampy-bk__share-item-icon', html: icon(t.icon), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          el('span', {}, t.label)
        ]));
      });
      // Copy link row
      menu.appendChild(el('button', {
        class: 'ampy-bk__share-item',
        type: 'button',
        role: 'menuitem',
        onclick: async () => {
          try { await navigator.clipboard.writeText(shareUrl); flash('Länk kopierad.'); track('share_completed', { job_id: job.id, verdict: verdictKey, channel: 'copy' }); }
          catch (e) { flash('Kopiera länken manuellt.', '--state-warning'); }
          closeMenu();
        }
      }, [
        el('span', { class: 'ampy-bk__share-item-icon', html: icon('link'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
        el('span', {}, 'Kopiera länk')
      ]));

      const closeMenu = () => {
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
        document.removeEventListener('click', onDocClick, true);
        document.removeEventListener('keydown', onKey, true);
      };
      const openMenu = () => {
        menu.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        document.addEventListener('click', onDocClick, true);
        document.addEventListener('keydown', onKey, true);
      };
      const onDocClick = (e) => { if (!anchor.contains(e.target)) closeMenu(); };
      const onKey = (e) => { if (e.key === 'Escape') { closeMenu(); btn.focus(); } };

      const btn = el('button', {
        class: 'ampy-bk__share',
        type: 'button',
        'aria-label': 'Dela resultatet',
        'aria-haspopup': 'menu',
        'aria-expanded': 'false',
        title: 'Dela resultatet',
        onclick: async () => {
          track('share_opened', { job_id: job.id, verdict: verdictKey });
          // Touch-enheter (mobil/platta): native share sheet — ger Instagram,
          // Messages, etc. Desktop ALWAYS gets our popover (Web Share exists on
          // desktop Chrome/Edge/Safari but gives inconsistent UX there). Detect
          // via pekare: 'coarse' = touch.
          const isTouch = typeof window.matchMedia === 'function' &&
                          window.matchMedia('(pointer: coarse)').matches;
          if (navigator.share && isTouch) {
            let file = null;
            try { file = await this.generateShareImage(job, verdictKey, shareUrl); } catch (e) {}
            try {
              const payload = { title: shareTitle, text: shareText, url: shareUrl };
              if (file && navigator.canShare && navigator.canShare({ files: [file] })) payload.files = [file];
              await navigator.share(payload);
              track('share_completed', { job_id: job.id, verdict: verdictKey, channel: 'native' });
              return;
            } catch (e) { /* the user cancelled or the share failed → fall back to the menu */ }
          }
          // Desktop (eller ingen native share): toggla popover med sociala val.
          if (menu.hidden) openMenu(); else closeMenu();
        }
      }, el('span', { html: icon('share'), 'aria-hidden': 'true', style: 'display:inline-flex' }));

      anchor.appendChild(btn);
      anchor.appendChild(menu);
      anchor.appendChild(status);
      return anchor;
    }

    /* ===================================================================
       ENTRY MODE — approved v4 structure, lightly trimmed to v5 tokens
       =================================================================== */
    renderEntryBlock() {
      if (this.heroMode) return this.renderHeroEntry();

      const block = el('div', { class: 'ampy-bk__block', role: 'region', 'aria-label': 'Elkollen' });

      // v5.2: the main heading and "Testa din kunskap" are removed from the block.
      // The H2 "Koppla elen — din guide till vad du får och inte får göra" lives
      // as the page heading ABOVE the shortcode embed (in Bricks/preview).
      // The block now starts directly with the search field's label — that is the affordance.

      const searchId = 'ampy-bk-search';
      const searchInput = el('input', {
        type: 'search',
        id: searchId,
        class: 'ampy-bk__search-input',
        placeholder: 'T.ex. vägguttag, badrum, spis…',
        autocomplete: 'off',
        value: this.searchQuery
      });
      searchInput.addEventListener('input', (e) => {
        this.searchQuery = e.target.value;
        if (this.searchQuery) {
          this.activeRoom = null;
          rooms.querySelectorAll('.ampy-bk__room').forEach(b => b.setAttribute('aria-selected', 'false'));
        }
        this.renderSwap();
      });
      const search = el('div', { class: 'ampy-bk__search' }, [
        el('label', { class: 'ampy-bk__search-label', for: searchId }, 'Sök eljobb'),
        el('div', { class: 'ampy-bk__search-field' }, [
          el('span', { class: 'ampy-bk__search-icon', html: icon('search'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          searchInput
        ])
      ]);
      block.appendChild(search);

      const rooms = el('ul', { class: 'ampy-bk__rooms', role: 'tablist' });
      (this.data.rooms || []).forEach(room => {
        const li = el('li');
        const btn = el('button', {
          class: 'ampy-bk__room',
          type: 'button',
          role: 'tab',
          'aria-selected': String(this.activeRoom === room.id),
          'aria-label': `Visa eljobb i ${room.label}`,
          onclick: () => {
            this.activeRoom = (this.activeRoom === room.id) ? null : room.id;
            this.searchQuery = '';
            searchInput.value = '';
            rooms.querySelectorAll('.ampy-bk__room').forEach(b => {
              b.setAttribute('aria-selected', String(b.dataset.room === this.activeRoom));
            });
            this.renderSwap();
          },
          data: { room: room.id }
        }, [
          el('span', { html: icon(room.icon), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          el('span', {}, room.label)
        ]);
        li.appendChild(btn);
        rooms.appendChild(li);
      });
      block.appendChild(rooms);

      this.swapNode = el('div', { class: 'ampy-bk__swap', role: 'region', 'aria-live': 'polite' });
      this.renderSwap();
      block.appendChild(this.swapNode);

      block.appendChild(el('p', { class: 'ampy-bk__source-line' },
        this.data.meta.source_line ||
        'Källa: Elsäkerhetsverket & Elsäkerhetslagen (2016:732). Vägledning, inte juridisk rådgivning.'
      ));

      return block;
    }

    /* ===================================================================
       HERO ENTRY (layout="hero") — v7, slides 1 + 2 of the 4-slide funnel.
       Slide 1: one heading + six IDENTICAL room tiles (no eyebrow, no search).
       Slide 2: one NEUTRAL job list per room in an in-panel drawer — no
       verdict grouping, no colour dots, nothing leaks before the choice step.
       Drawer swaps are in place (no full re-render); the S1<->S2 transitions
       run the same focus + scroll contract as every other slide change.
       =================================================================== */
    renderHeroEntry() {
      const data = this.data;
      const entry = (data.meta && data.meta.entry) || {};
      const block = el('div', { class: 'ampy-bk__block', role: 'region', 'aria-label': 'Elkollen' });

      // --- 1. Card heading (the card's only heading; page H1 lives in the hero column) ---
      const heading = el('h2', { class: 'ampy-bk__entry-title', 'data-focus-target': '', tabindex: '-1' },
        entry.prompt || 'Var i hemmet gäller det?');
      block.appendChild(heading);

      // --- 2. Room grid: 5 rooms + "Alla eljobb", all six tiles identical ---
      const grid = el('ul', { class: 'ampy-bk__roomgrid', role: 'list' });
      (data.rooms || []).forEach(room => {
        const li = el('li');
        li.appendChild(el('button', {
          class: 'ampy-bk__roomtile', type: 'button',
          'aria-label': `Visa eljobb i ${room.label}`, data: { room: room.id },
          onclick: () => {
            this.activeRoom = room.id;
            this.heroExpanded = false;
            track('room_selected', { room_id: room.id });
            updateDrawer(true);
          }
        }, [
          el('span', { class: 'ampy-bk__roomtile-chip', html: icon(room.icon), 'aria-hidden': 'true' }),
          el('span', { class: 'ampy-bk__roomtile-body' }, [
            el('span', { class: 'ampy-bk__roomtile-label' }, room.label),
            room.subline ? el('span', { class: 'ampy-bk__roomtile-sub' }, room.subline) : null
          ])
        ]));
        grid.appendChild(li);
      });
      // "Alla eljobb" tile — same anatomy and style as the five rooms.
      const allLi = el('li');
      allLi.appendChild(el('button', {
        class: 'ampy-bk__roomtile', type: 'button',
        'aria-label': 'Visa alla eljobb',
        onclick: () => {
          this.heroExpanded = true; this.activeRoom = null;
          track('see_all');
          updateDrawer(true);
        }
      }, [
        el('span', { class: 'ampy-bk__roomtile-chip', html: icon('grid'), 'aria-hidden': 'true' }),
        el('span', { class: 'ampy-bk__roomtile-body' }, [
          el('span', { class: 'ampy-bk__roomtile-label' }, entry.all_label || 'Alla eljobb'),
          el('span', { class: 'ampy-bk__roomtile-sub' },
            (entry.all_subline || 'Hela listan, A till Ö').replace('{count}', data.jobs.length))
        ])
      ]));
      grid.appendChild(allLi);
      block.appendChild(grid);

      // --- 3. Slide-2 drawer (hidden until a tile is tapped) ---
      const drawer = el('div', { class: 'ampy-bk__drawer', role: 'region', 'aria-live': 'polite', hidden: true });
      this.swapNode = drawer;
      block.appendChild(drawer);

      // --- 4. Source line, slides 1-2 only (pinned to the card floor) ---
      block.appendChild(el('p', { class: 'ampy-bk__source-line' },
        data.meta.source_line ||
        'Källa: Elsäkerhetsverket & Elsäkerhetslagen (2016:732). Vägledning, inte juridisk rådgivning.'
      ));

      const resetToRooms = () => {
        this.activeRoom = null; this.heroExpanded = false;
        updateDrawer(true);
      };

      // userTriggered: run the focus + scroll contract and the list_view event
      // only on real taps, never on the re-render restore (popstate / back).
      const updateDrawer = (userTriggered) => {
        let jobs = null, titleText = '', source = null;
        if (this.activeRoom) {
          const r = (data.rooms || []).find(x => x.id === this.activeRoom);
          jobs = r ? r.jobs.map(id => this.jobsById[id]).filter(Boolean) : [];
          titleText = r ? r.label : '';
          source = this.activeRoom;
        } else if (this.heroExpanded) {
          // "Alla eljobb": Swedish alphabetical (å/ä/ö collate correctly).
          jobs = data.jobs.slice().sort((a, b) => a.label.localeCompare(b.label, 'sv'));
          titleText = entry.drawer_all_title || 'Alla eljobb';
          source = 'all';
        }

        if (jobs === null) {
          // Slide 1
          drawer.hidden = true; drawer.replaceChildren();
          grid.hidden = false; heading.hidden = false;
          if (userTriggered) {
            try { heading.focus({ preventScroll: true }); } catch (e) { try { heading.focus(); } catch (e2) {} }
            this._syncScroll();
          }
          return;
        }
        // Slide 2 replaces the grid + heading.
        grid.hidden = true; heading.hidden = true;
        drawer.hidden = false;

        const back = el('button', {
          class: 'ampy-bk__drawer-back', type: 'button',
          'aria-label': 'Tillbaka till rummen', onclick: resetToRooms
        }, [
          el('span', { html: icon('arrowLeft'), 'aria-hidden': 'true', style: 'display:inline-flex' }),
          entry.drawer_back || 'Tillbaka'
        ]);
        const drawerTitle = el('p', { class: 'ampy-bk__drawer-title', 'data-focus-target': '', tabindex: '-1' }, titleText);
        const subtitle = el('p', { class: 'ampy-bk__list-subtitle' },
          entry.list_subtitle || 'Välj jobbet, så kollar vi vad som gäller i just ditt fall.');
        drawer.replaceChildren(back, drawerTitle, subtitle, this.renderJobList(jobs));
        drawer.scrollTop = 0;

        if (userTriggered) {
          track('list_view', { source: source });
          try { drawerTitle.focus({ preventScroll: true }); } catch (e) { try { drawerTitle.focus(); } catch (e2) {} }
          this._syncScroll();
        }
      };

      updateDrawer(false);
      return block;
    }

    renderSwap() {
      if (!this.swapNode) return;
      let jobs;
      if (this.searchQuery && this.searchQuery.trim()) {
        const q = this.searchQuery.trim().toLowerCase();
        jobs = this.data.jobs.filter(j => j.label.toLowerCase().includes(q) || j.id.includes(q));
      } else if (this.activeRoom) {
        const room = (this.data.rooms || []).find(r => r.id === this.activeRoom);
        jobs = room ? room.jobs.map(id => this.jobsById[id]).filter(Boolean) : [];
      } else {
        const picks = (this.data.meta.quick_picks || []).map(id => this.jobsById[id]).filter(Boolean);
        jobs = picks.length ? picks : this.data.jobs.slice(0, 8);
      }

      if (!jobs.length) {
        this.swapNode.replaceChildren(el('p', { class: 'ampy-bk__empty' },
          'Inget jobb matchar. Prova ett annat ord eller välj ett rum.'
        ));
        return;
      }

      const topHint = (this.searchQuery && this.searchQuery.trim())
        ? null : (this.activeRoom ? null : 'Vanliga eljobb');
      const frag = document.createDocumentFragment();
      if (topHint) frag.appendChild(el('p', { class: 'ampy-bk__joblist-hint' }, topHint));
      frag.appendChild(this.renderJobList(jobs));
      this.swapNode.replaceChildren(frag);
    }

    /* v7 (F1): ONE flat, neutral job list. No group headers, no colour dots,
       no verdict word anywhere — a row must be unreadable as an answer. */
    renderJobList(jobs) {
      const list = el('ul', { class: 'ampy-bk__joblist', role: 'list' });
      jobs.forEach(j => list.appendChild(this.renderJobRow(j)));
      return list;
    }

    renderJobRow(job) {
      const li = el('li');
      const btn = el('button', {
        class: 'ampy-bk__job-row',
        type: 'button',
        'aria-label': `Välj jobb: ${job.label}`,
        onclick: () => this.navigate({ jobId: job.id, answerIndex: null })
      }, [
        // Teal marks interactivity only; every row gets it, so it carries zero
        // verdict signal.
        el('span', { class: 'ampy-bk__job-row-icon', html: icon(job.icon === 'search' ? 'felsok' : job.icon), 'aria-hidden': 'true', style: 'display:inline-flex' }),
        el('span', { class: 'ampy-bk__job-row-label' }, job.label),
        // Chevron, not the CTA arrow: this is drill-in navigation, not an action.
        el('span', { class: 'ampy-bk__job-row-arrow', html: icon('chevronRight'), 'aria-hidden': 'true', style: 'display:inline-flex' })
      ]);
      li.appendChild(btn);
      return li;
    }

    /** Strip leading verb + trailing parens from label. */
    _shortLabel(label) {
      return String(label || '')
        .replace(/\s*\([^)]*\)\s*$/, '')
        .replace(/^(Installera|Byta|Ansluta|Dra|Skarva eller förlänga|Arbete i|Felsökning av|El i)\s+/i, '')
        .toLowerCase();
    }

    /** Tighten a citation for cite chip display. */
    _shortCitation(c) {
      if (!c) return '';
      const parts = String(c).split(';').map(s => s.trim());
      return parts[parts.length - 1] || parts[0];
    }

    /**
     * v5.1 B1: resolve the per-verdict source for the cite chip.
     * Priority: option.source → job.source → verdicts[verdictKey].source → fallback.
     * For conditional jobs the option's verdict color is honoured — the green branch
     * gets the Elsäkerhetsverket source, the red branch gets the 27 § source.
     */
    _resolveSource(job, verdictKey) {
      const optIdx = this.state.answerIndex;
      const opt = (optIdx != null && job.options && job.options[optIdx]) ? job.options[optIdx] : null;
      const v = this.data.verdicts[verdictKey] || {};
      const fallback = { text: this._shortCitation(job.rule_citation) || (v && v.label) || 'Källa', url: this.data.meta.koppla_sakert_url };
      return (opt && opt.source) || job.source || v.source || fallback;
    }

    /* ===================================================================
       Canvas-based shareable verdict card (PNG)
       =================================================================== */
    async generateShareImage(job, verdictKey, urlStr) {
      const W = 1200, H = 630;
      const canvas = document.createElement('canvas');
      canvas.width = W; canvas.height = H;
      const ctx = canvas.getContext('2d');

      const palette = {
        bg: '#090b32',
        green: { bg: '#5DCAA5', fg: '#04342C' },
        yellow: { bg: '#F5C97A', fg: '#3D2A00' },
        red: { bg: '#F09595', fg: '#501313' }
      };
      const accent = palette[verdictKey] || palette.green;

      ctx.fillStyle = palette.bg; ctx.fillRect(0, 0, W, H);
      const grad = ctx.createRadialGradient(W-200, 100, 0, W-200, 100, 600);
      grad.addColorStop(0, 'rgba(0,169,145,0.22)');
      grad.addColorStop(1, 'rgba(0,169,145,0)');
      ctx.fillStyle = grad; ctx.fillRect(0, 0, W, H);

      ctx.fillStyle = 'rgba(255,255,255,0.7)';
      ctx.font = '500 22px "Outfit", system-ui, sans-serif';
      ctx.fillText('AMPY · Elkollen', 80, 90);

      ctx.fillStyle = 'rgba(255,255,255,0.78)';
      ctx.font = '600 26px "Outfit", system-ui, sans-serif';
      ctx.fillText(job.label, 80, 175);

      const v = this.data.verdicts[verdictKey];
      ctx.font = '500 44px "Outfit", system-ui, sans-serif';
      const labelW = ctx.measureText(v.label).width;
      const padX = 32, badgeH = 84, iconBox = 50;
      const badgeW = Math.min(W - 160, iconBox + 16 + labelW + padX * 2);
      const badgeX = 80, badgeY = 230;

      ctx.fillStyle = accent.bg;
      this._roundRect(ctx, badgeX, badgeY, badgeW, badgeH, 42); ctx.fill();
      ctx.fillStyle = accent.fg;
      ctx.font = '700 38px "Outfit", system-ui, sans-serif';
      ctx.textBaseline = 'middle';
      const glyph = verdictKey === 'green' ? '✓' : (verdictKey === 'red' ? '✕' : '!');
      ctx.fillText(glyph, badgeX + padX, badgeY + badgeH/2 + 2);
      ctx.font = '500 36px "Outfit", system-ui, sans-serif';
      ctx.fillText(v.label, badgeX + padX + iconBox, badgeY + badgeH/2 + 2);
      ctx.textBaseline = 'alphabetic';

      const optSummary = (this.state.answerIndex != null && job.options && job.options[this.state.answerIndex])
        ? job.options[this.state.answerIndex].summary : null;
      const summary = optSummary || job.summary || (verdictKey === 'green' ? v.caveat_short : job.why_text);

      ctx.fillStyle = 'rgba(255,255,255,0.96)';
      ctx.font = '500 30px "Outfit", system-ui, sans-serif';
      this._wrap(ctx, summary, 80, 390, W - 160, 38);

      ctx.fillStyle = 'rgba(255,255,255,0.7)';
      ctx.font = '500 22px "Outfit", system-ui, sans-serif';
      ctx.fillText(this._resolveSource(job, verdictKey).text, 80, H - 110);

      ctx.fillStyle = 'rgba(255,255,255,0.55)';
      ctx.font = '500 20px "Outfit", system-ui, sans-serif';
      ctx.fillText(urlStr.replace(/^https?:\/\//, ''), 80, H - 70);

      const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png', 0.92));
      if (!blob) return null;
      const filename = `elkollen-${job.id}${this.state.answerIndex!=null?'-svar'+this.state.answerIndex:''}.png`;
      return new File([blob], filename, { type: 'image/png' });
    }

    _roundRect(ctx, x, y, w, h, r) {
      ctx.beginPath();
      ctx.moveTo(x + r, y);
      ctx.lineTo(x + w - r, y); ctx.quadraticCurveTo(x + w, y, x + w, y + r);
      ctx.lineTo(x + w, y + h - r); ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
      ctx.lineTo(x + r, y + h); ctx.quadraticCurveTo(x, y + h, x, y + h - r);
      ctx.lineTo(x, y + r); ctx.quadraticCurveTo(x, y, x + r, y);
      ctx.closePath();
    }

    _wrap(ctx, text, x, y, maxW, lineH) {
      const words = String(text || '').split(/\s+/);
      let line = '', yy = y;
      for (let i = 0; i < words.length; i++) {
        const test = line ? line + ' ' + words[i] : words[i];
        if (ctx.measureText(test).width > maxW && line) {
          ctx.fillText(line, x, yy); line = words[i]; yy += lineH;
        } else { line = test; }
      }
      if (line) ctx.fillText(line, x, yy);
    }

    downloadFile(file) {
      const url = URL.createObjectURL(file);
      const a = document.createElement('a');
      a.href = url; a.download = file.name;
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 500);
    }
  }

  /* ---------- Boot ------------------------------------------------------- */
  function boot(mount) {
    if (!mount || mount.dataset.booted === 'true') return;

    const injected = (window.AmpyBK && window.AmpyBK.data) || null;
    if (injected) {
      maybeApplyPreselect(mount);
      new ElkollenApp(mount, injected).render();
      return;
    }

    const dataUrl = mount.dataset.dataUrl || '../data/behorighetskollen-data.json';
    fetch(dataUrl, { credentials: 'same-origin' })
      .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(data => {
        maybeApplyPreselect(mount);
        new ElkollenApp(mount, data).render();
      })
      .catch(err => {
        console.error('[Elkollen] Could not load data:', err);
        mount.innerHTML = '<div class="ampy-bk__block"><p class="ampy-bk__loading">Kunde inte ladda eljobben just nu.</p></div>';
      });
  }

  function maybeApplyPreselect(mount) {
    const preselect = mount.dataset.preselectJob;
    if (!preselect) return;
    const p = new URLSearchParams(window.location.search);
    if (!p.get('jobb')) {
      p.set('jobb', preselect);
      const qs = p.toString();
      const url = window.location.pathname + (qs ? '?' + qs : '') + window.location.hash;
      history.replaceState({}, '', url);
    }
  }

  window.AmpyBK = window.AmpyBK || {};
  window.AmpyBK.resolve = resolve;
  window.AmpyBK.boot = boot;

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ampy-bk').forEach(boot);
  });
})();
