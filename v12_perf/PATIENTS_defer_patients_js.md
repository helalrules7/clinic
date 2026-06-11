# v12_perf — Patients page: defer the 399 KB `patients.js`

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## Audit (`/doctor/patients` → `DoctorController@patients` → `patients.php`)
The patients **folders/list** page was audited the same way as the dashboard and calendar.

- **`patients.js` is 399 KB / 9 655 lines** — the **largest single JS file in the project**, and it was loaded
  **non-deferred** (`patients.php:1549`). It is **hand-written feature code, not a bundled library** (no
  jszip/xlsx/jspdf/chart.js/moment/etc.; longest line 335 chars) — folders, tags, color-markers, table/cards/folders
  views, filters, batch ops, pagination, modals.
- The patient list is **client-rendered** by `renderPatientsTable()` (`patients.js:2529`) — `#patientsTableBody` is
  **server-empty** and filled on `DOMContentLoaded`. So this is the **calendar pattern**, not the dashboard's
  server-rendered-cards pattern.
- **On-load fetch fan-out is already lean.** The list is **paginated** (`itemsPerPage: 20`), and the only on-load
  enrichment — `fetchColorMarkersForTablePatients` / `fetchTagsForTablePatients` (the `/api/...batch` calls) — runs
  **only for the ~20 visible rows of the current page**, not all patients. No heavy below-the-fold own-fetch widget to
  lazy-load (unlike the dashboard's clinical snapshot).
- **No polling** of consequence (2 `setInterval` are not load-critical).
- **6 `DOMContentLoaded` handlers**; all real work is DCL-bound or top-level event bindings.

## Change — add `defer` to `patients.js`
A 399 KB **synchronous** `<script>` blocks first paint until it is **downloaded _and_ executed**, even sitting near
the end of the body (line 1549 of 1593). Deferring lets the **server-rendered shell** (toolbar, filters, the
table/cards/folders card chrome, all the modals) paint and become interactive immediately, then the 399 KB runs.

```diff
- <script src="/app/Views/doctor/assets/js/patients.js?v=…"></script>
+ <script defer src="/app/Views/doctor/assets/js/patients.js?v=…"></script>
```

### Why it's defer-safe (verified, 3 scripts total on the page)
1. **Inline `@1539` (before patients.js)** only assigns `window.PATIENTS_CONFIG = { patients, doctors }` (PHP→JSON
   data, no function calls). Runs at parse time, so the config is ready before the deferred `patients.js` executes.
2. **patients.js** does all its work in 6 `DOMContentLoaded` handlers + top-level event bindings that use optional
   chaining (`document.getElementById('createFolderForm')?.addEventListener(...)`). Deferred scripts run **after the
   DOM is fully parsed, before `DCL`** — so every `getElementById` finds its (server-rendered) target. Strictly
   **safer** than the previous non-deferred load (which executed mid-parse with only the DOM above line 1549 present).
3. **Inline `@1552` (after patients.js)** is wrapped in its own `DOMContentLoaded` handler and only locks the clinic
   `<select>` for the single-clinic (secretary) case — it does **not** call any `patients.js` function. Its handler
   runs after patients.js has executed (deferred → before DCL), so ordering is preserved.

Net: like the calendar, the **client-built list still appears on DCL** (defer doesn't make the list itself paint
sooner), but the **shell paints without waiting on 399 KB** and the page becomes interactive much earlier.

## Verified (local, real Chrome via CDP, dr_faramawy)
`/doctor/patients`: script tag `defer` ✓, `patients.js` HTTP 200 ✓, `renderPatientsTable` defined ✓,
`PATIENTS_CONFIG.patients` present (**2 271** patients) ✓, **20** table rows rendered on the first page ✓,
`initializePagination` defined ✓, search input + folders card + 8 custom-selects present ✓, **0 console errors**.

## Flagged for a future focused task (NOT done here — bigger refactor)
- **`PATIENTS_CONFIG` inlines the entire patient set** (`getAllPatients()` → **2 271 rows** JSON-encoded into the HTML
  document at `patients.php:1545`). This is very likely the page's **single largest payload** (larger than
  `patients.js` over the wire), shipped on every load to power instant client-side search/filter/pagination. Moving to
  a **server-paginated / API-fed list** (fetch a page at a time, server-side search) would shrink the initial document
  dramatically — but it changes the data-flow for search/filter/folders and needs careful, separate work.
- **Split `patients.js`** (folders/tags/color-marker feature code into a lazily-loaded module) — only worth it if the
  single deferred bundle proves too heavy.

## Apply on ortho
Add `defer` to ortho's `patients.js` `<script>` tag; first confirm the equivalent inline config script (sets the
patients JSON) sits **before** it and that no inline script **after** it calls a `patients.js` function synchronously.
