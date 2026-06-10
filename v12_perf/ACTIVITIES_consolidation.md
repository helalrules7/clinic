# v12_perf — Activities consolidation

**Date:** 2026-06-10 · **Branch:** v_12_perf · **Shipped to prod:** yes · **Ortho:** pending

## Why
The app had **two parallel activity systems**:

| | Dashboard "Recent Activities" card + "View All" modal | Notification Center "Activity" tab |
|---|---|---|
| Endpoint | `/api/recent-activity` (`ApiController::getRecentActivity`) | `/api/activity` (`ActivityController::feed`) |
| Source | `timeline_events` (2 JOINs) | `activity_log` (clinic-scoped, indexed) + 3 more sources |
| Scope | **NONE** — a doctor saw every activity in the system (data-isolation bug) | clinic-scoped (secretary sees her clinic; doctor sees all) |
| Cost | card 5 rows; **modal bulk-fetched up to 1000 rows** into JS | 4-source UNION, default 50 / 60s poll |

This was duplicated, the old path leaked cross-clinic data, and the modal dumped 1000 rows. We **retire the old
path** and consolidate everything onto the proper clinic-scoped `/api/activity`, add a dedicated page for deep
browsing, and cap the panel tab.

## What changed
1. **Backend — `ActivityController`**
   - New `page()` → `GET /api/activity/page?page&per_page&type&from&to&q`: windowed offset pagination across the
     merged 4-source feed, with `type` (all|appointment|consultation_note|alert|todo), inclusive `from/to` date
     range (applied per-source in SQL), and a post-merge `q` text filter. Returns `{events, page, per_page, has_more}`.
   - Extracted `resolveClinicScope($user)` — now shared by `feed()` and `page()` so the clinic scope (and therefore
     the secretary's view) is **identical** on both surfaces. The 4 source fetchers gained optional `$from/$to`
     (backward-compatible; `feed()` passes null).
2. **Dedicated Activities page** (doctor + secretary, one view)
   - View `app/Views/doctor/activities.php` (bilingual via `$activitiesLang`), JS `assets/js/activities.js`,
     CSS `assets/css/activities.css`. Filters: type select + date from/to + search + load-more pagination.
   - `activities.js` mirrors the notification center's `formatActivityLine`/`arActivityVerb` so rows are identical
     and **Arabic-aware** (language from `<html lang>`).
   - Routes `/doctor/activities` (`DoctorController::activities`, English) and `/secretary/activities`
     (`SecretaryController::activities`, Arabic, secretary layout). Both render the SAME view; the feed is
     clinic-scoped server-side, so the secretary sees her own actions + the doctor's actions in her clinic.
3. **Dashboard** — removed the Recent Activities card + the 1000-row "View All" modal (`dashboard.php`) and the
   ~386 lines of loader/render/modal JS (`dashboard.js`: `loadRecentActivity`, `renderRecentActivity`,
   `renderPagination`, `loadModalActivities`, `applyFilterAndRender`, `highlightText`, modal wiring). Dropped
   `'recent-activity'` from `DEFAULT_CARD_ORDER` (saved orders self-heal — non-matching ids are filtered out).
   `dashboard.js`: 4813 → 4427 lines.
4. **Notification Center** — Activity tab capped at **10** (`api('/api/activity?limit=10')`) + a context-aware
   **"View all activity →"** footer link to `/doctor/activities` or `/secretary/activities` (from `data-notif-context`).
5. **Routes** added to BOTH routers (`public/index.php` + `app/index.php`): `/api/activity/page`,
   `/doctor/activities`, `/secretary/activities`.

## Notifications vs Activities (point 5)
Already separated and left that way — `notifications` table (NotificationControllerV11, snooze/pin/dismiss) vs
`activity_log` + sources (ActivityController, read-only). The grouped notifications endpoint does NOT include
activity rows. The secretary's activities are clinic-scoped, her own actions are logged via
`Helpers::logActivity(actor_user_id, …, clinic_id, …)`. **No change needed** to keep notifications from absorbing
activities — they never did.

## Performance / value
Honest framing: the direct **dashboard-load** speedup is **modest** (one async card removed). The real wins are
**correctness** (retire the unscoped `/api/recent-activity` cross-clinic leak), **eliminating the 1000-row modal
dump** (replaced by a server-paginated page), **−386 lines off dashboard.js**, and **declutter**. The Activity tab
payload drops from ≤50 to ≤10 rows per 60s poll.

## Verified (local, CDP)
- `/doctor/activities`: rows render; `type=alert` filter → only alert rows; date filter returns only in-range ts;
  load-more pagination; **0 console errors**.
- `/api/activity?limit=10` returns exactly 10. `/api/activity/page` paginates with `has_more`.
- Dashboard: Recent Activities card + modal gone; card reorder intact; **0 console errors**.
- Secretary route wired (role-gated 403 for a doctor, 302 unauth — not 500). **Caveat:** a secretary-login smoke
  wasn't run locally (no secretary creds); the secretary path reuses the verified shared `resolveClinicScope` +
  `formatActivityLine`(Arabic). Recommend a quick secretary-login check on prod.

## Apply on ortho
1. `ActivityController`: add `page()` + `resolveClinicScope()` + optional `$from/$to` on the 4 fetchers.
2. Add the routes (both routers), the two controller methods, and the shared view/JS/CSS
   (`activities.php` / `activities.js` / `activities.css`).
3. Remove ortho's Recent Activities card + modal + loaders (re-map line ranges — they differ); drop it from
   `DEFAULT_CARD_ORDER`; keep shared helpers (`escapeHtml`, `getStatusBadgeClass`).
4. Cap the notification Activity tab to 10 + add the context-aware "View all" link.
5. `node --check` all JS, `php -l` all PHP, deploy, `systemctl reload php8.2-fpm`, CDP-smoke (incl. a secretary login).
