-- v11.0.1 (2026-06-07) — Stage D of the Patient Folders audit.
--
-- Adds patients.created_by_doctor_id (the doctor who registered the patient) to
-- replace the fragile `timeline_events.event_summary LIKE '%New patient
-- registered%'` text-match that "system" folders were computed from. The text
-- match is English-only and breaks if the wording is ever localised (the UI is
-- Arabic-first), and it can't be indexed. A real FK column makes the system
-- folder counts/lists deterministic, indexable, and locale-independent.
--
-- Idempotent: IF NOT EXISTS guards + the backfill only touches NULL rows.

ALTER TABLE patients
  ADD COLUMN IF NOT EXISTS created_by_doctor_id BIGINT UNSIGNED NULL
    COMMENT 'Doctor who registered this patient (drives system-folder grouping)'
    AFTER created_at;

ALTER TABLE patients
  ADD INDEX IF NOT EXISTS idx_patients_created_by_doctor (created_by_doctor_id);

-- Backfill from the registration timeline event:
--   timeline_events.actor_user_id -> doctors.user_id -> doctors.id
-- MIN(d.id) keeps it deterministic if a patient somehow has >1 matching event.
UPDATE patients p
JOIN (
    SELECT te.patient_id, MIN(d.id) AS doctor_id
    FROM timeline_events te
    JOIN doctors d ON d.user_id = te.actor_user_id
    WHERE te.event_type = 'Booking'
      AND te.event_summary LIKE '%New patient registered%'
    GROUP BY te.patient_id
) reg ON reg.patient_id = p.id
SET p.created_by_doctor_id = reg.doctor_id
WHERE p.created_by_doctor_id IS NULL;
