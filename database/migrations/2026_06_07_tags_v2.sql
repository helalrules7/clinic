-- Migration: Tags v2 — appointment tags, session labels, drug→patient tag links
-- Date: 2026-06-07

-- ============================================
-- Appointment Tags (persistent on appointment record)
-- ============================================
CREATE TABLE IF NOT EXISTS appointment_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#6366f1',
    icon VARCHAR(50) NULL DEFAULT 'bi-tag',
    doctor_id INT NULL COMMENT 'NULL = global, else private to doctor',
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_appt_tag_name_doctor (name, doctor_id),
    INDEX idx_appt_tag_doctor (doctor_id),
    INDEX idx_appt_tag_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS appointment_tag_assignments (
    appointment_id BIGINT UNSIGNED NOT NULL,
    tag_id INT NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (appointment_id, tag_id),
    INDEX idx_appt_tag_assign_tag (tag_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES appointment_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Session Labels (header-only, per appointment)
-- ============================================
CREATE TABLE IF NOT EXISTS appointment_session_labels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    appointment_id BIGINT UNSIGNED NOT NULL,
    label_text VARCHAR(80) NOT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#f59e0b',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_label_appt (appointment_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Drug → Patient Tag links (rx suggest)
-- ============================================
CREATE TABLE IF NOT EXISTS drug_patient_tag_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    drug_name VARCHAR(255) NOT NULL,
    patient_tag_id INT NOT NULL,
    doctor_id INT NULL COMMENT 'NULL = global link, else private',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_drug_patient_tag (drug_name(120), patient_tag_id, doctor_id),
    INDEX idx_drug_tag_name (drug_name(120)),
    FOREIGN KEY (patient_tag_id) REFERENCES patient_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
