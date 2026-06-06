-- v11 — Medical instruction templates (clinic-wide + per-doctor) and
-- per-appointment instruction items. Idempotent.

CREATE TABLE IF NOT EXISTS medical_instruction_templates (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED NULL COMMENT 'NULL = clinic-wide; set = personal template',
    title               VARCHAR(120) NOT NULL,
    body_ar             TEXT NOT NULL,
    body_en             TEXT NULL,
    category            VARCHAR(40) NULL,
    diagnosis_keywords  TEXT NULL COMMENT 'Comma-separated keywords for diagnosis matching',
    icd_code            VARCHAR(20) NULL,
    sort_order          SMALLINT NOT NULL DEFAULT 0,
    use_count           INT UNSIGNED NOT NULL DEFAULT 0,
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mit_scope_sort (user_id, is_active, sort_order, title),
    KEY idx_mit_category   (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointment_medical_instructions (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    appointment_id  INT UNSIGNED NOT NULL,
    template_id     INT UNSIGNED NULL,
    title           VARCHAR(120) NOT NULL,
    body_ar         TEXT NOT NULL,
    body_en         TEXT NULL,
    source          ENUM('auto_diagnosis','auto_history','template','custom') NOT NULL DEFAULT 'custom',
    sort_order      SMALLINT NOT NULL DEFAULT 0,
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ami_appt_sort (appointment_id, sort_order),
    KEY idx_ami_template  (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample clinic-wide template (ortho — knee osteoarthritis). Safe to re-run.
INSERT INTO medical_instruction_templates
    (user_id, title, body_ar, body_en, category, diagnosis_keywords, icd_code, sort_order, is_active)
SELECT NULL,
    'خشونة المفاصل — تعليمات عامة',
    '• تجنب حمل الأوزان الثقيلة والوقوف لفترات طويلة.\n• ممارسة تمارين تقوية العضلات حول الركبة يومياً.\n• استخدام كمادات دافئة قبل النشاط وباردة بعد الإجهاد.\n• المشي على أسطح مستوية وارتداء حذاء مريح.\n• مراجعة العيادة عند زيادة الألم أو تورم المفصل.',
    'Avoid heavy lifting and prolonged standing. Strengthen surrounding muscles daily. Use warm packs before activity and cold after exertion. Walk on even surfaces; wear supportive shoes. Return if pain or swelling worsens.',
    'general',
    'خشونة,خشونة المفاصل,osteoarthritis,OA,knee OA,تآكل المفصل',
    'M17.9',
    0,
    1
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM medical_instruction_templates
    WHERE user_id IS NULL AND title = 'خشونة المفاصل — تعليمات عامة'
    LIMIT 1
);
