# دليل نشر نظام عيادة طب العيون على لوحة تحكم Hestia

## المتطلبات الأساسية

1. **PHP 8.0 أو أحدث**
2. **MySQL/MariaDB**
3. **Apache مع mod_rewrite**
4. **Composer**

## خطوات التثبيت على Hestia

### 1. إعداد قاعدة البيانات
```bash
# إنشاء قاعدة البيانات
CREATE DATABASE roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# إنشاء مستخدم قاعدة البيانات
CREATE USER 'roaya_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON roaya.* TO 'roaya_user'@'localhost';
FLUSH PRIVILEGES;

# استيراد البيانات
mysql -u roaya_user -p roaya < sql/roaya_clinic.sql
```

### 2. رفع الملفات
```bash
# رفع جميع ملفات المشروع إلى مجلد الدومين
# مثال: /home/username/web/yourdomain.com/
```

### 3. إعداد متغيرات البيئة
```bash
# نسخ ملف البيئة
cp .env.example .env

# تحرير الملف وإضافة بيانات قاعدة البيانات
nano .env
```

### 4. إعداد صلاحيات المجلدات
```bash
# إعداد صلاحيات مجلد التخزين
chmod -R 755 storage/
chmod -R 755 public/

# إعداد ملكية الملفات
chown -R username:username /home/username/web/yourdomain.com/
```

### 5. تثبيت Dependencies
```bash
# تثبيت Composer dependencies
composer install --no-dev --optimize-autoloader
```

### 6. إعداد Apache Virtual Host في Hestia

في لوحة تحكم Hestia:

1. اذهب إلى **Web** → **yourdomain.com** → **Edit**
2. في خانة **Proxy Template**، اختر **default**
3. في خانة **Backend Template**، اختر **PHP-FPM-8.x**
4. احفظ التغييرات

### 7. إعداد Document Root

**مهم جداً:** يجب تعيين Document Root إلى مجلد `public/`

في Hestia:
1. **Web** → **yourdomain.com** → **Edit**
2. في **Advanced Options**
3. غيّر **Document Root** من `/home/username/web/yourdomain.com/public_html/` 
   إلى `/home/username/web/yourdomain.com/public_html/public/`

### 8. اختبار النظام

1. افتح المتصفح واذهب إلى `https://yourdomain.com`
2. يجب أن تظهر صفحة الترحيب
3. اضغط على "دخول النظام" للانتقال إلى صفحة تسجيل الدخول

## بيانات تسجيل الدخول التجريبية

### الأطباء:
- **اسم المستخدم:** `dr_ahmed` | **كلمة المرور:** `password`
- **اسم المستخدم:** `dr_faramawy` | **كلمة المرور:** `password`

### الموظفون:
- **سكرتيرة:** `sec` | **كلمة المرور:** `password`
- **مدير النظام:** `admin` | **كلمة المرور:** `password`

## حل المشاكل الشائعة

### مشكلة 404 عند تسجيل الدخول
- تأكد من أن mod_rewrite مفعل في Apache
- تأكد من وجود ملف `.htaccess` في المجلد الرئيسي والمجلد `public/`
- تحقق من صلاحيات الملفات

### مشكلة قاعدة البيانات
- تأكد من صحة بيانات الاتصال في ملف `.env`
- تحقق من أن قاعدة البيانات تم إنشاؤها واستيراد البيانات

### مشكلة الصلاحيات
```bash
# إعطاء صلاحيات الكتابة لمجلد التخزين
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

## الأمان

1. **غيّر كلمات المرور الافتراضية** فوراً بعد التثبيت
2. **احذف أو غيّر اسم** ملف `HESTIA_DEPLOYMENT.md` بعد التثبيت
3. **تأكد من تحديث** PHP وMySQL بانتظام
4. **فعّل SSL Certificate** من خلال لوحة تحكم Hestia

## الدعم

في حالة مواجهة أي مشاكل:
1. تحقق من log files في `/var/log/apache2/`
2. تحقق من PHP error log
3. تأكد من أن جميع المتطلبات متوفرة

---
**تم إنشاء هذا الدليل خصيصاً لنظام عيادة طب العيون - Roaya Clinic**
