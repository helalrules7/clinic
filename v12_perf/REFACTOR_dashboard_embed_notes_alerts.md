# Refactor — Dashboard Notes & Alerts as direct page embeds (externalize → embed)

**Date:** 2026-06-10 · **Shipped to roaya prod:** yes · **Ortho:** pending (this doc is the replay guide)

## Why
The doctor Dashboard carried **two bespoke re-implementations** of features that already exist as full pages:
- a **Notes board** widget (`dashboard.php` markup + ~790 lines inside `dashboard.js`) duplicating `/doctor/notes`;
- a **Today's Alerts** widget (`dashboard.php` + `loadTodayAlerts` in `dashboard.js`) — a lighter cousin of `/doctor/alerts`.

Two implementations drift apart and bloat the dashboard-only `dashboard.js` (6956 lines / 324 KB). Goal: make
each dashboard widget a **direct embed of the canonical page** (one source of truth) and shrink `dashboard.js`.

## The reference pattern — "patient boards" embed
`board.php` is included into the dashboard and styled down via a flag:
- `board.php:8` → `<div class="board-page <?= !empty($boardEmbedded) ? 'board-page--embedded' : '' ?>" ...>`
- `dashboard.php` → `<?php $boardEmbedded = true; $user = $user ?? $this->getCurrentUser(); include __DIR__ . '/board.php'; ?>`
- `board.php` loads its **external** `board.js` from the bottom of the view with `?v=<?= filemtime(...) ?>`.
- `board.css` has `.board-page--embedded { … }` rules hiding the page title and tightening spacing.

## Key insight — externalize FIRST, then embed
The board embed is cheap because **board.js is an external shared file**. The Notes/Alerts pages instead carried
their logic as **one big inline `<script>` each** (notes ≈ 2540 lines, alerts ≈ 657 lines, both with ZERO PHP
interpolation). A naive `include` would dump that inline JS onto the dashboard and *increase* load. So:
1. **Externalize** each page's inline `<script>` into an external file (`notes-page.js`, `alerts-page.js`).
2. **Embed** the now-thin view with the `$xEmbedded` flag, exactly like board.
3. **Delete** the duplicate notes/alerts code from `dashboard.js`.

Result: `dashboard.js` 6956 → 4813 lines (−2143, ~324 KB → 228 KB); the canonical page JS is now external,
cached, and shared between the standalone page and the dashboard embed.

## Files changed
| File | Change |
|---|---|
| `assets/js/notes-page.js` | **NEW** — verbatim move of notes/index.php inline `<script>` |
| `assets/js/alerts-page.js` | **NEW** — verbatim move of alerts/index.php inline `<script>` + boot/compact tweaks |
| `notes/index.php` | inline `<script>` → external; `$notesEmbedded` class on root |
| `alerts/index.php` | inline `<script>` → external; `$alertsEmbedded` class on root |
| `assets/css/notes.css` | `.notes-page--embedded` rules |
| `assets/css/alerts.css` | `.alerts-page--embedded` rules |
| `dashboard.php` | Notes + Alerts widgets → embed cards |
| `assets/js/dashboard.js` | deleted the duplicate notes/alerts code (−2143 lines) |

## Externalization mechanics (per page)
- **Do NOT wrap the moved body in an IIFE.** Both pages use inline `onclick=` handlers in their markup
  (notes ≈ 31, alerts ≈ 17, e.g. `deleteNote(id)`, `openAlertModal(...)`) that resolve against **global** scope.
  An IIFE would scope the functions and break every inline handler. Move the body **verbatim, top-level**
  (same as the eye-tools extraction). No idempotency guard is needed — each file loads once per page, and the
  code already has internal guards (`__notesPageSyncBound`).
- Replace the inline block with, at the **bottom** of the view (after the markup/`<style>`):
  `<script defer src="/app/Views/doctor/assets/js/notes-page.js?v=<?= filemtime(__DIR__ . '/../assets/js/notes-page.js') ?>"></script>`
  **Path gotcha:** these views live in `doctor/notes/` and `doctor/alerts/`, one level deeper than `board.php`,
  so the filemtime arg is `__DIR__ . '/../assets/js/…'` (the served URL stays absolute `/app/Views/doctor/assets/js/…`).

## Three subtle correctness fixes (all required for the embed to work)
1. **`loadNotes` must gate on the CONTAINER, not the path.** notes/index.php's init was
   `if (window.location.pathname.includes('/doctor/notes')) { … loadNotes() }`. On the dashboard the path is
   `/doctor/dashboard`, so the embed never populated. Change to `if (document.getElementById('notesContainer')) { … }`.
2. **`defer` + deferred-bus load order.** notes-page.js sits in the page content (~where `<?= $content ?>` renders),
   which is **before** the layout's deferred buses `note-bg.js` / `notes-sync.js` / `notes-bridge.js`. A deferred
   script runs at `readyState === 'interactive'` — *before* those buses — so its init ran too early
   (`window.NotesSync` undefined → drawer↔embed sync silently dead; `NoteBG` undefined → gradient-preset notes
   mis-render). Fix: change the init guards from `=== 'loading'` to **`!== 'complete'`** so the deferred script
   registers a `DOMContentLoaded` listener (DCL fires *after* all deferred scripts → buses available). The
   original inline script didn't have this bug because at parse time `readyState` was `'loading'`, which already
   routed init to DCL. (Applied to both notes-page.js init blocks and the alerts-page.js boot.)
3. **Alerts compact "today" mode.** `loadAlerts()` detects `document.querySelector('.alerts-page--embedded')` and
   fetches `/api/alerts/today` (same `{success, alerts}` shape) instead of the paginated `/api/alerts`, and skips
   `updateAlertStats` + pagination. Because every refresh path calls `loadAlerts()`, dismiss/edit/toggle stay
   today-scoped automatically. Also: the original alerts boot was a bare `DOMContentLoaded` with no fallback →
   converted to a readyState-safe `__alertsPageBoot` (so it runs when loaded late inside the dashboard).

## Embed CSS
- `.notes-page--embedded` → `padding:0`; hide `.notes-toolbar` (title + color picker + Add/Delete-All);
  `#notesContainer { min-height:420px }`. The dashboard card header carries an **Add Note** button that proxies
  `document.getElementById('addNoteBtn')?.click()` (the real handler stays the single source of truth; the hidden
  button is still clickable programmatically).
- `.alerts-page--embedded` → hide `.alerts-header`, `#alertsStatsGrid`, the list-card `> .card-header`
  (bulk actions + per-page select) and `#alertsPaginationNav`; strip the list card border/shadow.

## dashboard.js deletion map (the highest-risk step)
The notes/alerts code was **fragmented inside one giant `DOMContentLoaded` handler** (`283 … 3882 });`),
interleaved with must-keep code that shares the `dashboard*` prefix. Deleted ranges (sed in one pass, original
line numbers): `125-186` (loadTodayAlerts), `284` + `286-287` + `299-307` (handler calls + NotesSync listener),
`311-439` (note-alert functions), `1523-1541` (notes state: colorMap + drag + resize state), `1603-1687`
(notes-dashboard resize functions), `2058-2079` (color helpers), `2081-3881` (note widgets + autocomplete + drug
badges). **KEPT:** the three `escapeHtml` defs, `loadDoctorSettings`/`saveDoctorSettings`, card drag/drop +
`DEFAULT_CARD_ORDER`, `renderClinicalAlerts` (a different feature), all non-notes widgets.

**Two near-misses worth flagging for ortho:**
- The notes-resize block is `1603-1687`, **not** `1604-1706` — `1689-1704` is the `DEFAULT_CARD_ORDER` array + card-
  drag state (KEEP). Deleting to 1706 would silently break card reorder. *Always read the boundary; don't trust a range.*
- `loadDoctorSettings` (KEEP) contained dead notes-height code referencing `#notesDashboardCardBody` +
  `DEFAULT_NOTES_DASHBOARD_HEIGHT`. Neutralize that block (it's null-guarded so it wouldn't crash, but it
  referenced a deleted const) so the function just resolves and chains card-order init.

After every cut: `node --check dashboard.js`, then grep that KEEP symbols still exist and deleted symbols are gone.

## Cross-file collision note
On the dashboard, `dashboard.js` + `notes-page.js` + `alerts-page.js` all load together. A collision analysis
showed **zero shared `let`/`const`/`class` globals** (no SyntaxError). The only overlap is a `function escapeHtml`
in each (function declarations are last-wins across scripts, all three are equivalent escapers — harmless).

## Verification (local, real-browser CDP)
- Dashboard: notes-page.js / alerts-page.js / alert_modal.js each load once (200); embedded notes board renders a
  real note with working `onclick` handlers (delete/color); embedded alerts shows the today list only (stats +
  pagination hidden); **`__notesPageSyncBound === true`** (drawer↔embed sync live); card reorder intact (7 handles);
  **0 console errors**.
- Standalone `/doctor/notes` (toolbar visible) and `/doctor/alerts` (stats visible) unchanged, 0 errors.

## Apply on ortho
1. Externalize both inline `<script>`s into `notes-page.js` / `alerts-page.js` (verbatim, no IIFE). Apply the
   **three fixes** above (container-gate loadNotes; `!== 'complete'` init guards; alerts compact-mode + readyState boot).
2. Add `$notesEmbedded` / `$alertsEmbedded` class on each view root; load the external script (defer) at the view
   bottom with the `__DIR__ . '/../assets/js/…'` filemtime path.
3. Add the `--embedded` CSS rules.
4. Swap the two dashboard widgets for embed cards (keep the same `data-card-id`s so saved card order survives).
5. Delete the duplicate notes/alerts code from ortho's `dashboard.js` — **read every boundary first** (the ranges
   above are roaya-specific). Neutralize the notes-height block in `loadDoctorSettings`.
6. `node --check` all JS, `php -l` all views, deploy, `systemctl reload php8.2-fpm`, CDP-smoke.
