-- Update glasses prescription fields to allow text values with + and - signs
-- This script changes glasses fields from DECIMAL/INT to VARCHAR to support values like "+2.50", "-1.25", "90"

-- Change sphere fields from DECIMAL(4,2) to VARCHAR(20)
ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_sphere_r VARCHAR(20) NULL COMMENT 'Distance sphere right eye - supports values like 0.00, +2.50, -1.25';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_sphere_l VARCHAR(20) NULL COMMENT 'Distance sphere left eye - supports values like 0.00, +2.50, -1.25';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_sphere_r VARCHAR(20) NULL COMMENT 'Near sphere right eye - supports values like 0.00, +2.50, -1.25';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_sphere_l VARCHAR(20) NULL COMMENT 'Near sphere left eye - supports values like 0.00, +2.50, -1.25';

-- Change cylinder fields from DECIMAL(4,2) to VARCHAR(20)
ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_cylinder_r VARCHAR(20) NULL COMMENT 'Distance cylinder right eye - supports values like 0.00, +1.50, -0.75';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_cylinder_l VARCHAR(20) NULL COMMENT 'Distance cylinder left eye - supports values like 0.00, +1.50, -0.75';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_cylinder_r VARCHAR(20) NULL COMMENT 'Near cylinder right eye - supports values like 0.00, +1.50, -0.75';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_cylinder_l VARCHAR(20) NULL COMMENT 'Near cylinder left eye - supports values like 0.00, +1.50, -0.75';

-- Change axis fields from INT to VARCHAR(10)
ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_axis_r VARCHAR(10) NULL COMMENT 'Distance axis right eye - supports values like 0, 90, 180';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN distance_axis_l VARCHAR(10) NULL COMMENT 'Distance axis left eye - supports values like 0, 90, 180';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_axis_r VARCHAR(10) NULL COMMENT 'Near axis right eye - supports values like 0, 90, 180';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN near_axis_l VARCHAR(10) NULL COMMENT 'Near axis left eye - supports values like 0, 90, 180';

-- Change PD fields from DECIMAL(4,1) to VARCHAR(20)
ALTER TABLE glasses_prescriptions 
MODIFY COLUMN PD_DISTANCE VARCHAR(20) NULL COMMENT 'PD Distance - supports values like 62.0, +2, -1';

ALTER TABLE glasses_prescriptions 
MODIFY COLUMN PD_NEAR VARCHAR(20) NULL COMMENT 'PD Near - supports values like 60.0, +2, -1';

-- Add indexes for better performance on text fields
CREATE INDEX idx_distance_sphere_r ON glasses_prescriptions(distance_sphere_r);
CREATE INDEX idx_distance_sphere_l ON glasses_prescriptions(distance_sphere_l);
CREATE INDEX idx_near_sphere_r ON glasses_prescriptions(near_sphere_r);
CREATE INDEX idx_near_sphere_l ON glasses_prescriptions(near_sphere_l);
CREATE INDEX idx_PD_DISTANCE ON glasses_prescriptions(PD_DISTANCE);
CREATE INDEX idx_PD_NEAR ON glasses_prescriptions(PD_NEAR);
