# v12 — What's-New modal: RF (Roger Federer) codename + slide height-fill

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

> Scope note: this is a **naming / cosmetic** change only. The real version variable
> `$whatsNewVersion = 'v11_0_4'` (in `doctor/dashboard.php`, drives the show-once/dismiss logic) was **NOT changed** —
> only the displayed strings. Next version (v12) will be codenamed "Djokovic"; that rename happens later.

## 1) Version codename "Roger Federer" (RF)
**Banner** (`doctor/dashboard.php` + `secretary/dashboard.php`):
- pill badge: `v11.0.0` → `v11.0.0 (RF)`
- headline: `Real-time chat is here!` → `v11 Roger Federer Is Here`

**What's-New modal — first slide** (`app/Views/layouts/whats-new-v9-modal.php`): directly under the animated
version number (`.wn-v11-number`) added a codename block — the **RF portrait + "Roger Federer"**:
```html
<div class="wn-v11-codename">
  <span class="wn-rf-portrait">
    <img class="wn-rf-img wn-rf-light" src="/app/Views/doctor/assets/svg/rf-light.webp?v=…" alt="Roger Federer">
    <img class="wn-rf-img wn-rf-dark"  src="/app/Views/doctor/assets/svg/rf-dark.webp?v=…"  alt="Roger Federer">
  </span>
  <span class="wn-rf-name">Roger Federer</span>
</div>
```
- The portrait **switches with the theme**: `.wn-rf-dark { display:none }` by default; `.dark .wn-rf-light { display:none }`
  + `.dark .wn-rf-dark { display:block }`. So light mode shows the dark-line art, dark mode shows the light-line art.
- **Icon assets** (user-provided, line-art portraits, 452×591 webp): `app/Views/doctor/assets/svg/rf-light.webp`
  (74 KB) + `rf-dark.webp` (21 KB). Served from `/app/Views/doctor/assets/svg/…` (outside `uploads/`, so no
  .htaccess extension restriction). The user dropped the originals in `storage/logs/`; they were copied into the
  served asset dir.
- (The first-slide `<h3>` "Welcome to v11.0.0 RF" was the user's own edit — left as-is.)

## 2) Slides were leaving an empty bottom gap → fill the height
`.wn-track` is a flex row; slides are `min-width:100%` flex items, so **every slide stretches to the tallest slide's
height** (the first/version slide, ~613 px on desktop because of its 360 px version stage + the new RF codename).
Shorter slides (e.g. the prefetch slide, ~210 px stage) were top-aligned → a large empty area at the bottom.

**Fix** (CSS in the modal's `<style>`):
- `.wn-slide` → `display:flex; flex-direction:column; justify-content:center;` so each slide **centers its content
  vertically** in the shared height (balanced top/bottom instead of bottom-heavy).
- Modest enlargement (per request — "a bit bigger mockups / words / line-spacing"):
  - default `.wn-stage` height `210px → 238px`; `.wn-slide-prefetch .wn-prefetch-stage` `128px → 152px`
  - `.wn-slide h3` `1.25rem → 1.42rem` (more margin); `.wn-slide p` `.92rem → 1.02rem`, `line-height 1.55 → 1.72`,
    `max-width 460 → 500px`
- Kept it **modest** on purpose: the desktop modal has no max-height (`.modal-content { overflow:hidden }`), so
  over-enlarging would overflow/clip on small laptops. (The mobile `@media ≤575.98px` already has
  `max-height: calc(100dvh - 9.5rem)` + scroll, untouched.)

**Verified (CDP, desktop 760px):** slide 2 content is now centered (~108 px gap each side, was all at the bottom)
with the bigger mockup + text filling the slide.

## Deploy
`whats-new-v9-modal.php` (+ the two banner views, dashboard.js/css, and the 2 RF webp) scp'd, chown hclinic:hclinic,
chmod 644, `php -l` clean, `php8.2-fpm` reloaded. RF icons serve 200. Backups `*.bak.20260611h/i`.
Commits: `6216fde` (RF codename + mobile), `7b7666a` (slide fill).

## Not done / open
- The **secretary** What's-New modal is a separate file (`whats-new-secretary-modal.php`, Arabic) — the RF portrait +
  "Roger Federer" + the slide-fill were NOT mirrored there yet. Mirror if desired.
- Spelling: used **"Roger Federer"** (correct), not the typed "Rojer".
