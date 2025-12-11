# دليل نشر التوثيق على Hestia Control Panel

## المتطلبات

1. خادم Hestia مع Nginx أو Apache
2. Node.js و npm مثبتة على الخادم
3. وصول SSH للخادم

## الخطوات

### 1. بناء المشروع محلياً

```bash
cd docs
npm install
npm run build
```

سيتم إنشاء مجلد `dist/` يحتوي على الملفات الجاهزة للنشر.

### 2. إعداد موقع جديد في Hestia

1. سجل الدخول إلى Hestia Control Panel
2. اذهب إلى **Web** → **Add Web Domain**
3. أدخل اسم النطاق (مثلاً: `docs.yourdomain.com`)
4. اختر **Use another domain as alias** إذا كنت تريد استخدام نطاق فرعي
5. اضغط **Add**

### 3. رفع الملفات

#### الطريقة 1: استخدام SCP/SFTP

```bash
# من جهازك المحلي
cd docs/dist
scp -r * user@your-server.com:/home/user/web/docs.yourdomain.com/public_html/
```

#### الطريقة 2: استخدام Git (موصى به)

```bash
# على الخادم
cd /home/user/web/docs.yourdomain.com/public_html/
git clone your-repo-url .
cd docs
npm install
npm run build
# انسخ الملفات من dist إلى public_html
cp -r dist/* ../
```

### 4. إعداد Nginx (إذا كان Hestia يستخدم Nginx)

في Hestia، اذهب إلى **Web** → **Edit** → **Nginx Settings** وأضف:

```nginx
location / {
    try_files $uri $uri/ /index.html;
}

# Cache static assets
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 5. إعداد Apache (إذا كان Hestia يستخدم Apache)

في Hestia، اذهب إلى **Web** → **Edit** → **Apache Settings** وأضف:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.html [L]
</IfModule>
```

### 6. إعداد SSL (HTTPS)

1. في Hestia، اذهب إلى **Web** → **Edit**
2. اضغط **Let's Encrypt** لتفعيل SSL مجاناً
3. أو استخدم شهادة SSL موجودة

### 7. التحقق من النشر

افتح المتصفح وانتقل إلى:
- `https://docs.yourdomain.com`

## إعدادات متقدمة

### تحديث تلقائي مع Git

أنشئ ملف `deploy.sh` في مجلد المشروع:

```bash
#!/bin/bash
cd /home/user/web/docs.yourdomain.com/public_html/docs
git pull origin main
npm install
npm run build
cp -r dist/* ../public_html/
echo "Deployment completed!"
```

ثم قم بتشغيله:
```bash
chmod +x deploy.sh
./deploy.sh
```

### إعداد Cron للتحديث التلقائي (اختياري)

```bash
# افتح crontab
crontab -e

# أضف السطر التالي للتحديث كل يوم في الساعة 2 صباحاً
0 2 * * * /home/user/web/docs.yourdomain.com/public_html/docs/deploy.sh
```

## استكشاف الأخطاء

### المشكلة: الصفحات لا تعمل (404)

**الحل:** تأكد من إعداد rewrite rules بشكل صحيح في Nginx أو Apache.

### المشكلة: الملفات الثابتة لا تُحمّل

**الحل:** تحقق من مسارات الملفات في `vite.config.js` وتأكد من أن `base` مضبوط بشكل صحيح.

### المشكلة: الأخطاء في Console

**الحل:** تأكد من أن جميع الملفات تم رفعها بشكل صحيح وأن الصلاحيات صحيحة:
```bash
chmod -R 755 /home/user/web/docs.yourdomain.com/public_html/
```

## ملاحظات مهمة

1. تأكد من أن `base` في `vite.config.js` يطابق المسار على الخادم
2. إذا كان الموقع في مجلد فرعي، غيّر `base` إلى `/subfolder/`
3. تأكد من تفعيل gzip compression في Hestia
4. استخدم CDN للملفات الثابتة لتحسين الأداء (اختياري)
