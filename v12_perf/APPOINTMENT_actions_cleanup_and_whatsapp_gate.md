# v12 — Appointment page: action-button cleanup, WhatsApp gating fix, Medical Instructions header

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12) · **Ortho:** pending

Doctor appointment/booking-details page (`/doctor/appointments/{id}`) + patient profile.

## 1) Action-button header cleanup (desktop)
The header action row (`.action-buttons-group`) and the two overflow menus carried print/edit actions that
already live inside each card. Removed from the **header row**: **Edit Consultation**, **Print Report**,
**Print Prescription**, **Print Glasses**, **Print Lab Tests**. Kept: Schedule Followup, Reschedule, Set Alert,
Appointment History, Clinical Dashboard, Send WhatsApp (gated — see §3).

- **Desktop three-dots popover** (`#moreActionsBtn`) **removed entirely** — the row is short enough now that no
  overflow menu is needed on desktop. (`initMoreActionsPopover()` / `updateMoreActionsPopover()` are null-safe,
  so removing the element is safe.)
- **Mobile dropdown** (`.more-actions-btn` "Appointment Actions", ≤576px only): removed Edit Consultation +
  the three Print items; **added a gated "Send WhatsApp"** item. Now: Schedule Followup, Reschedule, Set Alert,
  Send WhatsApp (if enabled), View Patient Profile.

### GOTCHA — the buttons were ALSO injected by JS
`appointment.js` `updateActionPrintButtons()` fetched medications/glasses/lab-tests and **injected** Print
Prescription / Print Glasses / Print Lab Tests buttons into `.action-buttons-group` after load (classes
`btn-outline-warning/info/secondary`, not the PHP `btn-action-*`). So removing the PHP markup wasn't enough —
the JS re-added them on every load. Rewrote `updateActionPrintButtons()` to **only strip** any such buttons
from the header and never add them. (The per-card sync functions `updateMedications/Glasses/LabTestsPrintButton`
are kept — they manage the buttons *inside each card's* header.)

## 2) Lab Tests & Radiology print button — unified style
The Lab card's header print button was `btn btn-sm btn-outline-secondary`; changed to **`btn btn-sm btn-warning`**
to match the Medication-section print button (both PHP markup at the card header and the JS sync function that
recreates it after an AJAX add).

## 3) WhatsApp button gating — REAL BUG FIXED
WhatsApp buttons are wrapped in `if ($__waEnabled)` (whatsapp_enabled setting), but the gate was **broken**:
```php
// BROKEN: selects setting_key too, fetchColumn() returns column 0 = setting_key
$stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key='whatsapp_enabled'");
$stmt->execute();
$__waEnabled = (bool)($stmt->fetchColumn() ?? false);   // fetchColumn() == 'whatsapp_enabled' → ALWAYS true
```
`fetchColumn()` with no arg returns **column 0** = `setting_key` (the string `'whatsapp_enabled'`, always
truthy), so `$__waEnabled` was **always true** — WhatsApp showed even when the setting was OFF. Fixed in all
three sites by selecting only the value:
```php
$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key='whatsapp_enabled' LIMIT 1");
$stmt->execute();
$__waEnabled = ((string)$stmt->fetchColumn()) === '1';
```
Files: `doctor/appointment.php`, `doctor/patient.php`, `secretary/patient_details.php`. (The layout files
`main.php`/`secretary_main.php` were already correct — they use `fetchAll(PDO::FETCH_KEY_PAIR)`.)

**Verified** (curl, local): `whatsapp_enabled=0` → **0** WhatsApp buttons on appointment + patient pages;
`whatsapp_enabled=1` → 5 (appointment) / 8 (patient). No JS injects WhatsApp buttons, so markup gating is
authoritative.

## 4) Medical Instructions card header
- **Buttons moved to the title's row, right-aligned** (were wrapping below). All four cards in this column share
  `card-header > .d-flex (justify-content-between)`, but "Medical Instructions" is the **longest title**, so it
  alone wrapped. Removed the card's `flex-wrap gap-2`, then added targeted CSS (`appointment.css`):
  `#medicalInstructionsCard .card-header > .d-flex { flex-wrap:nowrap }` + a shrinkable/ellipsis-able `h5` +
  `flex-shrink:0` button group + slightly compacted buttons. CDP: at realistic widths the full title shows with
  buttons inline; only ellipsises on a genuinely too-narrow column.
- **Renamed**: Copy Suggested → **Suggest**, Templates → **Templates**, Add Custom → **Add**.
- **Solid colors** (full background, like the sibling cards' button groups): Suggest `btn-info`, Templates
  `btn-secondary`, Add `btn-primary`. Button **IDs unchanged** (`miCopySuggestedBtn` / `miFromTemplatesBtn` /
  `miAddCustomBtn`) so the `medical-instructions.js` handlers keep working — verified all wired.

## Files
- `app/Views/doctor/appointment.php` — header buttons removed, desktop popover removed, mobile dropdown trimmed
  + gated WhatsApp item, Lab print btn → `btn-warning`, WhatsApp gate query fixed, Medical Instructions header.
- `app/Views/doctor/assets/js/appointment.js` — `updateActionPrintButtons()` no longer injects header print
  buttons; Lab card sync button → `btn-warning`.
- `app/Views/doctor/assets/css/appointment.css` — Medical Instructions header nowrap + compact buttons.
- `app/Views/doctor/patient.php` — WhatsApp gate query fixed.
- `app/Views/secretary/patient_details.php` — WhatsApp gate query fixed.

## Deploy
3 PHP views (→ reload php8.2-fpm) + 1 JS + 1 CSS (static). Versioned by `?v=filemtime`.

## Gotchas observed
- **PHP opcache** served a stale compiled `appointment.php` mid-edit during CDP checks — the *served* HTML
  (curl) was the source of truth; re-checks after a moment matched.
- **Chrome HTTP cache** in the persistent CDP user-data-dir served stale assets — use
  `Network.setCacheDisabled(true)` + a cache-buster query when verifying fresh edits.
- These four cards (Medications, Medical Instructions, Glasses, Lab) all live in the same **narrow col-lg-4**;
  header buttons fit on one row only when title + buttons fit the column width.

---

## Follow-up fixes (same day)
- **Infinite recursion / stack overflow** — removing `#moreActionsBtn` meant the inner
  `window.updateMoreActionsPopover` (assigned only inside `initMoreActionsPopover`'s `if (moreActionsBtn)`)
  was never installed, so the wrapper `updateMoreActionsPopover()` resolved `window.updateMoreActionsPopover`
  back to **itself** → "Maximum call stack size exceeded". Fixed: removed the now-pointless call from
  `updateActionPrintButtons()` **and** guarded the wrapper (`fn !== updateMoreActionsPopover`).
- **Medical Instructions Suggest / Templates "did nothing"** — root cause: `window.showToast` is **not**
  defined on the appointment page, so `medical-instructions.js`'s `toast('info'/'success', …)` vanished
  silently; with no templates/suggestions in the DB both buttons only hit that silent info-toast path. Fix:
  gave `toast()` a self-contained Bootstrap-toast fallback, so the buttons now always give visible feedback
  ("No templates — create them from the sidebar page", "No suggestions …"). Button wiring was always fine
  (handlers bound by ID in `medical-instructions.js`); the picker opens normally once templates exist.
- **Session label → modal** (`appointment-tags.js`) — the "+ Label" header action used a native
  `prompt('Session label (shown in header only)')`. Replaced with a proper Bootstrap modal ("Add Session
  Label") whose **input lives in the modal**; Enter or the Add button commits, listeners are rebound per open,
  and it falls back to `prompt()` only if Bootstrap is somehow unavailable.

## Files (updated)
Adds: `app/Views/doctor/assets/js/medical-instructions.js` (toast fallback),
`app/Views/doctor/assets/js/appointment-tags.js` (session-label modal). The recursion guard is in
`appointment.js`.
