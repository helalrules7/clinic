# v12_perf — WhatsApp Integration & Clinics/Doctor Profile Management

**Date:** 2026-06-12 · **Branch:** v_12_perf · **Shipped to prod:** yes · **Ortho:** pending

## Context
1. **WhatsApp Patient Communication MVP**: There was a requirement to add a client-side Patient Communication module with WhatsApp deep links (using `wa.me`). This allows doctor/secretary staff to send pre-filled, personalized messages directly to patient phones without Meta API costs or setup.
2. **Clinics Management**: The clinic now operates multiple branches (e.g. Riyadh, Kafr El-Sheikh). A centralized clinics settings list was needed to view and update branch details (addresses and phone numbers).
3. **Doctor Profile Arabic Name**: The doctor's display name needs to support Arabic separately from English, specifically for patient-facing WhatsApp messages and templates (e.g. `{{doctor_name}}`).

## Implementation Details

### 1. Database Schema Extensions
- Added column `whatsapp_consent` to `patients` table.
- Added column `display_name_ar` to `doctors` table right after `display_name` to store the Arabic name.
- Created `communication_templates` table to store pre-defined templates (e.g., appointment confirmations, reminders, drop schedules, surgical instructions).
- Created `patient_communications` table to log generated messages, ensuring patient privacy and staff audit trails.

### 2. Backend Routing & APIs (`ApiController.php`, `WhatsappController.php`)
- Integrated routes in root `index.php` and `public/index.php`:
  - `GET/POST /api/clinics` endpoints to retrieve and edit clinic branches.
  - `GET /api/whatsapp/templates`: Fetches active messaging templates.
  - `POST /api/whatsapp/resolve`: Resolves placeholders (`{{patient_name}}`, `{{doctor_name}}`, `{{clinic_name}}`, `{{clinic_address}}`, etc.) including absolute URLs for prescriptions, glasses prescriptions, and patient records.
  - `POST /api/whatsapp/consent`: Updates patient opt-in preferences.
  - `POST /api/whatsapp/log`: Logs generated message history.
- Resolves clinic details dynamically based on the appointment's `clinic_id` (or fallback to logged-in user's clinic), formatting the clinic name as `"مركز رؤية - " . $branchCity` and address with contact info.
- Resolves `{{doctor_name}}` using the Arabic display name (`display_name_ar` if present), falling back to English.

### 3. Frontend & Settings UI
- **Clinics Manager** in Settings (`settings.php`, `settings.js`):
  - Displays a responsive list of registered clinics with editing controls.
  - Opens a Bootstrap modal to modify Arabic/English names, addresses, and phone numbers.
  - **No-Alert Update Flow**: Removed the redundant browser `alert()` popups upon successful clinic details updates, enabling a smoother, silent settings reload.
- **Doctor Profile** (`profile.php`, `DoctorController.php`):
  - Added display and input fields for Arabic Display Name, clearly marking it as being used for communication and templates.
- **WhatsApp Modal Integration** (`whatsapp-modal.php`, `whatsapp.js`):
  - Embeds a dual-pane responsive modal (RTL Arabic for secretaries, LTR English for doctors).
  - Prompts consent notices, categorizes templates, and filters sensitive clinical data with a privacy verification checkbox before launching the WhatsApp link.
  - Triggers confirmation popups upon booking creation and visit completion, using a custom, role-colored Bootstrap modal (`whatsappConfirmModal`).
- **Egypt Country Code Autocomplete & Phone Number Fix**:
  - Cleans input numbers by stripping spaces, hyphens, and parentheses.
  - If a patient's phone number is missing an explicit country key (`+` or `00`), it automatically prepends Egypt's country code (`+2`).
  - Specifically, formats Egyptian mobile formats (starting with `01` or `1`) to standard country-code format (e.g. `01xxxxxxxxx` becomes `+201xxxxxxxxx`).
  - Handled formatting seamlessly during modal initialization as well as WhatsApp link generation to ensure links are never broken.

### 4. Calendar Action Buttons (Re-sending confirmations)
- **Doctor Calendar (`calendar.js`, `calendar.css`)**:
  - Injected a WhatsApp button next to the standard patient view button in the `.appointment-actions` section of the card header.
  - Visible only when `window.WHATSAPP_CONFIG.enabled` is `true`.
  - **Aesthetic Alignment**: Styled in `calendar.css` to use a solid WhatsApp brand color background (`#25D366`), transition effects, opacity `0.9` (hovering to `1` with translateY transformation and brand shadow), and a matching dark mode hover state (`#128C7E`). This perfectly mirrors the solid pill layout of sibling action buttons (view medical history, view profile, edit, delete).
- **Secretary Calendar (`bookings.js`, `bookings.css`)**:
  - Injected a WhatsApp button next to the standard profile view button in `.appointment-actions` of the card header.
  - Visible only when `window.WHATSAPP_CONFIG.enabled` is `true`.
  - Localized tooltip to Arabic: `data-bs-title="تأكيد الحجز عبر الواتساب"`.
  - **Aesthetic Alignment**: Added CSS styling in `bookings.css` so `.send-whatsapp-btn` uses a brand-specific WhatsApp gradient (`linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important`) with identical card dimensions, border radius (10px), box shadows, hover-brightness adjustments, and pure white icons to look completely consistent with other secretary actions.

### 5. Automatic Appointment Cancellation Notifications
- **Cancellation Database Template**:
  - Inserted a new default template in `communication_templates` table with title `Appointment Cancellation` and category `cancellation`:
    `مرحبًا {{patient_name}}،\nنود إفادتكم بأنه تم إلغاء موعد حضرتك مع د. {{doctor_name}} الذي كان مقررًا يوم {{appointment_date}} الساعة {{appointment_time}}.\nلأي استفسار أو لإعادة جدولة الموعد، برجاء التواصل معنا.\nشكراً لتفهمكم.`
- **Fallback Resolution logic (`resolveMessage` API)**:
  - Since appointments are hard-deleted on deletion, placeholders cannot be queried post-delete.
  - Upgraded `/api/whatsapp/resolve` to support receiving fallback inputs in the request body (e.g. `appointment_date`, `appointment_time`, `doctor_name`, `clinic_id`) and resolving placeholders using them if the database record is missing.
- **Auto-Trigger Integration**:
  - **Doctor Calendar (`calendar.js`)**: Updated `deleteAppointment()` to gather patient ID, date, time, doctor name, and clinic ID. When the delete API succeeds, it pops up a custom Bootstrap confirmation asking if the user wants to notify the patient of the cancellation, opening the WhatsApp modal pre-filled with the `cancellation` template on consent.
- **Secretary Calendar (`bookings.js`)**: Similarly updated `deleteBooking()` and `confirmDeleteBooking()` to pass and store fallback data, triggering the same pre-filled WhatsApp cancellation modal after successful deletion.

### 6. Comprehensive Visit Report & Visit Completion Flow
- **Template Deletion**:
  - Deleted the obsolete `Pupil Dilation Instructions` template from the database since it is no longer needed.
- **Dynamic Visit Report Template**:
  - Updated the `Comprehensive Visit Report` template in `communication_templates` to support conditional layouts:
    `مرحبًا {{patient_name}}،\nإليك تقرير الزيارة الشامل لزيارتك لعيادة د. {{doctor_name}} بتاريخ {{appointment_date}}.\n\n{{prescription_section}}{{glasses_section}}{{instructions_section}}\nيمكنك عرض وتحميل التقرير الطبي الكامل عبر الرابط التالي:\n{{visit_report_pdf_url}}`
- **Dynamic resolveMessage PHP Logic**:
  - In `WhatsappController.php`, resolved `{{prescription_section}}` dynamically: if prescriptions were written for the visit, it inserts the label and `{{prescription_pdf_url}}` link, else it defaults to an empty string.
  - Resolved `{{glasses_section}}` dynamically: if a glasses prescription was registered, it inserts the label and `{{glasses_pdf_url}}` link, else it defaults to an empty string.
  - Resolved `{{instructions_section}}` dynamically: if any medical instructions were recorded, it converts them to text and prepends them, else it defaults to an empty string.
- **Auto-Trigger Update (`whatsapp.js`)**:
  - Changed the default category in `triggerCompletionModal` from `'instructions'` to `'report'`. This ensures that when a doctor completes a visit, the modal auto-selects the **Comprehensive Visit Report** template rather than obsolete instructions.
  - Updated the confirmation message dialog to clearly state that the comprehensive report (with links and instructions) will be prepared for sending.

## Verification
- Linted PHP files (`DoctorController.php`, `WhatsappController.php`) successfully.
- Verified database modifications and verified that `display_name_ar` is saved.
- Verified clinics retrieve/update endpoints function correctly.
- Placeholders resolve dynamically to output customized branch names and details (e.g., `مركز رؤية - كفر الشيخ` / `مركز رؤية - الرياض`).
- Verified phone formatting helper correctly normalizes input formats and handles local Egyptian numbers.
- Verified doctor calendar rendering displays the WhatsApp button matching the solid pill aesthetics of neighboring buttons.
- Verified secretary bookings calendar renders the button as a solid WhatsApp gradient pill that aligns perfectly with sibling buttons.
- Verified insertion of the `cancellation` template into `communication_templates` database table.
- Verified that deleting an appointment on doctor/secretary calendars successfully prompts the user to send a cancellation WhatsApp message and resolves the template variables using fallback parameters post-delete.
- Verified deletion of the `Pupil Dilation Instructions` template and update of `Comprehensive Visit Report` template in the database.
- Verified that completing a visit prompts the user with the report option, pre-selects the comprehensive report, and dynamically injects the prescription links, glasses prescription links, and medical instructions only if they were recorded during the visit.

---

# Addendum — Public Visit-Report Link + WhatsApp UX Overhaul (2026-06-12)

**Branch:** v_12_perf · **State:** built & verified locally (localhost:8080), NOT yet deployed to prod · **Migration:** `database/migrations/2026_06_13_prescription_share_links.sql` · **Companion runbook:** `v12_perf/PUBLIC_PRESCRIPTION_LINK.md`

## 0. Why
A multi-agent review of the WhatsApp feature surfaced a blocking problem: the prescription/glasses links the resolver embedded pointed at `/print/prescription/{id}` & `/print/glasses/{id}`, which `PrintController::__construct()` gates with a **401** for anyone without a staff session — so a patient opening the link from WhatsApp always hit "Unauthorized". The review also flagged a critical PHI IDOR in `resolveMessage` and several UX issues. This addendum is the fix + the product changes that followed.

## 1. Public, patient-accessible visit report (token link)
- **New tables** (`2026_06_13_prescription_share_links.sql`):
  - `prescription_share_tokens` — `token_hash` (SHA-256 of the raw token; raw lives only in the WhatsApp message), `appointment_id` (LOOSE, no FK — appointments are hard-deleted), `patient_id`, `clinic_id` snapshot, `created_by`, `expires_at` (default +90d), `revoked`, `used_count`, `max_uses`, and schema-ready `require_verify`/`verify_method`/`verify_fail_count` (OFF in v1).
  - `prescription_share_access_log` — audits every access outcome (`served | preview | invalid | not_found | revoked | expired | used_up | unavailable | rate_limited`) with IP + User-Agent.
- **`WhatsappController`**: `mintShareToken($appt,$user)` (mints only when `appointmentInScope()` — secretaries are clinic-pinned, closing the public-surface IDOR), `appointmentInScope()`, `revokeShare($tokenId)` (staff, clinic-scoped). `shortToken()` generates a **12-char base62** token (~71 bits) — see §9.
- **`PublicShareController`** (NEW, INTENTIONALLY PUBLIC — no `Auth::check`): `GET /p/v/{token}` validates the token shape pre-DB, looks it up by hash, rejects revoked/expired/over-uses/ghost-appointment with a **uniform 404 neutral page** (no enumeration oracle), throttles repeated failures per-IP, increments `used_count`, audits, and renders the report. Self-contained getters — never trusts a session.
- **Routes** added to BOTH `index.php` and `public/index.php` (kept byte-identical): `GET /p/v/{token}` + legacy alias `GET /p/visit/{token}` → `PublicShareController@visitDocuments`, and `POST /api/whatsapp/share/revoke/{tokenId}` → `WhatsappController@revokeShare`. Controller `require_once`'d in both.
- The staff `/print/*` routes and their 401 gate are **unchanged** — staff printing keeps full auth.

## 2. One combined message (not two)
- Template **`Comprehensive Visit Report`** (category `report`) body is a single link line:
  `إليك رابط تقرير الزيارة ويشمل (…): <link>` — the parenthetical lists ONLY the documents that exist for the visit. No inline duplication of instructions.
- `resolveMessage` builds `{{visit_documents_section}}` with the adaptive label and mints ONE `/p/v/{token}` link; `{{visit_report_pdf_url}}` (full record) is blanked for patient sends.
- **In-visit buttons** (`appointment.php` lines 251/758/777) and the visit-completion auto-trigger all default to category `report`, so the in-visit WhatsApp button always pre-selects the report.

## 3. Report contents & ordering
Sections render (and the label lists) in this priority order, each only when present:
**الوصفة العلاجية (priority) → التعليمات الطبية → مقاس النظارة → التحاليل → الأشعة**.
Labs/radiology come from `lab_tests.test_type` enum (`laboratory` / `radiology`), split into two sections.

## 4. Patient page (`app/Views/print/public/visit-documents.php`)
- **Cairo** Google Font; site **dark Indigo** palette (bg `#070B14`, card `#131A29`, indigo `#6366F1`/`#818CF8`).
- Header shows the **clinic logo** (settings `clinic_logo`, in a white chip with `onerror` hide), clinic **name + address + phone**, and a "تقرير الزيارة" badge.
- All values `htmlspecialchars`-escaped; `noindex`; manual "تحميل / طباعة PDF" button (no auto-print).
- **Print layout**: built as a `<table>` with `<thead class="sheet-head">` so the clinic header **repeats on every printed page**. Content grouped into two sheets — Sheet 1 = prescription + instructions, Sheet 2 = glasses + labs + radiology (`.group-2.has-prev { break-before: page }`), with `break-inside: avoid` on sections/tables/rows so nothing splits mid-table. Print CSS inverts to white/ink for clean PDFs.
- `link-invalid.php` matches the dark Indigo + Cairo styling.

## 5. Two-clinic correctness
The clinic operates two branches. `{{clinic_name}}`, `{{clinic_address}}` and the new `{{clinic_phone}}` placeholder are resolved from the **appointment's own `clinic_id`** (`clinics` table), NOT global settings. The report page resolves clinic from `appointment.clinic_id` the same way.

## 6. Templates cleanup + clinic signature
- Removed (DB + migration, idempotent): `Medication Prescription Link`, `Glasses Prescription Link`, `Eye Drops Schedule`, `Investigation Request`, `Visit Documents`; de-duplicated `Comprehensive Visit Report` to one row.
- Every active template gets `\n\n{{clinic_name}}\n📞 {{clinic_phone}}` appended (idempotent `body NOT LIKE '%{{clinic_phone}}%'`; migration runs `SET NAMES utf8mb4` to avoid a CONCAT collation error).

## 7. Secretary scope + full Arabic
- `getTemplates` filters to `category IN ('confirmation','reminder','cancellation')` when `role = secretary` — secretaries never see clinical documents, instructions, schedules, requests, reports or medical warnings.
- All `whatsapp.js` user-facing `alert()`/loading/empty strings are now bilingual via `_isRtl()` (Arabic when the modal is RTL/secretary).

## 8. Removed friction & cleanups
- **Privacy-confirmation gate removed**: the sensitive-data checkbox + `checkTextSensitivity` gating deleted from `whatsapp-modal.php` and `whatsapp.js`; debug `console.log`s removed.
- **Doctor honorific** strip now handles both `د.` and `Dr.` (`/^\s*(?:د|dr)\.?\s*/iu`) in the message and on the page (fixes "د. د." / "د. Dr.").
- **Templates manager modal** (`settings.php`) got scoped `.dark #templatesManagerModal` overrides for dark/light mode.

## 9. Short link
Token shortened from 64-hex (256-bit) to **12 base62 chars** (`shortToken()`, ~71 bits, CSPRNG `random_int`) and the path shortened to `/p/v/{token}` (legacy `/p/visit/` kept as alias). Still unguessable — links also expire and are rate-limited. Stored as SHA-256 hash (CHAR(64), no schema change). Example: `https://<host>/p/v/Q6t3ymWQjsTT`. The controller's shape check accepts `^[0-9A-Za-z]{8,64}$` (covers new short + legacy hex).

## 10. Social/link-preview metadata (Open Graph)
- The report page emits **Open Graph + Twitter Card** tags (`og:title`, `og:description`, `og:image` = absolute clinic-logo URL, `og:site_name`, `og:url`) so a shared link previews with the **clinic logo + name** — using **generic, PHI-free** title/description only.
- **Preview-bot guard**: `PublicShareController::isPreviewBot()` detects WhatsApp/Facebook/Twitter/etc. crawlers and serves a lightweight `print/public/link-preview.php` (clinic branding only — **no patient/clinical data**), logged as `preview` and **not** consuming a use of the link. Real users get the full page.

## 11. Patient-profile "Quick Send Templates" badges
- The doctor patient page (`doctor/patient.php`) had **hardcoded** quick-send badges that passed made-up categories (`eye_drops`, `summary`, `investigation`, `follow_up`) — several pointing at templates that were removed, so they silently fell back to the first template. Replaced with a **dynamic** `#waQuickTemplates` container populated by `WhatsAppIntegration.renderQuickTemplates(containerId, patientId)`.
- `renderQuickTemplates` pulls the **role-filtered** list from `/api/whatsapp/templates` and renders one badge per template; each badge calls `openModal(patientId, null, template.title)`. `renderTemplates` now matches the requested value against **category OR exact title**, so the badge opens the correct template every time, and only templates that actually exist are shown.
- Added the same dynamic badges to the **secretary** patient page (`secretary/patient_details.php`) — since `getTemplates` filters by role, the secretary only ever sees **appointment templates** (Confirmation, Reminder, Follow-up, Cancellation). RTL badge labels use `translateTitleToArabic`.
- The whole Patient Communication section is gated by `whatsapp_enabled` in both views (already the case; verified it disappears when the setting is off); `renderQuickTemplates` also early-returns when `WHATSAPP_CONFIG.enabled` is false (defense-in-depth).

## Files touched
- NEW: `app/Controllers/PublicShareController.php`; `app/Views/print/public/{visit-documents,link-invalid,link-preview}.php`; `database/migrations/2026_06_13_prescription_share_links.sql`.
- CHANGED (this section): `app/Views/doctor/patient.php` + `app/Views/secretary/patient_details.php` (dynamic quick-send badges); `app/Views/doctor/assets/js/whatsapp.js` (`renderQuickTemplates`, title-match).
- CHANGED: `app/Controllers/WhatsappController.php` (mint/scope/revoke/shortToken, `{{clinic_phone}}`, labs/radiology, label, honorific, secretary template filter); `app/Views/doctor/assets/js/whatsapp.js` (privacy removal, bilingual alerts, no console.log); `app/Views/layouts/whatsapp-modal.php` (privacy block removed); `app/Views/doctor/appointment.php` (3 buttons → `report`); `app/Views/doctor/settings.php` (templates-manager dark mode); `index.php` + `public/index.php` (routes).

## Verification (local)
`php -l` clean on all changed/new files; `diff index.php public/index.php` identical. Real `dr_faramawy` login → `resolveMessage` produced single-link messages with per-branch clinic name/phone signature; minted short `/p/v/…` links opened **logged out** (HTTP 200) rendering prescription/instructions/glasses/labs/radiology in order with repeating header; invalid/expired/revoked → uniform 404; staff `/print/*` still 401; secretary `getTemplates` returns only the 4 appointment templates; WhatsApp-bot UA received the PHI-free OG preview (no patient name) logged as `preview`.

## Deploy notes
Apply `2026_06_13_prescription_share_links.sql` with the **system-root** DB CLI on prod (app user is PDO-only). Two routers must stay byte-identical. Consider a cron to purge expired tokens. Still pending (separate tracks): broader resolve/getLogs/consent IDOR hardening, cancellation `fallbackData` fix, Arabic-digit / `patients.js` phone bugs, optional phone/DOB verify gate (schema ready, OFF).
