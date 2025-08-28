#!/bin/bash

echo "🔧 إصلاح سريع لمشكلة 404 في roaya.ahmedhelal.dev"
echo "================================================="

# تحديد مسار المشروع
PROJECT_PATH="/var/www/html/clinic"
PUBLIC_PATH="$PROJECT_PATH/public"

echo "📁 التحقق من مسارات الملفات..."
if [ ! -d "$PUBLIC_PATH" ]; then
    echo "❌ مجلد public غير موجود في: $PUBLIC_PATH"
    exit 1
fi

if [ ! -f "$PUBLIC_PATH/index.php" ]; then
    echo "❌ ملف index.php غير موجود في: $PUBLIC_PATH"
    exit 1
fi

echo "✅ الملفات موجودة"

echo ""
echo "🔧 الحل الأول: تحديث .htaccess في public/"
echo "=============================================="

# نسخ .htaccess المحسن
cat > "$PUBLIC_PATH/.htaccess" << 'HTACCESS_END'
RewriteEngine On

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

# Send ALL requests to index.php
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,QSA]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
HTACCESS_END

echo "✅ تم تحديث .htaccess في public/"

echo ""
echo "🔧 الحل الثاني: إنشاء Virtual Host"
echo "=================================="

# إنشاء virtual host
VHOST_FILE="/etc/apache2/sites-available/roaya.conf"
cat > "$VHOST_FILE" << 'VHOST_END'
<VirtualHost *:80>
    ServerName roaya.ahmedhelal.dev
    DocumentRoot /var/www/html/clinic/public
    
    <Directory /var/www/html/clinic/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        FallbackResource /index.php
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/roaya-error.log
    CustomLog ${APACHE_LOG_DIR}/roaya-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName roaya.ahmedhelal.dev
    DocumentRoot /var/www/html/clinic/public
    
    <Directory /var/www/html/clinic/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        FallbackResource /index.php
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/roaya-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/roaya-ssl-access.log combined
</VirtualHost>
VHOST_END

echo "✅ تم إنشاء Virtual Host في: $VHOST_FILE"

echo ""
echo "🔧 تفعيل الإعدادات"
echo "=================="

# تفعيل mod_rewrite
a2enmod rewrite
a2enmod headers

# تفعيل الموقع
a2ensite roaya.conf

# إعادة تشغيل Apache
systemctl reload apache2

echo "✅ تم تفعيل جميع الإعدادات"

echo ""
echo "🔍 ضبط الصلاحيات"
echo "=================="

# ضبط الصلاحيات
chmod 755 "$PUBLIC_PATH"
chmod 644 "$PUBLIC_PATH/index.php"
chmod 644 "$PUBLIC_PATH/.htaccess"

echo "✅ تم ضبط الصلاحيات"

echo ""
echo "🎉 تم الانتهاء!"
echo "==============="
echo "جرب الآن: https://roaya.ahmedhelal.dev/login"
echo ""
echo "إذا لم يعمل، تحقق من:"
echo "- sudo tail -f /var/log/apache2/roaya-error.log"
echo "- sudo apache2ctl -t"
echo ""
