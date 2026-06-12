-- Arabic display name for doctors (used by the WhatsApp message resolver to
-- address the doctor in Arabic). The column existed locally without a migration
-- file, so it was missing on prod and the WhatsApp modal 500'd
-- ("Unknown column 'display_name_ar'"). Idempotent.
ALTER TABLE doctors ADD COLUMN IF NOT EXISTS display_name_ar VARCHAR(100) NULL DEFAULT NULL AFTER display_name;
