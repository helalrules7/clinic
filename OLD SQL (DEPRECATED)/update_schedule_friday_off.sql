-- Update all doctors schedule: Friday is always off, other days are working
-- This script will set all doctors to work Sunday-Thursday and Saturday, but Friday is always off

-- First, let's see what doctors we have
SELECT id, display_name FROM doctors;

-- Update all existing doctor schedules
-- Delete existing schedules first
DELETE FROM doctor_schedule;

-- Insert new schedules for all doctors - working all days except Friday
-- Get all doctor IDs and create schedules for each day (0-6) except Friday (5)
INSERT INTO doctor_schedule (doctor_id, weekday, is_working, work_start, work_end)
SELECT 
    d.id as doctor_id,
    weekday_num.weekday,
    CASE 
        WHEN weekday_num.weekday = 5 THEN FALSE  -- Friday is always off
        ELSE TRUE  -- All other days are working
    END as is_working,
    CASE 
        WHEN weekday_num.weekday = 5 THEN '00:00:00'  -- Friday: no working hours
        ELSE '14:00:00'  -- Other days: 2 PM start
    END as work_start,
    CASE 
        WHEN weekday_num.weekday = 5 THEN '00:00:00'  -- Friday: no working hours
        ELSE '23:00:00'  -- Other days: 11 PM end
    END as work_end
FROM doctors d
CROSS JOIN (
    SELECT 0 as weekday UNION ALL  -- Sunday
    SELECT 1 UNION ALL             -- Monday
    SELECT 2 UNION ALL             -- Tuesday
    SELECT 3 UNION ALL             -- Wednesday
    SELECT 4 UNION ALL             -- Thursday
    SELECT 5 UNION ALL             -- Friday (OFF)
    SELECT 6                       -- Saturday
) weekday_num
ORDER BY d.id, weekday_num.weekday;

-- Verify the results
SELECT 
    ds.doctor_id,
    d.display_name,
    ds.weekday,
    CASE ds.weekday
        WHEN 0 THEN 'Sunday'
        WHEN 1 THEN 'Monday'
        WHEN 2 THEN 'Tuesday'
        WHEN 3 THEN 'Wednesday'
        WHEN 4 THEN 'Thursday'
        WHEN 5 THEN 'Friday (OFF)'
        WHEN 6 THEN 'Saturday'
    END as day_name,
    ds.is_working,
    ds.work_start,
    ds.work_end
FROM doctor_schedule ds
JOIN doctors d ON ds.doctor_id = d.id
ORDER BY ds.doctor_id, ds.weekday;
