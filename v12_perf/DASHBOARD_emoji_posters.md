# v12_perf — Chat/dashboard: reaction emoji load tiny posters, not full animated webp

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## The problem
The chat widget (`chat-widget.js`, loaded in the layout on every page) builds its reaction set
`['👍','❤️','😂','😮','😢','🙏']` into the dock during `build()` (the `reactPop` popover). `animEmojiImg()` used the
**full animated Noto WebP** as the initial `<img src>` — and those files are **70-310 KB each** (the whole
`assets/emoji/` set is 174 files / 25 MB). So ~**700 KB of animated emoji loaded eagerly on the dashboard** (and
every page), just to have the reaction picker ready. `animEmojiImg` also renders **message reactions** and
**emoji-only "big" messages**, so any of those loaded full webp too.

## The fix (one function — `animEmojiImg`)
The emoji set already ships **poster first-frames** (`emoji/poster/*.webp`, ~1-2 KB) that the big picker grid uses
as a static default. `animEmojiImg` now uses the **poster as the initial `src`** for locally-hosted emoji, keeping
`data-anim` = the full webp. The 3-loop animation still plays on hover/tap via `replayEmoji` (already wired:
mouseenter on react buttons / message emoji, and on react-pop show). CDN-fallback (non-local) emoji are unchanged
(no poster exists for them).

```js
var hasLocal = !!EMOJI_LOCAL[cp];
var initialSrc = hasLocal ? emojiPosterSrc(cp) : emojiSrc(cp);   // was always emojiSrc(cp)
// …<img src=initialSrc … data-anim=emojiSrc(cp) data-poster=emojiPosterSrc(cp)>
```

## Result
**Dashboard reaction emoji: ~700 KB → 13 KB** (6 × ~2 KB posters). CDP-verified: all 6 load from `poster/`,
`data-anim` still points to the full webp so hover animates. Full webp now loads **on demand** (first hover / react-pop
open), not on page load.

## Deploy
1 JS file (`chat-widget.js`) — confirmed in sync, scp, chown/chmod, **no fpm reload** (JS, versioned by `?v=filemtime`).
Prod has all **173 posters** (`app/Views/doctor/assets/emoji/poster/`, served from `/app/Views/…` — outside
`uploads/` so no .htaccess restriction); `poster/1f44d.webp` → 200, 1.9 KB. Backup `chat-widget.js.bak.20260611e`.

## Combined with the avatar fix, the dashboard's image weight on load
avatar **806 KB → ~5 KB** + reaction emoji **~700 KB → 13 KB** ≈ **1.5 MB → ~18 KB**.

## Note — eye-tools was already handled
`ophthalmology-tools.js` (266 KB) is **already gated** in `main.php` (`$__showEyeTools` =
`^/doctor/(appointments|patients)/\d+`) so it loads ONLY on the appointment + patient-profile detail pages — verified
it does NOT load on the dashboard/lists. (This was the campaign's earlier "eye-tools extraction"; no change needed.)

## Apply on ortho
If ortho's chat ships the same animated-emoji set + `poster/`, make the same `animEmojiImg` poster-src change.
