-- Secretary user preferences (key-value, mirrors doctor_settings pattern)
CREATE TABLE IF NOT EXISTS secretary_settings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id       INT UNSIGNED NOT NULL,
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT         NULL,
    setting_type  VARCHAR(20)  NOT NULL DEFAULT 'string',
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_secretary_settings_user_key (user_id, setting_key),
    KEY idx_secretary_settings_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
