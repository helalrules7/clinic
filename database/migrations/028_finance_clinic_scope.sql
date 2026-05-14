-- Migration 028: clinic-scope the financial layer.
--
-- Phase 1 of the financial overhaul (see docs/FINANCIAL_PLAN.md).
--
-- Adds `clinic_id` to every financial table so that:
--   - secretaries can be scoped to their own clinic's books;
--   - doctors / admins can filter or aggregate by clinic;
--   - daily closures can happen per-clinic (the legacy UNIQUE(date) constraint
--     prevented this entirely).
--
-- Columns are nullable here to make the rollout safe; a follow-up migration
-- can flip them to NOT NULL once every write path is verified to populate the
-- column. Backfill picks the appointment's clinic when known, otherwise
-- defaults to clinic 1 (Riyadh) as the historical owner.

-- ------------------------------------------------------------------
-- 1. payments
-- ------------------------------------------------------------------
ALTER TABLE payments
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER patient_id,
    ADD KEY idx_payments_clinic (clinic_id),
    ADD CONSTRAINT fk_payments_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------------
-- 2. expenses
-- ------------------------------------------------------------------
ALTER TABLE expenses
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER category,
    ADD KEY idx_expenses_clinic (clinic_id),
    ADD CONSTRAINT fk_expenses_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------------
-- 3. invoices
-- ------------------------------------------------------------------
ALTER TABLE invoices
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER doctor_id,
    ADD KEY idx_invoices_clinic (clinic_id),
    ADD CONSTRAINT fk_invoices_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------------
-- 4. daily_balances (cash drawer entries: opening, additional, withdrawal, closing)
-- ------------------------------------------------------------------
ALTER TABLE daily_balances
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER balance_type,
    ADD KEY idx_daily_balances_clinic (clinic_id),
    ADD CONSTRAINT fk_daily_balances_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------------
-- 5. daily_closures — also fix the broken UNIQUE(date) constraint
--    so each clinic can close its own day (and, optionally, each doctor
--    within a clinic). NULL doctor_id = clinic-wide closure.
-- ------------------------------------------------------------------
ALTER TABLE daily_closures
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER doctor_id,
    DROP INDEX unique_date,
    ADD UNIQUE KEY uq_closure_clinic_doctor_date (clinic_id, doctor_id, date),
    ADD KEY idx_daily_closures_clinic (clinic_id),
    ADD CONSTRAINT fk_daily_closures_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

-- ------------------------------------------------------------------
-- 6. Backfill
--    First: inherit from the linked appointment when possible.
--    Then:  fall back to clinic 1 (Riyadh) for anything still NULL.
-- ------------------------------------------------------------------
UPDATE payments p
    LEFT JOIN appointments a ON p.appointment_id = a.id
SET p.clinic_id = a.clinic_id
WHERE p.clinic_id IS NULL AND a.clinic_id IS NOT NULL;

-- (invoices.doctor_id can't tell us the clinic; appointments isn't joined
--  here. We'd need a separate `appointment_id` on invoices to inherit. Skip
--  the smart pass for invoices.)

UPDATE payments       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE expenses       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE invoices       SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE daily_balances SET clinic_id = 1 WHERE clinic_id IS NULL;
UPDATE daily_closures SET clinic_id = 1 WHERE clinic_id IS NULL;
