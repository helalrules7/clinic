# v12_perf — Dashboard: drop amCharts4, dedupe chart fetch, defer Chart.js

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes · **Ortho:** pending

## Context
A final dashboard perf review found three chart-related wins.

### 1. amCharts4 was ~500KB of dead, render-blocking weight (biggest win)
The dashboard loaded **three deprecated amCharts4 CDN scripts** (`core.js` + `charts.js` + `themes/animated.js`),
**non-deferred** (render-blocking), on **both** the doctor and secretary dashboards — to power a **single gender
pie chart**. But that chart's container `#genderPieChart` lives on the **Reports page** (`reports.php`), *not* the
dashboard. So on the dashboard `renderGenderPieChart()` returned early (no container), and the library was loaded
**for nothing** — plus `loadChartsData()` ran a **2-second retry-poll** (`setInterval` ×10) waiting for amCharts
just to call a function that no-ops (on the secretary dashboard, where amCharts wasn't even included, the poll
always exhausted its retries and logged an error). Reports renders its own gender chart with **Chart.js**.

**Fix:** removed the 3 amCharts `<script>` tags from `dashboard.php` and the amCharts retry-poll from
`dashboard.js`. (The now-unreachable `renderGenderPieChart()` / `updateChartsTheme` amCharts branch are left as
guarded dead code — they reference `am4core` only behind `typeof am4core === 'undefined'` / a never-set instance,
so they can't run; safe to delete later.)

### 2. `/api/dashboard-charts` was fetched twice per load
`loadChartsData()` and `loadStatsCardsData()` each fetched the same endpoint. Added a memoizer
`fetchDashboardChartsOnce()` (page-lifetime; theme changes redraw from the cached data) → **one** request.

### 3. Chart.js was render-blocking
`<script src=".../chart.umd.min.js">` was non-deferred. `dashboard.js` already polls `typeof Chart` and renders
charts on DOMContentLoaded (after deferred scripts run), so **`defer`** is safe and unblocks paint.

## Net effect (per dashboard load)
- **−3 render-blocking CDN scripts (~500KB)** + their round-trips (amCharts).
- **−1 redundant XHR** (`/api/dashboard-charts` 2 → 1).
- **−1 render-blocking script** (Chart.js now deferred).
- **−2s retry-poll** (+ the recurring console error on the secretary dashboard).

## Reassessed and skipped — 1-second display intervals
The review flagged "1-second `setInterval`s never pause on tab-hidden." On inspection this is a non-issue: 3 of the
4 are **clock-popover-scoped** (pushed to `clockCalendarPopover._intervals` and `clearInterval`'d when the popover
closes), and the only always-on one (notice-bar date/time) is a light text update that browsers already
auto-throttle in background tabs. Not worth a change.

## Verified (local, CDP)
`/doctor/dashboard`: **0** requests to amcharts.com, **1** request to `/api/dashboard-charts`, `am4core` is
`undefined`, the Chart.js charts still render (instances present), **0 console errors** — with Chart.js deferred.

## Apply on ortho
1. Remove the 3 amCharts `<script>` tags from ortho's dashboard view + the amCharts retry-poll in its `dashboard.js`
   (verify ortho's gender chart, if any, is on Reports and self-rendered).
2. Memoize the chart-data fetch if it's fetched more than once.
3. Add `defer` to the Chart.js `<script>` (confirm the chart renderers run on/after DOMContentLoaded).

## Further opportunities (not done — for a later pass)
- Defer/split `dashboard.js` (~205KB, still non-deferred) — riskier (one giant DOMContentLoaded handler + TDZ, see
  CARD_ORDER_localstorage_cache.md).
- Lazy-load below-the-fold widgets (clinical/board snapshots, missed appointments, news) via IntersectionObserver
  to cut the ~13-call on-load API fan-out.
- Batch the snapshot/summary endpoints server-side into fewer round-trips.
