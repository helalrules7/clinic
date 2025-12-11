export default async function AdminPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">⚙️ توثيق دور المدير</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لجميع ميزات ووظائف المدير في نظام عيادة رؤية
            </p>

            <!-- Dashboard -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📊 لوحة تحكم النظام</h2>
                <p>لوحة تحكم شاملة لإدارة النظام:</p>
                <ul>
                    <li><strong>إحصائيات النظام:</strong> عدد المستخدمين، المرضى، المواعيد</li>
                    <li><strong>الأحداث الأخيرة:</strong> سجل الأحداث الحديثة</li>
                    <li><strong>صحة النظام:</strong> حالة النظام والصحة العامة</li>
                    <li><strong>View As Mode:</strong> عرض النظام كدور آخر</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /admin/dashboard</code>
                </div>
            </section>

            <!-- Users Management -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>👥 إدارة المستخدمين</h2>
                <p>إدارة شاملة لمستخدمي النظام:</p>
                <ul>
                    <li><strong>قائمة المستخدمين:</strong> عرض جميع المستخدمين</li>
                    <li><strong>إنشاء مستخدم:</strong> إضافة مستخدم جديد</li>
                    <li><strong>تعديل المستخدم:</strong> تحديث بيانات المستخدم</li>
                    <li><strong>حذف المستخدم:</strong> حذف مستخدم</li>
                    <li><strong>الأدوار:</strong> إدارة الأدوار والصلاحيات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/users<br>
POST /admin/users<br>
PUT /admin/users/{id}<br>
DELETE /admin/users/{id}
                    </code>
                </div>
            </section>

            <!-- Settings -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>⚙️ إعدادات النظام</h2>
                <p>إعدادات عامة للنظام:</p>
                <ul>
                    <li>إعدادات قاعدة البيانات</li>
                    <li>إعدادات التطبيق</li>
                    <li>إعدادات الأمان</li>
                    <li>إعدادات الإشعارات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/settings<br>
POST /admin/settings
                    </code>
                </div>
            </section>

            <!-- Media Management -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🖼️ إدارة الوسائط</h2>
                <p>إدارة جميع الوسائط والملفات:</p>
                <ul>
                    <li><strong>عرض الوسائط:</strong> قائمة بجميع الوسائط</li>
                    <li><strong>حذف الوسائط:</strong> حذف وسائط محددة</li>
                    <li><strong>حذف الكل:</strong> حذف جميع الوسائط</li>
                    <li><strong>النسخ الاحتياطي:</strong> نسخ احتياطي للوسائط</li>
                    <li><strong>الاستعادة:</strong> استعادة النسخ الاحتياطي</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/media<br>
GET /api/admin/media/list<br>
POST /api/admin/media/delete<br>
POST /api/admin/media/backup<br>
POST /api/admin/media/restore
                    </code>
                </div>
            </section>

            <!-- Backup & Restore -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💾 النسخ الاحتياطي والاستعادة</h2>
                <p>نظام شامل للنسخ الاحتياطي:</p>
                <ul>
                    <li><strong>نسخ احتياطي لقاعدة البيانات:</strong> نسخ احتياطي للبيانات</li>
                    <li><strong>نسخ احتياطي كامل:</strong> قاعدة البيانات + الملفات</li>
                    <li><strong>نسخ احتياطي للموقع:</strong> نسخ احتياطي لجميع الملفات</li>
                    <li><strong>قائمة النسخ:</strong> عرض جميع النسخ الاحتياطية</li>
                    <li><strong>الاستعادة:</strong> استعادة من نسخة احتياطية</li>
                    <li><strong>التحميل:</strong> تحميل النسخ الاحتياطية</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/backup<br>
POST /api/admin/backup/database<br>
POST /api/admin/backup/full<br>
POST /api/admin/backup/website<br>
GET /api/admin/backup/list<br>
POST /api/admin/backup/restore<br>
GET /api/admin/backup/download/{type}/{name}
                    </code>
                </div>
            </section>

            <!-- Notifications -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🔔 إشعارات النظام</h2>
                <p>إدارة إشعارات النظام:</p>
                <ul>
                    <li>عرض جميع الإشعارات</li>
                    <li>إنشاء إشعار نظامي</li>
                    <li>إدارة الإشعارات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/notifications<br>
POST /api/notifications/system
                    </code>
                </div>
            </section>

            <!-- View As Mode -->
            <section class="card scroll-reveal">
                <h2>👁️ View As Mode</h2>
                <p>عرض النظام كدور آخر:</p>
                <ul>
                    <li><strong>تفعيل View As:</strong> عرض النظام كطبيب أو سكرتير</li>
                    <li><strong>إيقاف View As:</strong> العودة لدور المدير</li>
                    <li>مفيد للاختبار والدعم الفني</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /admin/view-as<br>
GET /admin/stop-view-as
                    </code>
                </div>
            </section>
        </div>
    `;
}
