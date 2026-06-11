# v12_perf — Scroll jank: suppress backdrop-filter blur during active scroll

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## Why
Load-weight fixes (avatar/emoji) do **not** touch scroll jank — that's a per-frame *render* cost. The dashboard's
scroll jank was the What's-New banner's animated box-shadow/gradient (fixed: `DASHBOARD_whatsnew_banner_jank.md`).
The remaining lever on any glass-heavy page: **scrolling past glass forces every `backdrop-filter` surface (263
rules in style.css) to re-blur its shifting backdrop on EVERY frame.** The codebase already suppresses this **during
the sidebar-collapse slide** (`body.sidebar-animating *{backdrop-filter:none}`) — but there was **no equivalent for
scroll**.

## Fix — `body.is-scrolling`
A passive, capture-phase scroll listener adds `body.is-scrolling` at scroll-start and removes it ~140 ms after
scrolling stops; CSS drops `backdrop-filter` on every glass surface while it's set. Blur is imperceptible during fast
motion and restored the instant scrolling stops. Paint-only (no reflow), mirrors the existing `sidebar-animating`
pattern exactly.

```js
(function () {
  var t = null;
  window.addEventListener('scroll', function () {
    var b = document.body; if (!b) return;
    if (!b.classList.contains('is-scrolling')) b.classList.add('is-scrolling');
    if (t) clearTimeout(t);
    t = setTimeout(function () { document.body.classList.remove('is-scrolling'); }, 140);
  }, { passive: true, capture: true });
})();
```
```css
body.is-scrolling *, body.is-scrolling *::before, body.is-scrolling *::after {
  backdrop-filter: none !important; -webkit-backdrop-filter: none !important;
}
```

## Both roles (different assets)
- **Doctor**: rule in `style.css`, JS inline in `main.php` (after main.js).
- **Secretary**: rule in `sec-style.css`, JS inline in `secretary_main.php` (before `</body>`). Secretary uses
  sec-style.css + inline JS, NOT style.css/main.js — so both had to be touched.

## Trade-off (user-accepted)
Glass shows **un-blurred WHILE scrolling**, re-blurs the moment scrolling stops. Same visual behaviour the
sidebar-collapse fix already ships. Accepted in exchange for smooth scroll.

## Verified (CDP, both dashboards)
Glass `blur(22px) saturate(1.65)` at rest → **`none` during scroll** → restored 140 ms after scroll stops;
`is-scrolling` toggles correctly (foreground tab). **Note:** could NOT measure the FPS delta — the local headless
renderer had thermally throttled to a 30 fps cap (a blank page also measured 31 fps), which makes the >20 ms-frame
jank metric meaningless. The mechanism is verified correct and is the same proven technique as the collapse fix
(documented there: collapse <30 fps frames 32% → 4%). Real-device confirmation pending.

## Deploy
4 files (`style.css`, `sec-style.css`, `main.php`, `secretary_main.php`) — in sync with prod, scp, chown/chmod,
`php -l` clean (views), `php8.2-fpm` reloaded. Prod `/doctor/dashboard` → 200. `body.is-scrolling` present in both
deployed stylesheets. Backups `*.bak.20260611f`. See [[roaya-perf-jank-fix]].

## Apply on ortho
Port `body.is-scrolling` rule into ortho's style.css (+ sec equivalent) and the inline scroll listener into its
layout(s). Same pattern as `body.sidebar-animating`.
