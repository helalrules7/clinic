-- إضافة حقول اسم العيادة بالعربية وشعار العيادة
-- Add fields for Arabic clinic name and clinic logo

-- إضافة اسم العيادة بالعربية
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('clinic_name_arabic', 'رؤية لطب وجراحة العيون', 'string', 'اسم العيادة باللغة العربية', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();

-- إضافة شعار العيادة
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('clinic_logo', '/assets/images/Light.png', 'string', 'مسار شعار العيادة', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();

-- إضافة شعار العيادة للطباعة (نسخة عالية الجودة)
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('clinic_logo_print', '/assets/images/Light.png', 'string', 'مسار شعار العيادة للطباعة', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();

-- إضافة شعار العيادة للعلامة المائية
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('clinic_logo_watermark', '/assets/images/Light.png', 'string', 'مسار شعار العيادة للعلامة المائية', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();
-- إضافة حقول تكلفة الزيارات
-- Add visit cost fields

-- إضافة تكلفة الزيارة الجديدة
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('new_visit_cost', '100', 'string', 'تكلفة الزيارة الجديدة', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();

-- إضافة تكلفة الزيارة المتكررة
INSERT INTO settings (setting_key, setting_value, setting_type, description, created_at, updated_at) 
VALUES ('repeated_visit_cost', '50', 'string', 'تكلفة الزيارة المتكررة', NOW(), NOW())
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    updated_at = NOW();
