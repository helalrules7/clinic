# دليل رفع المشروع على السيرفر 🚀

## 📦 محتويات الملف المضغوط

الملف `clinic-complete-project.zip` يحتوي على:

### 📁 المجلدات الأساسية:
- **app/** - كود التطبيق الرئيسي
- **public/** - ملفات الويب العامة (index.php, .htaccess)
- **vendor/** - مكتبات Composer
- **storage/** - ملفات التخزين (uploads, logs, exports)
- **sql/** - ملفات قاعدة البيانات

### 📄 الملفات المهمة:
- **composer.json/lock** - إعدادات Composer
- **env.example** - ملف البيئة النموذجي
- **.htaccess** - إعدادات Apache الرئيسية
- **install.sh** - سكريبت التثبيت التلقائي

## 🔧 خطوات الرفع على السيرفر

### 1. رفع الملفات:
```bash
# رفع وفك ضغط الملف
unzip clinic-complete-project.zip
cd clinic/
```

### 2. إعداد البيئة:
```bash
# نسخ ملف البيئة
cp env.example .env

# تحرير إعدادات قاعدة البيانات
nano .env
```

### 3. إعداد قاعدة البيانات:
```bash
# إنشاء قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# استيراد البيانات
mysql -u root -p roaya < sql/schema.sql
mysql -u root -p roaya < sql/seed.sql
```

### 4. إعداد الصلاحيات:
```bash
# صلاحيات المجلدات
chmod -R 755 storage/
chmod -R 755 public/
chown -R www-data:www-data storage/
```

### 5. إعداد Apache:
```bash
# نسخ إعدادات Virtual Host
cp apache-virtualhost.conf /etc/apache2/sites-available/clinic.conf
a2ensite clinic.conf
systemctl reload apache2
```

## 🔒 الأمان والإعدادات

### متطلبات PHP:
- PHP 8.2+
- ext-pdo, ext-json, ext-mbstring
- MySQL 8.0+

### إعدادات .env المطلوبة:
```env
DB_HOST=localhost
DB_NAME=roaya
DB_USER=your_user
DB_PASS=your_password

APP_ENV=production
APP_DEBUG=false
```

## ✅ التحقق من التثبيت

1. تصفح الموقع: `http://your-domain.com`
2. تسجيل الدخول بالحساب الافتراضي
3. اختبار وظائف التحاليل الطبية

## 📞 الدعم

في حالة وجود مشاكل، تحقق من:
- أخطاء Apache: `/var/log/apache2/error.log`
- أخطاء PHP: `storage/logs/`
- صلاحيات الملفات
