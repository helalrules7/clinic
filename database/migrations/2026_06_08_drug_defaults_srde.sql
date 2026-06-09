-- Anchor per-doctor drug templates (+ patient drug-tag links) to the stable SRDE
-- product code. The drug DB (hclinic_drugs.drugs) is fully DELETE+reINSERTed on
-- every external import (drugeye.xlsx), so the auto-increment ID reshuffles and the
-- free-text FirstName (drug_name) can drift — which silently orphans name-keyed rows.
-- SRDE comes from the source and survives the import. `orphaned_at` flags a template
-- whose drug vanished (we NEVER auto-delete — the doctor decides). Reconciliation on
-- each import re-links by SRDE and backfills SRDE by name. Idempotent (MariaDB).

ALTER TABLE drug_defaults
    ADD COLUMN IF NOT EXISTS srde        VARCHAR(60) NULL AFTER drug_name,
    ADD COLUMN IF NOT EXISTS orphaned_at DATETIME    NULL DEFAULT NULL AFTER updated_at;
ALTER TABLE drug_defaults
    ADD INDEX IF NOT EXISTS idx_doctor_srde (doctor_id, srde);

ALTER TABLE drug_patient_tag_links
    ADD COLUMN IF NOT EXISTS srde VARCHAR(60) NULL AFTER drug_name;
ALTER TABLE drug_patient_tag_links
    ADD INDEX IF NOT EXISTS idx_dptl_doctor_srde (doctor_id, srde);
