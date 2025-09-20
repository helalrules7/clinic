-- Update glasses_prescriptions table to add Near Vision fields
-- Run this script to update existing database

ALTER TABLE glasses_prescriptions 
ADD COLUMN near_sphere_r DECIMAL(4,2) NULL AFTER distance_axis_l,
ADD COLUMN near_cylinder_r DECIMAL(4,2) NULL AFTER near_sphere_r,
ADD COLUMN near_axis_r INT NULL AFTER near_cylinder_r,
ADD COLUMN near_sphere_l DECIMAL(4,2) NULL AFTER near_axis_r,
ADD COLUMN near_cylinder_l DECIMAL(4,2) NULL AFTER near_sphere_l,
ADD COLUMN near_axis_l INT NULL AFTER near_cylinder_l;
