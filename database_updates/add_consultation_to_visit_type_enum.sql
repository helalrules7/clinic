-- Add Consultation to visit_type ENUM in appointments table
-- إضافة Consultation إلى visit_type ENUM في جدول appointments

ALTER TABLE appointments 
MODIFY COLUMN visit_type ENUM('New', 'FollowUp', 'Procedure', 'Consultation') 
NOT NULL DEFAULT 'New';
