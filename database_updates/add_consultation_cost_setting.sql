-- Add consultation/medical procedure cost setting to settings table
-- إضافة إعداد تكلفة الاستشارة/الإجراء الطبي لجدول الإعدادات

INSERT INTO settings (setting_key, setting_value, setting_type, description) 
VALUES ('consultation_cost', '50.00', 'string', 'Cost for consultation/medical procedure')
ON DUPLICATE KEY UPDATE 
setting_value = '50.00',
setting_type = 'string',
description = 'Cost for consultation/medical procedure',
updated_at = CURRENT_TIMESTAMP;
