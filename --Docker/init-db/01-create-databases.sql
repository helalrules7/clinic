-- Create databases for the clinic application
CREATE DATABASE IF NOT EXISTS hclinic_roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS hclinic_drugs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create clinic_user with native password authentication
CREATE USER IF NOT EXISTS 'clinic_user'@'%' IDENTIFIED WITH mysql_native_password BY 'clinic_password';

-- Grant all privileges including SUPER for import operations
GRANT ALL PRIVILEGES ON *.* TO 'clinic_user'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
