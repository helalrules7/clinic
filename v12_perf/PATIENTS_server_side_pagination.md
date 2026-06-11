# v12_perf — Patients page: true server-side pagination (drop the 1.6 MB inline blob)

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## The problem
`/doctor/patients` inlined **every** patient (`getAllPatients()` → ~2 271 rows) as a JSON literal in the HTML:

- HTML document = **2 013 KB**, of which **PATIENTS_CONFIG.patients = 1 621 KB (80 %)**.
- That literal was parsed on the **main thread on every load**, and the heavy aggregation query (the
  `getAllPatients` SELECT with 5 correlated subqueries) ran during page render before any HTML was sent.
- All search / filter / sort / pagination ran **client-side** over that in-memory array.

This is the page's single biggest payload — bigger than `patients.js` itself — and it grows linearly with the
patient count (a 10 k-patient clinic would ship ~7 MB per load).

## The fix — fetch one page at a time from the server
The table/cards list now pages through a new endpoint; the full set is never shipped.

### Backend — `ApiController::getPatientsPaginated()` (route `/api/patients/paginated`)
- Params: `page`, `per_page` (or `all`, capped 500/100000), `search`, `gender`, `age_min/max`, `doctor_id`,
  `last_visit_from/to`, `color` (comma-sep, OR), `tag` (comma-sep, OR), `sort_by/order`.
- **All filters in ONE shared `WHERE`** used by both the `COUNT(*)` (total) and the page `SELECT` — they can never
  diverge. `search` matches first/last/full-name, phone, alt_phone, national_id.
- The **doctor** and **last-visit** filters use correlated subqueries so they match the exact derived columns the
  old client filtered on (`created_by_doctor_id` from the timeline-events subquery; `MAX(appointments.date)` — a
  patient with no visit is excluded by a date range, like the old client). `color` → `patient_color_markers`,
  `tag` → `patient_tag_assignments`.
- Per-patient row shape is **identical** to `getAllPatients()`, so the existing row renderers are unchanged.
- Response: `{ ok, patients:[…page…], pagination:{ total, page, per_page, total_pages } }`.
- Legacy `/api/patients` (full array) is **left untouched** for backward-compat.

**Validated** against the legacy full-array endpoint (filter client-side in Python, compare counts/ids):
`total 2271, search('مح')=770, gender Male=1003, doctor(timeline)=1573, age 30-40=275, last_visit≥2026-01-01=1165,
color=3, tag=2, sort first_name ASC (first 5 ids)` — **10/10 exact match**. Page query is **14.7 KB / ~20-80 ms**
vs the legacy **1.66 MB** (113× smaller).

### Backend — `DoctorController::getPatientStats()`
The 8 stat cards used to come from `array_filter()` loops over the full set. Now a **cheap aggregate** (two
GROUP-BY-free queries; date thresholds computed in PHP, not `CURDATE`, to match the old behaviour exactly).
Validated 8/8: `total 2271, visits 3192, recent 8, new_month 21, new_week 2, male 1003, female 1268, active 596`.
`DoctorController::patients()` now passes `patients => []` + `patientStats` (no heavy query, no inline blob).

### Client — `patients.js`
A single orchestrator **`loadPatientsPage(page)`** builds the query from the UnifiedFilterManager filters + the
search input + sort + per-page, fetches `/api/patients/paginated`, sets `filteredPatients` = that page +
`serverTotal`, and renders. A `paginationState.serverMode` flag gates branches in the render/pagination functions
(render the page as-is, compute totals/total-pages from `serverTotal`). Every trigger funnels into it:
`_doApplyFilters` (table/cards), `filterPatientsLocally` (search), `applyDoctorFilter`, `sortPatients`,
`clearSorting`, `changePage`, `changeItemsPerPage`, the cards per-page selector, `switchViewMode`,
`refreshPatientsData`. An out-of-order guard (`__patientsPageSeq`) drops stale responses.

**Folders view is untouched** — it has its own endpoints (`/api/patient-folders/*`) and its own
`currentFolderPatients`; it never depended on the inline array. The only live read of the (now-empty) `allPatients`
was the delete-modal avatar colour — changed to fall back to the visible page. (`filterCardsContent`'s reads are
dead code — zero callers.)

## Result
- HTML document **2 013 KB → 392 KB** (−80 %, the 1.6 MB blob is gone).
- The heavy aggregation query moves off the HTML render path; the page ships fast and fetches a 14.7 KB page after.
- Scales to any patient count (only ~20 rows ever transferred/held), and search/filter/sort are real DB queries.

## Verified (local, real Chrome via CDP) — 0 console errors throughout
initial 20 rows + stats (2 271 / male 1 003) · search('محمد')=638 · gender Male=1003 · color=3 · tag=2 ·
combined Female+'علي'=62 (matches server) · per-page 50 → 50 rows · page 2 (showing 21+) · sort first_name ASC ·
cards view 24 · **folders view 13 cards (still works)** · back-to-table 20.

## Prod deploy (2026-06-11)
6 files: `ApiController.php`, `DoctorController.php`, `patients.php`, `patients.js` scp'd (all confirmed in sync
with prod before overwrite); the route line **inserted in-place** into both **diverged** routers
(`public_html/index.php` + `public_html/app/index.php`) via a file-based Python script (never overwrite the union
routers — see [[git-poisoned-release-line]] sibling gotcha). `php -l` clean, `php8.2-fpm` reloaded. Smoke:
`/api/patients/paginated` → 401 JSON (wired + auth-gated, not 404), `/doctor/patients` → 200. Backups: `*.bak.20260611b`.

## Apply on ortho
Port `getPatientsPaginated` + route + `getPatientStats` + the `patients.js` `loadPatientsPage`/`serverMode` seam.
Watch: ortho's doctor-filter column + its color/tag table names; keep its folders view on its own endpoints.

## Future (not done)
- Server-paginate large **folders** too (currently a folder's patients load in one fetch on open).
- Debounce is already on the search input; consider a tiny min-length before firing the server search.
