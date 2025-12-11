# إصلاح مشكلة 404 للملفات

## المشكلة
الملفات لا تُحمّل لأن المسار الفرعي `/opth/docs` غير مضبوط بشكل صحيح.

## الحل

### 1. إعادة بناء المشروع

```bash
cd docs
npm run build
```

### 2. التأكد من المسار في vite.config.js

يجب أن يكون `base: '/opth/docs/'` في ملف `vite.config.js`

### 3. إعداد Apache/Nginx

#### Apache (.htaccess)
تم تحديث `.htaccess` ليدعم المسار `/opth/docs/`

#### Nginx
أضف هذا في إعدادات Nginx في Hestia:

```nginx
location /opth/docs {
    alias /home/username/web/yourdomain.com/public_html/opth/docs;
    try_files $uri $uri/ /opth/docs/index.html;
    index index.html;
}
```

### 4. هيكل المجلدات على الخادم

```
/home/username/web/yourdomain.com/public_html/
└── opth/
    └── docs/
        ├── index.html
        ├── assets/
        │   ├── main.js
        │   └── main.css
        └── .htaccess
```

### 5. التحقق من المسارات

بعد البناء، تأكد من أن الملفات في `dist/` تحتوي على المسارات الصحيحة:

```bash
# فحص ملف index.html بعد البناء
cat dist/index.html | grep -E "(href|src)"
```

يجب أن تبدأ جميع المسارات بـ `/opth/docs/` أو `./`

## استكشاف الأخطاء

### إذا كانت الملفات لا تزال لا تُحمّل:

1. **تحقق من المسار في المتصفح:**
   - افتح Developer Tools (F12)
   - اذهب إلى Network tab
   - شاهد المسارات الفعلية التي يتم طلبها

2. **تحقق من .htaccess:**
   ```bash
   # تأكد من أن .htaccess موجود في مجلد docs
   ls -la /path/to/docs/.htaccess
   ```

3. **تحقق من صلاحيات الملفات:**
   ```bash
   chmod -R 755 /path/to/docs/
   ```

4. **إعادة بناء المشروع:**
   ```bash
   cd docs
   rm -rf dist/
   npm run build
   ```

## ملاحظة مهمة

إذا قمت بتغيير `base` في `vite.config.js`، يجب إعادة بناء المشروع:
```bash
npm run build
```
