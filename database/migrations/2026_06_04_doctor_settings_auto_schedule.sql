-- v11.0.0 — auto dark/light schedule per user. Master toggle +
-- dark-from + light-from times. When the master toggle is ON, the
-- pre-paint script picks the theme based on local time. Idempotent.

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'doctor_settings'
      AND COLUMN_NAME  = 'theme_auto_schedule');
SET @sql := IF(@col = 0,
    'ALTER TABLE doctor_settings
        ADD COLUMN theme_auto_schedule TINYINT(1) NOT NULL DEFAULT 0,
        ADD COLUMN theme_dark_from     TIME       NOT NULL DEFAULT "19:00:00",
        ADD COLUMN theme_light_from    TIME       NOT NULL DEFAULT "07:00:00"',
    'SELECT "theme auto-schedule columns already present" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
