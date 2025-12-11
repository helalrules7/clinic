# دليل النشر السريع - Hestia

## الطريقة السريعة (موصى بها)

### 1. بناء المشروع

```bash
cd docs
npm install
npm run build
```

### 2. رفع الملفات إلى Hestia

#### خيار A: استخدام SCP

```bash
# من جهازك المحلي
cd docs/dist
scp -r * username@your-server.com:/home/username/web/docs.yourdomain.com/public_html/
```

#### خيار B: استخدام سكريبت النشر

```bash
# على الخادم
cd /home/username/web/docs.yourdomain.com/public_html/docs
chmod +x deploy.sh
./deploy.sh
```

### 3. إعداد Nginx في Hestia

1. اذهب إلى **Web** → اختر النطاق → **Edit**
2. اضغط **Nginx Settings**
3. الصق محتوى ملف `nginx.conf` في الإعدادات
4. احفظ

### 4. إعداد Apache في Hestia

1. اذهب إلى **Web** → اختر النطاق → **Edit**
2. اضغط **Apache Settings**
3. الصق محتوى ملف `.htaccess` (أو ارفع الملف)
4. احفظ

### 5. تفعيل SSL

1. في صفحة تحرير النطاق
2. اضغط **Let's Encrypt**
3. اختر النطاق واضغط **Save**

## هيكل المجلدات على Hestia

```
/home/username/web/docs.yourdomain.com/
├── public_html/          # الملفات المنشورة هنا
│   ├── index.html
│   ├── assets/
│   └── .htaccess
└── docs/                 # كود المصدر (اختياري)
    ├── src/
    ├── package.json
    └── deploy.sh
```

## تحديث الموقع

### تحديث يدوي

```bash
cd /home/username/web/docs.yourdomain.com/public_html/docs
git pull
npm install
npm run build
cp -r dist/* ../public_html/
```

### تحديث تلقائي مع Git Webhook

يمكنك إعداد webhook من GitHub/GitLab لتشغيل `deploy.sh` تلقائياً.

## استكشاف الأخطاء

### 404 على جميع الصفحات

**الحل:** تأكد من إعداد rewrite rules في Nginx/Apache

### الملفات الثابتة لا تُحمّل

**الحل:** تحقق من مسار `base` في `vite.config.js` - يجب أن يكون `/` للنطاق الرئيسي

### أخطاء CORS

**الحل:** تأكد من إعدادات الأمان في Hestia

## نصائح الأداء

1. ✅ فعّل Gzip compression في Hestia
2. ✅ استخدم CDN للملفات الثابتة (اختياري)
3. ✅ فعّل Browser caching
4. ✅ استخدم Cloudflare أو خدمة CDN مشابهة
