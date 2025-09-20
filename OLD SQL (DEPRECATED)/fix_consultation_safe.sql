-- إصلاح أعمدة consultation_notes - نسخة آمنة
-- هذا الملف يضيف الأعمدة المفقودة أو يحدث الموجودة إلى TEXT

-- إضافة أعمدة Slit Lamp للعين اليمنى واليسرى
ALTER TABLE consultation_notes 
ADD COLUMN slit_lamp_right TEXT NULL;

ALTER TABLE consultation_notes 
ADD COLUMN slit_lamp_left TEXT NULL;

-- إضافة أعمدة Fundus للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN fundus_right TEXT NULL;

ALTER TABLE consultation_notes
ADD COLUMN fundus_left TEXT NULL;

-- إضافة أعمدة External Appearance للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN external_appearance_right TEXT NULL;

ALTER TABLE consultation_notes
ADD COLUMN external_appearance_left TEXT NULL;

-- إضافة أعمدة Eyelid للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN eyelid_right TEXT NULL;

ALTER TABLE consultation_notes
ADD COLUMN eyelid_left TEXT NULL;

-- إضافة أعمدة إضافية
ALTER TABLE consultation_notes
ADD COLUMN systemic_disease TEXT NULL;

ALTER TABLE consultation_notes
ADD COLUMN medication TEXT NULL;

-- تحديث الأعمدة الموجودة إلى TEXT
ALTER TABLE consultation_notes 
MODIFY COLUMN slit_lamp_right TEXT NULL;

ALTER TABLE consultation_notes 
MODIFY COLUMN slit_lamp_left TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN fundus_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN fundus_left TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN external_appearance_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN external_appearance_left TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN eyelid_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN eyelid_left TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN systemic_disease TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN medication TEXT NULL;

-- نسخ البيانات الموجودة من الأعمدة القديمة إلى الجديدة
UPDATE consultation_notes 
SET slit_lamp_right = slit_lamp
WHERE slit_lamp IS NOT NULL AND slit_lamp != '' AND slit_lamp_right IS NULL;

UPDATE consultation_notes 
SET slit_lamp_left = slit_lamp
WHERE slit_lamp IS NOT NULL AND slit_lamp != '' AND slit_lamp_left IS NULL;

UPDATE consultation_notes 
SET fundus_right = fundus
WHERE fundus IS NOT NULL AND fundus != '' AND fundus_right IS NULL;

UPDATE consultation_notes 
SET fundus_left = fundus
WHERE fundus IS NOT NULL AND fundus != '' AND fundus_left IS NULL;

-- إضافة فهارس للأداء الأفضل
CREATE INDEX idx_slit_lamp_right ON consultation_notes(slit_lamp_right(100));
CREATE INDEX idx_slit_lamp_left ON consultation_notes(slit_lamp_left(100));
CREATE INDEX idx_fundus_right ON consultation_notes(fundus_right(100));
CREATE INDEX idx_fundus_left ON consultation_notes(fundus_left(100));
CREATE INDEX idx_external_appearance_right ON consultation_notes(external_appearance_right(100));
CREATE INDEX idx_external_appearance_left ON consultation_notes(external_appearance_left(100));
CREATE INDEX idx_eyelid_right ON consultation_notes(eyelid_right(100));
CREATE INDEX idx_eyelid_left ON consultation_notes(eyelid_left(100));
CREATE INDEX idx_systemic_disease ON consultation_notes(systemic_disease(100));
CREATE INDEX idx_medication ON consultation_notes(medication(100));

-- عرض هيكل الجدول للتأكد
DESCRIBE consultation_notes;
