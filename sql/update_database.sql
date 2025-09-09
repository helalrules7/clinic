-- Update database to add settings table and insert default settings

-- Create settings table if not exists
CREATE TABLE IF NOT EXISTS settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB;

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('clinic_name', 'Roaya Clinic', 'string', 'اسم العيادة'),
('clinic_email', 'info@roayaclinic.com', 'string', 'البريد الإلكتروني للعيادة'),
('clinic_phone', '+20 123 456 7890', 'string', 'رقم هاتف العيادة'),
('clinic_address', 'Cairo, Egypt', 'string', 'عنوان العيادة'),
('timezone', 'Africa/Cairo', 'string', 'المنطقة الزمنية'),
('date_format', 'Y-m-d', 'string', 'تنسيق التاريخ'),
('time_format', 'H:i', 'string', 'تنسيق الوقت'),
('items_per_page', '10', 'integer', 'عدد العناصر لكل صفحة'),
('backup_frequency', 'daily', 'string', 'تكرار النسخ الاحتياطي'),
('email_notifications', '1', 'boolean', 'إشعارات البريد الإلكتروني'),
('sms_notifications', '0', 'boolean', 'إشعارات الرسائل النصية'),
('maintenance_mode', '0', 'boolean', 'وضع الصيانة')
ON DUPLICATE KEY UPDATE
setting_value = VALUES(setting_value),
updated_at = CURRENT_TIMESTAMP;
