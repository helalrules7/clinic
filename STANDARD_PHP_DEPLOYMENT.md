# دليل تثبيت نظام عيادة طب العيون على خادم PHP عادي

## الطرق المختلفة للتشغيل

### 1. خادم Apache العادي

#### متطلبات النظام:
- PHP 8.0 أو أحدث
- Apache 2.4+ مع mod_rewrite
- MySQL/MariaDB 5.7+
- Composer

#### خطوات التثبيت:

##### أ) إعداد Apache Virtual Host

إنشاء ملف Virtual Host جديد:
```bash
sudo nano /etc/apache2/sites-available/roaya-clinic.conf
```

محتوى الملف:
```apache
<VirtualHost *:80>
    ServerName roaya-clinic.local
    ServerAlias www.roaya-clinic.local
    DocumentRoot /var/www/html/clinic/public
    
    <Directory /var/www/html/clinic/public>
        AllowOverride All
        Require all granted
        
        # Enable URL rewriting
        RewriteEngine On
        
        # Handle requests that don't match existing files/directories
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Security: Block access to sensitive directories
    <DirectoryMatch "/(app|config|storage|vendor|sql)">
        Require all denied
    </DirectoryMatch>
    
    # Block sensitive files
    <Files ".env">
        Require all denied
    </Files>
    
    <Files "composer.*">
        Require all denied
    </Files>
    
    ErrorLog ${APACHE_LOG_DIR}/roaya-clinic_error.log
    CustomLog ${APACHE_LOG_DIR}/roaya-clinic_access.log combined
</VirtualHost>
```

##### ب) تفعيل الموقع:
```bash
# تفعيل mod_rewrite
sudo a2enmod rewrite

# تفعيل الموقع
sudo a2ensite roaya-clinic.conf

# إعادة تشغيل Apache
sudo systemctl restart apache2

# إضافة الدومين إلى hosts file للاختبار المحلي
echo "127.0.0.1 roaya-clinic.local" | sudo tee -a /etc/hosts
```

### 2. خادم Nginx

#### إعداد Nginx Server Block:

إنشاء ملف إعداد جديد:
```bash
sudo nano /etc/nginx/sites-available/roaya-clinic
```

محتوى الملف:
```nginx
server {
    listen 80;
    server_name roaya-clinic.local www.roaya-clinic.local;
    root /var/www/html/clinic/public;
    index index.php index.html;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Block access to sensitive directories
    location ~ ^/(app|config|storage|vendor|sql)/ {
        deny all;
        return 404;
    }
    
    # Block sensitive files
    location ~ /\.(env|htaccess) {
        deny all;
        return 404;
    }
    
    location ~ /composer\.(json|lock) {
        deny all;
        return 404;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Logs
    access_log /var/log/nginx/roaya-clinic_access.log;
    error_log /var/log/nginx/roaya-clinic_error.log;
}
```

##### تفعيل الموقع:
```bash
# تفعيل الموقع
sudo ln -s /etc/nginx/sites-available/roaya-clinic /etc/nginx/sites-enabled/

# اختبار الإعدادات
sudo nginx -t

# إعادة تشغيل Nginx
sudo systemctl restart nginx

# إضافة الدومين للاختبار
echo "127.0.0.1 roaya-clinic.local" | sudo tee -a /etc/hosts
```

### 3. استخدام XAMPP/WAMP/LAMP

#### أ) XAMPP (Windows/Linux/Mac):
1. ضع مجلد `clinic` في `htdocs/`
2. تأكد من تفعيل mod_rewrite في Apache
3. اذهب إلى `http://localhost/clinic/public/`

#### ب) WAMP (Windows):
1. ضع المجلد في `www/`
2. تأكد من تفعيل rewrite_module
3. اذهب إلى `http://localhost/clinic/public/`

#### ج) LAMP (Linux):
1. ضع المجلد في `/var/www/html/`
2. اضبط الصلاحيات: `sudo chown -R www-data:www-data /var/www/html/clinic/`
3. اذهب إلى `http://localhost/clinic/public/`

### 4. خادم التطوير المدمج (Development Only)

للاختبار السريع فقط:
```bash
cd /var/www/html/clinic
php -S localhost:8000 -t public/
```

**ملاحظة:** هذا للتطوير فقط وليس للإنتاج!

## إعداد قاعدة البيانات

### إنشاء قاعدة البيانات:
```sql
CREATE DATABASE roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'roaya_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON roaya.* TO 'roaya_user'@'localhost';
FLUSH PRIVILEGES;
```

### استيراد البيانات:
```bash
mysql -u roaya_user -p roaya < sql/roaya_clinic.sql
```

## إعداد متغيرات البيئة

```bash
# نسخ ملف البيئة
cp .env.example .env

# تحرير الملف
nano .env
```

محتوى ملف `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://roaya-clinic.local

DB_HOST=localhost
DB_PORT=3306
DB_NAME=roaya
DB_USER=roaya_user
DB_PASS=your_password_here

SESSION_LIFETIME=120
```

## تثبيت Dependencies

```bash
# الانتقال إلى مجلد المشروع
cd /var/www/html/clinic

# تثبيت Composer dependencies
composer install --no-dev --optimize-autoloader

# إعداد الصلاحيات
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

## اختبار النظام

1. **افتح المتصفح** واذهب إلى:
   - Apache: `http://roaya-clinic.local`
   - XAMPP: `http://localhost/clinic/public/`
   - خادم التطوير: `http://localhost:8000`

2. **يجب أن تظهر صفحة الترحيب**

3. **اضغط "دخول النظام"** للانتقال إلى صفحة تسجيل الدخول

4. **استخدم بيانات الدخول التجريبية:**
   - أطباء: `dr_ahmed` / `password`
   - سكرتيرة: `sec` / `password`
   - مدير: `admin` / `password`

## حل المشاكل الشائعة

### مشكلة 404:
```bash
# تأكد من تفعيل mod_rewrite في Apache
sudo a2enmod rewrite
sudo systemctl restart apache2

# تحقق من ملف .htaccess
ls -la /var/www/html/clinic/public/.htaccess
```

### مشكلة الصلاحيات:
```bash
sudo chown -R www-data:www-data /var/www/html/clinic/
sudo chmod -R 755 /var/www/html/clinic/
sudo chmod -R 775 /var/www/html/clinic/storage/
```

### مشكلة قاعدة البيانات:
- تحقق من بيانات الاتصال في `.env`
- تأكد من تشغيل MySQL
- تحقق من صلاحيات المستخدم

## الأمان للإنتاج

1. **غيّر كلمات المرور الافتراضية**
2. **استخدم HTTPS**
3. **احذف الملفات الحساسة**
4. **حدّث النظام بانتظام**
5. **فعّل firewall**

---

**النظام الآن جاهز للعمل على أي خادم PHP عادي! 🚀**
