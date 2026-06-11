# v12_perf — Dashboard render jank: freeze the What's-New banner's paint-animations

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## Symptom
User reported the app felt heavy in render / "frames drop a lot," attributed to `main.js` + the layout.

## Diagnosis (CDP A/B isolation, real GPU headless Chrome)
Measured idle **and** scroll jank (% of rAF frames > 20 ms) per page:

| Page | idle jank | scroll jank | infinite anims | glass surfaces |
|------|-----------|-------------|----------------|----------------|
| settings | 0% | — | 40 | 89 |
| calendar | 0% | 2% | 20 | 97 |
| patients | 0% | 0% | 20 | 118 |
| **dashboard** | **25-32%** | **37-40%** | **44** | 140 |

**The layout shell is NOT the problem** — every non-dashboard page holds 60 fps with up to 118 `backdrop-filter`
glass surfaces. **Only the dashboard janks.** A/B: disabling *either* ALL animations OR ALL `backdrop-filter` on the
dashboard took it to **0%** — the classic *animation × backdrop-filter re-blur* interaction (same mechanism as the
2026-05-24 sidebar-collapse fix, but here **constant**).

The dashboard's distinguishing feature is the **`.whatsnew-notice` celebration banner** (`celebration.css`,
markup in doctor + secretary `dashboard.php`), which adds ~22 perpetual animations. Isolating them proved the cost
is **2 of them** — both *paint* animations on the banner container:

- **`wnNoticeShadowPulse`** (6s) animated `box-shadow` with a **60-72px blur halo**. box-shadow renders *outside*
  the element (`overflow:hidden` does not clip it), so the animated halo overlapped the **glass stat-cards below the
  banner** → forced them to re-blur **every frame**.
- **`wnCelebrateBgSweep`** (14s) animated `background-position` on a `background-size:320%` gradient → **full-banner
  repaint every frame**.

The decorative `wn-aurora / wn-orb / wn-sparkle / wn-shimmer / wn-border-beam` animations are `transform`/`opacity`
(compositor-only) — A/B-confirmed they do **not** cause jank. They stay.

A/B (fresh cold Chrome, dashboard): baseline idle 32% / scroll 37% → **container paint-anims off → idle 18% / scroll 6%**.

## Fix (`celebration.css`, `.whatsnew-notice.whatsnew-celebrate`)
Froze the two paint-animations; kept everything else:
- Removed the `animation: wnCelebrateBgSweep…, wnNoticeShadowPulse…` declaration.
- Static `box-shadow` (the existing base value = the animation's resting/0% frame).
- `background-size: 320% → 100%` so the **whole** indigo→purple→pink→rose gradient shows at once (the most faithful
  static representation; a frozen 320% slice would show only ~⅓ of the colours).

Visually the banner looks the same at rest and stays lively via its 16 remaining cheap animations.
The `@keyframes wnCelebrateBgSweep` / `wnNoticeShadowPulse` are left defined-but-unused (harmless, easy revert).

## Verified (CDP, fresh Chrome, doctor dashboard)
The two animations are gone (`hasSweep:false, hasShadowPulse:false`), 16 decorative animations still run, and
**idle jank 32% → 0%, scroll jank 37% → 0%**. Banner screenshot before/after = same look (full vibrant gradient).
Shared `celebration.css` ⇒ fixes **both doctor + secretary** dashboards in one change.

## Deploy
1 static CSS file. scp (confirmed in sync with prod first) → chown hclinic:hclinic → chmod 644. **No php-fpm reload**
(CSS only). Cloudflare caches the bare URL but the page links it with `?v=filemtime`, which changed → real browsers
fetch fresh. Verified: cache-busted public URL → 200 + contains the freeze. Backup `celebration.css.bak.20260611c`.

## General rule (for ortho + future)
**Never animate `box-shadow` or `background-position` perpetually on/near a glassy (backdrop-filter) page.** A large
animated box-shadow halo over glass, or a full-element repaint, forces every overlapping glass surface to re-blur
every frame. Use `transform`/`opacity` (compositor) for perpetual decorative motion, or freeze the effect.
See [[roaya-perf-jank-fix]] (the scroll + sidebar-collapse predecessor).
