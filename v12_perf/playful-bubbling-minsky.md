# Plan — Performance Mode (system-wide glassmorphism kill-switch)

## Context
The UI leans heavily on glassmorphism: **894 `backdrop-filter` blur declarations across 59 files**.
`backdrop-filter: blur()` is the single most expensive paint operation here — the browser re-samples
and re-blurs the backdrop on **every composite during scroll**, which is exactly the jank the user
feels when moving/navigating (already a known hot-spot — see memory `roaya-perf-jank-fix`, where the fix
was "drop backdrop-filter during the collapse slide").

The user wants a **Performance Mode**: one switch, at the **top of the first Settings page** for **both
doctor and secretary**, that turns off glassmorphism **across the literal entire system** (every page,
every component, both roles). Confirmed scope decisions:
- **What turns off:** glass blur + opaque surfaces **+ heavy multi-layer shadows and decorative
  `saturate()/contrast()` filters**. (NOT animations/transitions — that was the rejected "Lite" tier.)
- **Surface look when off:** **solid opaque** surfaces that respect the active theme/palette and dark mode.
- **No benchmarking** — just implement.

## Why a global kill-switch (not editing 894 rules)
Blur is **hardcoded 758×** vs only 27× via a CSS variable, so a variable-flip can't cover it. Instead:
one **root-gated override stylesheet** (`html.perf-mode …`) that (a) neutralizes all blur with a single
universal rule and (b) makes glass surfaces opaque. It's **inert when the class is absent → ~zero cost
when off**. This mirrors the app's existing whole-system mode pattern `body[data-focus-mode]` and the
theme system (`html.dark`, `html[data-palette]`).

## Architecture (reuse existing patterns)
- **Root flag:** `html.perf-mode` (+ pairs with existing `html.dark` for dark-aware opacity).
- **FOUC-free pre-paint:** both layouts already run a synchronous `<head>` script that reads
  `localStorage` and sets `html` classes/attrs *before body parse* (`main.php` ~L99-163, theme/palette;
  `secretary_main.php` ~L63+). Add a perf-mode line there reading `localStorage.appPerfMode === '1'`.
- **Persistence:** key-value settings tables already store arbitrary booleans. Doctor → `doctor_settings`
  via `PUT /api/doctor/settings` (`updateDoctorSettings()`); secretary → `secretary_settings` via
  `PUT /api/secretary/settings`. Both gate keys with an `$allowedSettings` allowlist → add one key each.
  **No migration.** localStorage `appPerfMode` is the pre-paint source (like `appTheme`); the server value
  syncs cross-device and is reflected in the settings UI (same dual model theme uses).

## Files to change

### NEW — the core override layer
**`app/Views/layouts/performance-mode.css`** — all rules gated under `html.perf-mode`:
1. **Kill all blur (one rule, covers all 894):**
   `html.perf-mode *, html.perf-mode *::before, html.perf-mode *::after { backdrop-filter:none!important; -webkit-backdrop-filter:none!important; }`
2. **Opaque surface tokens, theme/dark aware:**
   `html.perf-mode{--perf-surface:#fff;--perf-surface-2:#f8fafc;--perf-border:#e2e8f0}`
   `html.perf-mode.dark{--perf-surface:#1e293b;--perf-surface-2:#0f172a;--perf-border:#334155}`
3. **Make glass surfaces solid** — apply `background:var(--perf-surface)!important` to the surface/glass
   selectors. Use `[class*="glass"]` to catch every `*-glass` class (`.note-glass`, `.alert-toast-glass`,
   `.organizer-modal-glass`, filter-popovers, …) in one rule, plus the structural surfaces: `.card`,
   `.modal-content`, `.dropdown-menu`, `.popover`, `.offcanvas`, `.toast`, the sidebar/`.nav-menu`, the
   dock, drawers (notes-drawer / todo-drawer / notification-center), cmdk + global-search panels, ai/chat
   widgets. The `html.perf-mode.dark` token keeps dark mode correct automatically.
4. **Middle-tier extras:** on those surfaces, collapse heavy multi-layer `box-shadow` to one light shadow;
   strip **decorative** `filter: saturate()/contrast()/blur()` on background/gradient FX layers (e.g.
   `.qa-background`, weather/animated-blob layers). Do **not** blanket `filter:none` on `*` (would break
   functional icon/image filters).
5. Load this stylesheet **last** in `<head>` so its `!important` wins the cascade. Selector specificity
   `html.perf-mode *` = (0,1,1) already beats the common `.x-glass{…!important}` (0,1,0); add targeted
   higher-specificity overrides only for any stubborn rule found during verification.

### EDIT — apply globally (both layouts)
- **`app/Views/layouts/main.php`**: (a) in the pre-paint head script (after theme is resolved, near
  `html.classList.toggle('dark', …)` ~L136) add
  `html.classList.toggle('perf-mode', localStorage.getItem('appPerfMode')==='1')` inside the existing
  try/catch; (b) add the
  `<link rel="stylesheet" href="/app/Views/layouts/performance-mode.css?v=<?= filemtime(...) ?>">` as the
  **last** stylesheet in `<head>`.
- **`app/Views/layouts/secretary_main.php`**: identical two edits (head script ~L63+; link last in head).

### EDIT — the switch (top of first settings page)
- **`app/Views/doctor/settings.php`**: add a prominent Performance Mode toggle as the **first row of the
  card body**, above the "Personal Preferences" section (~L46) — reuse the existing
  `class="toggle-switch" id="performanceMode"` + status-label markup pattern (mirror `backToTopDisplay`
  ~L92). Arabic + English copy ("Performance Mode — تسريع الحركة والتنقل بإيقاف تأثيرات الزجاج").
- **`app/Views/secretary/settings.php`**: same toggle at the top (`id="secPerformanceMode"`, mirror
  `secBackToTopDisplay` ~L130).

### EDIT — wire / persist / live-apply (settings JS)
- **`app/Views/doctor/assets/js/settings.js`**: on load, reflect `personalPreferences.performance_mode`
  into the toggle (default `false`) and reconcile `localStorage.appPerfMode` + the `html.perf-mode` class;
  on change → `html.classList.toggle('perf-mode', on)`, write `localStorage.appPerfMode`, and
  `updatePersonalPreference('performance_mode', on)` (existing `PUT /api/doctor/settings` helper, ~L342).
- **`app/Views/secretary/assets/js/settings.js`**: same, persisting via `PUT /api/secretary/settings`.

### EDIT — allowlist one key each (no migration)
- **`app/Controllers/DoctorController.php`**: add `'performance_mode'` to `$allowedSettings` (~L4523).
- **`app/Controllers/SecretaryController.php`**: add `'performance_mode'` to `$allowedSettings` (~L3452);
  `normalizeSecretarySettingValue()` already maps PHP booleans → `boolean` type automatically.

### OPTIONAL — belt-and-suspenders live apply
- **`app/Views/layouts/main.js`** `applyPersonalPreferencesCallback` (+ secretary equivalent): also
  `html.classList.toggle('perf-mode', preferences.performance_mode === true)` so a save propagates without
  a reload even on other tabs. (The settings handler already applies live; this is for consistency.)

## Out of scope / guardrails
- **Keep `/api/organizer/month`** untouched (it feeds the dashboard donut + clock popover — unrelated).
- Pre-auth pages (`login.php`, `welcome.php`) have no toggle and no user setting → unaffected by design.
- No route changes → **both routers (`public/index.php` + root `public_html/index.php`) are unaffected.**

## Verification (local, no benchmark per user)
1. `node --check` the two settings.js files; `php -l` both controllers + both layouts + both settings.php.
2. Doctor: toggle ON in Settings → `document.documentElement.classList` has `perf-mode`; visit
   dashboard / patients / calendar / a modal / a popover / the dock / a drawer / a toast → **no blur**,
   surfaces are solid; flip to **dark mode** → surfaces still correct; **reload** → no flash of glass
   (FOUC-free); navigate to another page → still off; **log out / back in** → persisted (server).
3. Secretary: same toggle on `secretary/settings.php` → applies across secretary pages; scope is
   independent of doctor.
4. Toggle OFF → glass returns everywhere; confirm the override stylesheet is fully inert (no leftover
   opaque/flat surfaces).
5. Spot-check a few high-specificity glass spots (alert toasts, filter popovers, organizer modal) for any
   rule that beat the universal kill; add a targeted override if found.

## Deploy + document
- Deploy: new `performance-mode.css` + 2 layouts + 2 settings.php + 2 settings.js + 2 controllers
  (static CSS/JS auto-bust via `?v=filemtime`; PHP changes → `reload php8.2-fpm`). Smoke via GET on both
  doctor and secretary settings. Both routers unchanged → no `index.php` diff to manage.
- Write **`site/v12_perf/PERFORMANCE_mode.md`** (for ortho replay): the root-gated override pattern, the
  FOUC-free pre-paint hook, the `appPerfMode` localStorage + key-value persistence + allowlist keys, and
  the opaque-surface token approach.
