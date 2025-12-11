# دليل إلغاء التثبيت - Uninstall Guide

## إلغاء تثبيت التوثيق من Hestia

### الطريقة 1: حذف النطاق من Hestia (موصى به)

1. سجل الدخول إلى **Hestia Control Panel**
2. اذهب إلى **Web** → اختر النطاق (مثلاً: `docs.yourdomain.com`)
3. اضغط **Delete**
4. أكد الحذف

سيتم حذف:
- جميع الملفات في `/home/username/web/docs.yourdomain.com/`
- إعدادات Nginx/Apache
- قاعدة البيانات (إن وجدت)
- SSL certificates

### الطريقة 2: حذف الملفات يدوياً

```bash
# حذف مجلد التوثيق
rm -rf /home/username/web/docs.yourdomain.com/public_html/*

# أو حذف النطاق بالكامل
rm -rf /home/username/web/docs.yourdomain.com/
```

### الطريقة 3: حذف من Git Repository

إذا كنت تريد حذف مجلد `docs/` من المشروع:

```bash
# من جذر المشروع
cd /var/www/html/clinic
rm -rf docs/

# أو إذا كنت تريد الاحتفاظ بالملفات محلياً فقط
git rm -r docs/
git commit -m "Remove docs directory"
git push
```

## إلغاء تثبيت Dependencies

### حذف node_modules

```bash
cd docs
rm -rf node_modules/
rm -rf dist/
rm package-lock.json
```

### إلغاء تثبيت Vite (إذا كان مثبتاً عالمياً)

```bash
npm uninstall -g vite
```

## تنظيف النظام

### حذف ملفات البناء

```bash
cd docs
rm -rf dist/
rm -rf node_modules/
rm -rf .vite/
```

### حذف ملفات التخزين المؤقت

```bash
# حذف npm cache
npm cache clean --force

# حذف Vite cache
rm -rf node_modules/.vite
```

## التحقق من الحذف

```bash
# التحقق من وجود الملفات
ls -la /home/username/web/docs.yourdomain.com/public_html/

# يجب أن يكون المجلد فارغاً أو غير موجود
```

## ملاحظات مهمة

⚠️ **تحذير:** 
- تأكد من عمل نسخة احتياطية قبل الحذف إذا كنت تريد الاحتفاظ بالملفات
- حذف النطاق من Hestia سيحذف كل شيء بشكل دائم
- لا يمكن استرجاع الملفات المحذوفة إلا من النسخ الاحتياطية

## استعادة النسخ الاحتياطية

إذا كان لديك نسخة احتياطية:

```bash
# استعادة من Git
git checkout HEAD -- docs/

# أو استعادة من backup
cp -r /path/to/backup/docs /var/www/html/clinic/
```
