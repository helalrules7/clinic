-- Migration Script: Patient Color Markers and Tags Feature
-- Database: hclinic_roaya
-- Date: 2026-01-26
-- Description: Creates tables for patient color markers and tagging system

-- ============================================
-- Table: patient_color_markers
-- ============================================
-- Stores color marker assignments for patients
-- Each patient can have one color marker

CREATE TABLE IF NOT EXISTS patient_color_markers (
    patient_id BIGINT UNSIGNED PRIMARY KEY,
    color_code VARCHAR(7) NOT NULL COMMENT 'Hex color code e.g. #ef4444',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: patient_tags
-- ============================================
-- Defines available tags that can be assigned to patients
-- Global tags (doctor_id = NULL) are shared by all doctors

CREATE TABLE IF NOT EXISTS patient_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL COMMENT 'Tag display name',
    color VARCHAR(7) NOT NULL DEFAULT '#6366f1' COMMENT 'Hex color for tag badge',
    icon VARCHAR(50) NULL DEFAULT 'bi-tag' COMMENT 'Bootstrap icon class',
    doctor_id INT NULL COMMENT 'NULL = global tag, else private to doctor',
    sort_order INT NOT NULL DEFAULT 0 COMMENT 'Display order',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_name_doctor (name, doctor_id),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: patient_tag_assignments
-- ============================================
-- Junction table: Maps tags to patients
-- Composite primary key ensures one tag can only be assigned once per patient

CREATE TABLE IF NOT EXISTS patient_tag_assignments (
    patient_id BIGINT UNSIGNED NOT NULL,
    tag_id INT NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (patient_id, tag_id),
    INDEX idx_tag_id (tag_id),
    INDEX idx_patient_id (patient_id),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES patient_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Insert Default Global Tags
-- ============================================

INSERT INTO patient_tags (name, color, icon, doctor_id, sort_order) VALUES
('Priority', '#ef4444', 'bi-exclamation-triangle-fill', NULL, 1),
('Follow-up', '#f59e0b', 'bi-clock-history', NULL, 2),
('VIP', '#8b5cf6', 'bi-star-fill', NULL, 3),
('New Patient', '#22c55e', 'bi-person-plus-fill', NULL, 4),
('Chronic', '#06b6d4', 'bi-heart-pulse-fill', NULL, 5);

-- ============================================
-- Notes:
-- ============================================
-- 1. Color markers use 8 predefined colors:
--    #ef4444 Red, #f59e0b Orange, #eab308 Yellow, #22c55e Green,
--    #06b6d4 Cyan, #3b82f6 Blue, #8b5cf6 Purple, #ec4899 Pink
-- 2. When a patient is deleted, color markers and tag assignments
--    are automatically deleted due to CASCADE constraint.
-- 3. When a tag is deleted, all patient assignments are automatically
--    deleted due to CASCADE constraint.
-- 4. doctor_id = NULL means global tag (accessible to all doctors)

-- ============================================
-- Execution Instructions:
-- ============================================
--
-- Option 1: Using mysql CLI
-- mysql -h 217.76.57.212 -u hclinic_roaya -p hclinic_roaya < migration_patient_tags_colors.sql
--
-- Option 2: Interactive MySQL
-- mysql -h 217.76.57.212 -u hclinic_roaya -p hclinic_roaya
-- SOURCE /path/to/migration_patient_tags_colors.sql;
--
-- Option 3: Using phpMyAdmin or similar tool
-- Copy and paste the SQL statements above and execute them.
