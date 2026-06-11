# v12 — Drugs Report (reports page): responsive tables + chart legend on mobile/tablet

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12) · **Ortho:** pending

## Problem (doctor `/doctor/reports?type=drugs`, mobile + tablet)
1. **Tables couldn't be scrolled** — columns ran off-screen and were unreachable.
2. **Chart legend text was cut** — drug names showed truncated ("LACRITEARS EYE DROPS 1…").

## Cause + fix — tables (`reports.css`)
All report tables already use Bootstrap `.table-responsive`, BUT a rule overrode it:
`.reports-page .card-body .table-responsive { overflow: hidden }` (it rounds the corners because the parent
`.reports-page .card` is `overflow: visible` for dropdowns). `overflow: hidden` **clips** wide tables with **no
scroll**. Fix:
```css
.reports-page .card-body .table-responsive { overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; }
@media (max-width: 991.98px) { .reports-page .card-body .table-responsive > table { min-width: 560px; } }
```
- `overflow-x:auto` restores horizontal scroll (keeps `overflow-y:hidden` so the rounded corners / solid thead stay).
- The mobile `min-width:560px` keeps columns readable (no over-squish) so the table reliably overflows → scrolls.
- Desktop unaffected (auto = scrollbar only when needed; min-width only ≤991.98px). Applies to ALL report tables.

## Cause + fix — chart legend (`reports.js`)
The `drugTrendChart` dataset labels were truncated to **22 chars** + "…". Bumped to **40** so the full drug name
shows (longest current name is ~32 chars). One line: `…length > 40 ? …substring(0, 40) + '…' : …`.

## Verified (CDP)
- 390px: 3 tables `overflow-x: auto` and **all scrollable** (table 560-651px wider than the ~308px wrapper); legend
  label = full "LACRITEARS EYE DROPS 15 ML" (was "LACRITEARS EYE DROPS 1…").
- 820px: tables fit (no forced scroll).

## Deploy
2 static files (`reports.css`, `reports.js`), versioned, no fpm reload. Doctor reports page.

## Note
The x-axis month labels on the trend chart looking sparse/odd is a separate **data** concern (the trend spans many
empty months), not this text-cut fix — left untouched.
