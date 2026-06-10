# v12_perf — Dashboard card order: cache-first + redundant-settings-fetch cleanup

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes (dashboard.js only)

## Why
The dashboard card order was loaded from the **DB on every refresh** (an XHR + a visible reorder
"jump" after the network resolved), and the settings endpoint was fetched **redundantly**:
- `loadDoctorSettings()` fetched `/api/doctor/settings` and **discarded** the result (dead weight after
  v12_perf removed the notes-height setting) — then `loadDashboardCardOrder()` fetched it **again**.
- `toggleDashboardRearrangeButtons()` fetched `/api/doctor/settings` on load **and on every `resize`
  event** (for a static `dashboard_rearrange_mobile` setting) — spamming the endpoint while dragging.

## What changed (dashboard.js)
1. **Cache-first card order.** On reorder, `saveDashboardCardOrder()` writes the order to **localStorage**
   (instant) **and** the DB (source of truth). On load, `loadDashboardCardOrder()` applies the cached
   order from localStorage **synchronously** (no network wait, no jump), then **revalidates** against the
   DB in the background and re-applies only if it differs (e.g. reordered on another device).
   `applyCardOrder(order)` was split out (pure apply). DB stays the source of truth (localStorage is
   per-device; first load on a new device falls back to the DB).
2. **Removed the dead `loadDoctorSettings()` pre-fetch** (−1 XHR).
3. **One shared settings fetch** via `fetchDoctorSettingsOnce()` (memoized on the function) — the
   card-order and rearrange loaders share a single `/api/doctor/settings` GET.
4. **Rearrange toggle is cache-first too** + **resize no longer fetches**: the setting is read once
   (localStorage-cached); `resize` only calls `applyRearrangeVisibility()` (no network).

Net (dashboard.js's own settings traffic): ~3 GETs/load + N-on-resize → **1 GET/load + 0 on resize**,
plus instant cache-first apply (no reorder jump).

## ⚠️ Gotcha that cost real time — TDZ inside the giant DOMContentLoaded handler
All this code lives **inside one big `document.addEventListener('DOMContentLoaded', function(){ … })`**
handler (~221–1327). Function declarations hoist, but `const`/`let` do **not**. The boot calls
`loadDashboardCardOrder()` near the **top** of the handler, but `DEFAULT_CARD_ORDER` and the cache key
`const`s are declared **later** in the same handler → calling it synchronously throws
**`Cannot access 'X' before initialization`** (TDZ), which `readCachedCardOrder`'s try/catch **swallowed**
(so: no console error, cache silently `null`, order never applied — looked like a no-op).
The original code avoided this by calling `loadDashboardCardOrder` from
`loadDoctorSettings().then(...)` — i.e. **deferred to a microtask** (after the handler body ran, consts
initialized). The fix restores that: **`Promise.resolve().then(loadDashboardCardOrder)`** (and the same
for the rearrange loader), plus memoizing the shared fetch **on the function** (`fetchDoctorSettingsOnce._p`)
instead of a late `let`. Microtasks run before paint, so the cache apply is still instant.

**Lesson for ortho:** when adding code that's invoked early in that handler but depends on `const`/`let`
declared later in the same handler, **defer the call to a microtask** (or move the call after the consts).

## Verified (local, CDP, fresh session)
With DB and localStorage matching a custom order: the order is applied from cache and **held** (one
`applyCardOrder`, no revert); with the DB differing, it correctly reverts to the DB order. **5× resize
adds 0 settings GETs.** 0 console errors. (dashboard.js makes 1 settings GET/load; the other GETs are
from `main.js` in the layout — a separate, out-of-scope redundancy worth a later look.)
