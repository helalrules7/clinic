-- إصلاح أعمدة consultation_notes لتعمل مع النموذج
-- هذا الملف يضيف الأعمدة المفقودة أو يحدث الموجودة إلى TEXT

-- إضافة أو تحديث أعمدة Slit Lamp للعين اليمنى واليسرى
ALTER TABLE consultation_notes 
ADD COLUMN IF NOT EXISTS slit_lamp_right TEXT NULL AFTER IOP_left;

ALTER TABLE consultation_notes 
ADD COLUMN IF NOT EXISTS slit_lamp_left TEXT NULL AFTER slit_lamp_right;

-- تحديث الأعمدة الموجودة إلى TEXT إذا لم تكن كذلك
ALTER TABLE consultation_notes 
MODIFY COLUMN slit_lamp_right TEXT NULL;

ALTER TABLE consultation_notes 
MODIFY COLUMN slit_lamp_left TEXT NULL;

-- إضافة أو تحديث أعمدة Fundus للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS fundus_right TEXT NULL AFTER slit_lamp_left;

ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS fundus_left TEXT NULL AFTER fundus_right;

-- تحديث الأعمدة الموجودة إلى TEXT إذا لم تكن كذلك
ALTER TABLE consultation_notes
MODIFY COLUMN fundus_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN fundus_left TEXT NULL;

-- إضافة أو تحديث أعمدة External Appearance للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS external_appearance_right TEXT NULL AFTER fundus_left;

ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS external_appearance_left TEXT NULL AFTER external_appearance_right;

-- تحديث الأعمدة الموجودة إلى TEXT إذا لم تكن كذلك
ALTER TABLE consultation_notes
MODIFY COLUMN external_appearance_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN external_appearance_left TEXT NULL;

-- إضافة أو تحديث أعمدة Eyelid للعين اليمنى واليسرى
ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS eyelid_right TEXT NULL AFTER external_appearance_left;

ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS eyelid_left TEXT NULL AFTER eyelid_right;

-- تحديث الأعمدة الموجودة إلى TEXT إذا لم تكن كذلك
ALTER TABLE consultation_notes
MODIFY COLUMN eyelid_right TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN eyelid_left TEXT NULL;

-- إضافة أو تحديث أعمدة إضافية
ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS systemic_disease TEXT NULL AFTER diagnosis_code;

ALTER TABLE consultation_notes
ADD COLUMN IF NOT EXISTS medication TEXT NULL AFTER systemic_disease;

-- تحديث الأعمدة الموجودة إلى TEXT إذا لم تكن كذلك
ALTER TABLE consultation_notes
MODIFY COLUMN systemic_disease TEXT NULL;

ALTER TABLE consultation_notes
MODIFY COLUMN medication TEXT NULL;

-- نسخ البيانات الموجودة من الأعمدة القديمة إلى الجديدة
-- نسخ Slit Lamp
UPDATE consultation_notes 
SET slit_lamp_right = slit_lamp,
    slit_lamp_left = slit_lamp
WHERE slit_lamp IS NOT NULL AND slit_lamp != '' AND slit_lamp_right IS NULL;

-- نسخ Fundus
UPDATE consultation_notes 
SET fundus_right = fundus,
    fundus_left = fundus
WHERE fundus IS NOT NULL AND fundus != '' AND fundus_right IS NULL;

-- إضافة فهارس للأداء الأفضل (مع تجاهل الأخطاء إذا كانت موجودة)
CREATE INDEX IF NOT EXISTS idx_slit_lamp_right ON consultation_notes(slit_lamp_right(100));
CREATE INDEX IF NOT EXISTS idx_slit_lamp_left ON consultation_notes(slit_lamp_left(100));
CREATE INDEX IF NOT EXISTS idx_fundus_right ON consultation_notes(fundus_right(100));
CREATE INDEX IF NOT EXISTS idx_fundus_left ON consultation_notes(fundus_left(100));
CREATE INDEX IF NOT EXISTS idx_external_appearance_right ON consultation_notes(external_appearance_right(100));
CREATE INDEX IF NOT EXISTS idx_external_appearance_left ON consultation_notes(external_appearance_left(100));
CREATE INDEX IF NOT EXISTS idx_eyelid_right ON consultation_notes(eyelid_right(100));
CREATE INDEX IF NOT EXISTS idx_eyelid_left ON consultation_notes(eyelid_left(100));
CREATE INDEX IF NOT EXISTS idx_systemic_disease ON consultation_notes(systemic_disease(100));
CREATE INDEX IF NOT EXISTS idx_medication ON consultation_notes(medication(100));

-- عرض هيكل الجدول للتأكد
DESCRIBE consultation_notes;
