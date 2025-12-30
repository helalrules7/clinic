#!/bin/bash

# سكريبت لتهيئة قاعدة البيانات بعد بدء تشغيل MySQL

echo "⏳ انتظار بدء تشغيل MySQL..."

# انتظار حتى يكون MySQL جاهزاً
until mysqladmin ping -h db -u root -proot_password --silent; do
    echo "⏳ في انتظار MySQL..."
    sleep 2
done

echo "✅ MySQL جاهز!"

# التحقق من وجود قاعدة البيانات
DB_EXISTS=$(mysql -h db -u root -proot_password -e "SHOW DATABASES LIKE 'roaya';" | grep roaya)

if [ -z "$DB_EXISTS" ]; then
    echo "📦 إنشاء قاعدة البيانات..."
    mysql -h db -u root -proot_password -e "CREATE DATABASE IF NOT EXISTS roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    echo "📥 استيراد البيانات من ملف SQL..."
    if [ -f "/var/www/html/app/seed/hclinic_roaya.sql" ]; then
        mysql -h db -u root -proot_password roaya < /var/www/html/app/seed/hclinic_roaya.sql
        echo "✅ تم استيراد البيانات بنجاح!"
    else
        echo "⚠️  ملف SQL غير موجود: /var/www/html/app/seed/hclinic_roaya.sql"
    fi
else
    echo "ℹ️  قاعدة البيانات موجودة بالفعل"
fi

echo "✅ اكتملت عملية التهيئة!"
