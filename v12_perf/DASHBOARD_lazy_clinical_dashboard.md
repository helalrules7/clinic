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

## Not done (deliberate, for a future focused task)
- **Defer/split `dashboard.js` (~205KB, still non-deferred).** It sits *before* `main.js` in the document, so simply
  adding `defer` flips their execution order and changes its `readyState`-gated branches — too risky in this
  TDZ-fragile, single-giant-DOMContentLoaded-handler file (see CARD_ORDER_localstorage_cache.md). The right move is
  to *split* the below-the-fold widget code into a separate deferred file (like the ophthalmology-tools extraction),
  which is a larger, focused refactor.
- **Batch the snapshot/summary endpoints** server-side (fewer Cloudflare round-trips) — a backend change.

## Apply on ortho
Wrap ortho's eager `loadUnifiedClinicalDashboard()` (or equivalent heavy below-the-fold loader) in the same
IntersectionObserver pattern, keyed on its card's `data-card-id`.
