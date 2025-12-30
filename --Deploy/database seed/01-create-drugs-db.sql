-- إنشاء قاعدة بيانات الأدوية
CREATE DATABASE IF NOT EXISTS hclinic_drugs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- إنشاء مستخدم لقاعدة بيانات الأدوية
CREATE USER IF NOT EXISTS 'drugs_user'@'%' IDENTIFIED BY 'drugs_password';
GRANT ALL PRIVILEGES ON hclinic_drugs.* TO 'drugs_user'@'%';
FLUSH PRIVILEGES;
