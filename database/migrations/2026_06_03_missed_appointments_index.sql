-- 2026-06-03 — Composite index for the Missed Appointments dashboard card.
--
-- Query shape (after the dashboard perf fix in ApiController::getMissedAppointments):
--   WHERE doctor_id = ?
--     AND date >= ? AND date < ?
--     AND status NOT IN ('Completed','Cancelled')
--   ORDER BY date DESC, start_time DESC
--
-- (doctor_id, date) is the optimal prefix: equality on doctor_id, range on
-- date, and ORDER BY date DESC is satisfied directly from the index without
-- a filesort. `status` is intentionally left out — it's low-cardinality and
-- `NOT IN (...)` can't use a btree key prefix anyway.
--
-- Idempotent: silently skips if the index already exists.
SET @idx_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'appointments'
      AND INDEX_NAME   = 'idx_appointments_doctor_date'
);
SET @sql := IF(
    @idx_exists = 0,
    'CREATE INDEX idx_appointments_doctor_date ON appointments (doctor_id, date)',
    'SELECT ''idx_appointments_doctor_date already exists — skipping'' AS note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
