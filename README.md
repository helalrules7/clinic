# نظام عيادة طب العيون - Roaya Clinic Management System

## نظرة عامة

نظام شامل لإدارة عيادة طب العيون يدعم ثلاثة أدوار رئيسية:
- **الطبيب**: إدارة المرضى، المواعيد، الوصفات، والفحوصات
- **السكرتير**: إدارة الحجوزات، المدفوعات، وتسجيل المرضى
- **المدير**: إدارة النظام، المستخدمين، والتقارير

## الميزات الرئيسية

### 🤖 **المساعد الطبي الذكي (AI Assistant)** - جديد في الإصدار 8.0.0
- **تحليل تاريخ المريض**: تحليل شامل لتاريخ المريض الطبي الكامل
- **تلخيص الاستشارات**: تلخيص تلقائي للاستشارات مع التشخيص وخطة العلاج
- **إرشادات سريرية ذكية**: إرشادات مبنية على سياق بيانات المريض
- **ودجت محادثة ذكية**: واجهة محادثة عائمة في صفحات المرضى والمواعيد
- **سجل المحادثة**: حفظ سجل المحادثات عبر المواعيد المختلفة
- **الإكمال التلقائي الذكي**: اقتراحات ذكية لحقول الاستشارة
- **الشكاوى الشائعة**: استخراج وتحليل الشكاوى الأكثر شيوعاً
- **اقتراحات الوصفات**: اقتراحات ذكية للوصفات بناءً على التشخيص والشكاوى

### 🏥 **إدارة المرضى**
- تسجيل المرضى الجدد مع التاريخ الطبي
- ملف طبي شامل مع الأحداث الزمنية
- إرفاق الملفات والصور
- البحث المتقدم والتصفية
- التحقق من أرقام الهواتف ونسخها بسهولة
- عرض الجدول الزمني الكامل للمريض

### 📅 **إدارة المواعيد**
- تقويم تفاعلي مع تحديث تلقائي كل 60 ثانية
- فترات زمنية مدتها 15 دقيقة
- ساعات العمل: 2:00 مساءً - 11:00 مساءً
- إغلاق يوم الجمعة
- جدول عمل منفصل لكل طبيب
- إعادة جدولة المواعيد
- تتبع المواعيد الفائتة

### 💊 **الوصفات الطبية**
- وصفات الأدوية مع التفاصيل الكاملة
- وصفات النظارات مع القياسات الدقيقة
- طلبات الفحوصات المخبرية
- طباعة احترافية بتصميم RTL
- اقتراحات ذكية للوصفات بناءً على الشكاوى الشائعة

### 💰 **إدارة المالية**
- تسجيل المدفوعات بأنواع مختلفة
- نظام الخصومات والإعفاءات
- فواتير مفصلة مع التوازن
- تقارير الإيرادات والمصروفات
- تصدير البيانات المالية

### 📊 **التقارير والإحصائيات**
- لوحات تحكم تفاعلية
- تقارير مالية شاملة
- إحصائيات المواعيد والمرضى
- تصدير البيانات بصيغة CSV و Excel
- رسوم بيانية تفاعلية

### 🔒 **الأمان والصلاحيات**
- نظام مصادقة آمن
- إدارة الأدوار والصلاحيات (RBAC)
- حماية CSRF
- تسجيل الأحداث والتدقيق
- إدارة الجلسات الآمنة

### 💬 **منتدى النقاش**
- منتدى داخلي للأطباء
- إنشاء مواضيع ومناقشات
- رفع المرفقات والصور
- نظام الإعجاب والتعليقات
- التصنيفات والعلامات

### 🔔 **الإشعارات**
- إشعارات فورية للمواعيد والأحداث
- إشعارات النظام المهمة
- إشعارات Push للمتصفح
- تتبع الإشعارات غير المقروءة

## المتطلبات التقنية

### **الخادم**
- PHP 8.2 أو أحدث
- MySQL 8.0 أو أحدث
- Apache/Nginx مع mod_rewrite
- Composer

### **ملحقات PHP المطلوبة**
- ext-pdo
- ext-json
- ext-mbstring
- ext-gd
- ext-zip

### **مكتبات PHP**
- phpoffice/phpword: ^1.4
- openspout/openspout: ^4.0
- phpoffice/phpspreadsheet: ^1.29
- minishlink/web-push: ^7.0

## التثبيت والإعداد

### 1. **استنساخ المشروع**
```bash
git clone https://github.com/helalrules7/clinic.git
cd clinic
```

### 2. **تثبيت التبعيات**
```bash
composer install
```

### 3. **إعداد قاعدة البيانات**
```bash
# إنشاء قاعدة البيانات
mysql -u [username] -p -e "CREATE DATABASE roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# استيراد مخطط قاعدة البيانات
mysql -u [username] -p roaya < sql/schema.sql

# استيراد البيانات الأولية (قد تظهر تحذيرات للبيانات المكررة - هذا طبيعي)
mysql -u [username] -p roaya < sql/seed.sql
```

### 4. **تكوين البيئة**
```bash
# نسخ ملف الإعدادات
cp env.example .env

# تعديل ملف .env وإضافة بيانات قاعدة البيانات والمفاتيح
# تأكد من تغيير جميع القيم الافتراضية في بيئة الإنتاج
```

ملف `.env` يجب أن يحتوي على:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=roaya
DB_USER=your_username
DB_PASS=your_password

# Application Configuration
APP_ENV=production
APP_KEY=your-secret-key-here-32-chars-minimum
TIMEZONE=Africa/Cairo

# Security
SESSION_SECRET=your-session-secret-key-32-chars
CSRF_SECRET=your-csrf-secret-key-32-chars

# Logging
LOG_LEVEL=info
LOG_FILE=storage/logs/app.log

# AI Configuration (Optional)
GROQ_API_KEY=your-groq-api-key-if-using-ai-features
```

### 5. **إعداد خادم الويب**

#### **Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### **Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 6. **إعداد الصلاحيات**
```bash
# إنشاء المجلدات المطلوبة وتعيين الصلاحيات
mkdir -p storage/logs storage/uploads storage/exports storage/cache
chmod -R 755 storage/

# إعادة توليد autoloader
composer dump-autoload
```

### 7. **تشغيل النظام**
```bash
# تشغيل خادم PHP المدمج مع الراوتر المخصص
php -S localhost:8000 router.php

# أو تشغيله في الخلفية
php -S localhost:8000 router.php &
```

**الوصول للنظام:**
- **الصفحة الرئيسية**: `http://localhost:8000/`
- **دخول النظام**: `http://localhost:8000/public/`
- **تسجيل الدخول مباشرة**: `http://localhost:8000/public/login`

**أو استخدام Apache/Nginx:**
- وجه document root إلى مجلد المشروع الرئيسي
- تأكد من وجود ملف `.htaccess` في المجلد الرئيسي
- سيتم عرض صفحة ترحيبية مع رابط دخول النظام

## بيانات تسجيل الدخول الافتراضية

**⚠️ تحذير مهم:** يجب تغيير جميع كلمات المرور الافتراضية فوراً في بيئة الإنتاج!

| الدور | اسم المستخدم | البريد الإلكتروني |
|-------|-------------|------------------|
| **Dr. 1** | `dr_1` | `dr.1@yoursite.com` |
| **Dr. 2** | `dr_2` | `dr.2@yoursite.com` |
| **Secretary** | `sec` | `sec@yoursite.com` |
| **Admin** | `admin` | `admin@yoursite.com` |

**ملاحظة:** النظام يستخدم **اسم المستخدم** بدلاً من البريد الإلكتروني لتسجيل الدخول.

## هيكل المشروع

```
clinic/
├── app/
│   ├── Config/          # ملفات الإعدادات
│   ├── Controllers/     # وحدات التحكم
│   ├── Lib/            # المكتبات الأساسية
│   ├── Models/         # نماذج البيانات
│   ├── Services/       # خدمات الأعمال (حسابات طبية، تحليلات)
│   ├── Scripts/        # سكريبتات مساعدة
│   └── Views/          # قوالب الواجهة
│       ├── admin/      # واجهة المدير
│       ├── doctor/     # واجهة الطبيب
│       ├── secretary/  # واجهة السكرتير
│       └── print/      # قوالب الطباعة
├── documentation_site/  # موقع التوثيق (React + TypeScript)
├── public/             # نقطة الدخول
├── sql/                # ملفات قاعدة البيانات
├── storage/            # الملفات المخزنة
│   ├── logs/           # سجلات النظام
│   ├── uploads/        # الملفات المرفقة
│   ├── exports/        # التقارير المصدرة
│   └── cache/          # التخزين المؤقت
├── vendor/              # مكتبات Composer
├── .env                 # إعدادات البيئة (لا يتم رفعه إلى Git)
├── .env.example         # نموذج الإعدادات
├── composer.json        # تبعيات المشروع
└── README.md            # هذا الملف
```

## الاستخدام

### **الطبيب**
1. تسجيل الدخول باستخدام بيانات الطبيب
2. عرض لوحة التحكم مع الإحصائيات
3. إدارة التقويم والمواعيد
4. عرض ملفات المرضى وإنشاء الوصفات
5. استخدام المساعد الطبي الذكي لتحليل التاريخ وتلخيص الاستشارات
6. استخدام الإكمال التلقائي والشكاوى الشائعة لتسريع العمل
7. إغلاق اليوم وإقفال الحسابات

### **السكرتير**
1. تسجيل الدخول باستخدام بيانات السكرتير
2. إدارة الحجوزات والمواعيد
3. تسجيل المرضى الجدد
4. إدارة المدفوعات والفواتير
5. البحث عن المرضى وعرض ملفاتهم

### **المدير**
1. تسجيل الدخول باستخدام بيانات المدير
2. إدارة المستخدمين والصلاحيات
3. عرض تقارير النظام
4. مراقبة صحة النظام
5. تصدير البيانات والتقارير
6. إدارة النسخ الاحتياطية

## الطباعة

### **الوصفة الطبية**
- **المسار**: `/print/prescription/{id}`
- **الحجم**: 24.5 سم عرض
- **التصميم**: RTL للغة العربية
- **المحتوى**: تفاصيل الدواء، الجرعة، التعليمات

### **وصفة النظارات**
- **المسار**: `/print/glasses/{id}`
- **الحجم**: A4
- **المحتوى**: قياسات العينين، نوع العدسة، المسافة بين الحدقتين

### **طلب الفحوصات**
- **المسار**: `/print/lab-tests/{id}`
- **الحجم**: A5
- **المحتوى**: فحوصات العين، التشخيص، خطة العلاج

### **الفاتورة**
- **المسار**: `/print/invoice/{id}`
- **الحجم**: A4
- **المحتوى**: تفاصيل المدفوعات، التوازن، الشروط

## API

### **النقاط النهائية الرئيسية**

#### **المصادقة**
- `GET /api/auth/session-time` - وقت الجلسة المتبقي

#### **المرضى**
- `GET /api/patients` - قائمة جميع المرضى
- `GET /api/patients/search?q={query}` - البحث عن المرضى
- `GET /api/patients/{id}` - تفاصيل مريض
- `POST /api/patients` - إنشاء مريض جديد
- `PUT /api/patients/{id}/emergency-contact` - تحديث جهة اتصال الطوارئ
- `DELETE /api/patients/{id}` - حذف مريض
- `GET /api/patients/{id}/timeline` - الجدول الزمني الكامل للمريض
- `GET /api/patients/{id}/files` - ملفات المريض المرفقة
- `GET /api/patients/{id}/appointments` - مواعيد المريض الحالية
- `GET /api/patients/{id}/appointments/history` - تاريخ مواعيد المريض
- `GET /api/patients/{id}/appointments/check-active` - التحقق من وجود مواعيد نشطة
- `GET /api/patients/{id}/export` - تصدير بيانات المريض (CSV/Excel)
- `HEAD /api/patients/{id}/export` - التحقق من صلاحية التصدير
- `POST /api/patients/files/upload` - رفع ملف للمريض
- `GET /api/patients/files/view/{id}` - عرض ملف المريض
- `GET /api/patients/files/download/{id}` - تحميل ملف المريض
- `DELETE /api/patients/files/{id}` - حذف ملف المريض
- `POST /api/patients/notes` - إنشاء ملاحظة للمريض
- `PUT /api/patients/notes/{id}` - تحديث ملاحظة المريض
- `DELETE /api/patients/notes/{id}` - حذف ملاحظة المريض
- `GET /api/patients/{id}/medical-history` - التاريخ الطبي للمريض
- `POST /api/patients/{id}/medical-history` - إضافة سجل للتاريخ الطبي
- `GET /api/patients/{id}/medical-history/{historyId}` - تفاصيل سجل طبي محدد
- `PUT /api/patients/{id}/medical-history/{historyId}` - تحديث سجل طبي
- `DELETE /api/patients/{id}/medical-history/{historyId}` - حذف سجل طبي

#### **المواعيد**
- `GET /api/calendar` - بيانات التقويم (يتطلب `doctor_id` و `date`)
- `GET /api/organizer/month` - بيانات المنظم الشهرية
- `GET /api/appointments/{id}` - تفاصيل موعد
- `GET /api/appointments/search?q={query}` - البحث في المواعيد
- `POST /api/appointments` - إنشاء موعد جديد
- `PUT /api/appointments/{id}` - تحديث موعد
- `DELETE /api/appointments/{id}` - حذف موعد
- `POST /api/appointments/{id}/reschedule` - إعادة جدولة موعد
- `POST /api/appointments/{id}/reschedule-followup` - إعادة جدولة موعد المتابعة
- `GET /api/appointments/{id}/attachments` - مرفقات الموعد
- `GET /api/appointments/{id}/medications` - وصفات الأدوية للموعد
- `GET /api/appointments/{id}/glasses` - وصفات النظارات للموعد
- `GET /api/appointments/{id}/followup` - موعد المتابعة المرتبط
- `GET /api/appointments/{id}/original` - الموعد الأصلي
- `GET /api/upcoming-appointments` - المواعيد القادمة
- `GET /api/missed-appointments` - المواعيد الفائتة

#### **الاستشارات والملاحظات**
- `POST /api/consultations` - إنشاء استشارة جديدة
- `DELETE /api/consultation-notes/{id}` - حذف ملاحظة استشارة

#### **الوصفات الطبية**
- `POST /api/prescriptions/meds` - إنشاء وصفة أدوية
- `PUT /api/prescriptions/meds/{id}` - تحديث وصفة أدوية
- `DELETE /api/prescriptions/meds/{id}` - حذف وصفة أدوية
- `POST /api/prescriptions/glasses` - إنشاء وصفة نظارات
- `PUT /api/prescriptions/glasses/{id}` - تحديث وصفة نظارات
- `DELETE /api/prescriptions/glasses/{id}` - حذف وصفة نظارات
- `GET /api/prescriptions/glasses/{id}` - تفاصيل وصفة نظارات
- `GET /api/glasses/prescriptions` - قائمة وصفات النظارات
- `GET /api/glasses/prescriptions/patient` - وصفات النظارات لمريض محدد
- `GET /api/medications/prescriptions` - قائمة وصفات الأدوية
- `GET /api/medications/prescriptions/patient` - وصفات الأدوية لمريض محدد

#### **المساعد الطبي الذكي (AI)** - جديد في الإصدار 8.0.0
- `POST /api/ai/chat` - إرسال رسالة للمساعد الذكي
- `GET /api/ai/chat/history?patientId={id}&appointmentId={id}` - سجل المحادثة
- `GET /api/consultation/common-complaints` - الشكاوى الشائعة من قاعدة البيانات
- `GET /api/consultation/suggestions?complaint={text}` - اقتراحات الاستشارة بناءً على الشكوى
- `GET /api/prescriptions/suggestions?complaint={text}` - اقتراحات الوصفات بناءً على الشكوى

#### **الفحوصات المخبرية والأشعة**
- `POST /api/lab-tests` - إنشاء طلب فحص مخبري
- `PUT /api/lab-tests/{id}` - تحديث طلب فحص مخبري
- `DELETE /api/lab-tests/{id}` - حذف طلب فحص مخبري
- `GET /api/lab-tests/appointment/{id}` - فحوصات مخبرية لموعد محدد

#### **البحث**
- `GET /api/search/comprehensive?q={query}` - بحث شامل في النظام (مرضى، مواعيد، أدوية)
- `GET /api/searchDrugs?q={query}` - البحث في قاعدة بيانات الأدوية
- `GET /api/searchDrugsAutocomplete?q={query}` - الإكمال التلقائي للأدوية
- `GET /api/getDrugDetails` - تفاصيل دواء محدد
- `GET /api/getFilterOptions` - خيارات التصفية للأدوية
- `GET /api/getMostUsedDrugs` - الأدوية الأكثر استخداماً
- `POST /api/drugs/update-database` - تحديث قاعدة بيانات الأدوية

#### **المنتدى**
- `GET /api/forum/topics` - قائمة المواضيع
- `GET /api/forum/topics/{id}` - تفاصيل موضوع
- `POST /api/forum/topics` - إنشاء موضوع جديد
- `PUT /api/forum/topics/{id}` - تحديث موضوع
- `DELETE /api/forum/topics/{id}` - حذف موضوع
- `GET /api/forum/topics/patient/{patientId}` - مواضيع متعلقة بمريض
- `GET /api/forum/topics/appointment/{appointmentId}` - مواضيع متعلقة بموعد
- `GET /api/forum/posts/topic/{topicId}` - مشاركات موضوع محدد
- `GET /api/forum/posts/{id}` - تفاصيل مشاركة
- `POST /api/forum/posts` - إنشاء مشاركة جديدة
- `PUT /api/forum/posts/{id}` - تحديث مشاركة
- `DELETE /api/forum/posts/{id}` - حذف مشاركة
- `POST /api/forum/posts/{id}/like` - إعجاب بمشاركة
- `POST /api/forum/posts/{id}/dislike` - عدم إعجاب بمشاركة
- `DELETE /api/forum/posts/{id}/like` - إزالة إعجاب
- `POST /api/forum/topics/{id}/like` - إعجاب بموضوع
- `POST /api/forum/topics/{id}/dislike` - عدم إعجاب بموضوع
- `POST /api/forum/posts/{id}/images` - رفع صورة لمشاركة
- `DELETE /api/forum/images/{id}` - حذف صورة
- `POST /api/forum/topics/{id}/tags` - إضافة علامات لموضوع
- `DELETE /api/forum/topics/{id}/tags/{tagId}` - حذف علامة من موضوع
- `POST /api/forum/attachments/upload` - رفع مرفق للمنتدى
- `GET /api/forum/attachments/view/{id}` - عرض مرفق
- `DELETE /api/forum/attachments/{id}` - حذف مرفق
- `GET /api/forum/stats/categories` - إحصائيات التصنيفات
- `GET /api/forum/stats/top-meta` - العلامات الأكثر استخداماً
- `POST /api/forum/topics/{id}/toggle-resolved` - تبديل حالة الحل
- `POST /api/forum/topics/{id}/toggle-pin` - تثبيت/إلغاء تثبيت موضوع

#### **الإشعارات**
- `GET /api/notifications` - قائمة جميع الإشعارات
- `GET /api/notifications/unread-count` - عدد الإشعارات غير المقروءة
- `PUT /api/notifications/{id}/read` - تحديد إشعار كمقروء
- `PUT /api/notifications/read-all` - تحديد جميع الإشعارات كمقروءة
- `DELETE /api/notifications/clear-all` - حذف جميع الإشعارات
- `DELETE /api/notifications/{id}` - حذف إشعار
- `POST /api/notifications/system` - إنشاء إشعار نظام

#### **التنبيهات (Alerts)**
- `GET /api/alerts` - قائمة جميع التنبيهات
- `GET /api/alerts/{id}` - تفاصيل تنبيه
- `POST /api/alerts` - إنشاء تنبيه جديد
- `PUT /api/alerts/{id}` - تحديث تنبيه
- `DELETE /api/alerts/{id}` - حذف تنبيه
- `GET /api/alerts/today` - التنبيهات اليوم
- `GET /api/alerts/active` - التنبيهات النشطة
- `POST /api/alerts/dismiss` - إلغاء تنبيه
- `POST /api/alerts/{id}/toggle-status` - تبديل حالة التنبيه
- `GET /api/alerts/patient/{patientId}` - تنبيهات مريض محدد
- `POST /api/alerts/disable-all` - تعطيل جميع التنبيهات
- `DELETE /api/alerts/delete-all` - حذف جميع التنبيهات

#### **الملاحظات (Notes)**
- `GET /api/notes` - قائمة الملاحظات
- `GET /api/notes/{id}` - تفاصيل ملاحظة
- `POST /api/notes` - إنشاء ملاحظة جديدة
- `PUT /api/notes/{id}` - تحديث ملاحظة
- `DELETE /api/notes/{id}` - حذف ملاحظة
- `DELETE /api/notes/delete-all` - حذف جميع الملاحظات

#### **لوحة التحكم**
- `GET /api/dashboard-summary` - ملخص لوحة التحكم
- `GET /api/dashboard-charts` - بيانات الرسوم البيانية
- `GET /api/recent-activity` - النشاطات الأخيرة
- `GET /api/secretary/dashboard` - لوحة تحكم السكرتير
- `GET /api/clinical-dashboard/snapshot` - لقطة شاشة للوحة التحكم السريرية

#### **المدفوعات والمصروفات**
- `POST /api/payments` - تسجيل دفعة
- `PUT /api/payments/{id}` - تحديث دفعة
- `DELETE /api/payments/{id}` - حذف دفعة
- `GET /api/payments/{id}` - تفاصيل دفعة
- `POST /api/expenses` - تسجيل مصروف
- `PUT /api/expenses/{id}` - تحديث مصروف
- `DELETE /api/expenses/{id}` - حذف مصروف
- `GET /api/expenses/{id}` - تفاصيل مصروف
- `GET /api/financial-transactions` - جميع المعاملات المالية
- `GET /api/financial-transactions/export` - تصدير المعاملات المالية
- `POST /api/daily-balance` - إنشاء رصيد يومي
- `POST /api/daily-closure` - إغلاق يومي
- `POST /api/daily-closure/lock` - قفل الإغلاق اليومي

#### **المرفقات**
- `POST /api/attachments/upload` - رفع مرفق عام
- `GET /api/attachments/view/{id}` - عرض مرفق
- `GET /api/attachments/download/{id}` - تحميل مرفق
- `DELETE /api/attachments/{id}` - حذف مرفق

#### **الوسائط (Media)**
- `GET /api/media` - قائمة الوسائط
- `GET /api/media/patient` - صور المريض

#### **الخدمات الطبية المتقدمة**
- `POST /api/iol/calculate` - حساب قوة عدسة IOL
- `GET /api/iop/analyze` - تحليل اتجاه ضغط العين (IOP Trend)
- `POST /api/pediatric-iol/calculate` - حساب IOL للأطفال
- `POST /api/astigmatism/calculate` - حساب الاستجماتيزم القرني
- `POST /api/target-iop/calculate` - حساب ضغط العين المستهدف
- `POST /api/refraction/consistency` - فحص اتساق الانكسار
- `POST /api/visual-acuity/progress` - حساب تقدم حدة البصر
- `POST /api/osdi/calculate` - حساب مؤشر جفاف العين (OSDI)
- `GET /api/patients/:patientId/osdi/history` - تاريخ OSDI للمريض
- `POST /api/pachymetry-adjusted-iop/calculate` - حساب IOP المعدل بسمك القرنية
- `POST /api/diabetic-retinopathy/risk-estimate` - تقدير خطر اعتلال الشبكية السكري
- `POST /api/macular-thickness/trend` - تحليل اتجاه سمك البقعة
- `GET /api/patients/:patientId/macular-thickness/history` - تاريخ سمك البقعة للمريض
- `POST /api/cataract-surgery/readiness` - تقييم جاهزية جراحة الساد
- `POST /api/cataract-surgery/postop-outcome` - تحليل نتائج ما بعد الجراحة
- `GET /api/cataract-surgery/audit` - مراجعة نتائج الجراحات

#### **إدارة المستخدمين (Admin)**
- `POST /api/users/change-password` - تغيير كلمة المرور
- `POST /api/admin/backup/database` - نسخ احتياطي لقاعدة البيانات
- `POST /api/admin/backup/full` - نسخ احتياطي كامل
- `POST /api/admin/backup/website` - نسخ احتياطي للموقع
- `GET /api/admin/backup/list` - قائمة النسخ الاحتياطية
- `POST /api/admin/backup/restore` - استعادة نسخة احتياطية
- `POST /api/admin/backup/restore-upload` - استعادة من ملف مرفوع
- `GET /api/admin/backup/download/{type}/{name}` - تحميل نسخة احتياطية
- `GET /api/admin/media/list` - قائمة الوسائط
- `POST /api/admin/media/delete` - حذف وسائط
- `POST /api/admin/media/delete-all` - حذف جميع الوسائط
- `POST /api/admin/media/backup` - نسخ احتياطي للوسائط
- `GET /api/admin/media/backups` - قائمة نسخ الوسائط
- `POST /api/admin/media/restore` - استعادة وسائط
- `POST /api/admin/media/restore-upload` - استعادة وسائط من ملف
- `GET /api/admin/media/backup-download/{name}` - تحميل نسخة وسائط

#### **إعدادات الطبيب**
- `GET /api/doctor/settings` - إعدادات الطبيب
- `PUT /api/doctor/settings` - تحديث إعدادات الطبيب

#### **معلومات إضافية**
- `GET /api/weather` - معلومات الطقس
- `GET /api/weather-forecast` - توقعات الطقس
- `GET /api/weather-ar` - معلومات الطقس بالعربية
- `GET /api/weather-forecast-ar` - توقعات الطقس بالعربية
- `GET /api/ophthalmology-news` - أخبار طب العيون

### **استجابة API**
```json
{
    "ok": true,
    "data": {...},
    "error": null
}
```

## موقع التوثيق

يتضمن المشروع موقع توثيق شامل مبني بـ React + TypeScript + Vite:

- **الموقع**: متاح في مجلد `documentation_site/`
- **اللغات المدعومة**: العربية والإنجليزية
- **المحتوى**: 
  - نظرة عامة على النظام
  - دليل الميزات
  - وثائق API الكاملة
  - سجل التغييرات
  - معلومات المساعد الطبي الذكي

### **تشغيل موقع التوثيق**
```bash
cd documentation_site
npm install
npm run dev
```

## الأمان

### **حماية CSRF**
- جميع طلبات POST/PUT تتطلب رمز CSRF
- تجديد تلقائي للرموز

### **إدارة الجلسات**
- جلسات آمنة مع تجديد تلقائي
- إلغاء الجلسات عند تغيير كلمة المرور
- انتهاء الجلسة بعد فترة عدم نشاط

### **التحقق من الصلاحيات**
- نظام أدوار متقدم (RBAC)
- التحقق من الصلاحيات لكل عملية
- حماية نقاط النهاية API

### **حماية البيانات**
- تشفير كلمات المرور
- حماية من SQL Injection
- تنظيف المدخلات والتحقق منها
- حماية الملفات المرفقة

## الأداء

### **تحسين قاعدة البيانات**
- فهارس محسنة للاستعلامات المتكررة
- استعلامات محسنة مع JOIN
- استعلامات محسنة للبحث

### **التخزين المؤقت**
- ETags للاستجابات
- Last-Modified headers
- تخزين مؤقت للشكاوى الشائعة

### **التحديث التلقائي**
- التقويم: تحديث كل 60 ثانية
- لوحة التحكم: تحديث كل 30 ثانية
- الإشعارات: تحديث فوري

## استكشاف الأخطاء

### **مشاكل قاعدة البيانات**
```bash
# فحص الاتصال
mysql -u [username] -p -h localhost

# فحص قاعدة البيانات
SHOW DATABASES;
USE roaya;
SHOW TABLES;
```

### **مشاكل PHP**
```bash
# فحص إصدار PHP
php -v

# فحص الملحقات
php -m | grep -E "(pdo|json|mbstring|gd|zip)"
```

### **مشاكل الصلاحيات**
```bash
# فحص صلاحيات المجلدات
ls -la storage/

# إعادة تعيين الصلاحيات
chmod -R 755 storage/
```

### **مشاكل المساعد الذكي**
- تأكد من إعداد `GROQ_API_KEY` في ملف `.env`
- تحقق من سجلات النظام في `storage/logs/app.log`
- تأكد من اتصال الإنترنت للوصول إلى API

## التطوير

### **إضافة ميزات جديدة**
1. إنشاء Controller جديد في `app/Controllers/`
2. إضافة القوالب في `app/Views/`
3. تحديث المسارات في `index.php`
4. إضافة API endpoints إذا لزم الأمر

### **تخصيص التصميم**
- تعديل CSS في `app/Views/layouts/style.css`
- تخصيص قوالب الطباعة في `app/Views/print/`
- إضافة JavaScript مخصص في `app/Views/doctor/assets/js/`

### **إضافة خدمات طبية**
- إضافة Services جديدة في `app/Services/`
- استخدام الخدمات الموجودة كمرجع
- تحديث API Controller لاستخدام الخدمة الجديدة

## الدعم

### **المساعدة**
- فحص ملفات السجل في `storage/logs/`
- مراجعة إعدادات قاعدة البيانات
- التأكد من صحة ملف `.env`
- مراجعة موقع التوثيق في `documentation_site/`

### **التحديثات**
```bash
# تحديث التبعيات
composer update

# تحديث قاعدة البيانات
mysql -u [username] -p roaya < sql/migrations/new_migration.sql
```

## الإصدار

**الإصدار الحالي**: 8.0.0  
**آخر تحديث**: ديسمبر 2025

### **ملاحظات الإصدار 8.0.0**
- إضافة المساعد الطبي الذكي (AI Assistant)
- نظام الإكمال التلقائي الذكي
- استخراج وتحليل الشكاوى الشائعة
- اقتراحات ذكية للوصفات
- تحسينات في واجهة المستخدم
- موقع توثيق شامل

## الترخيص

هذا المشروع مرخص تحت رخصة MIT. راجع ملف `LICENSE` للتفاصيل.

## المساهمة

نرحب بالمساهمات! يرجى:
1. Fork المشروع
2. إنشاء فرع للميزة الجديدة (`git checkout -b feature/AmazingFeature`)
3. Commit التغييرات (`git commit -m 'Add some AmazingFeature'`)
4. Push إلى الفرع (`git push origin feature/AmazingFeature`)
5. فتح Pull Request

---

**⚠️ تحذير أمني مهم:**
- تأكد من تغيير جميع كلمات المرور الافتراضية في بيئة الإنتاج
- لا ترفع ملف `.env` إلى Git
- استخدم مفاتيح أمان قوية وفريدة
- قم بعمل نسخ احتياطية منتظمة
- راجع إعدادات الأمان قبل النشر
