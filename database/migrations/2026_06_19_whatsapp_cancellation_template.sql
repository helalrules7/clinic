-- Seed the missing WhatsApp "cancellation" template. Both the web cancel trigger
-- and the mobile buildBookingWhatsApp('cancellation') look up a template with
-- category = 'cancellation'; none was ever seeded, so the cancellation prompt
-- never produced a message. Idempotent: only inserts when absent.
INSERT INTO communication_templates (title, category, specialty, body, is_active)
SELECT 'Appointment Cancellation', 'cancellation', 'ophthalmology',
       'مرحبًا {{patient_name}}،\nنأسف لإبلاغكم بأنه تم إلغاء موعدكم مع د. {{doctor_name}} يوم {{appointment_date}} الساعة {{appointment_time}}.\nبرجاء التواصل معنا لإعادة تحديد موعد جديد.\n\n{{clinic_name}}\n📞 {{clinic_phone}}',
       1
WHERE NOT EXISTS (SELECT 1 FROM communication_templates WHERE category = 'cancellation');
