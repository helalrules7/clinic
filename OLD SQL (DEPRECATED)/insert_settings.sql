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
