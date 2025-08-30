# نظام إدارة عيادة رؤية (Roaya Clinic Management System)

## نظرة عامة
نظام إدارة شامل لعيادة طب العيون يتضمن إدارة المرضى، المواعيد، الاستشارات، الوصفات الطبية، والمدفوعات.

## المتطلبات
- PHP 8.2 أو أحدث
- MySQL 5.7 أو أحدث
- Apache 2.4 مع mod_rewrite
- Composer

## التثبيت والإعداد

### 1. تثبيت المتطلبات
```bash
# تثبيت PHP 8.2
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install php8.2 php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-intl libapache2-mod-php8.2

# تفعيل PHP 8.2 لـ Apache
sudo a2dismod php8.1
sudo a2enmod php8.2
sudo a2enmod rewrite

# تثبيت Composer
sudo apt install composer
```

### 2. إعداد قاعدة البيانات
```bash
# إنشاء قاعدة البيانات والمستخدم
mysql -u root -p -e "
CREATE DATABASE IF NOT EXISTS AhmedHelal_roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'AhmedHelal_roaya'@'localhost' IDENTIFIED BY 'Carmen@1230';
GRANT ALL PRIVILEGES ON AhmedHelal_roaya.* TO 'AhmedHelal_roaya'@'localhost';
FLUSH PRIVILEGES;
"

# إنشاء الجداول
mysql -u AhmedHelal_roaya -p'Carmen@1230' AhmedHelal_roaya < sql/schema.sql
```

### 3. تثبيت التبعيات
```bash
composer install
```

### 4. إعداد Apache
```bash
# نسخ ملف التكوين
sudo cp roaya-clinic.conf /etc/apache2/sites-available/

# تعطيل الموقع الافتراضي وتفعيل الموقع الجديد
sudo a2dissite 000-default
sudo a2ensite roaya-clinic

# إعادة تشغيل Apache
sudo systemctl restart apache2
```

### 5. إعداد ملف البيئة
```bash
# نسخ ملف البيئة
cp env.example .env

# تحديث معلومات قاعدة البيانات
sed -i 's/DB_NAME=roaya/DB_NAME=AhmedHelal_roaya/' .env
sed -i 's/DB_USER=root/DB_USER=AhmedHelal_roaya/' .env
sed -i 's/DB_PASS=\*\*\*\*\*\*\*\*/DB_PASS=Carmen@1230/' .env
```

## الميزات

### للمرضى
- إدارة الملفات الطبية
- جدولة المواعيد
- عرض التاريخ الطبي
- إدارة المدفوعات

### للأطباء
- لوحة تحكم شاملة
- إدارة الاستشارات
- كتابة الوصفات الطبية
- عرض تقويم المواعيد

### للسكرتارية
- إدارة المواعيد
- تسجيل المرضى الجدد
- إدارة المدفوعات
- طباعة الفواتير

### للمدير
- إدارة المستخدمين
- التقارير والإحصائيات
- إعدادات النظام

## المسارات الرئيسية

- `/` - صفحة تسجيل الدخول
- `/login` - تسجيل الدخول
- `/admin/dashboard` - لوحة تحكم المدير
- `/doctor/dashboard` - لوحة تحكم الطبيب
- `/secretary/dashboard` - لوحة تحكم السكرتارية

## الأمان

- حماية من CSRF
- رؤوس أمان HTTP
- تشفير كلمات المرور
- حماية الملفات الحساسة
- تسجيل الأحداث

## الدعم

للمساعدة والدعم التقني، يرجى التواصل مع فريق التطوير.

## الترخيص

هذا النظام مملوك لشركة رؤية للخدمات الطبية.
