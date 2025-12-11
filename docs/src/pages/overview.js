import { i18n } from '../i18n.js';

export default async function OverviewPage() {
    const hash = window.location.hash;
    const section = hash.split('/').pop() || 'introduction';
    
    const content = {
        introduction: getIntroductionContent(),
        features: getFeaturesContent(),
        requirements: getRequirementsContent(),
        'quick-start': getQuickStartContent()
    };

    return content[section] || content.introduction;
}

function getIntroductionContent() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text typing-effect">نظام عيادة رؤية</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                نظام شامل ومتقدم لإدارة عيادة طب العيون يدعم ثلاثة أدوار رئيسية مع واجهة مستخدم حديثة وتجربة مستخدم محسنة
            </p>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">نظرة عامة</h2>
                </div>
                <p>
                    نظام <strong>Roaya Clinic Management System</strong> هو نظام متكامل مصمم خصيصاً لإدارة عيادات طب العيون.
                    يوفر النظام حلولاً شاملة لإدارة المرضى، المواعيد، الوصفات الطبية، المدفوعات، والتقارير.
                </p>
                <p>
                    تم تطوير النظام باستخدام أحدث التقنيات مع التركيز على الأداء، الأمان، وسهولة الاستخدام.
                </p>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">الأدوار الرئيسية</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                    <div class="card hover-lift">
                        <h3 style="color: var(--primary); margin-bottom: 1rem;">👨‍⚕️ الطبيب</h3>
                        <p>إدارة المرضى، المواعيد، الوصفات، والفحوصات مع واجهة متخصصة للطبيب</p>
                    </div>
                    <div class="card hover-lift">
                        <h3 style="color: var(--secondary); margin-bottom: 1rem;">📋 السكرتير</h3>
                        <p>إدارة الحجوزات، المدفوعات، وتسجيل المرضى الجدد</p>
                    </div>
                    <div class="card hover-lift">
                        <h3 style="color: var(--accent); margin-bottom: 1rem;">⚙️ المدير</h3>
                        <p>إدارة النظام، المستخدمين، والتقارير الشاملة</p>
                    </div>
                </div>
            </div>

            <div class="card scroll-reveal">
                <div class="card-header">
                    <h2 class="card-title">المميزات الرئيسية</h2>
                </div>
                <ul>
                    <li>✅ واجهة مستخدم حديثة وسهلة الاستخدام</li>
                    <li>✅ دعم كامل للغة العربية مع RTL</li>
                    <li>✅ تقويم تفاعلي مع تحديث تلقائي</li>
                    <li>✅ نظام وصفات طبية متقدم</li>
                    <li>✅ إدارة مالية شاملة</li>
                    <li>✅ تقارير وإحصائيات مفصلة</li>
                    <li>✅ نظام تنبيهات ذكي</li>
                    <li>✅ منتدى طبي للتواصل</li>
                    <li>✅ إدارة الوسائط والملفات</li>
                    <li>✅ إشعارات فورية (Push Notifications)</li>
                </ul>
            </div>
        </div>
    `;
}

function getFeaturesContent() {
    return `
        <div class="page-content animate-fade-in">
            <h1>الميزات الرئيسية</h1>
            
            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🏥 إدارة المرضى</h2>
                <ul>
                    <li>تسجيل المرضى الجدد مع التاريخ الطبي الكامل</li>
                    <li>ملف طبي شامل مع الأحداث الزمنية (Timeline)</li>
                    <li>إرفاق الملفات والصور</li>
                    <li>البحث المتقدم والتصفية</li>
                    <li>تصدير بيانات المريض</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📅 إدارة المواعيد</h2>
                <ul>
                    <li>تقويم تفاعلي مع تحديث تلقائي كل 60 ثانية</li>
                    <li>فترات زمنية مدتها 15 دقيقة</li>
                    <li>ساعات العمل: 2:00 مساءً - 11:00 مساءً</li>
                    <li>إغلاق يوم الجمعة</li>
                    <li>جدول عمل منفصل لكل طبيب</li>
                    <li>إعادة الجدولة والمتابعة</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💊 الوصفات الطبية</h2>
                <ul>
                    <li>وصفات الأدوية مع التفاصيل الكاملة</li>
                    <li>وصفات النظارات مع القياسات الدقيقة</li>
                    <li>طلبات الفحوصات المخبرية</li>
                    <li>طباعة احترافية بتصميم RTL</li>
                    <li>بحث متقدم في قاعدة بيانات الأدوية</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💰 إدارة المالية</h2>
                <ul>
                    <li>تسجيل المدفوعات بأنواع مختلفة</li>
                    <li>نظام الخصومات والإعفاءات</li>
                    <li>فواتير مفصلة مع التوازن</li>
                    <li>تقارير الإيرادات والمصروفات</li>
                    <li>إغلاق اليوم وإقفال الحسابات</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📊 التقارير والإحصائيات</h2>
                <ul>
                    <li>لوحات تحكم تفاعلية</li>
                    <li>تقارير مالية شاملة</li>
                    <li>إحصائيات المواعيد والمرضى</li>
                    <li>تصدير البيانات بصيغة CSV و Excel</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🔒 الأمان والصلاحيات</h2>
                <ul>
                    <li>نظام مصادقة آمن</li>
                    <li>إدارة الأدوار والصلاحيات (RBAC)</li>
                    <li>حماية CSRF</li>
                    <li>تسجيل الأحداث والتدقيق</li>
                </ul>
            </div>
        </div>
    `;
}

function getRequirementsContent() {
    return `
        <div class="page-content animate-fade-in">
            <h1>المتطلبات التقنية</h1>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الخادم</h2>
                <ul>
                    <li><strong>PHP:</strong> 8.2 أو أحدث</li>
                    <li><strong>MySQL:</strong> 8.0 أو أحدث</li>
                    <li><strong>Web Server:</strong> Apache/Nginx مع mod_rewrite</li>
                    <li><strong>Composer:</strong> لإدارة التبعيات</li>
                </ul>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>ملحقات PHP المطلوبة</h2>
                <ul>
                    <li><code>ext-pdo</code> - للاتصال بقاعدة البيانات</li>
                    <li><code>ext-json</code> - لمعالجة JSON</li>
                    <li><code>ext-mbstring</code> - لدعم Unicode</li>
                    <li><code>ext-gd</code> - لمعالجة الصور</li>
                    <li><code>ext-zip</code> - لضغط الملفات</li>
                </ul>
            </div>

            <div class="card scroll-reveal">
                <h2>المكتبات الخارجية</h2>
                <ul>
                    <li><code>phpoffice/phpword</code> - لإنشاء مستندات Word</li>
                    <li><code>phpoffice/phpspreadsheet</code> - لمعالجة Excel</li>
                    <li><code>openspout/openspout</code> - لتصدير البيانات</li>
                    <li><code>minishlink/web-push</code> - للإشعارات الفورية</li>
                </ul>
            </div>
        </div>
    `;
}

function getQuickStartContent() {
    return `
        <div class="page-content animate-fade-in">
            <h1>البدء السريع</h1>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>1. تثبيت التبعيات</h2>
                <div class="code-block">
                    <code>composer install</code>
                </div>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>2. إعداد قاعدة البيانات</h2>
                <div class="code-block">
                    <code>
# إنشاء قاعدة البيانات<br>
mysql -u root -p -e "CREATE DATABASE roaya CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"<br><br>
# استيراد المخطط<br>
mysql -u root -p roaya < sql/schema.sql
                    </code>
                </div>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>3. تكوين البيئة</h2>
                <p>إنشاء ملف <code>.env</code> من <code>.env.example</code> وتعديل الإعدادات:</p>
                <div class="code-block">
                    <code>
DB_HOST=localhost<br>
DB_NAME=roaya<br>
DB_USER=root<br>
DB_PASS=your_password<br>
APP_ENV=local
                    </code>
                </div>
            </div>

            <div class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>4. تشغيل النظام</h2>
                <div class="code-block">
                    <code>php -S localhost:8000 router.php</code>
                </div>
                <p style="margin-top: 1rem;">
                    الوصول للنظام: <a href="http://localhost:8000/public/login" target="_blank">http://localhost:8000/public/login</a>
                </p>
            </div>

            <div class="card scroll-reveal">
                <h2>5. بيانات تسجيل الدخول الافتراضية</h2>
                <table>
                    <thead>
                        <tr>
                            <th>الدور</th>
                            <th>اسم المستخدم</th>
                            <th>كلمة المرور</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>الطبيب</td>
                            <td>dr_ahmed</td>
                            <td>password</td>
                        </tr>
                        <tr>
                            <td>السكرتير</td>
                            <td>sec</td>
                            <td>password</td>
                        </tr>
                        <tr>
                            <td>المدير</td>
                            <td>admin</td>
                            <td>password</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
}
