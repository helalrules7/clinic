# حل مشكلة 404 - دليل استكشاف الأخطاء 🔧

## المشكلة: 404 عند الوصول لـ https://roaya.ahmedhelal.dev/login

### الحلول المرتبة حسب الأولوية:

## 🎯 الحل الأول: إعداد Apache Virtual Host (الأفضل)

### 1. إنشاء Virtual Host:
```bash
# نسخ ملف الإعداد
sudo cp apache-subdomain.conf /etc/apache2/sites-available/roaya.conf

# تفعيل الموقع
sudo a2ensite roaya.conf

# تفعيل mod_rewrite
sudo a2enmod rewrite
sudo a2enmod headers

# إعادة تشغيل Apache
sudo systemctl reload apache2
```

### 2. التأكد من DocumentRoot:
DocumentRoot يجب أن يشير إلى: `/var/www/html/clinic/public`

## 🎯 الحل الثاني: استخدام .htaccess في الجذر

### إذا لم تستطع تغيير Virtual Host:
```bash
# نسخ .htaccess للجذر
cp .htaccess-root .htaccess

# التأكد من الصلاحيات
chmod 644 .htaccess
```

## 🎯 الحل الثالث: فحص مسار الملفات

### تحقق من المسارات:
```bash
# التأكد من وجود الملفات
ls -la /var/www/html/clinic/public/index.php
ls -la /var/www/html/clinic/public/.htaccess

# فحص الصلاحيات
chmod 755 /var/www/html/clinic/public/
chmod 644 /var/www/html/clinic/public/index.php
```

## 🔍 تشخيص المشاكل:

### 1. فحص Apache Error Log:
```bash
sudo tail -f /var/log/apache2/error.log
```

### 2. اختبار الوصول المباشر:
```bash
# اختبار index.php مباشرة
curl -I https://roaya.ahmedhelal.dev/index.php
curl -I https://roaya.ahmedhelal.dev/public/index.php
```

### 3. فحص mod_rewrite:
```bash
# التأكد من تفعيل mod_rewrite
apache2ctl -M | grep rewrite
```

## 🛠️ إعدادات بديلة:

### إذا كان الموقع في مجلد فرعي:
```apache
# في .htaccess
RewriteBase /clinic/
```

### لمشاكل SSL:
```bash
# فحص شهادة SSL
openssl s_client -connect roaya.ahmedhelal.dev:443 -servername roaya.ahmedhelal.dev
```

## 📋 Checklist سريع:

- [ ] Apache Virtual Host يشير إلى `/public/`
- [ ] mod_rewrite مفعل
- [ ] AllowOverride All في Directory
- [ ] ملف index.php موجود في public/
- [ ] صلاحيات الملفات صحيحة (755/644)
- [ ] .htaccess موجود في public/
- [ ] DNS يشير للسيرفر الصحيح

## 🚨 الأخطاء الشائعة:

1. **DocumentRoot خطأ** - يجب أن يشير لمجلد public
2. **mod_rewrite غير مفعل** - `a2enmod rewrite`
3. **AllowOverride None** - يجب أن يكون All
4. **صلاحيات خاطئة** - المجلدات 755، الملفات 644
5. **مسار .htaccess خطأ** - يجب أن يكون في public/

## 📞 اختبار سريع:

```bash
# اختبار الاتصال
curl -v https://roaya.ahmedhelal.dev/

# فحص DNS
nslookup roaya.ahmedhelal.dev

# فحص Apache config
sudo apache2ctl -t
```
