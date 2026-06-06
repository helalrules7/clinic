-- v11.x — board notes (`notes` table) used VARCHAR(7) for background_color to
-- hold a single hex like "#fbbf24". Gradient/glassmorphism presets are stored
-- as tokens ("grad-aurora", up to ~16 chars), so widen the column to match the
-- quick_notes column (VARCHAR 64). Idempotent: only alters when still narrow.

SET @len := (
    SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'notes'
      AND COLUMN_NAME = 'background_color'
);

SET @ddl := IF(@len IS NOT NULL AND @len < 64,
    'ALTER TABLE notes MODIFY COLUMN background_color VARCHAR(64) NULL DEFAULT ''#ffffff''',
    'SELECT 1');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
