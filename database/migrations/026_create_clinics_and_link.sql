-- Migration 026: Clinics master table + link to appointments and patients
-- Background: business now operates two clinics (Riyadh + Kafr El-Sheikh).
-- The clinic must be selected when booking an appointment or registering a new patient.
-- A dedicated table (not just an enum/column) is used because clinic identity will
-- be referenced later by financial accounts and secretary scope.

CREATE TABLE IF NOT EXISTS clinics (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code            VARCHAR(20)  NOT NULL,
    name_ar         VARCHAR(100) NOT NULL,
    name_en         VARCHAR(100) NOT NULL,
    address_ar      VARCHAR(255) DEFAULT NULL,
    address_en      VARCHAR(255) DEFAULT NULL,
    phone           VARCHAR(30)  DEFAULT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order      INT          NOT NULL DEFAULT 0,
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clinics_code (code),
    KEY idx_clinics_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO clinics (id, code, name_ar, name_en, is_active, sort_order)
VALUES
    (1, 'riyadh', 'عيادة الرياض',     'Riyadh Clinic',         1, 1),
    (2, 'kfs',    'عيادة كفر الشيخ',  'Kafr El-Sheikh Clinic', 1, 2)
ON DUPLICATE KEY UPDATE
    name_ar = VALUES(name_ar),
    name_en = VALUES(name_en),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order);

ALTER TABLE appointments
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER doctor_id,
    ADD KEY idx_appointments_clinic (clinic_id),
    ADD CONSTRAINT fk_appointments_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE patients
    ADD COLUMN clinic_id BIGINT UNSIGNED DEFAULT NULL AFTER national_id,
    ADD KEY idx_patients_clinic (clinic_id),
    ADD CONSTRAINT fk_patients_clinic
        FOREIGN KEY (clinic_id) REFERENCES clinics(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;
