# Roaya Clinic — Release v9.0

**Theme:** Multi-clinic architecture + on-tablet drawing for medical attachments.

This release introduces a complete two-clinic separation (Riyadh + Kafr El-Sheikh) and a Fabric.js-powered drawing canvas accessible from the appointment and patient pages.

---

## 1. Database

### Migration `database/migrations/026_create_clinics_and_link.sql`
- New table **`clinics`**: `id, code, name_ar, name_en, address_ar, address_en, phone, is_active, sort_order, timestamps`.
- Seeds two clinics:
  - `id=1, code=riyadh` → عيادة الرياض / Riyadh Clinic
  - `id=2, code=kfs` → عيادة كفر الشيخ / Kafr El-Sheikh Clinic
- Adds `clinic_id BIGINT UNSIGNED NULL FK clinics(id)` to **`appointments`** and **`patients`**, with indices.

### Migration `database/migrations/027_users_clinic_scope_and_new_secretaries.sql`
- Adds `clinic_id` to **`users`** (FK → clinics). NULL on doctors/admin (= cross-clinic visibility).
- Deletes the legacy single `sec` account.
- Inserts two clinic-scoped secretary accounts:

| Username      | Password | Clinic                    |
|---------------|----------|---------------------------|
| `sec_riyadh`  | `sec123` | Riyadh   (`id=1`)         |
| `sec_kfs`     | `sec123` | Kafr El-Sheikh (`id=2`)   |

> ⚠️ Change both passwords from each user's profile screen on first login.

---

## 2. Backend (PHP)

### New endpoints in `app/Controllers/ApiController.php`

| Method | Route | Purpose |
|--------|-------|---------|
| `GET`  | `/api/clinics` | Lists active clinics; secretaries get only their own (`users.clinic_id` filter). |
| `POST` | `/api/attachments/replace/{id}` | Overwrites an existing attachment file in place. Used by the Draw modal's autosave. |
| `POST` | `/api/attachments/bulk-delete` | Body: `{ appointment_id, ids[] }`. Defence-in-depth: only deletes rows belonging to the given appointment. |
| `POST` | `/api/patients/files/replace/{id}` | Same as above but for `patient_files`. |
| `POST` | `/api/patients/files/bulk-delete` | Body: `{ patient_id, ids[] }`. |

### Pagination
- `GET /api/appointments/{id}/attachments` and `GET /api/patients/{id}/files` now accept `?page=N&perPage=M`. Response includes `pagination: { page, perPage, total, totalPages }`. `perPage=0` (default) disables pagination for back-compat.

### Server-side clinic enforcement
- `ApiController@createPatient`, `ApiController@createAppointment`, `SecretaryController@createBooking`: when the caller is a secretary, **`clinic_id` is forcibly set to that user's clinic** regardless of what the client sends.
- All single-booking actions on the secretary side (`viewBooking`, `getBookingDetails`, `updateBooking`, `deleteBooking`, `confirmAttendance`) call a new `assertBookingInScope()` helper that returns HTTP 403 if the booking belongs to another clinic.
- `SecretaryController@getAllAppointmentsForDate` (the bookings carousel) filters by `clinic_id` when the user is a secretary.

### Read endpoints
- `getAppointmentDetails`, `getAppointmentsForDate`, `getAllAppointmentsForDate`, `getPatient`, `getPatientDetails`, `getAppointmentForAllDoctors` all `LEFT JOIN clinics` and return `clinic_name_en`, `clinic_name_ar`, `clinic_code` for downstream rendering.

### Files touched
- `app/Controllers/ApiController.php`
- `app/Controllers/SecretaryController.php`
- `app/Controllers/DoctorController.php`
- `app/Config/Constants.php` — `APP_VERSION` bumped to `9.0.0`
- `index.php`, `app/index.php`, `public/index.php` — new route registrations

---

## 3. Frontend

### Clinic dropdown (shared)
- New module **`app/Views/layouts/clinics-loader.js`** exposes `ClinicsLoader.populate(selectId, { lang, … })` and `ClinicsLoader.getVisual(code)`.
- Per-clinic icon + colour mapping:
  - `riyadh` → `bi-buildings-fill`, `#0d6efd`
  - `kfs`    → `bi-hospital-fill`,  `#10b981`
- Auto-loaded by `app/Views/layouts/main.php` and `app/Views/layouts/secretary_main.php`.
- When the response contains a single clinic (secretary case), the dropdown auto-selects it and locks the toggle.

### Modals updated to require clinic
- Doctor calendar: Add Appointment + Add Patient
- Doctor patients list: Add Patient
- Secretary bookings: Add Booking + Add Patient
- Secretary patients: Add Patient

### Calendar card + tooltip
- Doctor calendar card (`app/Views/doctor/assets/js/calendar.js`) renders a colourful "clinic chip" with the icon and Arabic name. Tooltip and detail pages reflect the same.

### Draw Consultation
- New module **`app/Views/doctor/assets/js/draw-consultation.js`** and **`draw-consultation.css`**.
- Vendored **Fabric.js v5.3** under `app/Views/layouts/vendor/fabric.min.js` (no CDN).
- Triggers: `DrawConsultation.openForAppointment(appointmentId, patientId)` and `DrawConsultation.openForPatient(patientId)`.
- Toolbar: Select, Pencil, Pen, Marker, Eraser, Rectangle, Circle, Triangle, Line, Undo, Redo, Delete, Clear; stroke + fill colour pickers; stroke-width slider; per-clinic icon mapping.
- Keyboard shortcuts: V/P/B/M/E for tools, R/C/T/L for shapes, Ctrl+Z / Ctrl+Shift+Z / Ctrl+S / Delete.
- 30-second autosave that **overwrites the same attachment file** on the server (via the `replace` endpoints above). First save creates the attachment; subsequent saves of the same session reuse its id.
- 404 recovery: if the cached attachment id was deleted from the list, the next save drops the id and creates a fresh attachment instead of looping on errors.
- **Every click on the trigger button starts a brand-new session** — fresh canvas, no carry-over id, no leftover history.
- Hides the global `.quick-access-dock` while the modal is open (`body.draw-modal-open`).

### Images & Attachments / Patient Files & Documents
- Old duplicate "Upload File / Take Photo" buttons at the bottom of the empty state are removed; the toolbar at the top is the single source of actions.
- Toolbar split into two button-groups with a 1rem gap:
  - **Bulk actions** (left): Select All (icon `bi-check2-square`) + Delete All (icon `bi-trash`, disabled until selection exists, with a badge counter).
  - **Add** (right): Upload + Draw + Capture.
- **Per-card checkbox** (rounded square, 24×24, `top: 8px; right: 8px`, white background with shadow) lives inside the image thumbnail wrapper so it sits visibly on the image.
- Selected cards get a strong outline + tinted background + glow shadow.
- Bulk delete uses a project-style confirmation modal (red header, "cannot be undone" alert, count line).
- **Pagination at 4 cards per page**, AJAX-driven, with a loading overlay (spinner + "Loading…") that blurs the grid during the fetch. Markup matches the project's existing pagination (`<ul class="pagination pagination-sm">` with `bi-chevron-right`/`bi-chevron-left`).
- Filenames now show in full, ellipsised by CSS only when they actually overflow the available width — previously they were truncated server-side to "first 10 chars + …" even when there was room.

### Files touched / created
- New: `app/Views/layouts/clinics-loader.js`, `app/Views/layouts/vendor/fabric.min.js`, `app/Views/doctor/assets/js/draw-consultation.js`, `app/Views/doctor/assets/css/draw-consultation.css`
- Modified: `app/Views/doctor/assets/js/calendar.js`, `appointment.js`, `patient.js`, `patients.js`; `app/Views/doctor/assets/css/calendar.css`; `app/Views/layouts/style.css`; `app/Views/layouts/main.php`, `secretary_main.php`, `calendar.php`; all doctor/secretary view templates listed above.

---

## 4. Deferred work — see `docs/FINANCIAL_PLAN.md`

A separate document captures the multi-clinic restructuring of the financial layer (`payments`, `expenses`, `invoices`, `daily_balances`, `daily_closures`) plus a punch list of bugs in the existing stats cards (revenue/expense totals not refreshing after creation). **Not implemented in this release** — the financial tables remain global until that plan is executed.

---

## 5. Local dev environment (not in this PR)

For reference: the local Docker setup (PHP 8.2 + MariaDB 10.11 + phpMyAdmin) lives at the repo owner's local path under `docker/` + `docker-compose.yml` and is intentionally not committed here.

---

## 6. Migration checklist for production deploy

1. Pull `release/v9.0-multi-clinic-drawing` onto the server.
2. Apply migration **026** then **027** in order against the production database.
3. Change the default passwords for `sec_riyadh` and `sec_kfs` immediately.
4. Bump `APP_VERSION` is already set to `9.0.0`.
5. Verify:
   - `/api/clinics` returns both clinics for doctors, one for each secretary.
   - Creating an appointment as a secretary forces the clinic to their own (try sending another `clinic_id` from devtools and confirm rejection/override).
   - The Draw Consultation button is reachable from the appointment page and from each patient page; saves appear in Images & Attachments / Patient Files & Documents.
   - Pagination kicks in once there are more than 4 items in a list; loading overlay shows on page change.
