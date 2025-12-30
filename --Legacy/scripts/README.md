# Drugs Database Update Scripts

## الوصف
هذه السكريبتات تقوم بتحديث قاعدة بيانات الأدوية تلقائياً من موقع drugeye.pharorg.com

## الملفات
- `update_drugs_database.php` - سكريبت PHP CLI يقوم بتحديث قاعدة البيانات
- `update_drugs_database.sh` - سكريبت Shell يستدعي سكريبت PHP

## الاستخدام اليدوي

### تشغيل السكريبت مباشرة:
```bash
cd /home/hclinic/web/roaya.hclinic.clinic
php scripts/update_drugs_database.php
```

أو:
```bash
cd /home/hclinic/web/roaya.hclinic.clinic
./scripts/update_drugs_database.sh
```

## Cron Job
تم إعداد cron job ليعمل تلقائياً كل يوم اثنين في الساعة 1:00 صباحاً

```bash
0 1 * * 1 /home/hclinic/web/roaya.hclinic.clinic/scripts/update_drugs_database.sh >> /home/hclinic/web/roaya.hclinic.clinic/logs/cron_drugs_update.log 2>&1
```

### عرض Cron Job:
```bash
crontab -u hclinic -l
```

### تعديل Cron Job:
```bash
crontab -u hclinic -e
```

## السجلات (Logs)
- سجلات التحديث اليومية: `/home/hclinic/web/roaya.hclinic.clinic/logs/drugs_update_YYYY-MM-DD.log`
- سجلات Cron Job: `/home/hclinic/web/roaya.hclinic.clinic/logs/cron_drugs_update.log`

## معلومات قاعدة البيانات
- قاعدة البيانات: `hclinic_drugs`
- المستخدم: `hclinic_drugs`
- كلمة المرور: `Carmen@1230`
- Host: من ملف `.env` أو `localhost`

## ملاحظات
- السكريبت يقوم بتنزيل ملف SQLite من الموقع
- يقوم بتحويل البيانات إلى MySQL
- يقوم بحذف البيانات القديمة وإدراج البيانات الجديدة
- بعض الصفوف قد تفشل بسبب أحرف غير صالحة (سيتم تسجيلها في السجلات)

