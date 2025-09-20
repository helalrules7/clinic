-- Update IOP fields to allow text values with + and - signs
-- This script changes IOP fields from DECIMAL to VARCHAR to support values like "+2", "-1", "15.0"

-- Change IOP_right from DECIMAL(5,2) to VARCHAR(20)
ALTER TABLE consultation_notes 
MODIFY COLUMN IOP_right VARCHAR(20) NULL;

-- Change IOP_left from DECIMAL(5,2) to VARCHAR(20)  
ALTER TABLE consultation_notes
MODIFY COLUMN IOP_left VARCHAR(20) NULL;

-- Add comments to clarify the change
ALTER TABLE consultation_notes 
MODIFY COLUMN IOP_right VARCHAR(20) NULL COMMENT 'Intraocular pressure right eye (mmHg) - supports values like 15.0, +2, -1';

ALTER TABLE consultation_notes
MODIFY COLUMN IOP_left VARCHAR(20) NULL COMMENT 'Intraocular pressure left eye (mmHg) - supports values like 15.0, +2, -1';

-- Add indexes for better performance on text fields
CREATE INDEX idx_IOP_right ON consultation_notes(IOP_right);
CREATE INDEX idx_IOP_left ON consultation_notes(IOP_left);
