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

---

## Follow-up (2026-06-11) — clock POPOVER on mobile: height cap + close button (both roles)
Once the clock showed on mobile, tapping it opened the clock/calendar popover, which ran **too tall** — hard to
dismiss (backdrop tap awkward) and not all details were reachable. Fixed for **doctor + secretary** (consistent):

- **Close button** added at the top-right of the popover (`<button class="clock-popover-close"><i class="bi bi-x-lg">`),
  injected in `createClockCalendarPopover()` and wired to `closeClockCalendarPopover()` — in `main.js` (doctor) and
  `secretary-notice-bar.js` (secretary).
- **Height cap + scroll** (mobile `@media (max-width: 900px)`): the popover becomes a flex column
  `max-height: 80vh; display:flex; flex-direction:column; overflow:hidden`; the close button is a non-scrolling flex
  item pinned at the top (`position:static; align-self:flex-end; flex-shrink:0`), and the body
  (`.clock-calendar-popover-content`) takes `overflow-y:auto; flex:1; min-height:0` — so the **close button always
  stays visible while the content scrolls**, and all details are reachable.
- Base `.clock-popover-close` styling (circular glass button, light/dark) added to both `style.css` and
  `secretary-notice-bar.css`.

**Verified (CDP, 390px, both roles):** popover ≤ 80vh (675px), bottom on-screen, close button visible, and clicking
it dismisses the popover.

**Deploy:** `main.js` + `style.css` (doctor) + `secretary-notice-bar.js` + `secretary-notice-bar.css` (secretary) —
static assets, versioned, no fpm reload.

---

## Follow-up (2026-06-12) — appointments popover empty state full-width centered (both roles)
In the "Upcoming Appointments" popover, the empty/error message (`.appointments-empty` / `.appointments-error`:
icon + `<span>`) was a centered flex column, but the `<span>` shrank to its content so a wrapped second line
("No upcoming / appointments") looked left-shifted. Fixed in `style.css` (doctor) + `secretary-notice-bar.css`
(secretary): the container gets `width:100%; text-align:center` and the `span` gets `width:100%; text-align:center`
— so the message spans the full popover width and centers under the icon. CSS only.
