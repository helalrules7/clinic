-- v11.0.0 — extend the existing `notifications` table with snooze + pin +
-- a server-side group_key for the new iOS-style notification center.
-- Idempotent: each ALTER is gated by an information_schema check.

-- snoozed_until + pinned_at + group_key
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'notifications'
      AND COLUMN_NAME  = 'snoozed_until');
SET @sql := IF(@col = 0,
    'ALTER TABLE notifications
        ADD COLUMN snoozed_until DATETIME NULL AFTER is_read,
        ADD COLUMN pinned_at     DATETIME NULL AFTER snoozed_until,
        ADD COLUMN group_key     VARCHAR(64) NULL AFTER pinned_at',
    'SELECT "notifications v11 columns already present" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_notif_snooze
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
      AND INDEX_NAME = 'idx_notif_snooze');
SET @sql := IF(@idx = 0,
    'CREATE INDEX idx_notif_snooze ON notifications (user_id, snoozed_until)',
    'SELECT "idx_notif_snooze already exists" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_notif_pin
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
      AND INDEX_NAME = 'idx_notif_pin');
SET @sql := IF(@idx = 0,
    'CREATE INDEX idx_notif_pin ON notifications (user_id, pinned_at)',
    'SELECT "idx_notif_pin already exists" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- idx_notif_group
SET @idx := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications'
      AND INDEX_NAME = 'idx_notif_group');
SET @sql := IF(@idx = 0,
    'CREATE INDEX idx_notif_group ON notifications (user_id, group_key, created_at)',
    'SELECT "idx_notif_group already exists" AS note');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
