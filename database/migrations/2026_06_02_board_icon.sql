-- 2026-06-02 — Add per-board icon (Bootstrap Icons class name, e.g. "bi-kanban").
-- Idempotent: skips if the column already exists.
SET @col := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'patient_board_columns'
      AND COLUMN_NAME  = 'icon'
);
SET @sql := IF(
    @col = 0,
    'ALTER TABLE patient_board_columns
         ADD COLUMN icon VARCHAR(40) NOT NULL DEFAULT ''bi-kanban'' AFTER color',
    'SELECT ''icon column already exists — skipping ALTER'' AS note'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
