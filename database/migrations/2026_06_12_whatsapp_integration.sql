-- Create communication templates table
CREATE TABLE IF NOT EXISTS communication_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    specialty VARCHAR(100) DEFAULT 'ophthalmology',
    body TEXT NOT NULL,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create communication logs table
CREATE TABLE IF NOT EXISTS patient_communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT(20) UNSIGNED NOT NULL,
    visit_id BIGINT(20) UNSIGNED NULL,
    appointment_id BIGINT(20) UNSIGNED NULL,
    channel VARCHAR(50) DEFAULT 'whatsapp',
    template_id INT NULL,
    message_body TEXT NOT NULL,
    phone_number VARCHAR(50) NOT NULL,
    sent_by BIGINT(20) UNSIGNED NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'opened', -- 'opened', 'sent_manually', 'cancelled'
    related_eye VARCHAR(50) DEFAULT 'not_applicable', -- 'right', 'left', 'both', 'not_applicable'
    related_service VARCHAR(100) NULL, -- 'consultation', 'surgery', 'injection', 'investigation', 'follow_up'
    related_test_type VARCHAR(100) NULL, -- 'OCT', 'Fundus Photo', 'Visual Field', 'Topography', 'Pachymetry', 'Other'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add whatsapp consent field to patients table if not exists
ALTER TABLE patients ADD COLUMN IF NOT EXISTS whatsapp_consent TINYINT DEFAULT 1;

-- Insert default templates (using IGNORE or check to prevent duplicates)
INSERT INTO communication_templates (title, category, specialty, body) VALUES
('Appointment Confirmation', 'confirmation', 'ophthalmology', 'مرحبًا {{patient_name}}،\nتم تأكيد موعد حضرتك مع د. {{doctor_name}} يوم {{appointment_date}} الساعة {{appointment_time}}.\nالعنوان: {{clinic_address}}\nبرجاء الحضور قبل الموعد بـ 10 دقائق.'),
('Appointment Reminder', 'reminder', 'ophthalmology', 'تذكير بموعد حضرتك مع د. {{doctor_name}} غدًا {{appointment_date}} الساعة {{appointment_time}}.\nبرجاء إحضار أي نظارة طبية أو فحوصات/تقارير سابقة إن وجدت.'),
('Pupil Dilation Instructions', 'instructions', 'ophthalmology', 'مرحبًا {{patient_name}}،\nقد يحتاج الطبيب إلى توسيع حدقة العين أثناء الكشف.\nبعد التوسيع قد يحدث زغللة وحساسية للضوء لعدة ساعات، ويفضل عدم القيادة بعد الكشف مباشرة.'),
('Eye Drops Schedule', 'schedule', 'ophthalmology', 'مرحبًا {{patient_name}}،\nجدول القطرات حسب تعليمات د. {{doctor_name}}:\n{{eye_drops_schedule}}\nبرجاء الالتزام بالمواعيد وعدم إيقاف القطرات بدون الرجوع للطبيب.'),
('Post-Cataract Surgery', 'instructions', 'ophthalmology', 'مرحبًا {{patient_name}}،\nتعليمات ما بعد عملية المياه البيضاء:\n- الالتزام بالقطرات في مواعيدها\n- عدم دعك العين\n- تجنب دخول الماء أو الصابون في العين\n- تجنب المجهود الشديد أول أيام بعد العملية\n- في حالة ألم شديد أو نقص مفاجئ في النظر، برجاء التواصل مع العيادة فورًا.'),
('Post-LASIK / PRK', 'instructions', 'ophthalmology', 'مرحبًا {{patient_name}}،\nتعليمات ما بعد تصحيح الإبصار:\n- استخدام القطرات حسب الجدول\n- عدم دعك العين\n- تجنب المياه/الأتربة/الدخان\n- استخدام النظارة الشمسية عند الحاجة\n- الالتزام بموعد المتابعة.'),
('Post-Injection Instructions', 'instructions', 'ophthalmology', 'مرحبًا {{patient_name}}،\nتعليمات ما بعد حقن العين:\nقد يحدث احمرار بسيط أو إحساس بجسم غريب.\nفي حالة ألم شديد، احمرار شديد، إفرازات، أو انخفاض واضح في النظر، برجاء التواصل مع العيادة فورًا.'),
('Investigation Request', 'request', 'ophthalmology', 'مرحبًا {{patient_name}}،\nيرجى عمل الفحوصات التالية قبل المتابعة:\n{{requested_tests}}\nثم حجز موعد مراجعة مع د. {{doctor_name}}.'),
('Follow-up Reminder', 'reminder', 'ophthalmology', 'مرحبًا {{patient_name}}،\nهذا تذكير بموعد المتابعة الخاص بحضرتك مع د. {{doctor_name}} يوم {{follow_up_date}}.\nبرجاء إحضار الفحوصات أو التقارير السابقة إن وجدت.'),
('Emergency Warning', 'instructions', 'ophthalmology', 'مرحبًا {{patient_name}}،\nفي حالة حدوث ألم شديد بالعين، احمرار شديد، نقص مفاجئ في النظر، رؤية ومضات أو عوائم كثيرة بشكل مفاجئ، برجاء التواصل مع العيادة فورًا أو التوجه للطوارئ.');
