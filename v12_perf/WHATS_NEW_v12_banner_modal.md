# v12.0.0 — "Novak Djokovic" banner + What's-New modal (doctor-only)

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12)

## Versioning
- App version labels `v11.0.0` → **`v12.0.0`** across the UI (footers in `main.php`/`secretary_main.php`,
  About/settings, and the `<!-- v11.0.0 … -->` code comments). **Excluded** (kept as v11): the old
  `whats-new-v9-modal.php` (retained on disk) and the **secretary fanfare** (`secretary/dashboard.php` banner +
  `whats-new-secretary-modal.php`) — the secretary is intentionally left out of the v12 fanfare.

## Banner (`doctor/dashboard.php`)
- Pill **`v12.0.0 ND`**, headline **“v12 Novak Djokovic Is Here”**, WhatsApp-focused subcopy.
- `$whatsNewVersion` → `v12_0_0` (re-triggers the dismiss/opt-out gate for every doctor), launch window reset
  (2026-06-12, 7-day TTL).
- CTA `data-bs-target` → **`#whatsNewV12Modal`**.
- **Celebration:** Novak as a faded **watermark** (theme-switched `nd-light.png`/`nd-dark.png`, masked to fade
  left) + an animated **tennis ball** rolling across (`@keyframes wnBallRoll`, in `celebration.css`).
- Banner is rendered only from the doctor dashboard — never the secretary.

## Modal (`layouts/whats-new-v12-modal.php`, NEW — included from `main.php` only)
Self-contained (`.wn12-*` CSS + nav JS), id `#whatsNewV12Modal`. 10 slides:
1. **Intro** — big gradient **“12 · NOVAK · ND”** + Novak portrait, with a tennis celebration layer (floating
   balls, spinning rackets, twinkling sparkles).
2–4. **WhatsApp Integration** (the headline) — one-tap send + consent + Settings gate; ophthalmology templates
   (confirmation/reminder/eye-drops/post-op/follow-up/emergency, auto-signed with clinic name+phone); the secure
   **public patient link** `/p/v/{token}` (Rx + glasses + instructions, expires/revocable/audited, PDF/print).
5. Reports (responsive tables + full chart labels) · 6. Notifications (secretary Arabic prompt + weekly
   reminder) · 7. Appointment & print polish (smart pagination, no table cuts) · 8. Performance · 9. Fixes
   (WhatsApp gate, chat-above-AI, notice-bar clock) · 10. Outro.
- **Every slide stage is an animated CSS mockup** (phone chat, template list, report doc, equalizer chart,
  notification toast, print paper, performance gauge, checklist) — not just an icon. All animations are gated by
  `prefers-reduced-motion`.
- The old v9 modal include in `main.php` was swapped for v12; the v9 file stays on disk (kept, per request).

## Assets
- `app/Views/doctor/assets/svg/nd-light.png` / `nd-dark.png` (Novak line-art, provided by the user, theme-switched).

## Deploy
Doctor PHP views + CSS/JS + the two PNGs (→ reload php8.2-fpm). Secretary layout untouched beyond the footer
version label. Versioned by `?v=filemtime`.
