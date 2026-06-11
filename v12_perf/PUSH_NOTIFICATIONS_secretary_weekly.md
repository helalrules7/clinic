# v12 — Secretary "Enable Notifications" toast (Arabic) + weekly reminder for both roles

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12) · **Ortho:** pending

## What was asked
1. Add the "enable push notifications" prompt for the **secretary** — but in **Arabic** (the doctor already
   has one, in English, in `layouts/main.js`).
2. For **both doctor and secretary**, nudge the user **once a week** about the importance of enabling
   notifications.
3. **If the user is already subscribed**, never remind them.

## Background (doctor, pre-existing)
The doctor's prompt is a **top-center toast** (not a literal modal) built inside the big IIFE in
`app/Views/layouts/main.js` (`showPushNotificationToast` / `initPushNotifications`). It:
- registers `/sw.js`, requests `Notification.requestPermission()`, subscribes via VAPID, and saves the
  subscription to `doctor_settings` (`push_notifications_enabled`, `push_subscription`,
  `dont_ask_push_notifications_browsers`, `push_notification_remind_later`) through `/api/doctor/settings`.
- The secretary layout (`secretary_main.php`) does **not** load `main.js`, so the secretary had **no** prompt
  — even though `secretary_settings` + `/api/secretary/settings` already accept the same four push keys
  (`SecretaryController::updateSecretarySettings`, allow-list lines ~3457-3468). **No PHP change needed.**

## 1) Secretary Arabic toast — NEW files
- **`app/Views/secretary/assets/js/secretary-push-notifications.js`** — self-contained IIFE
  (`window.__secPushInited` guard), mirrors the doctor flow but: talks to `/api/secretary/settings`, all copy
  in Arabic, registers `/sw.js` itself (the secretary didn't before), and carries the weekly + skip-if-subscribed
  gates from the start. Has its own bottom-center success/error feedback toast (`#secPushFeedbackContainer`)
  because the secretary has no global `showToast`.
  - Buttons: **تفعيل** (enable) · **ذكّرني لاحقًا** (remind me later) · **لا تسأل في هذا المتصفح** (don't ask on
    this browser).
- **`app/Views/secretary/assets/css/secretary-push-notifications.css`** — the **glass visual** styling
  (light + dark), header/body/footer, buttons, close button, and a **Y-only** exit animation
  (`@keyframes secPushSlideUp`). Positioning + the slide-down intro come from the already-loaded
  `layouts/push-toast-center.css` (the toast reuses the same `#pushToastContainer` + `.push-notification-toast`
  class names). The doctor's exit animation in `style.css` shifts on X (`translateX(-50%)` on the toast); the
  secretary container is already centered, so the toast must **not** re-shift on X — hence a dedicated keyframe.
- **`secretary_main.php`** wiring: CSS `<link>` right after the `push-toast-center.css` link; `<script defer>`
  right after `clinics-loader.js` (Bootstrap bundle is already loaded above, so `bootstrap.Toast` exists).

CDP-verified (secretary dashboard, fresh session): toast appears after the 3s delay, `dir="rtl"`, full Arabic
copy, all three buttons present, glass styling in dark mode, **zero console errors**.

## 2) Weekly reminder + skip-if-subscribed (BOTH roles)
Applied identically in the doctor `main.js` and baked into the new secretary JS:
- **Cadence 24h → 7 days.** `loadPushSettings` computed `shouldRemind` against a 24-hour window; now 7 days
  (`oneWeekInMs`). So the prompt re-surfaces **at most once a week**.
- **Record on show.** The "remind me later" timestamp (`push_notification_remind_later`) is now written
  **whenever the toast is shown** (doctor: `saveRemindLater()` right after `toast.show()`; secretary:
  `recordPromptShown()`), not only when the user clicks "remind me later". So merely **closing** the toast
  still snoozes it a full week — the weekly cadence holds without nagging on every page load.
- **Skip if subscribed.** Both `initPushNotifications` and `showPushNotificationToast` now early-return when
  `settings.enabled` (i.e. `push_notifications_enabled === true`). Once the user enables notifications anywhere,
  they are never reminded again — satisfying "لو المستخدم مشترك فعليا مفيش داعي لتذكيره".

## Gating summary (per load)
Show the prompt only if **all** hold: push **not** enabled · this browser **not** in the "don't ask" list ·
≥ 7 days since the last prompt. Otherwise stay silent.

## Design decision — "don't ask for this browser" stays a hard opt-out
The weekly reminder targets users who **ignore/close** the prompt or pick "remind me later". A user who
explicitly clicks **"لا تسأل في هذا المتصفح"** is still permanently opted out on that browser (the weekly
nudge does **not** override it). If the product wants the weekly reminder to override even that, drop the
`isDeclined` early-return — flag for review.

## Files
- NEW `app/Views/secretary/assets/js/secretary-push-notifications.js`
- NEW `app/Views/secretary/assets/css/secretary-push-notifications.css`
- `app/Views/layouts/secretary_main.php` (2 includes)
- `app/Views/layouts/main.js` (weekly cadence + skip-if-subscribed + record-on-show)

## Deploy
4 files (2 new static + 1 static main.js + 1 PHP view). Only `secretary_main.php` is PHP → **reload
php8.2-fpm** after this one (the static JS/CSS don't need it). Versioned by `?v=filemtime`.

## Notes / gotchas
- VAPID public key is duplicated as a literal in the secretary JS (same key as `main.js` / doctor
  `settings.js`). If the key ever rotates, update all three.
- Headless Chrome shows the toast fine (it renders **before** asking permission); the actual
  `Notification.requestPermission()` / VAPID subscribe can't be fully exercised headless — manual check on a
  real browser recommended for the end-to-end enable path.
- Secretary settings page (`secretary/settings.php`) still has **no** push toggle UI (doctor does). Out of
  scope here; the toast is the enable path. Could be a follow-up.
