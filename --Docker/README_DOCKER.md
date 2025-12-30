# دليل استخدام Docker للمشروع

## المتطلبات الأساسية

- Docker
- Docker Compose

## البدء السريع

### 1. إنشاء ملف `.env`

انسخ ملف `.env.docker` إلى `.env`:

```bash
cp .env.docker .env
```

### 2. تشغيل المشروع

```bash
docker-compose up -d --build
```

هذا الأمر سيقوم بـ:
- بناء صورة Docker للمشروع
- تشغيل حاوية MySQL
- تشغيل حاوية Apache/PHP
- استيراد البيانات تلقائياً من `app/seed/hclinic_roaya.sql`

### 3. الوصول للتطبيق

- **التطبيق الرئيسي**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081

### 4. معلومات قاعدة البيانات

- **Host**: `db` (داخل Docker) أو `localhost` (من خارج Docker)
- **Port**: `3306`
- **Database**: `roaya`
- **Username**: `roaya_user`
- **Password**: `roaya_password`
- **Root Password**: `root_password`

## الأوامر المفيدة

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

### إعادة استيراد قاعدة البيانات
```bash
docker exec -i clinic_db mysql -u root -proot_password roaya < app/seed/hclinic_roaya.sql
```

## هيكل المشروع

```
.
├── Dockerfile              # ملف بناء صورة Docker
├── docker-compose.yml      # ملف تكوين Docker Compose
├── .dockerignore          # الملفات المستثناة من Docker
├── docker-init.sh         # سكريبت تهيئة قاعدة البيانات
├── .env.docker            # ملف متغيرات البيئة لـ Docker
└── app/seed/
    └── hclinic_roaya.sql  # ملف SQL للبيانات
```

## استكشاف الأخطاء

### المشكلة: لا يمكن الاتصال بقاعدة البيانات

**الحل**: تأكد من أن حاوية MySQL تعمل:
```bash
docker-compose ps
```

### المشكلة: البيانات لم يتم استيرادها

**الحل**: تحقق من وجود الملف:
```bash
ls -lh app/seed/hclinic_roaya.sql
```

ثم قم بإعادة استيراد البيانات يدوياً:
```bash
docker exec -i clinic_db mysql -u root -proot_password roaya < app/seed/hclinic_roaya.sql
```

### المشكلة: مشاكل في الصلاحيات

**الحل**: تأكد من الصلاحيات الصحيحة:
```bash
docker exec clinic_web chmod -R 777 storage public/uploads uploads
```

## ملاحظات مهمة

1. **البيانات**: البيانات محفوظة في volume اسمه `db_data`، حتى عند إيقاف الحاويات، البيانات تبقى محفوظة.

2. **الملفات المرفوعة**: الملفات المرفوعة محفوظة في مجلدات `public/uploads` و `uploads` على الجهاز المضيف.

3. **السجلات**: يمكنك عرض سجلات Apache من خلال:
   ```bash
   docker exec clinic_web tail -f /var/log/apache2/error.log
   ```

4. **Composer**: إذا كنت بحاجة لتثبيت حزم جديدة:
   ```bash
   docker exec clinic_web composer install
   ```

## التطوير

للتطوير المحلي، يمكنك تعديل الملفات مباشرة على الجهاز المضيف، والتغييرات ستظهر مباشرة في الحاوية.

## الإنتاج

للاستخدام في الإنتاج، تأكد من:
1. تغيير كلمات المرور في `docker-compose.yml`
2. تعطيل `APP_DEBUG` في `.env`
3. استخدام HTTPS
4. إعداد النسخ الاحتياطي لقاعدة البيانات
