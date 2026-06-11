# v12_perf — Dashboard/shell: avatar thumbnails (806 KB profile photo → ~5 KB)

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## The problem (found by a real CDP load profile of the dashboard)
Profiling `/doctor/dashboard` load, the biggest single resource was **`user_2_*.jpg` = 806 KB** — the doctor's
profile photo, stored at **2048×2048** and served as-is for a **~48 px** sidebar avatar (≈99.7 % wasted bytes). The
same photo is reused in the dock and a 200 px hover preview. The avatar is in the **layout sidebar**, so this hit
every page (doctor + secretary). (Honourable mention: ~700 KB of emoji-as-images from the chat widget — see Follow-ups.)

## Fix — `avatar_thumb()` (app/Lib/global_helpers.php)
A global helper (already autoloaded by both routers) that **lazily generates + disk-caches** a small, square,
centre-cropped thumbnail next to the original under a `thumbs/` sub-folder, and returns the thumbnail URL:

- First render per (user, size) runs a one-time GD resize (~50-150 ms); every later load is a **static cached file**.
- **Output is JPEG, not WebP** — `public/uploads/.htaccess` only serves an extension allowlist
  (`jpg|jpeg|png|gif|svg|pdf|doc|docx`) and 403s the rest, so a `.webp` thumb under `uploads/` would be forbidden.
- The **thumb URL is derived from the original's URL** (same dir + `/thumbs/`) so it inherits the existing docroot
  mapping; the **filesystem path uses `__DIR__`** (`…/app/Lib/../../public`).
- **Returns the original path unchanged on ANY failure** (no GD, missing file, write fail, path-traversal, non-local
  image) — it can never break an avatar. Path-traversal guard: the resolved source must live inside
  `public/uploads/users/`.

Wired in `main.php` (doctor) + `secretary_main.php`: sidebar + dock avatars → **96 px (~4-5 KB)**; the 200 px hover
preview → **256 px (~18 KB)**. `data-profile-image` left as the original (no JS reads it; it's not an `<img src>` so
it never downloads).

## Result
`user_2` avatar: **806 KB → 4.4 KB (96 px) + 18 KB (256 px)** ≈ **97 % smaller**. CDP-verified: avatar renders
correctly (circular, centre-cropped, crisp), the 806 KB original no longer loads, biggest image on the page is now
the 18 KB preview. Secretary dashboard likewise thumbnails its avatar (`user_7_*_t96.jpg`). Works for any user.

## Deploy (2026-06-11)
3 PHP files (`global_helpers.php`, `main.php`, `secretary_main.php`) — confirmed in sync with prod first, scp'd,
chown hclinic:hclinic, chmod 644, `php -l` clean, **php8.2-fpm reloaded** (OPcache, since global_helpers + views).
Prod infra confirmed: `public_html/public/uploads/users/` is 0777 (php-fpm can create `thumbs/`), the same 806 KB
avatar is on prod, and the prod `.htaccess` allowlist permits jpg / denies webp (matches the JPEG choice). Thumbs
generate on first authenticated render; safe fallback means avatars work even if generation fails. Smoke: prod
`/doctor/dashboard` → 200. Backups `*.bak.20260611d`.

## Follow-ups (not done)
- **Chat emoji-as-images** — `app/Views/doctor/assets/emoji/` is **174 files / 25 MB** of animated Noto WebP
  (70-310 KB EACH). The picker already lazy-loads tiny `poster/` first-frames, but reactions rendered in message
  lists/previews use the **full animated** webp → ~700 KB can load on the dashboard. Fix: render reactions with the
  poster (animate on hover) instead of the full webp; or lazy-load. Content-dependent, lives in `chat-widget.js`.
- **Generate the thumbnail at upload time too** (DoctorController:735 / SecretaryController) so the very first render
  isn't the one paying the resize — minor, the lazy path already caches.
- **32 API calls on dashboard load** — candidate for batching / further below-the-fold lazy-loading.

## Apply on ortho
Port `avatar_thumb()` into ortho's `global_helpers.php` + swap its sidebar/dock avatar `<img src>`. Keep JPEG output
unless ortho's `uploads/.htaccess` allows webp. Confirm ortho's `uploads/` is writable by its php-fpm user.
