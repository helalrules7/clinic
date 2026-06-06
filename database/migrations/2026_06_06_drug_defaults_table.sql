-- v11 — per-doctor drug prescription templates.
--
-- From the "Add prescription" modal a doctor can hit "Save as template" to
-- remember the dose / frequency / duration / route / instructions they just
-- typed for a drug, keyed by (doctor_id, drug_name). The next time that doctor
-- picks the same drug the modal auto-fills every field instantly. They can
-- still edit / clear / override before saving the actual prescription.
--
-- `instructions` maps onto the prescriptions.notes field. The other columns
-- mirror the prescriptions vocabulary (route: Topical / Oral / IV / IM / ...).
--
-- Idempotent — safe to run repeatedly (also re-adds the template columns if an
-- earlier route+instructions-only version of the table already exists).

CREATE TABLE IF NOT EXISTS drug_defaults (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    doctor_id    BIGINT UNSIGNED NOT NULL,
    drug_name    VARCHAR(120) NOT NULL,
    dose         VARCHAR(60) NULL,
    frequency    VARCHAR(60) NULL,
    duration     VARCHAR(60) NULL,
    route        VARCHAR(60) NULL,
    instructions TEXT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_doctor_drug (doctor_id, drug_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill the template columns for installs created from the first cut of this
-- migration (which only had route + instructions).
ALTER TABLE drug_defaults
    ADD COLUMN IF NOT EXISTS dose      VARCHAR(60) NULL AFTER drug_name,
    ADD COLUMN IF NOT EXISTS frequency VARCHAR(60) NULL AFTER dose,
    ADD COLUMN IF NOT EXISTS duration  VARCHAR(60) NULL AFTER frequency;
