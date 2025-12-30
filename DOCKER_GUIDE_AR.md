# دليل استخدام Docker - نظام إدارة العيادة

## البدء السريع

### الخطوة 1: إعداد ملف البيئة

انسخ ملف `.env.docker` إلى `.env`:

```bash
cp .env.docker .env
```

أو أنشئ ملف `.env` يدوياً مع المحتوى التالي:

```env
DB_HOST=db
DB_NAME=roaya
DB_USER=roaya_user
DB_PASS=roaya_password
APP_ENV=local
APP_DEBUG=true
```

### الخطوة 2: تشغيل المشروع

```bash
docker-compose up -d --build
```

### الخطوة 3: الوصول للتطبيق

- **التطبيق**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

## معلومات قاعدة البيانات

- **المضيف**: `db` (داخل Docker) أو `localhost` (من خارج Docker)
- **المنفذ**: `3306`
- **اسم القاعدة**: `roaya`
- **اسم المستخدم**: `roaya_user`
- **كلمة المرور**: `roaya_password`
- **كلمة مرور root**: `root_password`

## الأوامر الأساسية

### إيقاف المشروع
```bash
docker-compose down
```

### إيقاف المشروع مع حذف البيانات
```bash
docker-compose down -v
```

### عرض السجلات
```bash
docker-compose logs -f
```

### إعادة بناء الصور
```bash
docker-compose build --no-cache
```

### الدخول إلى حاوية PHP
```bash
docker exec -it clinic_web bash
```

### الدخول إلى حاوية MySQL
```bash
docker exec -it clinic_db mysql -u root -proot_password
```

## استيراد البيانات

البيانات يتم استيرادها تلقائياً عند أول تشغيل من ملف:
```
app/seed/hclinic_roaya.sql
```

إذا أردت إعادة الاستيراد يدوياً:

```bash
docker exec -i clinic_db mysql -u root -proot_password roaya < app/seed/hclinic_roaya.sql
```

## استكشاف الأخطاء

### المشكلة: لا يمكن الاتصال بقاعدة البيانات

**الحل**: تحقق من حالة الحاويات:
```bash
docker-compose ps
```

انتظر حتى تظهر حالة `healthy` لحاوية `db`.

### المشكلة: البيانات لم يتم استيرادها

**الحل**: 
1. تأكد من وجود الملف: `app/seed/hclinic_roaya.sql`
2. احذف volume قاعدة البيانات وأعد التشغيل:
```bash
docker-compose down -v
docker-compose up -d
```

### المشكلة: مشاكل في الصلاحيات

**الحل**:
```bash
docker exec clinic_web chmod -R 777 storage public/uploads uploads
```

### المشكلة: خطأ في Composer

**الحل**: قم بتثبيت التبعيات داخل الحاوية:
```bash
docker exec clinic_web composer install
```

## ملاحظات مهمة

1. **البيانات محفوظة**: البيانات محفوظة في volume، حتى عند إيقاف الحاويات تبقى البيانات موجودة.

2. **حذف البيانات**: لإعادة تشغيل نظيف مع حذف البيانات:
   ```bash
   docker-compose down -v
   docker-compose up -d --build
   ```

3. **الملفات المرفوعة**: الملفات المرفوعة محفوظة في:
   - `public/uploads/`
   - `uploads/`

4. **السجلات**: عرض سجلات Apache:
   ```bash
   docker exec clinic_web tail -f /var/log/apache2/error.log
   ```

## البنية

```
clinic/
├── Dockerfile              # ملف بناء صورة Docker
├── docker-compose.yml      # تكوين Docker Compose
├── .dockerignore          # الملفات المستثناة
├── docker-init.sh         # سكريبت التهيئة
├── .env.docker            # مثال ملف البيئة
└── app/seed/
    └── hclinic_roaya.sql  # ملف البيانات
```

## للتطوير

يمكنك تعديل الملفات مباشرة على الجهاز المضيف، والتغييرات ستظهر مباشرة في الحاوية.

## للإنتاج

قبل النشر في الإنتاج:
1. غيّر كلمات المرور في `docker-compose.yml`
2. عطّل `APP_DEBUG` في `.env`
3. استخدم HTTPS
4. أعد النسخ الاحتياطي لقاعدة البيانات
