# v12 — WhatsApp modal full-height Message Content + repeating print header (patient+doctor)

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes (2026-06-12)
**Files:** `app/Views/layouts/whatsapp-modal.php`, `app/Views/print/public/visit-documents.php`

## Final behavior (TL;DR — the rest of this doc is the iteration log + gotchas)
- **WhatsApp modal:** the *Message Content* textarea fills the full editor-panel height on desktop (the form is a
  growing flex column); mobile unchanged.
- **Visit report (`/p/v/{token}` — the WhatsApp "Comprehensive Visit Report"):**
  - **Header** = logo + clinic name + address + phone **and** `المريض:` + `الطبيب المعالج:` + age + visit date.
    Colons sit **after** the label. Header shows **once on screen**, and repeats **at the top of every page** in
    the **PDF download and native print** only.
  - **Smart pagination:** the five sections (Rx, medical instructions, glasses, labs, radiology) flow as siblings
    and **reflow** to fill pages — if a section is absent the next pulls up; a section only moves to the next page
    when it doesn't fit, and **a table is never split across two pages**.
  - **Mechanisms (key gotcha):** native **print** repeats the header via the table `<thead>`
    (`display:table-header-group`, which also auto-reserves space); the **PDF** (html2pdf/html2canvas) can't
    repeat a thead, so it renders the body from `#reportBody` (excludes the thead) and **stamps a rasterised
    header image on every page** via `pdf.addImage`, with `pagebreak: avoid-all` to prevent cuts. A `beforeprint`
    + injected `@page{margin-top}` + `position:fixed` approach was tried and **rejected** (Chrome doesn't reliably
    apply `@page` injected at `beforeprint`, which hid the first section).

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

---

## Fix (header was NOT repeating in the PDF / page 2)
First attempt put the header in a table `<thead class="sheet-head">` (`display: table-header-group`). That repeats
under **native** `@media print` only — but the "تحميل PDF" button uses **html2pdf (html2canvas)**, which
rasterises the DOM to one tall canvas and slices it into pages, so a `<thead>` renders **once** and page 2 had no
header. Fix: dropped the `<table class="sheet">` wrapper and made the header a reusable PHP closure
(`$renderSheetHeader()`) emitted at the top of **each sheet** — once before the body, and again inside `group-2`
(the glasses/labs/radiology sheet, which carries `break-before: page`) whenever sheet 1 (`$group1`) exists. So the
header now appears on every page in **both** html2pdf export and native print. Single-sheet reports render exactly
one header. `.sheet-header { break-inside: avoid }` keeps it intact across boundaries. Verified by curl: 1 sheet →
1 header; the table wrapper is gone; colons (`المريض:` / `الطبيب المعالج:`) intact.

## Refinement — repeated header is PRINT/PDF ONLY (not in the on-screen link preview)
The per-sheet header copy made the header appear twice in the **web link preview** too. The repeated copies
(sheet 2+) now carry a `sheet-header--print-only` class — `display:none` by default, shown only under
`@media print` and `body.pdf-mode` (the html2pdf rasterise pass). The FIRST header (top of the document) always
shows. Net: link preview = one header; PDF/print = header atop every page.

---

## Smart pagination + header stamped on every page (PDF + print)
**Goal:** sections reflow to fill pages and a table is **never** split across two pages; the clinic+patient header
shows atop **every** page — but only in the PDF/print, not the on-screen preview.

### Reflow (no rigid grouping, no cutting)
Removed the fixed `group-1` (Rx + instructions) / `group-2` (glasses + labs + radiology) split with its forced
`break-before: page`. All five sections (Rx, instructions, glasses, labs, radiology) now flow as **siblings**
inside `#reportBody`. Each `.section` / `.dtable` / `ul.instr li` keeps `break-inside: avoid` (already present in
both `@media print` and `body.pdf-mode`), and the html2pdf `pagebreak` mode is now `['css','legacy','avoid-all']`.
Result: when (say) instructions are absent, glasses pulls up onto page 1; a section only moves to the next page
when it doesn't fit, and is never cut.

### Header on every page
- **Download PDF (html2pdf):** the header (`#reportHeader`) is rasterised once with `html2canvas` → JPEG, the PDF
  body is rendered from `#reportBody` with the top margin reserved (`margin-top = headerHeight`), then the header
  image is **stamped on every page** via `pdf.addImage` in a `.get('pdf').then(…)` pass before `.save()`. Using an
  image (not `jsPDF.text`) keeps the Arabic intact. Falls back to a single-header render if `html2canvas` isn't
  exposed.
- **Native print (`window.print()`):** a `beforeprint` handler measures the live header height, injects
  `@page{margin-top:Nmm}` + `#reportHeader{position:fixed;top:0}` so the browser repeats the running header on
  every page; `afterprint` removes it.
- **On-screen link preview:** unchanged — one header at the top (the fixed/stamped repetition only applies to
  print/PDF).

## Fix — native print was dropping sections (only glasses showed)
The first cut replaced the auto-repeating `<thead>` with a `beforeprint` `position:fixed` header +
JS-injected `@page{margin-top}`. Chrome does **not** reliably apply an `@page` rule injected during
`beforeprint`, so the tall fixed header overlapped the first section (the Rx table was hidden behind it; only
the lower glasses table peeked out). Reverted native print to the **proven `<thead class="sheet-head">` +
`display:table-header-group`** mechanism: the header repeats AND reserves its own space automatically per page,
and all sections sit in one `<tbody>` cell so they still reflow and never cut. The html2pdf path is unchanged —
it renders from `#reportBody` (which excludes the thead) and stamps the rasterised header image on every page,
so the two mechanisms don't double up. Removed the `beforeprint`/`afterprint` hack entirely.
