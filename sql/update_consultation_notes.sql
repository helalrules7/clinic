-- Update consultation_notes table to split Slit Lamp and Fundus examinations by eye
-- This script adds new columns for right and left eye examinations

-- Add new columns for Slit Lamp Examination
ALTER TABLE consultation_notes 
ADD COLUMN slit_lamp_right TEXT NULL AFTER IOP_left,
ADD COLUMN slit_lamp_left TEXT NULL AFTER slit_lamp_right;

-- Add new columns for Fundus Examination  
ALTER TABLE consultation_notes
ADD COLUMN fundus_right TEXT NULL AFTER slit_lamp_left,
ADD COLUMN fundus_left TEXT NULL AFTER fundus_right;

-- Migrate existing data from old columns to new columns
-- For Slit Lamp: Copy existing data to both eyes (assuming it was general)
UPDATE consultation_notes 
SET slit_lamp_right = slit_lamp,
    slit_lamp_left = slit_lamp
WHERE slit_lamp IS NOT NULL AND slit_lamp != '';

-- For Fundus: Copy existing data to both eyes (assuming it was general)
UPDATE consultation_notes 
SET fundus_right = fundus,
    fundus_left = fundus
WHERE fundus IS NOT NULL AND fundus != '';

-- Add indexes for better performance
CREATE INDEX idx_slit_lamp_right ON consultation_notes(slit_lamp_right(100));
CREATE INDEX idx_slit_lamp_left ON consultation_notes(slit_lamp_left(100));
CREATE INDEX idx_fundus_right ON consultation_notes(fundus_right(100));
CREATE INDEX idx_fundus_left ON consultation_notes(fundus_left(100));

-- Note: The old columns (slit_lamp, fundus) are kept for backward compatibility
-- They can be removed in a future migration if needed
