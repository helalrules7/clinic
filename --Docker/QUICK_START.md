# البدء السريع - Docker

## خطوات التشغيل

```bash
# 1. إنشاء ملف .env
cp .env.docker .env

# 2. تشغيل المشروع
docker-compose up -d --build

# 3. الوصول للتطبيق
# المتصفح: http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

## معلومات قاعدة البيانات

- **Host**: `db` (داخل Docker) أو `localhost` (من خارج Docker)
- **Database**: `roaya`
- **User**: `roaya_user`
- **Password**: `roaya_password`

## الأوامر المفيدة

```bash
# إيقاف
docker-compose down

# إيقاف مع حذف البيانات
docker-compose down -v

# عرض السجلات
docker-compose logs -f

# إعادة استيراد البيانات
docker exec -i clinic_db mysql -u root -proot_password roaya < app/seed/hclinic_roaya.sql
```

## ملاحظة

البيانات يتم استيرادها تلقائياً من `app/seed/hclinic_roaya.sql` عند أول تشغيل.
