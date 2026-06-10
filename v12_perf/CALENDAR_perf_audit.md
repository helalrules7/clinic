# v12_perf — Calendar perf audit + defer scripts

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes · **Ortho:** pending

## Audit (it's already fairly lean)
The doctor calendar (`/doctor/calendar` → `calendar.php` + `calendar.js` 117KB) was audited the same way as the
dashboard. Findings:
- **5s live-update poll is already well-behaved.** `checkCalendarVersion()` (every 5s) starts with
  `if (document.hidden) return;` — no `/api/calendar/version` request while the tab is hidden. The version check is a
  cheap cursor compare; a full re-fetch (`refreshCalendarData`) only runs when the cursor actually changed.
- **No chart libraries** (no amCharts/Chart.js dead weight like the dashboard had).
- **No redundant fetches.** The three `/api/calendar` calls are distinct triggers (initial load, poll-driven
  refresh, date navigation). `/api/appointments/*` calls are user-action CRUD, not load-time.
- The grid is **client-rendered** by `calendar.js` (`#calendarContainer` is an empty shell that `loadCalendar()`
  fills on DOMContentLoaded).

## Change — defer the 3 calendar scripts
`calendar.js` (117KB), `medical-history-popover.js`, and `book-by-phone.js` were **non-deferred**, so the 117KB
download **blocked HTML parsing** of the rest of the body (all the modal markup, etc.). Added `defer` to all three.

Safe because: all run their work on DOMContentLoaded (calendar.js has 4 DCL handlers); deferred scripts keep DOM
order and execute before DCL, so the inline `CALENDAR_CONFIG` (set earlier in parse) is available and the relative
order among the 3 is preserved. The inline modal-sync handler (`calendar.php`) is independent — its
`syncClinicCustomSelect`/`lockIfSingleClinic` operate on **server-rendered** custom-select markup (not built by
calendar.js), and `lockIfSingleClinic` no-ops for doctors (multiple clinics); the `shown.bs.modal` syncs are bound
and fire on modal show.

Note: the grid is client-built, so deferring doesn't make the *grid* appear sooner (it still builds on DCL) — the
win is that the **rest of the body parses + becomes interactive without waiting on the 117KB**, and the
server-rendered shell (header/date-nav/toolbar/modals) is ready immediately.

## Verified (local, CDP, 1400×900)
`/doctor/calendar`: `loadCalendar`/`startAutoRefresh` defined, `#calendarContainer` filled (12.4KB / 184 elements),
add-appointment button + clinic `<select>` present, auto-refresh active, **0 console errors**.

## Apply on ortho
Add `defer` to ortho's calendar script tags (keep their order; confirm the inline config/modal-sync scripts don't
call calendar.js functions synchronously).
