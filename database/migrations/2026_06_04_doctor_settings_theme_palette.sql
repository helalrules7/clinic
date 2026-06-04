-- v11.0.0 — per-user accent palette. 6 named palettes (indigo, emerald,
-- rose, slate, amber, ocean). Default indigo (matches current app accent).
-- Stored as a short string so future palette additions don't need a schema
-- change. Idempotent via information_schema check.

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'doctor_settings'
      AND COLUMN_NAME  = 'theme_palette');
SET @sql := IF(@col = 0,
    'ALTER TABLE doctor_settings
        ADD COLUMN theme_palette VARCHAR(16) NOT NULL DEFAULT "indigo"',
    'SELECT "theme_palette column already present" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
