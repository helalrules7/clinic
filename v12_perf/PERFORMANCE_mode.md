# Performance Mode — system-wide glassmorphism kill-switch (v12_perf)

A single switch (top of Settings, for **both doctor and secretary**) that disables ALL
glassmorphism across the entire system to speed up scrolling/navigation. Opt-in,
per-user, default OFF. When OFF the feature is completely inert (zero cost).

## Why
The UI carries **894 `backdrop-filter: blur()`** declarations across 59 files. The chrome
and cards float as frosted glass over an **aurora gradient painted on `<body>`** (see
`design-system.css` AURORA VISUAL IDENTITY; `.main-content { background: transparent }`).
`backdrop-filter` re-samples + re-blurs the backdrop on **every composite during scroll**,
which is the dominant scroll/navigation jank (same root cause the `roaya-perf-jank-fix`
addressed by dropping backdrop-filter during the collapse slide). Removing the blur means
the now-translucent surfaces must be made **solid** to stay readable.

## Architecture (one root-gated override layer + the theme/persistence patterns)
- **Root flag:** `html.perf-mode`. Set FOUC-free by the existing pre-paint `<head>` script
  in `layouts/main.php` + `layouts/secretary_main.php` (reads `localStorage.appPerfMode`,
  same place it resolves `dark`/`data-palette`). One added line:
  `try { if (localStorage.getItem('appPerfMode')==='1') html.classList.add('perf-mode'); } catch(e){}`
- **Override stylesheet:** NEW `layouts/performance-mode.css`, linked **last** in `<head>` of
  both layouts so its `!important` wins the cascade. Inert unless `html.perf-mode`.
- **Toggle:** first section of `doctor/settings.php` (`#performanceMode` →
  `updatePerformanceMode()`) and `secretary/settings.php` (`#secPerformanceMode` →
  `secUpdatePreference('performance_mode', …)`). Each flips the `<html>` class + writes
  `localStorage.appPerfMode` instantly (live, no reload) and persists server-side.
- **Persistence (no migration):** key-value tables. Add `'performance_mode'` to the
  `$allowedSettings` allowlist in `DoctorController::updateDoctorSettings()` and
  `SecretaryController` (secretary normalizes booleans automatically). `settings.js` reflects
  the saved value into the toggle and reconciles `localStorage` + class on load.

## What the stylesheet does
1. **Kill all blur (one rule covers all 894):**
   `html.perf-mode *, *::before, *::after { backdrop-filter:none !important; -webkit-backdrop-filter:none !important }`
   — removing a backdrop-filter can never hide content; this is the real perf win.
2. **Opaque surfaces via the app's own `var(--card)` token** (flips with `.dark` → always
   paired with `var(--text)`, so white-on-white is impossible). Applied ONLY to bounded /
   display-gated surfaces: `.card, .modal-content, .dropdown-menu, .popover, .popover-body,
   .toast*, .sidebar, .nav-menu, #quickAccessDock, .note-glass, .alert-toast-glass,
   .chat-fab--glass, .{gender,last-visit,age,mobile}-filter-popover-glass`.
3. **Flatten heavy shadows** on `.card/.modal-content/.dropdown-menu/.popover`.

### Refinement round 2 — surfaces that still read as glass
- **Header**: `.top-bar` (+`.scrolled`) → `var(--card)` (same as sidebar). The notice-bar
  items (clock / next-appointment / eye-tools / weather) have **no background of their own**
  — they ride the bar, so solidifying `.top-bar` solidifies the whole notice bar for free.
  Plus the eye-tools dropdown `.notice-bar-tools-child` and the header chip vars
  (`--header-chip-bg/-hover-bg`, a scoped var — safe, unlike the global `--glass-*`).
- **Slide-in panels — target the INNER panel, never the shell**: `.todo-drawer`,
  `.notes-drawer__panel` (NOT `.notes-drawer`), `.notif-panel` (+`__inner`; this is also the
  Activity feed → the "activity palette"), `.global-search-container.expanded .global-search-input-wrapper`.
  ⚠️ The class names are non-obvious: notification centre is `.notif-panel` (NOT
  `.notification-center`), notes inner is `.notes-drawer__panel`.
- **Floating widgets/FABs**: `.chat-panel` + `.chat-fab` (doctor chat), `.ai-chat-window` +
  `.ai-chat-toggle` (AI assistant), `.scroll-to-top` (back-to-top).
- **Minimized dock — keep two separate buttons**: the merge came from painting the whole dock
  one solid box. The base CSS already makes the minimized `.dock-container` a transparent
  wrapper, so scope the solid box to `.quick-access-dock:not(.minimized) .dock-container` and
  give `.dock-chat-btn`/`.dock-minimize-btn` (minimized) their own solid shadowed circle.
- **Notice-bar popovers** (clock calendar / next-appointments / weather): built in main.js,
  appended to `<body>`, bounded, base bg `rgba(…,0.35)!important` → `.clock-calendar-popover`,
  `.appointments-popover`, `.weather-popover` → `var(--card)` (their `*-backdrop` scrims stay translucent).

### Refinement round 3 — faster dashboard open/close + navigation
**Measured** (real Chrome via CDP, 4× CPU throttle, sidebar `#sidebarToggle` collapse/expand,
frame-delta sampling): the **calendar** toggle is smooth (~56fps, jank ~36ms) but the
**widget-heavy dashboard** is janky (~41fps, jank ~200–250ms) because changing `.main-content`'s
width (sidebar open/close, `margin-left` transition at style.css:440) re-lays-out + repaints the
whole dashboard card tree. **Removing the transition makes it WORSE** (concentrates the cost into
one frame) — the fix is to shrink the work, not the animation.

Fix = **`content-visibility:auto; contain-intrinsic-size:auto 320px`** on the dashboard's own
simple card classes (`.stats-card-wrapper, .dash-mini-card, .clinical-indicator-card,
.mini-trend-card, .chart-card`) → the browser skips layout/paint for off-screen cards, so the
width change only touches what's visible (and page load is faster too). **Measured −~30% collapse
jank, +5–6 fps.** Deliberately **NOT** on the `.dashboard-card` embed wrappers (board / notes
board / alerts) — their absolutely-positioned / self-measuring internals can misrender if skipped
while off-screen (verified the simple cards still render correctly when scrolled into view).
Method scripts: `/tmp/perf-measure.mjs` (A/B), `/tmp/perf-experiment.mjs` (strategy bake-off).

## ⚠️ The two traps (both hit during build — do NOT repeat on ortho)
1. **Do NOT flip the global `--glass-*` design tokens** (`--glass-bg`, `--tp-surface`, …).
   They're used by **full-screen fixed panels** too; flipping them opaque globally turned an
   always-present layer into a solid sheet that **covered the whole UI**. Use `var(--card)`
   on an explicit bounded selector list instead.
2. **Do NOT opaque sliding drawers/panels** (`.notes-drawer, .todo-drawer,
   .notification-center, .cmdk, .ai-chat-widget, .chat-widget, .offcanvas`). They are fixed
   **full-viewport shells that are transparent when closed**; a solid background blanks the
   entire page. (Verified: the dashboard rendered as a solid dark rectangle.) They keep their
   own background when the user opens them.
   Also: never use `[class*="glass"]` — it wrongly catches **eyewear** classes
   (`.glasses-rx-card`, `.btn-action-glasses`). Enumerate the real `*-glass` classes.

## Verify (real Chrome via CDP, both themes, multiple pages)
Drive headless Chrome (login `dr_faramawy`/`password` on localhost:8080), set
`localStorage.appPerfMode='1'`, reload `/doctor/{dashboard,patients,calendar,settings}` in
**light AND dark**, and assert: (a) no element overlapping the viewport centre is opaque +
fixed (no full-screen cover), and (b) a `.card`'s computed bg/text stay contrasting. Confirm
with screenshots. Script: `/tmp/perfmode-verify.mjs`.

## Deploy
Static CSS/JS auto-bust via `?v=filemtime`; PHP (controllers/views) → `reload php8.2-fpm`.
No route changes → both routers untouched. CSS-only fixes need no fpm reload (just re-scp).
