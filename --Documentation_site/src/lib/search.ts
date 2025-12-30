import Fuse from 'fuse.js';

export interface SearchResult {
    title: string;
    path: string;
    category: string;
    content: string;
}

const searchIndex: SearchResult[] = [
    // Home Page
    {
        title: 'Project Overview',
        path: '/',
        category: 'General',
        content: 'Roaya Clinic Management System ophthalmology overview features scheduling EMR inventory finance multilang whatsapp notifications'
    },
    {
        title: 'Key Features',
        path: '/',
        category: 'General',
        content: 'Real-time appointment scheduling digital electronic medical records EMR inventory glasses management financial reporting analytics multi-language support Arabic English automated WhatsApp notifications'
    },

    // Architecture
    {
        title: 'Architecture',
        path: '/architecture',
        category: 'Technical',
        content: 'MVC PHP CodeIgniter MySQL lifecycle request response routing controllers views models services folder structure'
    },
    {
        title: 'MVC Pattern',
        path: '/architecture',
        category: 'Technical',
        content: 'Model View Controller separation modularity maintenance PHP framework'
    },
    {
        title: 'Request Lifecycle',
        path: '/architecture',
        category: 'Technical',
        content: 'Entry point routing controller execution response rendering browser'
    },
    {
        title: 'Folder Structure',
        path: '/architecture',
        category: 'Technical',
        content: 'Controllers Views Models Services Public directory organization'
    },

    // Doctor Dashboard
    {
        title: 'Doctor Dashboard',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Doctor dashboard statistics appointments weather widget pollen dry eye risk news ticker quick actions upcoming appointments recent activities notes board visual analytics alerts missed appointments'
    },
    {
        title: 'Statistics Cards',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Total appointments completed missed new patients prescriptions counters metrics charts trends'
    },
    {
        title: 'Weather Widget',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Environmental factors eye health pollen index allergic conjunctivitis humidity wind temperature forecast location API'
    },
    {
        title: 'Ophthalmology News',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'News ticker research headlines journals industry trends updates RSS feed scrolling'
    },
    {
        title: 'Quick Actions',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Quick action cards iOS widgets add patient add appointment navigation modal horizontal scroll touch support'
    },
    {
        title: 'Upcoming Appointments',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Upcoming appointments today future dates patient information time status countdown progress bar pagination API'
    },
    {
        title: 'Recent Activities',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Recent activities timeline feed appointment bookings status changes file uploads prescription created chronological order modal filter'
    },
    {
        title: 'Notes Board',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Notes board sticky notes drag drop resize color customization autocomplete patients appointments drugs alerts date time'
    },
    {
        title: 'Visual Analytics',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Visual analytics charts graphs trends statistics 30 days appointments trend new patients trend Chart.js theme support light dark'
    },
    {
        title: 'Today Alerts',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Today alerts active alerts scheduled patient linked appointment linked time based filtering quick access management'
    },
    {
        title: 'Missed Appointments',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Missed appointments previous days not completed cancelled track follow up pagination status tags quick actions mark completed'
    },
    {
        title: 'Widget Rearrangement',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Widget rearrangement drag drop move widgets reorder customize dashboard order arrow buttons up down save order persistent'
    },
    {
        title: 'Mobile Settings',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'Mobile settings hide rearrange buttons mobile devices toggle visibility screen width 768px persistent setting preferences'
    },
    {
        title: 'Unified Clinical Dashboard',
        path: '/dashboards/doctor',
        category: 'Dashboards',
        content: 'unified clinical dashboard clinical snapshot patient data consultation notes OSDI results cataract surgery readiness macular thickness IOP status visual acuity dry eye status clinical alerts mini trends clinical summary auto-detect patient profile appointment ClinicalDataParserService'
    },
    {
        title: 'لوحة المعلومات السريرية الموحدة',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'لوحة معلومات سريرية موحدة لقطة سريرية بيانات مريض ملاحظات استشارة نتائج OSDI جاهزية جراحة إعتام عدسة سماكة بقعة حالة IOP حدة بصر حالة جفاف عين تنبيهات سريرية اتجاهات مصغرة ملخص سريري اكتشاف تلقائي ملف مريض موعد ClinicalDataParserService'
    },
    {
        title: 'Secretary Dashboard',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Secretary dashboard statistics appointments payments quick actions weather widget auto refresh real-time updates check-in patients'
    },
    {
        title: 'Secretary Statistics Cards',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Total appointments booked checked in completed missed statistics counters real-time dashboard'
    },
    {
        title: 'Secretary Quick Actions',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Quick actions bookings patients payments expenses profile horizontal scroll touch support RTL'
    },
    {
        title: 'Today Appointments Secretary',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Today appointments table patient information doctor visit type status check-in view details'
    },
    {
        title: 'Recent Payments Secretary',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Recent payments list patient name payment type amount timestamp transactions'
    },
    {
        title: 'Auto Refresh Secretary',
        path: '/dashboards/secretary',
        category: 'Dashboards',
        content: 'Auto refresh 30 seconds polling API visibility detection live updates error handling'
    },
    {
        title: 'Admin Dashboard',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Admin dashboard system statistics users patients appointments financial health monitoring recent activities view as role preview'
    },
    {
        title: 'Admin System Statistics',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'System statistics cards users patients appointments financial metrics 30 days revenue transactions'
    },
    {
        title: 'Admin Users Statistics',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Users statistics total users active users doctors secretaries role breakdown'
    },
    {
        title: 'Admin Patients Statistics',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Patients statistics total patients active patients count'
    },
    {
        title: 'Admin Appointments Statistics',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Appointments statistics 30 days total completed cancelled status breakdown'
    },
    {
        title: 'Admin Financial Statistics',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Financial statistics revenue transactions discounts payments 30 days'
    },
    {
        title: 'Admin Recent Activities',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'Recent activities audit logs timeline user actions login attempts system changes'
    },
    {
        title: 'Admin System Health',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'System health database connection storage space PHP version extensions monitoring status indicators'
    },
    {
        title: 'Admin View As',
        path: '/dashboards/admin',
        category: 'Dashboards',
        content: 'View as role preview interface doctor secretary switch accounts preview mode'
    },

    // Admin Module
    {
        title: 'Admin Module',
        path: '/modules/admin',
        category: 'Modules',
        content: 'Admin user management settings configuration audit logs security access control'
    },
    {
        title: 'User Management',
        path: '/modules/admin',
        category: 'Modules',
        content: 'Create edit deactivate user accounts doctors secretaries roles permissions'
    },
    {
        title: 'System Settings',
        path: '/modules/admin',
        category: 'Modules',
        content: 'Global configuration clinic details working hours localization preferences'
    },
    {
        title: 'Audit Logs',
        path: '/modules/admin',
        category: 'Modules',
        content: 'Track system usage login attempts data modifications security compliance'
    },

    // Doctor Module
    {
        title: 'Doctor Portal',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Doctor portal diagnosis prescriptions refraction appointments examination patient history'
    },
    {
        title: 'Diagnosis',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Visual acuity slit lamp exams fundus observations eye examination'
    },
    {
        title: 'Prescriptions',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Generate print digital prescriptions medications drugs'
    },
    {
        title: 'Refraction',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Glasses contact lens measurements vision correction'
    },
    {
        title: 'Patient Examination',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Examination interface templates general retina glaucoma drawing tools imaging devices'
    },
    {
        title: 'Medications Database',
        path: '/modules/doctor',
        category: 'Modules',
        content: 'Ophthalmic drugs database search trade name active ingredient frequently used'
    },

    // Secretary Module
    {
        title: 'Secretary Desk',
        path: '/modules/secretary',
        category: 'Modules',
        content: 'Secretary desk appointments registration intake workflow payment check-in queue'
    },
    {
        title: 'Appointments',
        path: '/modules/secretary',
        category: 'Modules',
        content: 'Book reschedule cancel appointments collision detection scheduling'
    },
    {
        title: 'Patient Registration',
        path: '/modules/secretary',
        category: 'Modules',
        content: 'Quick intake forms new patients registration details'
    },
    {
        title: 'Daily Workflow',
        path: '/modules/secretary',
        category: 'Modules',
        content: 'Patient check-in queue assignment payment collection consultation fees receipts'
    },
    {
        title: 'Communication',
        path: '/modules/secretary',
        category: 'Modules',
        content: 'WhatsApp reminders appointment notifications messaging'
    },

    // API Reference
    {
        title: 'API Reference',
        path: '/api',
        category: 'Technical',
        content: 'REST API endpoints authentication Bearer token session cookie'
    },
    {
        title: 'Authentication',
        path: '/api',
        category: 'Technical',
        content: 'API authentication session cookie Bearer token security'
    },
    {
        title: 'Patients API',
        path: '/api',
        category: 'Technical',
        content: 'Search patients by name phone get patient profile create register new patient'
    },
    {
        title: 'Appointments API',
        path: '/api',
        category: 'Technical',
        content: 'List appointments today book new slot cancel existing appointment'
    },

    // Setup
    {
        title: 'Setup Guide',
        path: '/setup',
        category: 'Guide',
        content: 'Installation deployment composer git database migration environment setup'
    },
    {
        title: 'Installation Steps',
        path: '/setup',
        category: 'Guide',
        content: 'Clone repository install dependencies environment setup database migration schema'
    },
    {
        title: 'System Requirements',
        path: '/setup',
        category: 'Guide',
        content: 'PHP 8.2 MySQL 8.0 Composer SSL production requirements'
    },

    // Arabic search terms
    {
        title: 'نظرة عامة',
        path: '/',
        category: 'عام',
        content: 'نظام إدارة عيادة رؤية طب العيون ميزات جدولة مواعيد سجلات طبية مخزون مالية'
    },
    {
        title: 'هيكلة النظام',
        path: '/architecture',
        category: 'تقني',
        content: 'MVC PHP CodeIgniter MySQL دورة حياة طلب توجيه متحكمات عروض نماذج'
    },
    {
        title: 'لوحة تحكم الطبيب',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'إحصائيات مواعيد طقس حبوب لقاح جفاف عين أخبار إجراءات سريعة مواعيد قادمة أنشطة حديثة لوحة ملاحظات تحليلات مرئية تنبيهات مواعيد فائتة'
    },
    {
        title: 'المواعيد القادمة',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'مواعيد قادمة اليوم تواريخ مستقبلية معلومات مريض وقت حالة عد تنازلي شريط تقدم تصفح'
    },
    {
        title: 'الأنشطة الأخيرة',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'أنشطة حديثة شريط زمني حجوزات مواعيد تغييرات حالة رفع ملفات وصفات طبية ترتيب زمني نافذة منبثقة تصفية'
    },
    {
        title: 'لوحة الملاحظات',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'لوحة ملاحظات ملاحظات لاصقة سحب إفلات تغيير حجم تخصيص ألوان إكمال تلقائي مرضى مواعيد أدوية تنبيهات'
    },
    {
        title: 'التحليلات المرئية',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'تحليلات مرئية مخططات رسوم بيانية اتجاهات إحصائيات 30 يوم مواعيد مرضى جدد Chart.js وضع نهاري ليلي'
    },
    {
        title: 'تنبيهات اليوم',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'تنبيهات اليوم تنبيهات نشطة مجدولة مرتبطة بمريض مرتبطة بموعد تصفية وقت وصول سريع إدارة'
    },
    {
        title: 'المواعيد الفائتة',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'مواعيد فائتة أيام سابقة غير مكتملة ملغاة تتبع متابعة تصفح علامات حالة إجراءات سريعة وضع علامة مكتمل'
    },
    {
        title: 'إعادة ترتيب الودجتات',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'إعادة ترتيب ودجتات سحب إفلات تحريك ودجتات تخصيص ترتيب لوحة تحكم أزرار أسهم أعلى أسفل حفظ ترتيب دائم'
    },
    {
        title: 'إعدادات الموبايل',
        path: '/dashboards/doctor',
        category: 'لوحات التحكم',
        content: 'إعدادات موبايل إخفاء أزرار إعادة ترتيب أجهزة موبايل تبديل رؤية عرض شاشة 768px إعداد دائم تفضيلات'
    },
    {
        title: 'لوحة تحكم السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'لوحة تحكم سكرتارية إحصائيات مواعيد مدفوعات إجراءات سريعة ودجت طقس تحديث تلقائي تحديثات فورية تسجيل حضور مرضى'
    },
    {
        title: 'بطاقات إحصائيات السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'إجمالي مواعيد محجوز تم الحضور مكتمل لم يحضر إحصائيات عدادات لوحة تحكم فورية'
    },
    {
        title: 'إجراءات سريعة السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'إجراءات سريعة حجوزات مرضى مدفوعات مصروفات ملف شخصي تمرير أفقي دعم لمس RTL'
    },
    {
        title: 'مواعيد اليوم السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'مواعيد اليوم جدول معلومات مريض طبيب نوع زيارة حالة تسجيل حضور عرض تفاصيل'
    },
    {
        title: 'المدفوعات الأخيرة السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'مدفوعات حديثة قائمة اسم مريض نوع دفع مبلغ طابع زمني معاملات'
    },
    {
        title: 'التحديث التلقائي السكرتارية',
        path: '/dashboards/secretary',
        category: 'لوحات التحكم',
        content: 'تحديث تلقائي 30 ثانية استطلاع API اكتشاف رؤية تحديثات مباشرة معالجة أخطاء'
    },
    {
        title: 'لوحة تحكم المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'لوحة تحكم مسؤول إحصائيات نظام مستخدمين مرضى مواعيد مالية مراقبة صحة أنشطة حديثة معاينة دور'
    },
    {
        title: 'إحصائيات نظام المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'بطاقات إحصائيات نظام مستخدمين مرضى مواعيد مقاييس مالية 30 يوم إيرادات معاملات'
    },
    {
        title: 'إحصائيات مستخدمين المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'إحصائيات مستخدمين إجمالي مستخدمين مستخدمين نشطين أطباء سكرتارية تفصيل دور'
    },
    {
        title: 'إحصائيات مرضى المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'إحصائيات مرضى إجمالي مرضى مرضى نشطين عدد'
    },
    {
        title: 'إحصائيات مواعيد المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'إحصائيات مواعيد 30 يوم إجمالي مكتمل ملغى تفصيل حالة'
    },
    {
        title: 'إحصائيات مالية المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'إحصائيات مالية إيرادات معاملات خصومات مدفوعات 30 يوم'
    },
    {
        title: 'أنشطة حديثة المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'أنشطة حديثة سجلات تدقيق شريط زمني إجراءات مستخدم محاولات تسجيل دخول تغييرات نظام'
    },
    {
        title: 'صحة نظام المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'صحة نظام اتصال قاعدة بيانات مساحة تخزين إصدار PHP إضافات مراقبة مؤشرات حالة'
    },
    {
        title: 'معاينة دور المسؤول',
        path: '/dashboards/admin',
        category: 'لوحات التحكم',
        content: 'معاينة دور واجهة طبيب سكرتارية تبديل حسابات وضع معاينة'
    },
    {
        title: 'وحدة المسؤول',
        path: '/modules/admin',
        category: 'الوحدات',
        content: 'إدارة مستخدمين إعدادات تكوين سجلات تدقيق أمان'
    },
    {
        title: 'بوابة الطبيب',
        path: '/modules/doctor',
        category: 'الوحدات',
        content: 'تشخيص وصفات طبية انكسار فحص مريض أدوية'
    },
    {
        title: 'مكتب السكرتارية',
        path: '/modules/secretary',
        category: 'الوحدات',
        content: 'مواعيد تسجيل مرضى سير عمل دفع واتساب'
    },
    {
        title: 'واجهة برمجة التطبيقات',
        path: '/api',
        category: 'تقني',
        content: 'API REST مصادقة مرضى مواعيد نقاط نهاية'
    },
    {
        title: 'التثبيت والتشغيل',
        path: '/setup',
        category: 'دليل',
        content: 'تثبيت نشر composer git قاعدة بيانات ترحيل'
    },
    {
        title: 'The Sidebar',
        path: '/ui-components/sidebar',
        category: 'UI Components',
        content: 'sidebar navigation component user avatar profile image responsive mobile desktop toggle menu role-based navigation submenu collapsible customizable items settings'
    },
    {
        title: 'الشريط الجانبي',
        path: '/ui-components/sidebar',
        category: 'مكونات الواجهة',
        content: 'شريط جانبي تنقل مكون صورة رمزية مستخدم صورة ملف شخصي متجاوب هاتف محمول سطح مكتب تبديل قائمة تنقل قائم على الدور قائمة فرعية قابلة للطي عناصر قابلة للتخصيص إعدادات'
    },
    {
        title: 'Notifications',
        path: '/ui-components/notifications',
        category: 'UI Components',
        content: 'notifications bell icon badge counter unread count panel swipe delete mobile gesture touchstart touchend polling real-time sound alert mark read delete clear all'
    },
    {
        title: 'الإشعارات',
        path: '/ui-components/notifications',
        category: 'مكونات الواجهة',
        content: 'إشعارات أيقونة جرس شارة عداد عدد غير مقروء لوحة سحب حذف هاتف محمول إيماءة لمس استطلاع فوري صوت تنبيه وضع علامة مقروء حذف مسح الكل'
    },
    {
        title: 'Theme Switch',
        path: '/ui-components/theme-switch',
        category: 'UI Components',
        content: 'theme switch toggle light dark mode animation sun moon stars clouds localStorage database synchronization settings login page persistent storage'
    },
    {
        title: 'مبدل الوضع',
        path: '/ui-components/theme-switch',
        category: 'مكونات الواجهة',
        content: 'مبدل وضع تبديل فاتح داكن رسوم متحركة شمس قمر نجوم غيوم localStorage قاعدة بيانات مزامنة إعدادات صفحة تسجيل دخول تخزين دائم'
    },
    {
        title: 'Tool Bar',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'tool bar notice bar top navigation weather widget temperature pollen index dry eye risk date time clock calendar appointments scroll ophthalmology tools calculators IOL IOP OSDI visual acuity macular thickness cataract surgery diabetic retinopathy pachymetry refraction consistency target IOP pediatric IOL corneal astigmatism post-operative outcome auto-detect patient profile appointment'
    },
    {
        title: 'Notice Bar Weather Widget',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'weather widget temperature icon conditions pollen index allergic conjunctivitis dry eye risk humidity wind speed forecast 5-day API geolocation localStorage cache OpenWeatherMap'
    },
    {
        title: 'Notice Bar Date Time',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'date time widget clock calendar popover real-time updates analog clock hour minute second hands interactive calendar date navigation updateNoticeBarDateTime'
    },
    {
        title: 'Notice Bar Appointments',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'upcoming appointments scroll widget auto-scroll animation real-time updates next day appointments popover pagination quick actions loadNextAppointment API'
    },
    {
        title: 'Ophthalmology Tools Menu',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'ophthalmology tools calculators analyzers IOL power calculator pediatric IOL corneal astigmatism IOP trend analyzer target IOP refraction consistency visual acuity progress OSDI dry eye pachymetry adjusted IOP diabetic retinopathy risk macular thickness trend cataract surgery readiness post-operative outcome auto-detect patient profile appointment'
    },
    {
        title: 'IOL Power Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'IOL intraocular lens power calculator SRK/T Hoffer Q Holladay 1 formulas axial length keratometry target refraction A-constant biometric data IOLCalculatorService IOLCalculatorInterface'
    },
    {
        title: 'Pediatric IOL Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'pediatric IOL undercorrection calculator age eye growth development PediatricIOLUndercorrectionService CalculatorInterface'
    },
    {
        title: 'Corneal Astigmatism Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'corneal astigmatism calculator vector analysis surgical recommendations CornealAstigmatismService CalculatorInterface'
    },
    {
        title: 'IOP Trend Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'IOP trend analyzer intraocular pressure spikes treatment response clinical alerts auto-detect patient profile appointment readings visualization graph IOPTrendAnalyzerService IOPTrendAnalyzerInterface'
    },
    {
        title: 'Target IOP Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'target IOP calculator glaucoma stage baseline IOP life expectancy risk factors clinical guidelines TargetIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'Refraction Consistency Checker',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'refraction consistency checker subjective objective measurements discrepancies validation RefractionConsistencyService CalculatorInterface'
    },
    {
        title: 'Visual Acuity Progress Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'visual acuity progress calculator Snellen LogMAR conversion improvement worsening trend auto-detect patient history summary graph VisualAcuityProgressService CalculatorInterface'
    },
    {
        title: 'OSDI Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'OSDI dry eye severity index calculator 12 questions normal mild moderate severe scoring assessment OSDICalculatorService CalculatorInterface'
    },
    {
        title: 'Pachymetry-Adjusted IOP Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'pachymetry adjusted IOP calculator central corneal thickness CCT measurement artifacts corrected IOP PachymetryAdjustedIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'Diabetic Retinopathy Risk Estimator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'diabetic retinopathy risk estimator diabetes duration HbA1c blood pressure fundus examination grade progression risk DiabeticRetinopathyRiskEstimatorService AnalyzerInterface'
    },
    {
        title: 'Macular Thickness Trend Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'macular thickness trend analyzer central thickness worsening improving stable auto-detect patient data alerts clinical summary MacularThicknessTrendAnalyzerService AnalyzerInterface'
    },
    {
        title: 'Cataract Surgery Readiness Score',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'cataract surgery readiness score visual acuity visual complaints lens opacity grade complications classification recommendation CataractSurgeryReadinessService SurgicalToolInterface'
    },
    {
        title: 'Post-Operative Outcome Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'post-operative outcome analyzer pre-operative post-operative visual acuity refractive results comparison PostOperativeOutcomeAnalyzerService SurgicalToolInterface'
    },
    {
        title: 'شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'شريط أدوات شريط إشعارات تنقل علوي ودجت طقس درجة حرارة مؤشر حبوب لقاح خطر جفاف عين تاريخ وقت ساعة تقويم مواعيد تمرير أدوات طب عيون حاسبات IOL IOP OSDI حدة بصر سماكة بقعة جراحة إعتام عدسة اعتلال شبكية سكري قياس سماكة قرنية اتساق انكسار ضغط عين مستهدف IOL أطفال استجماتيزم قرني نتائج ما بعد جراحة اكتشاف تلقائي ملف مريض موعد'
    },
    {
        title: 'ودجت الطقس شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'ودجت طقس درجة حرارة أيقونة حالات مؤشر حبوب لقاح التهاب ملتحمة تحسسي خطر جفاف عين رطوبة سرعة رياح توقعات 5 أيام API تحديد موقع تخزين محلي ذاكرة تخزين مؤقت OpenWeatherMap'
    },
    {
        title: 'ودجت التاريخ والوقت شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'ودجت تاريخ وقت ساعة تقويم نافذة منبثقة تحديثات فورية ساعة تناظرية ساعة دقيقة ثانية عقارب تقويم تفاعلي تنقل تاريخ updateNoticeBarDateTime'
    },
    {
        title: 'مواعيد شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مواعيد قادمة ودجت تمرير تمرير تلقائي رسوم متحركة تحديثات فورية مواعيد اليوم التالي نافذة منبثقة تصفح إجراءات سريعة loadNextAppointment API'
    },
    {
        title: 'قائمة أدوات طب العيون',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'أدوات طب عيون حاسبات محللات حاسبة قوة IOL IOL أطفال استجماتيزم قرني محلل اتجاه IOP حاسبة IOP مستهدف مدقق اتساق انكسار تقدم حدة بصر OSDI جفاف عين IOP معدل بقياس سماكة قرنية مقدر خطر اعتلال شبكية سكري محلل اتجاه سماكة بقعة جاهزية جراحة إعتام عدسة نتائج ما بعد جراحة اكتشاف تلقائي ملف مريض موعد'
    },
    {
        title: 'حاسبة قوة IOL',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة قوة عدسة عين داخل مقلة IOL SRK/T Hoffer Q Holladay 1 صيغ طول محوري قياس قرنية انكسار مستهدف ثابت A بيانات قياسات حيوية IOLCalculatorService IOLCalculatorInterface'
    },
    {
        title: 'حاسبة IOL للأطفال',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة تقليل قوة IOL للأطفال عمر نمو عين تطور PediatricIOLUndercorrectionService CalculatorInterface'
    },
    {
        title: 'حاسبة الاستجماتيزم القرني',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة استجماتيزم قرني تحليل متجهات توصيات جراحية CornealAstigmatismService CalculatorInterface'
    },
    {
        title: 'محلل اتجاه IOP',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل اتجاه ضغط عين داخل مقلة IOP ارتفاعات استجابة علاج تنبيهات سريرية اكتشاف تلقائي ملف مريض موعد قراءات تصور رسم بياني IOPTrendAnalyzerService IOPTrendAnalyzerInterface'
    },
    {
        title: 'حاسبة IOP المستهدف',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة ضغط عين مستهدف مرحلة جلوكوما ضغط عين أساسي متوسط عمر متوقع عوامل خطر مبادئ توجيهية سريرية TargetIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'مدقق اتساق الانكسار',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مدقق اتساق انكسار قياسات ذاتية موضوعية تناقضات تحقق RefractionConsistencyService CalculatorInterface'
    },
    {
        title: 'حاسبة تقدم حدة البصر',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة تقدم حدة بصر تحويل Snellen LogMAR تحسن تدهور اتجاه اكتشاف تلقائي مريض تاريخ ملخص رسم بياني VisualAcuityProgressService CalculatorInterface'
    },
    {
        title: 'حاسبة OSDI',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة مؤشر شدة جفاف عين OSDI 12 سؤال عادي خفيف متوسط شديد تصنيف تقييم OSDICalculatorService CalculatorInterface'
    },
    {
        title: 'حاسبة IOP المعدلة بقياس سماكة القرنية',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة IOP معدلة بقياس سماكة قرنية سماكة قرنية مركزية CCT أخطاء قياس IOP مصحح PachymetryAdjustedIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'مقدر خطر اعتلال الشبكية السكري',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مقدر خطر اعتلال شبكية سكري مدة سكري HbA1c ضغط دم فحص قاع عين درجة خطر تطور DiabeticRetinopathyRiskEstimatorService AnalyzerInterface'
    },
    {
        title: 'محلل اتجاه سماكة البقعة',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل اتجاه سماكة بقعة سماكة مركزية تدهور تحسن مستقر اكتشاف تلقائي بيانات مريض تنبيهات ملخص سريري MacularThicknessTrendAnalyzerService AnalyzerInterface'
    },
    {
        title: 'درجة جاهزية جراحة إعتام عدسة العين',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'درجة جاهزية جراحة إعتام عدسة عين حدة بصر شكاوى بصرية درجة عتامة عدسة مضاعفات تصنيف توصية CataractSurgeryReadinessService SurgicalToolInterface'
    },
    {
        title: 'محلل نتائج ما بعد الجراحة',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل نتائج ما بعد جراحة قبل جراحة بعد جراحة حدة بصر نتائج انكسارية مقارنة PostOperativeOutcomeAnalyzerService SurgicalToolInterface'
    },
    {
        title: 'The Dock',
        path: '/ui-components/dock',
        category: 'UI Components',
        content: 'dock quick access navigation macOS minimize maximize autohide mobile radial menu C-shape stack menu genie effect localStorage database settings'
    },
    {
        title: 'الـ Dock',
        path: '/ui-components/dock',
        category: 'مكونات الواجهة',
        content: 'dock وصول سريع تنقل macOS تصغير تكبير إخفاء تلقائي هاتف محمول قائمة شعاعية شكل C قائمة مكدسة تأثير genie localStorage قاعدة بيانات إعدادات'
    },
    {
        title: 'Global Search',
        path: '/ui-components/search',
        category: 'UI Components',
        content: 'global search comprehensive search appointments patients drugs prescriptions media consultation notes smart filter patient ID date filter & operator # operator'
    },
    {
        title: 'البحث الشامل',
        path: '/ui-components/search',
        category: 'مكونات الواجهة',
        content: 'بحث شامل مواعيد مرضى أدوية وصفات وسائط ملاحظات استشارة تصفية ذكية رقم مريض فلتر تاريخ عامل & عامل #'
    },
    {
        title: 'Tool Bar',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'tool bar notice bar top navigation weather widget temperature pollen index dry eye risk date time clock calendar appointments scroll ophthalmology tools calculators IOL IOP OSDI visual acuity macular thickness cataract surgery diabetic retinopathy pachymetry refraction consistency target IOP pediatric IOL corneal astigmatism post-operative outcome'
    },
    {
        title: 'Tool Bar Weather Widget',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'weather widget temperature icon conditions pollen index allergic conjunctivitis dry eye risk humidity wind speed forecast 5-day API geolocation localStorage cache'
    },
    {
        title: 'Tool Bar Date Time',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'date time widget clock calendar popover real-time updates analog clock hour minute second hands interactive calendar date navigation'
    },
    {
        title: 'Tool Bar Appointments',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'upcoming appointments scroll widget auto-scroll animation real-time updates next day appointments popover pagination quick actions'
    },
    {
        title: 'Ophthalmology Tools',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'ophthalmology tools calculators analyzers IOL power calculator pediatric IOL corneal astigmatism IOP trend analyzer target IOP refraction consistency visual acuity progress OSDI dry eye pachymetry adjusted IOP diabetic retinopathy risk macular thickness trend cataract surgery readiness post-operative outcome auto-detect patient profile appointment'
    },
    {
        title: 'IOL Power Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'IOL intraocular lens power calculator SRK/T Hoffer Q Holladay 1 formulas axial length keratometry target refraction A-constant biometric data'
    },
    {
        title: 'IOP Trend Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'IOP trend analyzer intraocular pressure spikes treatment response clinical alerts auto-detect patient profile appointment readings visualization graph'
    },
    {
        title: 'Visual Acuity Progress',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'visual acuity progress calculator Snellen LogMAR conversion improvement worsening trend auto-detect patient history summary graph'
    },
    {
        title: 'OSDI Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'OSDI dry eye severity index calculator 12 questions normal mild moderate severe scoring assessment'
    },
    {
        title: 'Macular Thickness Trend Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'macular thickness trend analyzer central thickness worsening improving stable auto-detect patient data alerts clinical summary'
    },
    {
        title: 'Pediatric IOL Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'pediatric IOL undercorrection calculator age eye growth development PediatricIOLUndercorrectionService CalculatorInterface'
    },
    {
        title: 'Corneal Astigmatism Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'corneal astigmatism calculator vector analysis surgical recommendations CornealAstigmatismService CalculatorInterface'
    },
    {
        title: 'Target IOP Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'target IOP calculator glaucoma stage baseline IOP life expectancy risk factors clinical guidelines TargetIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'Refraction Consistency Checker',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'refraction consistency checker subjective objective measurements discrepancies validation RefractionConsistencyService CalculatorInterface'
    },
    {
        title: 'Pachymetry-Adjusted IOP Calculator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'pachymetry adjusted IOP calculator central corneal thickness CCT measurement artifacts corrected IOP PachymetryAdjustedIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'Diabetic Retinopathy Risk Estimator',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'diabetic retinopathy risk estimator diabetes duration HbA1c blood pressure fundus examination grade progression risk DiabeticRetinopathyRiskEstimatorService AnalyzerInterface'
    },
    {
        title: 'Cataract Surgery Readiness Score',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'cataract surgery readiness score visual acuity visual complaints lens opacity grade complications classification recommendation CataractSurgeryReadinessService SurgicalToolInterface'
    },
    {
        title: 'Post-Operative Outcome Analyzer',
        path: '/ui-components/notice-bar',
        category: 'UI Components',
        content: 'post-operative outcome analyzer pre-operative post-operative visual acuity refractive results comparison PostOperativeOutcomeAnalyzerService SurgicalToolInterface'
    },
    {
        title: 'شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'شريط أدوات شريط إشعارات تنقل علوي ودجت طقس درجة حرارة مؤشر حبوب لقاح خطر جفاف عين تاريخ وقت ساعة تقويم مواعيد تمرير أدوات طب عيون حاسبات IOL IOP OSDI حدة بصر سماكة بقعة جراحة إعتام عدسة اعتلال شبكية سكري قياس سماكة قرنية اتساق انكسار ضغط عين مستهدف IOL أطفال استجماتيزم قرني نتائج ما بعد جراحة'
    },
    {
        title: 'ودجت الطقس شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'ودجت طقس درجة حرارة أيقونة حالات مؤشر حبوب لقاح التهاب ملتحمة تحسسي خطر جفاف عين رطوبة سرعة رياح توقعات 5 أيام API تحديد موقع تخزين محلي ذاكرة تخزين مؤقت'
    },
    {
        title: 'ودجت التاريخ والوقت شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'ودجت تاريخ وقت ساعة تقويم نافذة منبثقة تحديثات فورية ساعة تناظرية ساعة دقيقة ثانية عقارب تقويم تفاعلي تنقل تاريخ'
    },
    {
        title: 'مواعيد شريط الأدوات',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مواعيد قادمة ودجت تمرير تمرير تلقائي رسوم متحركة تحديثات فورية مواعيد اليوم التالي نافذة منبثقة تصفح إجراءات سريعة'
    },
    {
        title: 'أدوات طب العيون',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'أدوات طب عيون حاسبات محللات حاسبة قوة IOL IOL أطفال استجماتيزم قرني محلل اتجاه IOP حاسبة IOP مستهدف مدقق اتساق انكسار تقدم حدة بصر OSDI جفاف عين IOP معدل بقياس سماكة قرنية مقدر خطر اعتلال شبكية سكري محلل اتجاه سماكة بقعة جاهزية جراحة إعتام عدسة نتائج ما بعد جراحة اكتشاف تلقائي ملف مريض موعد'
    },
    {
        title: 'حاسبة قوة IOL',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة قوة عدسة عين داخل مقلة IOL SRK/T Hoffer Q Holladay 1 صيغ طول محوري قياس قرنية انكسار مستهدف ثابت A بيانات قياسات حيوية'
    },
    {
        title: 'محلل اتجاه IOP',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل اتجاه ضغط عين داخل مقلة IOP ارتفاعات استجابة علاج تنبيهات سريرية اكتشاف تلقائي ملف مريض موعد قراءات تصور رسم بياني'
    },
    {
        title: 'حاسبة تقدم حدة البصر',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة تقدم حدة بصر تحويل Snellen LogMAR تحسن تدهور اتجاه اكتشاف تلقائي مريض تاريخ ملخص رسم بياني'
    },
    {
        title: 'حاسبة OSDI',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة مؤشر شدة جفاف عين OSDI 12 سؤال عادي خفيف متوسط شديد تصنيف تقييم'
    },
    {
        title: 'محلل اتجاه سماكة البقعة',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل اتجاه سماكة بقعة سماكة مركزية تدهور تحسن مستقر اكتشاف تلقائي بيانات مريض تنبيهات ملخص سريري'
    },
    {
        title: 'حاسبة IOL للأطفال',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة تقليل قوة IOL للأطفال عمر نمو عين تطور PediatricIOLUndercorrectionService CalculatorInterface'
    },
    {
        title: 'حاسبة الاستجماتيزم القرني',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة استجماتيزم قرني تحليل متجهات توصيات جراحية CornealAstigmatismService CalculatorInterface'
    },
    {
        title: 'حاسبة IOP المستهدف',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة ضغط عين مستهدف مرحلة جلوكوما ضغط عين أساسي متوسط عمر متوقع عوامل خطر مبادئ توجيهية سريرية TargetIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'مدقق اتساق الانكسار',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مدقق اتساق انكسار قياسات ذاتية موضوعية تناقضات تحقق RefractionConsistencyService CalculatorInterface'
    },
    {
        title: 'حاسبة IOP المعدلة بقياس سماكة القرنية',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'حاسبة IOP معدلة بقياس سماكة قرنية سماكة قرنية مركزية CCT أخطاء قياس IOP مصحح PachymetryAdjustedIOPCalculatorService CalculatorInterface'
    },
    {
        title: 'مقدر خطر اعتلال الشبكية السكري',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'مقدر خطر اعتلال شبكية سكري مدة سكري HbA1c ضغط دم فحص قاع عين درجة خطر تطور DiabeticRetinopathyRiskEstimatorService AnalyzerInterface'
    },
    {
        title: 'درجة جاهزية جراحة إعتام عدسة العين',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'درجة جاهزية جراحة إعتام عدسة عين حدة بصر شكاوى بصرية درجة عتامة عدسة مضاعفات تصنيف توصية CataractSurgeryReadinessService SurgicalToolInterface'
    },
    {
        title: 'محلل نتائج ما بعد الجراحة',
        path: '/ui-components/notice-bar',
        category: 'مكونات الواجهة',
        content: 'محلل نتائج ما بعد جراحة قبل جراحة بعد جراحة حدة بصر نتائج انكسارية مقارنة PostOperativeOutcomeAnalyzerService SurgicalToolInterface'
    },
    {
        title: 'Calendar',
        path: '/doctors-pages/calendar',
        category: "Doctor Modules",
        content: 'calendar appointments time slots filters auto refresh medical history add appointment delete appointment progress bars quick actions'
    },
    {
        title: 'التقويم',
        path: '/doctors-pages/calendar',
        category: 'صفحات الطبيب',
        content: 'تقويم مواعيد فترات زمنية فلاتر تحديث تلقائي سجل طبي إضافة موعد حذف موعد أشرطة تقدم إجراءات سريعة'
    },
    {
        title: 'Appointment Details',
        path: '/doctors-pages/appointment',
        category: "Doctor Modules",
        content: 'appointment details patient information medications lab tests glasses prescription attachments consultation notes medical history carousel forum topics status colors doctor detection follow-up appointment original appointment mark completed status change modal tools actions more actions patient card camera capture upload files print prescription edit consultation IOP visual acuity slit lamp diagnosis treatment'
    },
    {
        title: 'تفاصيل الموعد',
        path: '/doctors-pages/appointment',
        category: 'صفحات الطبيب',
        content: 'تفاصيل موعد معلومات مريض أدوية اختبارات مختبر وصفة نظارات مرفقات ملاحظات استشارة سجل طبي عجلة مواضيع منتدى ألوان حالة اكتشاف طبيب موعد متابعة موعد أصلي تحديد كمكتمل تغيير حالة نافذة أدوات إجراءات مزيد إجراءات بطاقة مريض التقاط كاميرا رفع ملفات طباعة وصفة تعديل استشارة IOP حدة بصر مصباح شقي تشخيص علاج'
    },
    {
        title: 'Secretary Bookings',
        path: '/doctors-pages/secretary-bookings',
        category: "Calendar",
        content: 'secretary bookings calendar appointments statistics cards total pending checked in completed auto refresh keyboard shortcuts time slots 2pm 11pm 15 minute intervals friday holiday add booking edit booking confirm attendance delete booking print booking details patient search doctor selection visit type payment integration status tracking booked checkedin inprogress completed cancelled SecretaryController createBooking updateBooking confirmAttendance deleteBooking getBookingsCalendar'
    },
    {
        title: 'حجوزات السكرتير',
        path: '/doctors-pages/secretary-bookings',
        category: 'التقويم',
        content: 'حجوزات سكرتير تقويم مواعيد بطاقات إحصائية إجمالي معلق تم الحضور مكتمل تحديث تلقائي اختصارات لوحة مفاتيح فترات زمنية 2 مساء 11 مساء فواصل 15 دقيقة عطلة الجمعة إضافة حجز تعديل حجز تأكيد حضور حذف حجز طباعة تفاصيل حجز بحث مريض اختيار طبيب نوع زيارة تكامل دفع تتبع حالة محجوز تم الحضور قيد التنفيذ مكتمل ملغي SecretaryController createBooking updateBooking confirmAttendance deleteBooking getBookingsCalendar'
    },
    {
        title: 'Secretary Patients',
        path: '/doctors-pages/secretary-patients',
        category: "Patients",
        content: 'secretary patients management search filter statistics cards total active recent payments auto refresh keyboard shortcuts quick search advanced search modal autocomplete phone number national ID gender filter age range filter last visit filter pagination quick actions view patient book appointment view payments patient details appointments history payment history SecretaryController patients getPatientsData viewPatient newPatient createPatient getPatientStats getPatientsWithFilters'
    },
    {
        title: 'مرضى السكرتير',
        path: '/doctors-pages/secretary-patients',
        category: 'المرضى',
        content: 'مرضى سكرتير إدارة بحث تصفية بطاقات إحصائية إجمالي نشط حديث مدفوعات تحديث تلقائي اختصارات لوحة مفاتيح بحث سريع بحث متقدم نافذة إكمال تلقائي رقم هاتف رقم قومي فلتر جنس فلتر فئة عمرية فلتر آخر زيارة تصفح إجراءات سريعة عرض مريض حجز موعد عرض مدفوعات تفاصيل مريض سجل مواعيد سجل مدفوعات SecretaryController patients getPatientsData viewPatient newPatient createPatient getPatientStats getPatientsWithFilters'
    },
    {
        title: 'Secretary Payments',
        path: '/doctors-pages/secretary-payments',
        category: "Finance",
        content: 'secretary payments management daily balance opening balance total received total expenses current balance payment types new booking followup consultation procedure transactions log pagination date filter type filter export excel payments table quick search type filter method filter add balance add expense payment details expense details financial transactions SecretaryController payments viewPayment viewExpense getDailyBalance getPaymentTypesSummary getTodayPayments getPaymentsByPatient'
    },
    {
        title: 'مدفوعات السكرتير',
        path: '/doctors-pages/secretary-payments',
        category: 'المالية',
        content: 'مدفوعات سكرتير إدارة رصيد يومي رصيد افتتاحي إجمالي مستلم إجمالي مصروفات رصيد حالي أنواع مدفوعات حجز جديد إعادة كشف استشارة طبية إجراء طبي سجل معاملات تصفح فلتر تاريخ فلتر نوع تصدير excel جدول مدفوعات بحث سريع فلتر نوع فلتر طريقة إضافة رصيد إضافة مصروف تفاصيل دفعة تفاصيل مصروف معاملات مالية SecretaryController payments viewPayment viewExpense getDailyBalance getPaymentTypesSummary getTodayPayments getPaymentsByPatient'
    },
    {
        title: 'Admin Module',
        path: '/admin-module',
        category: "Admin",
        content: 'admin module users management backup restore media management system notifications create user edit user delete user role assignment admin doctor secretary account status database backup full backup website backup restore upload download media files thumbnail view table view folder view sort filter bulk operations context menu file info system notifications notification types recipients preview user selection AdminController users createUser updateUser deleteUser backup apiBackupDatabase apiBackupFull apiBackupWebsite apiBackupList apiBackupRestore apiMediaList apiMediaDelete apiMediaBackup apiNotificationsSystem'
    },
    {
        title: 'وحدة الأدمن',
        path: '/admin-module',
        category: 'الأدمن',
        content: 'وحدة أدمن إدارة مستخدمين نسخ احتياطي استعادة إدارة وسائط إشعارات نظام إنشاء مستخدم تعديل مستخدم حذف مستخدم تعيين دور أدمن طبيب سكرتير حالة حساب نسخ احتياطي قاعدة بيانات نسخ احتياطي كامل نسخ احتياطي موقع استعادة رفع تحميل ملفات وسائط عرض صور مصغرة عرض جدول عرض مجلدات ترتيب تصفية عمليات مجمعة قائمة سياق معلومات ملف إشعارات نظام أنواع إشعارات مستلمين معاينة اختيار مستخدم AdminController users createUser updateUser deleteUser backup apiBackupDatabase apiBackupFull apiBackupWebsite apiBackupList apiBackupRestore apiMediaList apiMediaDelete apiMediaBackup apiNotificationsSystem'
    },
    {
        title: 'Patients',
        path: '/doctors-pages/patients',
        category: "Doctor Modules",
        content: 'patients search filter sort pagination statistics cards add patient edit patient delete patient quick actions phone tooltip doctor filter gender filter age filter last visit filter keyboard shortcuts'
    },
    {
        title: 'المرضى',
        path: '/doctors-pages/patients',
        category: 'صفحات الطبيب',
        content: 'مرضى بحث تصفية ترتيب تصفح بطاقات إحصائية إضافة مريض تعديل مريض حذف مريض إجراءات سريعة تلميح هاتف فلتر طبيب فلتر جنس فلتر عمر فلتر آخر زيارة اختصارات لوحة مفاتيح'
    },
    {
        title: 'Forum',
        path: '/doctors-pages/forum',
        category: "Doctor Modules",
        content: 'forum discussion topics posts replies likes dislikes pin resolved meta tags autocomplete rich text editor attachments images categories search filter'
    },
    {
        title: 'المنتدى',
        path: '/doctors-pages/forum',
        category: 'صفحات الطبيب',
        content: 'منتدى مناقشة مواضيع منشورات ردود إعجاب عدم إعجاب تثبيت محلول علامات وصفية إكمال تلقائي محرر نص غني مرفقات صور فئات بحث تصفية'
    },

    // API Endpoints - Doctor
    {
        title: 'Doctor API',
        path: '/api',
        category: 'API Reference',
        content: 'doctor settings organizer month ophthalmology news personal preferences theme dock sidebar items'
    },
    {
        title: 'Doctor Settings API',
        path: '/api',
        category: 'API Reference',
        content: 'GET PUT doctor settings personal preferences theme dock state sidebar items customization'
    },

    // API Endpoints - Alerts
    {
        title: 'Alerts API',
        path: '/api',
        category: 'API Reference',
        content: 'alerts today active patient create update delete dismiss disable all delete all GET POST PUT DELETE'
    },
    {
        title: 'Today Alerts',
        path: '/api',
        category: 'API Reference',
        content: 'GET today alerts scheduled patient linked appointment linked time based filtering'
    },

    // API Endpoints - Notes
    {
        title: 'Notes API',
        path: '/api',
        category: 'API Reference',
        content: 'notes create update delete get list delete all sticky notes dashboard board'
    },

    // API Endpoints - Prescriptions
    {
        title: 'Prescriptions API',
        path: '/api',
        category: 'API Reference',
        content: 'prescriptions medications glasses create update delete get list filter patient appointment'
    },
    {
        title: 'Medication Prescriptions',
        path: '/api',
        category: 'API Reference',
        content: 'POST PUT DELETE medications prescriptions drugs dosage frequency duration'
    },
    {
        title: 'Glasses Prescriptions',
        path: '/api',
        category: 'API Reference',
        content: 'POST PUT DELETE glasses prescriptions vision correction measurements'
    },

    // API Endpoints - Lab Tests
    {
        title: 'Lab Tests API',
        path: '/api',
        category: 'API Reference',
        content: 'lab tests radiology create update delete get appointment results reports'
    },

    // API Endpoints - Consultations
    {
        title: 'Consultations API',
        path: '/api',
        category: 'API Reference',
        content: 'consultations create delete note examination diagnosis treatment plan'
    },

    // API Endpoints - Secretary
    {
        title: 'Secretary API',
        path: '/api',
        category: 'API Reference',
        content: 'secretary dashboard bookings calendar create update delete confirm attendance patients payments'
    },
    {
        title: 'Secretary Bookings',
        path: '/api',
        category: 'API Reference',
        content: 'POST GET DELETE bookings calendar create update delete confirm attendance details'
    },
    {
        title: 'Secretary Patients API',
        path: '/api',
        category: 'API Reference',
        content: 'GET secretary patients data list view create new patient'
    },

    // API Endpoints - Admin
    {
        title: 'Admin API',
        path: '/api',
        category: 'API Reference',
        content: 'admin users create update delete view as backup database full website media restore download'
    },
    {
        title: 'Admin Users Management',
        path: '/api',
        category: 'API Reference',
        content: 'GET POST PUT DELETE admin users create update delete accounts roles permissions'
    },
    {
        title: 'Admin Backup',
        path: '/api',
        category: 'API Reference',
        content: 'POST GET admin backup database full website list restore upload download'
    },
    {
        title: 'Admin Media Management',
        path: '/api',
        category: 'API Reference',
        content: 'GET POST admin media list delete backup restore download files'
    },
    {
        title: 'Admin View As',
        path: '/api',
        category: 'API Reference',
        content: 'GET admin view as stop view as role preview switch accounts'
    },

    // API Endpoints - Financial
    {
        title: 'Financial API',
        path: '/api',
        category: 'API Reference',
        content: 'payments expenses transactions create update delete get daily balance closure lock export'
    },
    {
        title: 'Payments API',
        path: '/api',
        category: 'API Reference',
        content: 'POST GET PUT DELETE payments create update delete get amount type receipt'
    },
    {
        title: 'Expenses API',
        path: '/api',
        category: 'API Reference',
        content: 'POST GET PUT DELETE expenses create update delete get category amount description'
    },
    {
        title: 'Daily Closure',
        path: '/api',
        category: 'API Reference',
        content: 'POST daily closure balance lock prevent modifications financial records'
    },

    // API Endpoints - Media
    {
        title: 'Media API',
        path: '/api',
        category: 'API Reference',
        content: 'GET media list patient images filter options'
    },

    // API Endpoints - Weather
    {
        title: 'Weather API',
        path: '/api',
        category: 'API Reference',
        content: 'GET weather current forecast Arabic English environmental factors eye health'
    },

    // API Endpoints - Drugs
    {
        title: 'Drugs API',
        path: '/api',
        category: 'API Reference',
        content: 'GET POST drugs search details filter options most used update database autocomplete'
    },
    {
        title: 'Drug Search',
        path: '/api',
        category: 'API Reference',
        content: 'GET searchDrugs getDrugDetails getFilterOptions getMostUsedDrugs searchDrugsAutocomplete'
    },

    // API Endpoints - Appointment Details
    {
        title: 'Appointment Details API',
        path: '/api',
        category: 'API Reference',
        content: 'GET appointments attachments medications glasses followup original reschedule followup'
    },

    // Arabic API search terms
    {
        title: 'واجهة برمجة الطبيب',
        path: '/api',
        category: 'مرجع API',
        content: 'إعدادات طبيب منظم شهر أخبار طب عيون تفضيلات شخصية وضع dock عناصر شريط جانبي'
    },
    {
        title: 'واجهة برمجة التنبيهات',
        path: '/api',
        category: 'مرجع API',
        content: 'تنبيهات اليوم نشطة مريض إنشاء تحديث حذف إلغاء تعطيل الكل حذف الكل'
    },
    {
        title: 'واجهة برمجة الملاحظات',
        path: '/api',
        category: 'مرجع API',
        content: 'ملاحظات إنشاء تحديث حذف الحصول على قائمة حذف الكل ملاحظات لاصقة لوحة'
    },
    {
        title: 'واجهة برمجة الوصفات الطبية',
        path: '/api',
        category: 'مرجع API',
        content: 'وصفات طبية أدوية نظارات إنشاء تحديث حذف الحصول على قائمة تصفية مريض موعد'
    },
    {
        title: 'واجهة برمجة الفحوصات المخبرية',
        path: '/api',
        category: 'مرجع API',
        content: 'فحوصات مخبرية أشعة إنشاء تحديث حذف الحصول على موعد نتائج تقارير'
    },
    {
        title: 'واجهة برمجة الاستشارات',
        path: '/api',
        category: 'مرجع API',
        content: 'استشارات إنشاء حذف ملاحظة فحص تشخيص خطة علاج'
    },
    {
        title: 'واجهة برمجة السكرتارية',
        path: '/api',
        category: 'مرجع API',
        content: 'سكرتارية لوحة تحكم حجوزات تقويم إنشاء تحديث حذف تأكيد حضور مرضى مدفوعات'
    },
    {
        title: 'واجهة برمجة المسؤول',
        path: '/api',
        category: 'مرجع API',
        content: 'مسؤول مستخدمين إنشاء تحديث حذف معاينة نسخ احتياطية قاعدة بيانات كامل موقع وسائط استعادة تنزيل'
    },
    {
        title: 'واجهة برمجة المالية',
        path: '/api',
        category: 'مرجع API',
        content: 'مدفوعات مصروفات معاملات إنشاء تحديث حذف الحصول على رصيد يومي إقفال قفل تصدير'
    },
    {
        title: 'واجهة برمجة الوسائط',
        path: '/api',
        category: 'مرجع API',
        content: 'وسائط قائمة صور مريض خيارات تصفية'
    },
    {
        title: 'واجهة برمجة الطقس',
        path: '/api',
        category: 'مرجع API',
        content: 'طقس حالية توقعات عربية إنجليزية عوامل بيئية صحة عين'
    },
    {
        title: 'واجهة برمجة الأدوية',
        path: '/api',
        category: 'مرجع API',
        content: 'أدوية بحث تفاصيل خيارات تصفية الأكثر استخداماً تحديث قاعدة بيانات إكمال تلقائي'
    },
    {
        title: 'واجهة برمجة تفاصيل الموعد',
        path: '/api',
        category: 'مرجع API',
        content: 'مواعيد مرفقات أدوية نظارات متابعة أصلي إعادة جدولة متابعة'
    },

    // Drugs Page
    {
        title: 'Drug Search',
        path: '/doctors-pages/drugs',
        category: "Doctor Modules",
        content: 'drug search medications database autocomplete filtering real-time results pagination drug details modal database update cronjob weekly automated'
    },
    {
        title: 'بحث الأدوية',
        path: '/doctors-pages/drugs',
        category: 'صفحات الطبيب',
        content: 'بحث أدوية قاعدة بيانات أدوية إكمال تلقائي تصفية نتائج فورية تقسيم على صفحات نافذة تفاصيل دواء تحديث قاعدة بيانات cronjob أسبوعي تلقائي'
    },

    // Finance Page
    {
        title: 'Financial Management',
        path: '/doctors-pages/finance',
        category: "Doctor Modules",
        content: 'financial management payments expenses daily balance daily closure transactions export excel csv filtering pagination payment types methods cash card transfer wallet'
    },
    {
        title: 'الإدارة المالية',
        path: '/doctors-pages/finance',
        category: 'صفحات الطبيب',
        content: 'إدارة مالية مدفوعات مصروفات رصيد يومي إقفال يومي معاملات تصدير excel csv تصفية تقسيم على صفحات أنواع دفع طرق نقدي بطاقة تحويل محفظة'
    },

    // Reports Page
    {
        title: 'Reports',
        path: '/doctors-pages/reports',
        category: "Doctor Modules",
        content: 'reports analytics appointments patients revenue prescriptions visual charts line pie doughnut summary statistics detailed table pagination export csv pdf chart.js date range filters quick dates report types'
    },
    {
        title: 'التقارير',
        path: '/doctors-pages/reports',
        category: 'صفحات الطبيب',
        content: 'تقارير تحليلات مواعيد مرضى إيرادات وصفات رسوم مرئية خطية دائرية مجوفة إحصائيات ملخصة جدول تفصيلي تقسيم على صفحات تصدير csv pdf chart.js مرشحات نطاق تاريخ تواريخ سريعة أنواع تقارير'
    },

    // Medications Page
    {
        title: 'Medications Prescriptions Gallery',
        path: '/doctors-pages/medications',
        category: "Doctor Modules",
        content: 'medications prescriptions gallery patient search autocomplete filter cards grid pagination lazy loading load more modal preview accordion appointments drug name dose frequency duration route notes'
    },
    {
        title: 'معرض وصفات الأدوية',
        path: '/doctors-pages/medications',
        category: 'صفحات الطبيب',
        content: 'وصفات أدوية معرض بحث مريض إكمال تلقائي تصفية بطاقات شبكة تقسيم على صفحات تحميل كسول تحميل المزيد نافذة معاينة accordion مواعيد اسم دواء جرعة تكرار مدة طريقة ملاحظات'
    },

    // Glasses Page
    {
        title: 'Glasses Prescriptions Gallery',
        path: '/doctors-pages/glasses',
        category: "Doctor Modules",
        content: 'glasses prescriptions gallery patient search autocomplete filter cards grid pagination lazy loading load more modal preview eye measurements OD OS sphere cylinder axis near sphere pupillary distance PD lens type'
    },
    {
        title: 'معرض وصفات النظارات',
        path: '/doctors-pages/glasses',
        category: 'صفحات الطبيب',
        content: 'نظارات وصفات معرض بحث مريض إكمال تلقائي تصفية بطاقات شبكة تقسيم على صفحات تحميل كسول تحميل المزيد نافذة معاينة قياسات عين OD OS sphere cylinder axis near sphere مسافة بؤرية PD نوع عدسة'
    },
    {
        title: 'Patients Media Gallery',
        path: '/doctors-pages/media',
        category: "Doctor Modules",
        content: 'media gallery patient images search autocomplete filter cards grid pagination lazy loading load more image modal carousel bootstrap navigation arrows image counter view patient view appointment'
    },
    {
        title: 'معرض صور المرضى',
        path: '/doctors-pages/media',
        category: 'صفحات الطبيب',
        content: 'معرض صور مرضى صور بحث إكمال تلقائي تصفية بطاقات شبكة تقسيم على صفحات تحميل كسول تحميل المزيد نافذة صور carousel bootstrap أسهم تنقل عداد صور عرض مريض عرض موعد'
    },
    {
        title: 'Alerts Management',
        path: '/doctors-pages/alerts',
        category: "Doctor Modules",
        content: 'alerts management notifications reminders toast push notifications time picker material ui patient search autocomplete repeat count interval status active inactive dismissed edit delete toggle dismiss snooze automatic appointment alerts settings'
    },
    {
        title: 'إدارة التنبيهات',
        path: '/doctors-pages/alerts',
        category: 'صفحات الطبيب',
        content: 'إدارة تنبيهات إشعارات تذكيرات toast push notifications منتقي وقت material ui بحث مريض إكمال تلقائي عدد تكرار فترة حالة نشط غير نشط مرفوض تعديل حذف تبديل رفض تأجيل تنبيهات تلقائية مواعيد إعدادات'
    },
    {
        title: 'Notes Management',
        path: '/doctors-pages/notes',
        category: "Doctor Modules",
        content: 'notes management personal notes drag drop resize autocomplete drugs patients appointments $ @ # drug badge patient link appointment link popover color customization alert creation dashboard integration'
    },
    {
        title: 'إدارة الملاحظات',
        path: '/doctors-pages/notes',
        category: 'صفحات الطبيب',
        content: 'إدارة ملاحظات ملاحظات شخصية سحب إفلات تغيير حجم إكمال تلقائي أدوية مرضى مواعيد $ @ # شارة دواء رابط مريض رابط موعد نافذة منبثقة تخصيص لون إنشاء تنبيه تكامل لوحة تحكم'
    },
    {
        title: 'Profile Management',
        path: '/doctors-pages/profile',
        category: "Doctor Modules",
        content: 'profile management account information profile image password change validation strength indicator sidebar dock patients filter treating doctor badge avatar hover preview'
    },
    {
        title: 'إدارة الملف الشخصي',
        path: '/doctors-pages/profile',
        category: 'صفحات الطبيب',
        content: 'إدارة ملف شخصي معلومات حساب صورة ملف شخصي تغيير كلمة مرور تحقق مؤشر قوة شريط جانبي قائمة فلتر مرضى شارة طبيب معالج رمز معاينة تحويم'
    },
    {
        title: 'Settings',
        path: '/doctors-pages/settings',
        category: "Doctor Modules",
        content: 'settings personal preferences alerts notifications back to top dock autohide theme dark light push notifications sidebar items general settings clinic information logos visit costs new visit followup consultation drugs database update progress statistics'
    },
    {
        title: 'الإعدادات',
        path: '/doctors-pages/settings',
        category: 'صفحات الطبيب',
        content: 'إعدادات تفضيلات شخصية تنبيهات إشعارات العودة للأعلى قائمة إخفاء تلقائي ثيم داكن فاتح إشعارات فورية عناصر شريط جانبي إعدادات عامة معلومات عيادة شعارات تكاليف زيارات زيارة جديدة متابعة استشارة قاعدة بيانات أدوية تحديث تقدم إحصائيات'
    },

    // Patient Profile Page
    {
        title: 'Patient Profile - Doctor\'s View',
        path: '/doctors-pages/patient-profile',
        category: "Doctor Modules",
        content: 'patient profile doctor view timeline prescriptions history medical history appointment history patient files documents images camera capture upload alerts forum topics full timeline expand collapse export print edit patient treating doctor badge contact information actions'
    },
    {
        title: 'ملف المريض - عرض الطبيب',
        path: '/doctors-pages/patient-profile',
        category: 'صفحات الطبيب',
        content: 'ملف مريض عرض طبيب جدول زمني تاريخ وصفات تاريخ طبي تاريخ مواعيد ملفات مريض مستندات صور كاميرا التقاط رفع تنبيهات مواضيع منتدى جدول زمني كامل توسيع طي تصدير طباعة تحرير مريض شارة طبيب معالج معلومات اتصال إجراءات'
    },
    {
        title: 'Change Log',
        path: '/changelog',
        category: 'Documentation',
        content: 'changelog version history features updates v6.1 v6.0 v5.1 v5.0 v4.0 v3.0 v2.0 v1.0 patient alert system media gallery prescriptions management drug database financial management security patient management admin dashboard'
    },
    {
        title: 'سجل التغييرات',
        path: '/changelog',
        category: 'التوثيق',
        content: 'سجل تغييرات تاريخ إصدارات ميزات تحديثات v6.1 v6.0 v5.1 v5.0 v4.0 v3.0 v2.0 v1.0 نظام تنبيهات المرضى معرض وسائط إدارة وصفات قاعدة بيانات أدوية إدارة مالية أمان إدارة مرضى لوحة تحكم مسؤول'
    },
];

const fuse = new Fuse(searchIndex, {
    keys: ['title', 'content', 'category'],
    threshold: 0.3,
    ignoreLocation: true,
    minMatchCharLength: 2,
});

export const searchDocs = (query: string) => {
    if (!query.trim()) return [];
    return fuse.search(query).map(result => result.item);
};
