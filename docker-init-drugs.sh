#!/bin/bash

# سكريبت لتهيئة قاعدة بيانات الأدوية بعد بدء تشغيل MySQL

echo "⏳ انتظار بدء تشغيل MySQL..."

# انتظار حتى يكون MySQL جاهزاً
until mysqladmin ping -h db -u root -proot_password --silent; do
    echo "⏳ في انتظار MySQL..."
    sleep 2
done

echo "✅ MySQL جاهز!"

# إنشاء قاعدة بيانات الأدوية إذا لم تكن موجودة
DB_EXISTS=$(mysql -h db -u root -proot_password -e "SHOW DATABASES LIKE 'hclinic_drugs';" | grep hclinic_drugs)

if [ -z "$DB_EXISTS" ]; then
    echo "📦 إنشاء قاعدة بيانات الأدوية..."
    mysql -h db -u root -proot_password -e "CREATE DATABASE IF NOT EXISTS hclinic_drugs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # إنشاء مستخدم لقاعدة بيانات الأدوية
    echo "👤 إنشاء مستخدم قاعدة بيانات الأدوية..."
    mysql -h db -u root -proot_password -e "CREATE USER IF NOT EXISTS 'drugs_user'@'%' IDENTIFIED BY 'drugs_password';"
    mysql -h db -u root -proot_password -e "GRANT ALL PRIVILEGES ON hclinic_drugs.* TO 'drugs_user'@'%';"
    mysql -h db -u root -proot_password -e "FLUSH PRIVILEGES;"
    
    echo "📥 استيراد البيانات من ملف SQL..."
    if [ -f "/var/www/html/app/seed/hclinic_drugs.sql" ]; then
        mysql -h db -u root -proot_password hclinic_drugs < /var/www/html/app/seed/hclinic_drugs.sql
        echo "✅ تم استيراد بيانات الأدوية بنجاح!"
    else
        echo "⚠️  ملف SQL غير موجود: /var/www/html/app/seed/hclinic_drugs.sql"
    fi
else
    echo "ℹ️  قاعدة بيانات الأدوية موجودة بالفعل"
fi

echo "✅ اكتملت عملية تهيئة قاعدة بيانات الأدوية!"
