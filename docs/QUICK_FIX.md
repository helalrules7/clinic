# إصلاح سريع لمشكلة 404

## المشكلة
الملفات لا تُحمّل لأن المسار `/opth/docs` غير مضبوط.

## الحل السريع

### 1. إعادة بناء المشروع

```bash
cd docs
npm run build
```

### 2. رفع الملفات إلى المسار الصحيح

```bash
# تأكد من أن الملفات في المسار الصحيح
# المسار يجب أن يكون:
# /home/username/web/yourdomain.com/public_html/opth/docs/
```

### 3. التحقق من vite.config.js

يجب أن يحتوي على:
```javascript
base: '/opth/docs/',
```

### 4. بعد البناء، تحقق من dist/index.html

يجب أن تكون المسارات في الملف المبنى (dist/index.html) تبدأ بـ `/opth/docs/`

### 5. إذا كانت المشكلة مستمرة

افتح `dist/index.html` بعد البناء وتحقق من المسارات. إذا كانت لا تزال خاطئة:

1. احذف `dist/`
2. أعد البناء: `npm run build`
3. تحقق من المسارات في `dist/index.html`

### 6. إعداد Apache في Hestia

في Hestia → Web → Edit → Apache Settings، أضف:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /opth/docs/
    RewriteRule ^index\.html$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /opth/docs/index.html [L]
</IfModule>
```

### 7. إعداد Nginx في Hestia

في Hestia → Web → Edit → Nginx Settings، أضف:

```nginx
location /opth/docs {
    alias /home/username/web/yourdomain.com/public_html/opth/docs;
    try_files $uri $uri/ /opth/docs/index.html;
    index index.html;
}
```

## اختبار

بعد التطبيق، افتح:
- `https://yourdomain.com/opth/docs/`
- يجب أن تعمل جميع الملفات بدون أخطاء 404
