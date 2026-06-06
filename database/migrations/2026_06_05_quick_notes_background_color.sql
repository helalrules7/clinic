-- v11.x — give quick notes the same gradient / glassmorphism background support
-- the rich board notes already have. Stored as a token string that the client
-- maps to a gradient + frosted-glass treatment (e.g. "grad-aurora") or a plain
-- hex colour. NULL = default themed surface. Idempotent-ish (guarded below).

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'quick_notes'
      AND COLUMN_NAME = 'background_color'
);

SET @ddl := IF(@col_exists = 0,
    'ALTER TABLE quick_notes ADD COLUMN background_color VARCHAR(64) NULL AFTER body',
    'SELECT 1');

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
