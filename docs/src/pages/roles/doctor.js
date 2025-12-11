export default async function DoctorPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">👨‍⚕️ توثيق دور الطبيب</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لجميع ميزات ووظائف الطبيب في نظام عيادة رؤية
            </p>

            <!-- Dashboard -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📊 لوحة التحكم (Dashboard)</h2>
                <p>لوحة تحكم شاملة تعرض الإحصائيات اليومية والأحداث الأخيرة:</p>
                <ul>
                    <li><strong>الإحصائيات اليومية:</strong> عدد المواعيد، الحالات المكتملة، المدفوعات</li>
                    <li><strong>الأحداث الأخيرة:</strong> Timeline للأحداث الحديثة</li>
                    <li><strong>المواعيد القادمة:</strong> قائمة بالمواعيد القادمة</li>
                    <li><strong>التحديث التلقائي:</strong> تحديث كل 30 ثانية</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /doctor/dashboard</code>
                </div>
            </section>

            <!-- Calendar -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📅 التقويم (Calendar)</h2>
                <p>تقويم تفاعلي لإدارة المواعيد:</p>
                <ul>
                    <li><strong>عرض شهري:</strong> عرض جميع المواعيد في الشهر</li>
                    <li><strong>فترات زمنية:</strong> فترات مدتها 15 دقيقة</li>
                    <li><strong>ساعات العمل:</strong> 2:00 مساءً - 11:00 مساءً</li>
                    <li><strong>إغلاق الجمعة:</strong> يوم الجمعة مغلق تلقائياً</li>
                    <li><strong>التحديث التلقائي:</strong> تحديث كل 60 ثانية</li>
                    <li><strong>إنشاء مواعيد:</strong> النقر على الفترة الزمنية لإنشاء موعد</li>
                    <li><strong>تعديل المواعيد:</strong> النقر على الموعد للتعديل</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /doctor/calendar</code>
                </div>
            </section>

            <!-- Organizer -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📋 المنظم (Organizer)</h2>
                <p>عرض منظم للمواعيد حسب الشهر:</p>
                <ul>
                    <li>عرض جميع المواعيد في الشهر</li>
                    <li>تصفية حسب الحالة</li>
                    <li>بحث سريع</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /doctor/organizer</code>
                </div>
            </section>

            <!-- Patients -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>👥 إدارة المرضى</h2>
                <p>إدارة شاملة لملفات المرضى:</p>
                <ul>
                    <li><strong>قائمة المرضى:</strong> عرض جميع المرضى مع البحث والتصفية</li>
                    <li><strong>ملف المريض:</strong> عرض شامل لملف المريض</li>
                    <li><strong>التاريخ الطبي:</strong> عرض التاريخ الطبي الكامل</li>
                    <li><strong>Timeline:</strong> عرض الأحداث الزمنية</li>
                    <li><strong>الملفات المرفقة:</strong> إدارة الملفات والصور</li>
                    <li><strong>تعديل البيانات:</strong> تحديث معلومات المريض</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/patients<br>
GET /doctor/patients/{id}<br>
GET /doctor/patients/{id}/edit
                    </code>
                </div>
            </section>

            <!-- Appointments -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📝 المواعيد والاستشارات</h2>
                <p>إدارة المواعيد والاستشارات:</p>
                <ul>
                    <li><strong>عرض الموعد:</strong> تفاصيل الموعد الكاملة</li>
                    <li><strong>إنشاء استشارة:</strong> إنشاء استشارة جديدة</li>
                    <li><strong>تعديل الاستشارة:</strong> تحديث بيانات الاستشارة</li>
                    <li><strong>حفظ الاستشارة:</strong> حفظ الاستشارة مع التاريخ الطبي</li>
                    <li><strong>إعادة الجدولة:</strong> تغيير موعد الموعد</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/appointments/{id}<br>
GET /doctor/appointments/{id}/edit<br>
GET /doctor/appointments/{id}/edit/new<br>
POST /doctor/appointments/{id}/consultation
                    </code>
                </div>
            </section>

            <!-- Prescriptions -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💊 الوصفات الطبية</h2>
                
                <h3>وصفات الأدوية</h3>
                <ul>
                    <li>إنشاء وصفة دواء جديدة</li>
                    <li>البحث في قاعدة بيانات الأدوية</li>
                    <li>تعديل أو حذف الوصفة</li>
                    <li>طباعة الوصفة</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/drugs<br>
POST /api/prescriptions/meds<br>
PUT /api/prescriptions/meds/{id}
                    </code>
                </div>

                <h3 style="margin-top: 2rem;">وصفات النظارات</h3>
                <ul>
                    <li>إنشاء وصفة نظارات</li>
                    <li>قياسات العينين (OD, OS)</li>
                    <li>نوع العدسة والمسافة بين الحدقتين</li>
                    <li>طباعة الوصفة</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/glasses<br>
POST /api/prescriptions/glasses<br>
PUT /api/prescriptions/glasses/{id}
                    </code>
                </div>
            </section>

            <!-- Alerts -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🔔 التنبيهات (Alerts)</h2>
                <p>نظام تنبيهات ذكي للمرضى:</p>
                <ul>
                    <li><strong>إنشاء تنبيه:</strong> تنبيهات مخصصة للمرضى</li>
                    <li><strong>التنبيهات اليومية:</strong> عرض التنبيهات النشطة</li>
                    <li><strong>إدارة التنبيهات:</strong> تعديل، حذف، أو إيقاف التنبيهات</li>
                    <li><strong>تنبيهات تلقائية:</strong> تنبيهات للمواعيد القادمة</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/alerts<br>
GET /api/alerts/today<br>
POST /api/alerts<br>
PUT /api/alerts/{id}
                    </code>
                </div>
            </section>

            <!-- Forum -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💬 المنتدى الطبي</h2>
                <p>منتدى للتواصل والمناقشة:</p>
                <ul>
                    <li><strong>إنشاء مواضيع:</strong> مواضيع جديدة للمناقشة</li>
                    <li><strong>المشاركات:</strong> الردود والتعليقات</li>
                    <li><strong>الإعجابات:</strong> نظام إعجاب للمشاركات</li>
                    <li><strong>الصور والمرفقات:</strong> إرفاق الصور والملفات</li>
                    <li><strong>Tags:</strong> تصنيف المواضيع</li>
                    <li><strong>الحلول المميزة:</strong> تمييز المواضيع المحلولة</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/forum<br>
GET /doctor/forum/topic/{id}<br>
POST /api/forum/topics<br>
POST /api/forum/posts
                    </code>
                </div>
            </section>

            <!-- Notes -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📝 الملاحظات (Notes)</h2>
                <p>نظام ملاحظات شخصية:</p>
                <ul>
                    <li>إنشاء ملاحظات</li>
                    <li>تعديل وحذف الملاحظات</li>
                    <li>البحث في الملاحظات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/notes<br>
GET /api/notes<br>
POST /api/notes
                    </code>
                </div>
            </section>

            <!-- Media -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🖼️ الوسائط (Media)</h2>
                <p>إدارة الصور والملفات:</p>
                <ul>
                    <li>عرض جميع الوسائط</li>
                    <li>رفع صور جديدة</li>
                    <li>ربط الصور بالمرضى</li>
                    <li>حذف الوسائط</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/media<br>
GET /api/media<br>
GET /api/media/patient
                    </code>
                </div>
            </section>

            <!-- Payments -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💰 المدفوعات</h2>
                <p>عرض المدفوعات المالية:</p>
                <ul>
                    <li>عرض جميع المدفوعات</li>
                    <li>البحث والتصفية</li>
                    <li>تفاصيل الدفعة</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /doctor/payments</code>
                </div>
            </section>

            <!-- Daily Closure -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🔒 إغلاق اليوم</h2>
                <p>إغلاق اليوم وإقفال الحسابات:</p>
                <ul>
                    <li>عرض ملخص اليوم</li>
                    <li>إغلاق اليوم</li>
                    <li>إقفال الحسابات</li>
                    <li>منع التعديل بعد الإغلاق</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/daily-closure<br>
POST /api/daily-closure<br>
POST /api/daily-closure/lock
                    </code>
                </div>
            </section>

            <!-- Reports -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📊 التقارير</h2>
                <p>تقارير وإحصائيات شاملة:</p>
                <ul>
                    <li>تقارير المواعيد</li>
                    <li>تقارير المدفوعات</li>
                    <li>تقارير المرضى</li>
                    <li>تصدير التقارير</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/reports<br>
GET /doctor/reports/export
                    </code>
                </div>
            </section>

            <!-- Settings -->
            <section class="card scroll-reveal">
                <h2>⚙️ الإعدادات</h2>
                <p>إعدادات الطبيب الشخصية:</p>
                <ul>
                    <li><strong>الملف الشخصي:</strong> تحديث البيانات الشخصية</li>
                    <li><strong>كلمة المرور:</strong> تغيير كلمة المرور</li>
                    <li><strong>إعدادات الطبيب:</strong> إعدادات خاصة بالطبيب</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /doctor/profile<br>
GET /doctor/settings<br>
POST /doctor/profile/update<br>
POST /doctor/profile/change-password
                    </code>
                </div>
            </section>
        </div>
    `;
}
