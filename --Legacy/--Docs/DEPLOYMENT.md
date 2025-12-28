# دليل النشر على الخادم البعيد

## معلومات الاتصال

- **IP**: 45.93.138.184
- **Username**: root
- **Password**: Carmen1230@@
- **SSH Keys**: 
  - PuTTY: `connect/AhmedHelalPrivate.ppk`
  - OpenSSH: `connect/OpenSSHPrivate`
  - Public Key: `connect/AhmedHelalPublic`

## خطوات النشر

### 1. الاتصال بالخادم

#### باستخدام OpenSSH:
```bash
ssh -i connect/OpenSSHPrivate root@45.93.138.184
```

#### باستخدام PuTTY:
```bash
# تحويل المفتاح من PPK إلى OpenSSH format إذا لزم الأمر
puttygen connect/AhmedHelalPrivate.ppk -O private-openssh -o connect/OpenSSHPrivate
ssh -i connect/OpenSSHPrivate root@45.93.138.184
```

### 2. نسخ الملفات المحدثة

```bash
# من الجهاز المحلي
cd /var/www/html/clinic/--Docs

# نسخ الملفات المحدثة
scp -i connect/OpenSSHPrivate \
    public/assets/css/style.css \
    public/assets/js/app.js \
    database.sqlite \
    root@45.93.138.184:/path/to/docs/

# أو نسخ المجلد بالكامل
rsync -avz -e "ssh -i connect/OpenSSHPrivate" \
    --exclude 'vendor' \
    --exclude 'node_modules' \
    --exclude '.git' \
    ./ root@45.93.138.184:/path/to/docs/
```

### 3. تحديث قاعدة البيانات على الخادم

```bash
# الاتصال بالخادم
ssh -i connect/OpenSSHPrivate root@45.93.138.184

# الانتقال إلى مجلد التوثيق
cd /path/to/docs

# تشغيل سكريبت التحديث
python3 update-getting-started.py
```

### 4. التحقق من الصلاحيات

```bash
# على الخادم
chmod -R 755 public/
chmod 644 database.sqlite
chown -R www-data:www-data .
```

### 5. التحقق من عمل النظام

- افتح المتصفح وانتقل إلى: `http://45.93.138.184/docs/opth/`
- تحقق من عرض محتوى Getting Started
- اختبر التبديل بين الوضعين Dark/Light
- اختبر التبديل بين العربية والإنجليزية

## الملفات المحدثة

- `public/assets/css/style.css` - أنماط CSS محسّنة
- `public/assets/js/app.js` - وظائف JavaScript تفاعلية
- `database.sqlite` - قاعدة البيانات مع المحتوى الجديد
- `content/getting-started-en.html` - المحتوى الإنجليزي
- `content/getting-started-ar.html` - المحتوى العربي

## ملاحظات

- تأكد من وجود Python 3 على الخادم لتشغيل سكريبت التحديث
- تأكد من وجود صلاحيات القراءة/الكتابة على قاعدة البيانات
- في حالة وجود مشاكل، تحقق من سجلات الأخطاء في Apache/Nginx
