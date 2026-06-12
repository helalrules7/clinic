# v12 — WhatsApp modal full-height Message Content + repeating print header (patient+doctor)

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12)

## 1) WhatsApp modal — Message Content uses the full available height (desktop)
`whatsapp-modal.php`: `.wa-editor-panel` is `d-flex flex-column`, but its only child was the `<form>` (a plain
block), so the inner `flex-grow-1` on the message-body div had no flex context to grow within — the textarea
stayed at `rows="6"` with dead space below. Fix: made the form itself a growing flex column —
`<form id="whatsappMessageForm" class="d-flex flex-column flex-grow-1">`. Now the message-body div (and its
`flex-grow-1` textarea) fills the editor panel down to the templates-panel height. Mobile is unaffected: the
existing `@media (max-width: 767.98px)` rule sets `.wa-editor-panel { display:block }` + `.flex-grow-1 { flex:0 0 auto }`,
so the form falls back to natural block flow there.

## 2) Public visit-documents report (the WhatsApp "Comprehensive Visit Report" link, `/p/v/{token}`)
- **Patient + treating-doctor now repeat at the top of EVERY printed page.** The clinic header (logo / name /
  address / phone) already lived in `<thead class="sheet-head">` (`display: table-header-group` under
  `@media print` → repeats per page). The patient/doctor/age/visit-date `.meta` band was in `<tbody>`, so it
  only showed once. Moved the `.meta` band INTO the `<thead>` (a second header row) so it repeats with the
  clinic header.
- **Label "الطبيب:" → "الطبيب المعالج:"** to match the requested wording.
- **Colon placement** is correct (`<b>المريض:</b> value`, `<b>الطبيب المعالج:</b> …`) — the colon sits AFTER the
  label, as required. Verified in the rendered output: `المريض: …`, `السن: …`, `الطبيب المعالج: …`, `تاريخ الزيارة: …`.

## Files
- `app/Views/layouts/whatsapp-modal.php` — form flex-grow.
- `app/Views/print/public/visit-documents.php` — patient/doctor meta into the repeating thead + label wording.

## Deploy
Both PHP → reload php8.2-fpm. (No DB / JS / CSS changes.)

## Follow-up (NOT done — confirm if wanted)
The doctor's **direct** print views (`appointment_report.php`, `medication-prescription.php`,
`glasses-prescription.php`, `lab-tests*.php`, `single-lab-test.php`) use a different structure (a `.header` div
+ a separate patient-info table) and do **not** repeat their header per page. Applying the same
table-header-group treatment there is a separate, larger batch — pending product confirmation.
