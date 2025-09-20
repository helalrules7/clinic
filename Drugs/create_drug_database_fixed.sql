-- =====================================================
-- ملف إنشاء قاعدة بيانات الأدوية المصرية
-- Egyptian Drug Database Creation Script
-- =====================================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS egyptian_drugs;
USE egyptian_drugs;

-- إنشاء جدول الأدوية
CREATE TABLE IF NOT EXISTS drugs (
    ID INTEGER NOT NULL PRIMARY KEY,
    FirstName VARCHAR(86),
    LastName VARCHAR(100),
    price VARCHAR(100),
    priceold VARCHAR(100),
    imageid VARCHAR(30),
    Company VARCHAR(54),
    Pharmacology VARCHAR(96),
    SRDE VARCHAR(60),
    GI VARCHAR(1000),
    Route VARCHAR(100),
    
    -- فهارس لتحسين الأداء
    INDEX idx_company (Company),
    INDEX idx_pharmacology (Pharmacology),
    INDEX idx_price (price),
    INDEX idx_route (Route)
);

-- إنشاء جدول للشركات
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(54) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إنشاء جدول للفئات الدوائية
CREATE TABLE IF NOT EXISTS drug_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(96) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إنشاء جدول لطرق الإعطاء
CREATE TABLE IF NOT EXISTS administration_routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ملء جدول الشركات
INSERT IGNORE INTO companies (name)
SELECT DISTINCT Company 
FROM drugs 
WHERE Company IS NOT NULL AND Company != '';

-- ملء جدول الفئات الدوائية
INSERT IGNORE INTO drug_categories (name)
SELECT DISTINCT Pharmacology 
FROM drugs 
WHERE Pharmacology IS NOT NULL AND Pharmacology != '';

-- ملء جدول طرق الإعطاء
INSERT IGNORE INTO administration_routes (name)
SELECT DISTINCT Route 
FROM drugs 
WHERE Route IS NOT NULL AND Route != '';

-- إنشاء Views مفيدة
CREATE VIEW drug_summary AS
SELECT 
    ID,
    FirstName as drug_name,
    LastName as active_ingredient,
    price,
    Company,
    Pharmacology as category,
    Route as administration_route
FROM drugs;

CREATE VIEW company_stats AS
SELECT 
    Company,
    COUNT(*) as drug_count,
    AVG(CAST(price AS DECIMAL(10,2))) as avg_price
FROM drugs 
WHERE Company IS NOT NULL AND Company != ''
GROUP BY Company
ORDER BY drug_count DESC;

CREATE VIEW category_stats AS
SELECT 
    Pharmacology as category,
    COUNT(*) as drug_count
FROM drugs 
WHERE Pharmacology IS NOT NULL AND Pharmacology != ''
GROUP BY Pharmacology
ORDER BY drug_count DESC;

-- إنشاء Stored Procedures
DELIMITER //

CREATE PROCEDURE SearchDrugs(IN search_term VARCHAR(255))
BEGIN
    SELECT 
        ID,
        FirstName as drug_name,
        LastName as active_ingredient,
        price,
        Company,
        Pharmacology as category,
        Route as administration_route
    FROM drugs 
    WHERE 
        FirstName LIKE CONCAT('%', search_term, '%') OR
        LastName LIKE CONCAT('%', search_term, '%') OR
        Company LIKE CONCAT('%', search_term, '%') OR
        Pharmacology LIKE CONCAT('%', search_term, '%') OR
        SRDE LIKE CONCAT('%', search_term, '%')
    ORDER BY ID
    LIMIT 100;
END //

CREATE PROCEDURE GetDrugsByCompany(IN company_name VARCHAR(54))
BEGIN
    SELECT 
        ID,
        FirstName as drug_name,
        LastName as active_ingredient,
        price,
        Pharmacology as category
    FROM drugs 
    WHERE Company = company_name
    ORDER BY FirstName;
END //

CREATE PROCEDURE GetDrugsByCategory(IN category_name VARCHAR(96))
BEGIN
    SELECT 
        ID,
        FirstName as drug_name,
        LastName as active_ingredient,
        price,
        Company
    FROM drugs 
    WHERE Pharmacology = category_name
    ORDER BY FirstName;
END //

DELIMITER ;

-- إنشاء Triggers للتحديث التلقائي
DELIMITER //

CREATE TRIGGER update_company_after_insert
AFTER INSERT ON drugs
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO companies (name) VALUES (NEW.Company);
END //

CREATE TRIGGER update_category_after_insert
AFTER INSERT ON drugs
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO drug_categories (name) VALUES (NEW.Pharmacology);
END //

CREATE TRIGGER update_route_after_insert
AFTER INSERT ON drugs
FOR EACH ROW
BEGIN
    INSERT IGNORE INTO administration_routes (name) VALUES (NEW.Route);
END //

DELIMITER ;

-- إنشاء مستخدم للقاعدة
CREATE USER IF NOT EXISTS 'drug_user'@'localhost' IDENTIFIED BY 'DrugPassword123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON egyptian_drugs.* TO 'drug_user'@'localhost';
FLUSH PRIVILEGES;

-- إحصائيات نهائية
SELECT 
    'Total Drugs' as metric,
    COUNT(*) as value
FROM drugs
UNION ALL
SELECT 
    'Total Companies',
    COUNT(DISTINCT Company)
FROM drugs
WHERE Company IS NOT NULL AND Company != ''
UNION ALL
SELECT 
    'Total Categories',
    COUNT(DISTINCT Pharmacology)
FROM drugs
WHERE Pharmacology IS NOT NULL AND Pharmacology != ''
UNION ALL
SELECT 
    'Total Routes',
    COUNT(DISTINCT Route)
FROM drugs
WHERE Route IS NOT NULL AND Route != '';

-- انتهاء السكريبت
SELECT 'Database creation completed successfully!' as status;
