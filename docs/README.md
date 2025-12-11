# Roaya Clinic Documentation

موقع توثيق شامل لنظام إدارة عيادة رؤية بتصميم Crypto/Web3 style.

## الميزات

- ✅ تصميم Crypto/Web3 مع ألوان نيون وتأثيرات متوهجة
- ✅ Dark mode افتراضي
- ✅ دعم كامل للغة العربية والإنجليزية
- ✅ RTL/LTR support
- ✅ Sidebar للتنقل
- ✅ Quick Search متقدم
- ✅ Animations وتأثيرات بصرية
- ✅ SPA مع Client-side routing
- ✅ Responsive design

## التثبيت

```bash
cd docs
npm install
```

## التشغيل

```bash
# Development
npm run dev

# Build
npm run build

# Preview
npm run preview
```

## البنية

```
docs/
├── index.html              # نقطة الدخول
├── src/
│   ├── main.js            # نقطة دخول JS
│   ├── router.js          # نظام التوجيه
│   ├── search.js          # نظام البحث
│   ├── i18n.js            # نظام الترجمة
│   ├── styles/            # الأنماط
│   ├── components/        # المكونات
│   ├── pages/             # الصفحات
│   └── data/              # البيانات
└── public/                # الملفات الثابتة
```

## الاستخدام

1. افتح `http://localhost:3000` بعد تشغيل `npm run dev`
2. استخدم Sidebar للتنقل
3. استخدم Ctrl+K للبحث السريع
4. استخدم زر اللغة لتبديل اللغة

## التطوير

- إضافة صفحة جديدة: أنشئ ملف في `src/pages/`
- إضافة route: أضف في `src/router.js`
- إضافة ترجمة: أضف في `src/data/translations.js`
