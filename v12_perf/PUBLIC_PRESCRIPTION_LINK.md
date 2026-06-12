# v12_perf — Public Visit-Documents Link (patient-accessible prescription / glasses / instructions)

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** no (local, verified) · **Ortho:** pending

## Problem
The WhatsApp resolver embedded `/print/prescription/{id}` and `/print/glasses/{id}` links, but `PrintController::__construct()` returns **401** for anyone without a staff session — so a patient opening the link from WhatsApp always saw "Unauthorized". We need a public, no-login, secure way for a patient to view/download their own visit documents.

## Decision (per product owner)
- **Drop** the comprehensive visit-report link from patient messages (not needed).
- Put the **prescription + glasses + medical instructions** for a visit into **one message** via **one public link → one page** that shows whichever of the three exist.

## What was built
1. **Migration** `database/migrations/2026_06_13_prescription_share_links.sql`
   - `prescription_share_tokens` — `token_hash` (sha256 of a 256-bit random token; raw lives only in the WhatsApp message), `appointment_id` (LOOSE, no FK — appointments are hard-deleted), `patient_id`, `clinic_id` snapshot, `created_by`, `expires_at` (default +90d), `revoked`, `used_count`, `max_uses`, plus schema-ready `require_verify`/`verify_method`/`verify_fail_count` (OFF in v1).
   - `prescription_share_access_log` — audits every access outcome (`served|invalid|not_found|revoked|expired|used_up|unavailable|rate_limited`) with IP + UA.
   - Seeds one idempotent template `Visit Documents` (category `documents`).
2. **`WhatsappController`** — `resolveMessage()` now, after resolving a visit's meds/glasses/instructions, calls `mintShareToken($appt,$user)` (only if `appointmentInScope()` — secretaries are clinic-pinned, closing the public-surface IDOR) and emits ONE link `{{visit_documents_section}}` = `/p/visit/{token}` with an adaptive Arabic label. Legacy `{{prescription_section}}`/`{{glasses_section}}`/`{{*_pdf_url}}` now point at the same combined page; the staff `/print/*` links are no longer sent to patients; `{{visit_report_pdf_url}}` is blanked. Added `revokeShare($tokenId)` (staff, clinic-scoped). Also normalized `{{doctor_name}}` to strip a leading "د." (fixed the "د. د." double-honorific across ALL templates).
3. **`PublicShareController`** (NEW, INTENTIONALLY PUBLIC — no `Auth::check`) — `visitDocuments($token)`: shape-validate `/^[a-f0-9]{64}$/` pre-DB → lookup by sha256 → reject revoked/expired/over-uses/ghost-appointment with a uniform 404 neutral page (no enumeration oracle) → renders the combined page → increments `used_count` + audits. Per-IP failure throttle. Self-contained getters (no session trust).
4. **Views** `app/Views/print/public/visit-documents.php` (mobile RTL, all `htmlspecialchars`-escaped, `noindex`, manual "تحميل / طباعة PDF" button — NO auto-print) + `link-invalid.php`.
5. **Routes** added to BOTH `index.php` and `public/index.php` (kept byte-identical): `GET /p/visit/{token}` (public) and `POST /api/whatsapp/share/revoke/{tokenId}` (staff). Controller `require_once`'d in both (matches `WhatsappController` precedent).

## Verification (local, localhost:8080)
- `php -l` clean on all changed/new files; `diff index.php public/index.php` identical.
- Real flow: logged in as `dr_faramawy`, `POST /api/whatsapp/resolve` (appt 3434, template 15) → message contained prescription + instructions + ONE `/p/visit/<64hex>` link; minted exactly 1 token (created_by=2, clinic 1, +90d).
- Opened the minted link **logged out** → HTTP 200, rendered patient/age/doctor (single "د.")/date + prescription table + instructions; glasses appt 3411 rendered the optics table; sections render only when data exists.
- Invalid / not-found / expired / revoked tokens → uniform HTTP 404 neutral page; every outcome written to `prescription_share_access_log`. Staff `/print/glasses/3434` still 401 logged-out (unchanged).

## Not done (separate tracks, from the 33-finding review)
- Broader resolve/getLogs/consent IDOR hardening (only the mint path is scope-checked here).
- Optional phone/DOB verify gate (schema ready, OFF). Cancellation-template seeding + fallback-data fix. Arabic-digit / `patients.js` phone bugs.

## Revision 2 (2026-06-12, same day) — per product feedback
- **One message, not two.** Template retitled `Comprehensive Visit Report` / category **`report`** (was `Visit Documents`/`documents`); body drops the inline `{{instructions_section}}` and keeps only `{{visit_documents_section}}`. That section is now a single line: `إليك رابط تقرير الزيارة ويشمل (مقاس النظارة - التعليمات الطبية - الوصفة العلاجية): <link>` — the parenthetical lists ONLY the documents that exist (order: glasses, instructions, meds).
- **In-visit WhatsApp buttons default to the report.** `appointment.php` lines 251/758/777 now pass `'report'` to `openModal` (the 777 one was a broken `'summary'`). The existing `triggerCompletionModal` already used `'report'`, and the Arabic title map already had `Comprehensive Visit Report → تقرير الزيارة الشامل`, so it all lines up.
- **Privacy confirmation removed.** Deleted `waSensitiveWarning`/`waSensitiveConfirm` block from `whatsapp-modal.php` and all `checkTextSensitivity` gating + the two `console.log`s from `whatsapp.js`. Send is no longer blocked by a sensitivity checkbox.
- **Patient page restyle.** `visit-documents.php` + `link-invalid.php` now use the **Cairo** Google Font and the site's **dark Indigo** palette (bg `#070B14`, card `#131A29`, indigo `#6366F1`/`#818CF8`), show the **clinic logo** (settings `clinic_logo`, in a white chip with `onerror` hide) and the **clinic address + phone** in the header, plus a "تقرير الزيارة" badge. Print CSS inverts to white/ink for clean PDFs. `PublicShareController::getClinicForAppointment` now returns `logo`.
- Section order on the page matches the message: glasses → instructions → meds.

## Revision 3 (2026-06-12) — per product feedback
- **Report now includes التحاليل + الأشعة.** `lab_tests.test_type` enum (`laboratory`/`radiology`) split into two sections; `PublicShareController::getLabTests($apptId,$type)`; mint condition + message label include them. Two-clinic safe.
- **Section order / priority:** الوصفة العلاجية (first/priority) → التعليمات الطبية → مقاس النظارة → التحاليل → الأشعة. Message label parenthetical follows the same order.
- **Print pagination + repeating header.** Page rebuilt as a `<table>` with `<thead class="sheet-head">` so the clinic header (logo + name + per-branch address/phone) **repeats on every printed page**. Content grouped into two sheets — Sheet 1 = prescription + instructions, Sheet 2 = glasses + labs + radiology (`.group-2.has-prev{break-before:page}`); `break-inside:avoid` on sections/tables/rows so nothing splits mid-table.
- **Two-clinic correctness.** `{{clinic_phone}}` placeholder added and resolved from the **appointment's own branch** (`clinics.phone`), not global settings. Same for name/address. Report page already resolves clinic from `appointment.clinic_id`.
- **Templates cleaned up.** Removed Medication Prescription Link, Glasses Prescription Link, Eye Drops Schedule, Investigation Request, (Visit Documents), and de-duplicated Comprehensive Visit Report → one row. Migration does this idempotently (`SET NAMES utf8mb4` to avoid CONCAT collation error).
- **Clinic signature on ALL templates.** Every active template gets `\n\n{{clinic_name}}\n📞 {{clinic_phone}}` appended (idempotent `body NOT LIKE '%{{clinic_phone}}%'`).
- **Secretaries see appointment templates only.** `getTemplates` filters to `category IN (confirmation,reminder,cancellation)` for `role=secretary` — no clinical documents/instructions/reports/warnings. WhatsApp modal alerts now fully bilingual (Arabic when the modal is RTL/secretary).
- **Privacy-confirmation removed** (sensitive-data checkbox + gating gone from modal & JS; debug console.logs removed).
- **Doctor honorific** strip now handles both `د.` and `Dr.` (`/^\s*(?:د|dr)\.?\s*/iu`) in the message and the page.
- **Templates manager modal** (settings) now has dark/light styling (scoped `.dark #templatesManagerModal` overrides).

## Deploy notes
- Apply the migration with the **system-root** DB CLI on prod (app user is PDO-only). Locally: `docker exec -i roaya_db mariadb -uroot -proot hclinic_roaya < database/migrations/2026_06_13_prescription_share_links.sql`.
- Consider a cron to purge expired tokens.
