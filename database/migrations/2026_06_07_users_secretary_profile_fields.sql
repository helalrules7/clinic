-- Secretary profile fields on users (display name + department).
-- Required by SecretaryController::updateProfile() and secretary/profile.php.

SET @has_secretary_name := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'secretary_name'
);
SET @ddl_sn := IF(
    @has_secretary_name = 0,
    'ALTER TABLE users ADD COLUMN secretary_name VARCHAR(100) NULL DEFAULT NULL AFTER phone',
    'SELECT ''secretary_name exists'' AS note'
);
PREPARE s1 FROM @ddl_sn; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @has_department := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'department'
);
SET @ddl_dept := IF(
    @has_department = 0,
    'ALTER TABLE users ADD COLUMN department VARCHAR(50) NULL DEFAULT ''Administration'' AFTER secretary_name',
    'SELECT ''department exists'' AS note'
);
PREPARE s2 FROM @ddl_dept; EXECUTE s2; DEALLOCATE PREPARE s2;

-- Backfill display name from full name for existing secretaries (optional, non-destructive).
UPDATE users
   SET secretary_name = name
 WHERE role = 'secretary'
   AND (secretary_name IS NULL OR secretary_name = '');
