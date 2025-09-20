-- Add new fields to consultation_notes table
ALTER TABLE consultation_notes
ADD COLUMN external_appearance_right TEXT NULL AFTER fundus_left,
ADD COLUMN external_appearance_left TEXT NULL AFTER external_appearance_right,
ADD COLUMN eyelid_right TEXT NULL AFTER external_appearance_left,
ADD COLUMN eyelid_left TEXT NULL AFTER eyelid_right,
ADD COLUMN systemic_disease TEXT NULL AFTER diagnosis_code,
ADD COLUMN medication TEXT NULL AFTER systemic_disease;

-- Add indexes for new columns (optional, but good practice)
ALTER TABLE consultation_notes
ADD INDEX idx_external_appearance_right (external_appearance_right(255)),
ADD INDEX idx_external_appearance_left (external_appearance_left(255)),
ADD INDEX idx_eyelid_right (eyelid_right(255)),
ADD INDEX idx_eyelid_left (eyelid_left(255)),
ADD INDEX idx_systemic_disease (systemic_disease(255)),
ADD INDEX idx_medication (medication(255));
