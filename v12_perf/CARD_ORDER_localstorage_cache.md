# v12_perf — Dashboard card order: cache-first (localStorage) + fewer settings fetches

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes (dashboard.js) · **Ortho:** pending

## Problem
On every dashboard load the saved card order was fetched from the DB (`/api/doctor/settings`) and applied
**after** the network round-trip — so the cards rendered in the default order, then **jumped** to the saved
order once the fetch resolved. Worse:
- `/api/doctor/settings` was fetched **multiple times per load**: a dead `loadDoctorSettings()` pre-fetch (left
  over from the v12_perf notes-height removal — it fetched and discarded), `loadDashboardCardOrder()`, and the
  rearrange-toggle loader.
- The mobile "rearrange" toggle re-fetched `/api/doctor/settings` on **every `resize` event** (undebounced) for a
  static setting.

## Fix
1. **Cache-first card order (localStorage).** `loadDashboardCardOrder()` now applies the cached order from
   `localStorage['dashboard_cards_order']` **instantly** (no network), then revalidates against the DB in the
   background and re-applies **only if it differs** (cross-device). `saveDashboardCardOrder()` writes localStorage
   **and** the DB (DB stays the source of truth). `applyCardOrder(order)` was extracted as a pure apply.
2. **One settings fetch per load.** Added a memoizer `fetchDoctorSettingsOnce()` — the card-order and rearrange
   loaders share a single `/api/doctor/settings` GET.
3. **No fetch on resize.** The rearrange toggle is cache-first too (`dashboard_rearrange_mobile` in localStorage);
   `resize` only calls `applyRearrangeVisibility()` (no network).
4. **Removed the dead `loadDoctorSettings()`** pre-fetch entirely.

Net: was ~3 settings GETs on load + N on resize → **1 GET on load, 0 on resize**, and the saved order applies with
**no jump** (localStorage is synchronous, applied before paint).

## ⚠️ Critical gotcha — Temporal Dead Zone (the bug that cost the most time)
The dashboard's card functions + their `const`s (`DEFAULT_CARD_ORDER`, `CARD_ORDER_LS_KEY`) live **inside one big
`DOMContentLoaded` handler**, and the boot calls the loader near the **top** of that handler — *before* those
`const`s are declared further down. `function` declarations are hoisted (so the loader is callable), but `const`
is **not** (TDZ). Calling the loader **synchronously** there throws `Cannot access 'X' before initialization`,
which the cache reader's `try/catch` **silently swallowed** → returned null → no reorder (the cards just stayed in
the default order, looking like the feature did nothing).

The original code accidentally avoided this because it called the loader via
`loadDoctorSettings().then(() => loadDashboardCardOrder())` — a promise `.then` defers to a **microtask**, which
runs after the handler body finishes (consts initialized). The fix keeps that deferral explicitly:
```js
Promise.resolve().then(loadDashboardCardOrder);   // microtask: TDZ-safe, still runs before paint
```
**Lesson for ortho:** when moving a call earlier inside that monolithic handler, defer anything that touches
later-declared `const`s to a microtask — and never let a `try/catch` hide a `ReferenceError`.

## Verified (local, CDP, fresh session)
- Set a custom order in localStorage + DB → reload → the cards apply the **custom** order (matches), no revert,
  **0 console errors**.
- dashboard.js issues exactly **one** `/api/doctor/settings` GET on load (memoized); 3× `resize` adds **0** GETs.

## Apply on ortho
Mirror the four changes in ortho's `dashboard.js` (cache-first order + `applyCardOrder` split + `fetchDoctorSettingsOnce`
memoizer + rearrange cache-first + drop the dead `loadDoctorSettings`), and **keep the microtask deferral** of the
card-order loader inside the DOMContentLoaded handler (TDZ).
