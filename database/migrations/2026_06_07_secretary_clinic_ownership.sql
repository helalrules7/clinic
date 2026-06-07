-- ============================================================================
-- Secretary clinic-owned patient organization (folders / tags / color markers)
-- + administrative file audience.
--
-- Context: folders & tags are owned by `doctor_id` (NULL = global). A secretary
-- is users.role='secretary' with a clinic_id and NO doctor_id, so the doctor
-- scoping doesn't fit. Per product decision, the secretary's folders/tags/markers
-- are SEPARATE and owned at the CLINIC level. Color markers were global (PK =
-- patient_id), so clinic markers live in a NEW sibling table.
--
-- Owner model after this migration (folders/tags):
--   doctor-owned : doctor_id = X, clinic_id NULL
--   clinic-owned : doctor_id NULL, clinic_id = Y     (secretary space)
--   global       : doctor_id NULL, clinic_id NULL
-- The doctor read queries must therefore guard the "global" branch with
-- `clinic_id IS NULL` so clinic-owned rows never leak into the doctor's view
-- (done in ApiController alongside this migration).
--
-- All changes are ADDITIVE + backfill-safe. Idempotent where MariaDB allows.
-- ============================================================================

-- 1) Folders — clinic owner dimension --------------------------------------
ALTER TABLE patient_folders
  ADD COLUMN clinic_id INT NULL DEFAULT NULL AFTER doctor_id;
ALTER TABLE patient_folders
  ADD INDEX idx_pf_clinic (clinic_id);

-- 2) Tags — clinic owner dimension + widen the unique key -------------------
ALTER TABLE patient_tags
  ADD COLUMN clinic_id INT NULL DEFAULT NULL AFTER doctor_id;
ALTER TABLE patient_tags
  ADD INDEX idx_pt_clinic (clinic_id);
-- Old key uk_name_doctor(name, doctor_id) would block two clinics (both
-- doctor_id NULL) from sharing a tag name. Widen to include clinic_id.
ALTER TABLE patient_tags
  DROP INDEX uk_name_doctor;
ALTER TABLE patient_tags
  ADD UNIQUE KEY uk_name_owner (name, doctor_id, clinic_id);

-- 3) Color markers — separate per-clinic table -----------------------------
-- The existing patient_color_markers is global (PK = patient_id, one per
-- patient). A patient can now ALSO carry an independent per-clinic marker that
-- the secretary owns; the doctor's global marker is untouched.
CREATE TABLE IF NOT EXISTS patient_clinic_color_markers (
  patient_id BIGINT UNSIGNED NOT NULL,
  clinic_id  INT NOT NULL,
  color_code VARCHAR(7) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (patient_id, clinic_id),
  INDEX idx_pccm_clinic (clinic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Patient files — administrative vs clinical audience + admin category ---
-- Secretary uploads are audience='administrative' (+ a category like
-- id/insurance/receipt). Secretary listing filters to audience='administrative'.
-- Existing rows stay NULL (treated as clinical/doctor-only → secretary won't see them).
ALTER TABLE patient_files
  ADD COLUMN audience VARCHAR(20) NULL DEFAULT NULL AFTER description;
ALTER TABLE patient_files
  ADD COLUMN category VARCHAR(40) NULL DEFAULT NULL AFTER audience;
ALTER TABLE patient_files
  ADD INDEX idx_pf_audience (audience);
