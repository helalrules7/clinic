-- Promote all instruction templates to clinic-wide (user_id = NULL).
-- Safe to run multiple times.

UPDATE medical_instruction_templates
SET user_id = NULL,
    updated_at = NOW()
WHERE user_id IS NOT NULL;
