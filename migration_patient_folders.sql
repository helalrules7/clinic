-- Migration Script: Patient Folders Feature
-- Database: hclinic_roaya
-- Date: 2026-01-25
-- Description: Creates tables for patient folders feature (custom folders and folder-patient mappings)

-- ============================================
-- Table: patient_folders
-- ============================================
-- Stores custom folders created by doctors/users
-- System folders (by doctor) are computed dynamically and not stored here

CREATE TABLE IF NOT EXISTS patient_folders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doctor_id INT NULL COMMENT 'If NULL: global folder for clinic. If set: private to specific doctor',
    name VARCHAR(120) NOT NULL COMMENT 'Folder name',
    created_by_user_id INT NULL COMMENT 'User who created this folder',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_created_by_user_id (created_by_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: patient_folder_patients
-- ============================================
-- Junction table: Maps patients to folders
-- Composite primary key ensures one patient can only be in a folder once

CREATE TABLE IF NOT EXISTS patient_folder_patients (
    folder_id INT NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (folder_id, patient_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_folder_id (folder_id),
    FOREIGN KEY (folder_id) REFERENCES patient_folders(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Notes:
-- ============================================
-- 1. System folders (grouped by treating doctor) are computed dynamically
--    and do NOT need to be stored in this table.
-- 2. When a folder is deleted, all patient mappings are automatically
--    deleted due to CASCADE constraint.
-- 3. When a patient is deleted, all folder mappings are automatically
--    deleted due to CASCADE constraint.
-- 4. doctor_id = NULL means the folder is global (accessible to all doctors)
-- 5. doctor_id != NULL means the folder is private to that specific doctor

-- ============================================
-- Add icon and gradient_color columns (if not exists)
-- ============================================
-- Add icon column for custom folder icons
ALTER TABLE patient_folders 
ADD COLUMN IF NOT EXISTS icon VARCHAR(50) NULL DEFAULT 'bi-folder' COMMENT 'Bootstrap icon class or custom icon path';

-- Add gradient_color column for folder card styling
ALTER TABLE patient_folders 
ADD COLUMN IF NOT EXISTS gradient_color VARCHAR(200) NULL DEFAULT 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' COMMENT 'CSS gradient for folder card background';

-- ============================================
-- Add parent_id and parent_type for sub-folders
-- ============================================
-- Add parent_id column for sub-folders
ALTER TABLE patient_folders 
ADD COLUMN IF NOT EXISTS parent_id INT NULL COMMENT 'Parent folder ID for sub-folders';

-- Add parent_type column to specify if parent is system or custom folder
ALTER TABLE patient_folders 
ADD COLUMN IF NOT EXISTS parent_type ENUM('system', 'custom') NULL COMMENT 'Type of parent folder (system or custom)';

-- Add index for performance
ALTER TABLE patient_folders 
ADD INDEX IF NOT EXISTS idx_parent_id (parent_id);

-- Add foreign key constraint with CASCADE DELETE
-- Note: This will fail if there are existing rows with invalid parent_id values
-- First, ensure all parent_id values are valid or NULL
ALTER TABLE patient_folders 
ADD CONSTRAINT fk_parent_folder 
FOREIGN KEY (parent_id) REFERENCES patient_folders(id) ON DELETE CASCADE;

-- ============================================
-- Execution Instructions:
-- ============================================
-- 
-- Option 1: Using mysql CLI
-- mysql -h 217.76.57.212 -u hclinic_roaya -p hclinic_roaya < migration_patient_folders.sql
--
-- Option 2: Interactive MySQL
-- mysql -h 217.76.57.212 -u hclinic_roaya -p hclinic_roaya
-- SOURCE /path/to/migration_patient_folders.sql;
--
-- Option 3: Using phpMyAdmin or similar tool
-- Copy and paste the SQL statements above and execute them.
