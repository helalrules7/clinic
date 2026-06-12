-- =====================================================================
-- Public prescription / glasses / instructions share links
-- 2026-06-13 (v_12_perf)
--
-- Lets a patient open ONE public link (sent over WhatsApp) that shows
-- whichever of {medication prescription, glasses prescription, medical
-- instructions} exist for a single visit — WITHOUT a staff login.
--
-- Security model: the link carries a 256-bit random token; only its
-- SHA-256 hash is stored here (raw token lives only in the WhatsApp
-- message). Links expire, can be revoked, and every access is audited.
-- appointment_id is intentionally a LOOSE reference (NO foreign key)
-- because appointments are hard-deleted in this app; the public
-- controller treats a missing appointment as "link no longer available".
-- =====================================================================

-- Ensure string literals below match the utf8mb4 columns (avoids CONCAT
-- "illegal mix of collations" on the template-signature UPDATE).
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_share_tokens (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash        CHAR(64) NOT NULL,                 -- sha256(raw token) hex
    appointment_id    BIGINT UNSIGNED NOT NULL,          -- loose ref (appts hard-deleted)
    patient_id        BIGINT UNSIGNED NOT NULL,
    clinic_id         BIGINT UNSIGNED NULL,              -- snapshot for scope/audit
    created_by        BIGINT UNSIGNED NOT NULL,          -- staff user.id who minted
    require_verify    TINYINT(1) NOT NULL DEFAULT 0,     -- schema-ready, OFF in v1
    verify_method     VARCHAR(20) DEFAULT 'phone4',      -- 'phone4' | 'dob' | 'either'
    max_uses          INT NOT NULL DEFAULT 0,            -- 0 = unlimited
    used_count        INT NOT NULL DEFAULT 0,
    verify_fail_count INT NOT NULL DEFAULT 0,
    revoked           TINYINT(1) NOT NULL DEFAULT 0,
    expires_at        TIMESTAMP NULL DEFAULT NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_token_hash (token_hash),
    KEY idx_appointment (appointment_id),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_share_access_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_id    BIGINT UNSIGNED NULL,                    -- null when token unknown
    outcome     VARCHAR(30) NOT NULL,                    -- served|invalid|not_found|revoked|expired|used_up|unavailable|rate_limited
    ip_address  VARCHAR(64) NULL,
    user_agent  VARCHAR(255) NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_token_time (token_id, accessed_at),
    KEY idx_ip_time (ip_address, accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comprehensive Visit Report WhatsApp template (idempotent insert).
-- ONE link → ONE page; the parenthetical in {{visit_documents_section}} lists only
-- the documents that actually exist for the visit (glasses / instructions / meds).
INSERT INTO communication_templates (title, category, specialty, body)
SELECT * FROM (
    SELECT
        'Comprehensive Visit Report' AS title,
        'report'        AS category,
        'ophthalmology' AS specialty,
        'مرحبًا {{patient_name}}،\nشكرًا لزيارتك د. {{doctor_name}} في {{clinic_name}}.\n\n{{visit_documents_section}}نتمنى لك دوام الصحة والعافية.' AS body
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM communication_templates WHERE category = 'report'
);

-- Remove templates that are no longer needed.
DELETE FROM communication_templates
WHERE title IN (
    'Medication Prescription Link',
    'Glasses Prescription Link',
    'Eye Drops Schedule',
    'Investigation Request',
    'Visit Documents'
);

-- Keep only ONE 'Comprehensive Visit Report' (highest id), drop any duplicates.
DELETE t1 FROM communication_templates t1
INNER JOIN communication_templates t2
  ON t1.title = 'Comprehensive Visit Report'
 AND t2.title = 'Comprehensive Visit Report'
 AND t1.id < t2.id;

-- Every remaining active template must carry the clinic name + phone (per-branch,
-- resolved at send time). Idempotent: only appends where not already present.
UPDATE communication_templates
SET body = CONCAT(TRIM(TRAILING '\n' FROM body), '\n\n{{clinic_name}}\n📞 {{clinic_phone}}')
WHERE is_active = 1 AND body NOT LIKE '%{{clinic_phone}}%';

-- WhatsApp module flags (which surfaces are enabled) — default ON.
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type) VALUES
('whatsapp_mod_appointments', '1', 'boolean'),
('whatsapp_mod_visits',       '1', 'boolean'),
('whatsapp_mod_report',       '1', 'boolean'),
('whatsapp_mod_patientlog',   '1', 'boolean');
