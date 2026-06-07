-- Ensure users.profile_image exists (shared by doctor + secretary avatars).
-- Idempotent for fresh installs and older databases.

SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'profile_image'
);

SET @ddl := IF(
    @col_exists = 0,
    'ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL DEFAULT NULL AFTER phone',
    'SELECT ''profile_image column already exists'' AS migration_note'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
