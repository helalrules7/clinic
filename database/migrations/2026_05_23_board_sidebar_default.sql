-- Make the Patients Board sidebar item visible by default for ALL doctors.
-- Doctors who saved sidebar preferences before the board existed have a
-- `sidebar_items_enabled` JSON list that omits 'board', so it stayed hidden.
-- Append 'board' wherever it's missing (idempotent: re-running is a no-op).
-- (New doctors with no setting already see all items, board included.)
UPDATE doctor_settings
SET setting_value = JSON_ARRAY_APPEND(setting_value, '$', 'board')
WHERE setting_key = 'sidebar_items_enabled'
  AND JSON_VALID(setting_value)
  AND JSON_SEARCH(setting_value, 'one', 'board') IS NULL;
