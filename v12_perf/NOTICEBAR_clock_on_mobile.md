# v12 — Doctor notice-bar: show the clock on mobile (drop the "Next Appointment:" label)

**Date:** 2026-06-11 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-11) · **Ortho:** pending

## Ask
On mobile the doctor's notice-bar **clock** (`.notice-bar-column-1` = clock icon + live date/time) was hidden.
Make it show — and free the room by **removing the "Next Appointment:" label** (the appointment itself still shows).

## Where it was hidden
`app/Views/layouts/style.css` — the clock (`.notice-bar-column-1`) was `display: none !important` at THREE breakpoints:
`@media (max-width: 768px)`, `(max-width: 600px)`, `(max-width: 385px)` (commented "Priority 4: Clock - Always hidden").

## Change (style.css, doctor only — secretary uses sec-style.css)
At each of the three mobile breakpoints:
- **Show the clock:** `.notice-bar-column-1 { display: inline-flex !important; flex-shrink: 0; }` (was `display:none`).
  `flex-shrink: 0` so the clock never shrinks/clips.
- **Hide the label** (≤768, cascades down): `.notice-bar-appointment-label { display: none !important; }` — frees the
  "Next Appointment:" text width.
- **Make the appointment column give way:** `.notice-bar-column-3` changed from `flex-shrink: 0; min-width: fit-content`
  → `flex-shrink: 1; min-width: 0; overflow: hidden` (≤768 + ≤600), so when the appointment text is long (e.g. the
  "No upcoming appointments" empty state) it shrinks/scrolls instead of pushing the clock off-screen.
  At ≤385px the appointment column is already hidden, so the clock simply has the room.

## Verified (CDP, doctor dashboard)
- 390px: clock **shown + fully visible**, label hidden.
- 360px: clock shown + fully visible, label hidden, no layout overflow.
The appointment slider (`flex:1` inside column-3) shrinks with the column; the clock (flex-shrink:0) always fits.

## Deploy
1 static file (`style.css`, versioned by `?v=filemtime`, no fpm reload). Doctor layout only.
