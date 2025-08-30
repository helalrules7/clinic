-- Remove Add Power columns from glasses_prescriptions table
ALTER TABLE glasses_prescriptions 
DROP COLUMN add_power_r,
DROP COLUMN add_power_l;
