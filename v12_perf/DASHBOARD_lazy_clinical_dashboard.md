# v12_perf — Dashboard: lazy-load the Unified Clinical Dashboard

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes · **Ortho:** pending

## Context
The Unified Clinical Dashboard card (section ~#8, well below the fold) calls
`/api/clinical-dashboard/snapshot?patient_id=…` — a heavy per-patient aggregation (IOP / VA / cataract / dry-eye).
It was fetched eagerly on `DOMContentLoaded` for any user with a `lastViewedPatientId` (i.e. most active doctors),
putting that heavy query on the initial-load critical path even though the card is far below the fold.

## Fix
Wrapped the eager call in an `IntersectionObserver` (the same pattern already used for the Missed-Appointments
card — `lazyMissedAppointments`), so the snapshot is fetched only when the card scrolls within ~400px of the
viewport:
```js
(function lazyUnifiedClinicalDashboard() {
    const card = document.querySelector('[data-card-id="unified-clinical-dashboard"]');
    let loaded = false;
    const trigger = () => { if (loaded) return; loaded = true; loadUnifiedClinicalDashboard(); };
    if (!card || typeof IntersectionObserver === 'undefined') { trigger(); return; }
    const io = new IntersectionObserver((e) => { for (const x of e) if (x.isIntersecting) { trigger(); io.disconnect(); break; } }, { rootMargin: '400px 0px' });
    io.observe(card);
})();
```
`loadUnifiedClinicalDashboard()` is unchanged (it still no-ops when there's no `lastViewedPatientId`).

## Verified (local, CDP, 1280×800)
With `lastViewedPatientId` set: `/api/clinical-dashboard/snapshot` is **not** requested on load (deferred), and
fires **once** when the card is scrolled into view. 0 console errors.

## Why only this widget
A pass over the dashboard's ~13 on-load API calls showed most are above/near the fold (hero, stats+weather,
upcoming appointments, the at-a-glance mini-widgets) and need eager loading, or share a fetch with an above-the-fold
widget (the charts' `/api/dashboard-charts` is also consumed by the above-the-fold stat-card sparklines). The two
genuinely heavy, below-the-fold, own-fetch widgets are Missed Appointments (already lazy) and this one — now both lazy.

## Also done — deferred `dashboard.js` (~205KB)
Added `defer` to the `dashboard.js` `<script>` so the server-rendered cards paint before the 205KB bundle loads.
The order-flip with `main.js` (which now runs first) and the `readyState`-gated branches turned out benign — all of
dashboard.js's work runs on DOMContentLoaded (deferred scripts execute before DCL), and no inline script in
dashboard.php calls a dashboard.js function. **CDP-verified comprehensively** (1280×900): dashboard.js executes
(its globals defined), Chart.js charts render, status donut + weather + the 6 cards + reorder handles all present,
the cache-first **card order still applies** correctly, lazy clinical/missed still fire, **0 console errors**.

## Not done (for a future focused task)
- **Split** dashboard.js further (move below-the-fold widget code into its own deferred file, eye-tools style) — a
  larger refactor, only worth it if the single deferred bundle proves too heavy.
- **Batch the snapshot/summary endpoints** server-side (fewer Cloudflare round-trips) — a backend change.

## Apply on ortho
Wrap ortho's eager `loadUnifiedClinicalDashboard()` (or equivalent heavy below-the-fold loader) in the same
IntersectionObserver pattern, keyed on its card's `data-card-id`.
