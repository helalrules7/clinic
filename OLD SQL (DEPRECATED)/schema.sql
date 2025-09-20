-- Roaya Clinic Database Schema
-- MySQL 8.0+ with InnoDB engine

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET FOREIGN_KEY_CHECKS = 0;

-- Users table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('doctor', 'secretary', 'admin') NOT NULL DEFAULT 'secretary',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- Settings table
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB;


-- Patient Files Table
CREATE TABLE IF NOT EXISTS patient_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    description TEXT,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_patient_files_patient_id (patient_id),
    INDEX idx_patient_files_created_at (created_at)
);

-- Patient Notes Table
CREATE TABLE IF NOT EXISTS patient_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_patient_notes_patient_id (patient_id),
    INDEX idx_patient_notes_doctor_id (doctor_id),
    INDEX idx_patient_notes_created_at (created_at)
);

-- Doctors table
CREATE TABLE doctors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) NOT NULL DEFAULT 'Ophthalmology',
    license_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_specialty (specialty)
) ENGINE=InnoDB;

-- Doctor schedules
CREATE TABLE doctor_schedule (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id BIGINT UNSIGNED NOT NULL,
    weekday TINYINT NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
    is_working BOOLEAN NOT NULL DEFAULT TRUE,
    work_start TIME NOT NULL,
    work_end TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_doctor_weekday (doctor_id, weekday),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_weekday (weekday)
) ENGINE=InnoDB;

-- Patients table
CREATE TABLE patients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    dob DATE NULL,

    gender ENUM('Male', 'Female', 'Other') NULL,
    address TEXT NULL,
    phone VARCHAR(20) NOT NULL,
    alt_phone VARCHAR(20) NULL,
    national_id VARCHAR(20) NULL,
    emergency_contact VARCHAR(100) NULL,
    emergency_phone VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_phone (phone),
    INDEX idx_national_id (national_id),
    INDEX idx_name (last_name, first_name),
    INDEX idx_dob (dob)
) ENGINE=InnoDB;

-- Medical history (Old format - for backward compatibility)
CREATE TABLE medical_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    allergies TEXT NULL,
    medications TEXT NULL,
    systemic_history TEXT NULL,
    ocular_history TEXT NULL,
    prior_surgeries TEXT NULL,
    family_history TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_patient_id (patient_id)
) ENGINE=InnoDB;

-- Medical history entries (New detailed format)
CREATE TABLE medical_history_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    condition_name VARCHAR(255) NOT NULL,
    diagnosis_date DATE NULL,
    status ENUM('active', 'resolved', 'chronic', 'inactive') DEFAULT 'active',
    notes TEXT NULL,
    category ENUM('general', 'allergy', 'medication', 'surgery', 'family_history', 'social_history') DEFAULT 'general',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_patient_id (patient_id),
    INDEX idx_created_by (created_by),
    INDEX idx_status (status),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Appointments table
CREATE TABLE appointments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    booked_by BIGINT UNSIGNED NOT NULL,
    source ENUM('Walk-in', 'Phone') NOT NULL DEFAULT 'Walk-in',
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('Booked', 'CheckedIn', 'InProgress', 'Completed', 'Cancelled', 'NoShow', 'Rescheduled') NOT NULL DEFAULT 'Booked',
    visit_type ENUM('New', 'FollowUp', 'Procedure') NOT NULL DEFAULT 'New',
    notes TEXT NULL,
    cancellation_reason TEXT NULL,
    rescheduled_from BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    FOREIGN KEY (booked_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rescheduled_from) REFERENCES appointments(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_doctor_date_time (doctor_id, date, start_time),
    INDEX idx_patient_id (patient_id),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_date (date),
    INDEX idx_status (status),
    INDEX idx_visit_type (visit_type),
    INDEX idx_source (source)
) ENGINE=InnoDB;

-- Consultation notes
CREATE TABLE consultation_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    chief_complaint TEXT NULL,
    hx_present_illness TEXT NULL,
    visual_acuity_right VARCHAR(20) NULL,
    visual_acuity_left VARCHAR(20) NULL,
    refraction_right VARCHAR(50) NULL,
    refraction_left VARCHAR(50) NULL,
    IOP_right DECIMAL(5,2) NULL,
    IOP_left DECIMAL(5,2) NULL,
    slit_lamp TEXT NULL,
    fundus TEXT NULL,
    diagnosis TEXT NULL,
    diagnosis_code VARCHAR(12) NULL COMMENT 'ICD-10 code',
    plan TEXT NULL,
    followup_days INT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_diagnosis_code (diagnosis_code),
    INDEX idx_followup_days (followup_days)
) ENGINE=InnoDB;

-- Glasses prescriptions
CREATE TABLE glasses_prescriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    distance_sphere_r DECIMAL(4,2) NULL,
    distance_cylinder_r DECIMAL(4,2) NULL,
    distance_axis_r INT NULL,
    distance_sphere_l DECIMAL(4,2) NULL,
    distance_cylinder_l DECIMAL(4,2) NULL,
    distance_axis_l INT NULL,
    near_sphere_r DECIMAL(4,2) NULL,
    near_cylinder_r DECIMAL(4,2) NULL,
    near_axis_r INT NULL,
    near_sphere_l DECIMAL(4,2) NULL,
    near_cylinder_l DECIMAL(4,2) NULL,
    near_axis_l INT NULL,
    PD_NEAR DECIMAL(4,1) NULL,
    PD_DISTANCE DECIMAL(4,1) NULL,
    lens_type ENUM('Single Vision', 'Bifocal', 'Progressive', 'Reading') NOT NULL DEFAULT 'Single Vision',
    comments TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_lens_type (lens_type)
) ENGINE=InnoDB;

-- Lab tests
CREATE TABLE lab_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NOT NULL,
    patient_id INT NOT NULL,
    test_type ENUM('laboratory', 'radiology') NOT NULL,
    test_category VARCHAR(100) NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    priority ENUM('normal', 'high', 'urgent') DEFAULT 'normal',
    status ENUM('ordered', 'pending', 'completed', 'cancelled') DEFAULT 'ordered',
    ordered_date DATE NOT NULL,
    expected_date DATE,
    notes TEXT,
    results TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_appointment (appointment_id),
    INDEX idx_patient (patient_id),
    INDEX idx_status (status),
    INDEX idx_type (test_type)
) ENGINE=InnoDB;

-- Medication prescriptions
CREATE TABLE prescriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NOT NULL,
    drug_name VARCHAR(120) NOT NULL,
    dose VARCHAR(60) NOT NULL,
    frequency VARCHAR(60) NOT NULL,
    duration VARCHAR(60) NOT NULL,
    route VARCHAR(60) NOT NULL DEFAULT 'Topical',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_drug_name (drug_name)
) ENGINE=InnoDB;

-- Payments table
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id BIGINT UNSIGNED NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    received_by BIGINT UNSIGNED NOT NULL,
    type ENUM('Booking', 'Consultation', 'FollowUp', 'Procedure', 'Other') NOT NULL,
    method ENUM('Cash', 'Card', 'Wallet', 'Transfer') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'EGP',
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_reason TEXT NULL,
    is_exempt BOOLEAN NOT NULL DEFAULT FALSE,
    exempt_reason TEXT NULL,
    approval_user_id BIGINT UNSIGNED NULL,
    approval_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approval_user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_patient_id (patient_id),
    INDEX idx_received_by (received_by),
    INDEX idx_type (type),
    INDEX idx_method (method),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Invoices table
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) UNIQUE NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NULL,
    date DATE NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    balance DECIMAL(10,2) GENERATED ALWAYS AS (total - paid) STORED,
    status ENUM('Paid', 'Partial', 'Unpaid') NOT NULL DEFAULT 'Unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    
    INDEX idx_invoice_no (invoice_no),
    INDEX idx_patient_id (patient_id),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_date (date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Timeline events
CREATE TABLE timeline_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    appointment_id BIGINT UNSIGNED NULL,
    actor_user_id BIGINT UNSIGNED NOT NULL,
    event_type ENUM('Booking', 'StatusChange', 'Consultation', 'Rx', 'GlassesRx', 'Payment', 'Discount', 'Exemption', 'Edit', 'Cancel', 'Attachment', 'DailyClosure') NOT NULL,
    event_summary VARCHAR(255) NOT NULL,
    event_payload JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Daily closures
CREATE TABLE daily_closures (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id BIGINT UNSIGNED NULL,
    date DATE NOT NULL,
    closed_by BIGINT UNSIGNED NOT NULL,
    total_payments DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_appointments INT NOT NULL DEFAULT 0,
    completed_appointments INT NOT NULL DEFAULT 0,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE SET NULL,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_date (date),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_date (date)
) ENGINE=InnoDB;

-- Audit logs
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    entity_table VARCHAR(40) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    action ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL,
    before_json JSON NULL,
    after_json JSON NULL,
    reason TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_entity (entity_table, entity_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Patient attachments
CREATE TABLE patient_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    appointment_id BIGINT UNSIGNED NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_patient_id (patient_id),
    INDEX idx_appointment_id (appointment_id),
    INDEX idx_uploaded_by (uploaded_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB;

-- Session management for password changes
CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_session (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
