export default async function AlertsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">🔔 التنبيهات (Alerts)</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام التنبيهات الذكي
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظرة عامة</h2>
                <p>نظام تنبيهات ذكي يسمح بإنشاء تنبيهات مخصصة للمرضى:</p>
                <ul>
                    <li>تنبيهات للمواعيد القادمة</li>
                    <li>تنبيهات للمتابعة</li>
                    <li>تنبيهات مخصصة</li>
                    <li>تنبيهات تلقائية</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إنشاء تنبيه</h2>
                <div class="code-block">
                    <code>
POST /api/alerts<br>
{<br>
  "patient_id": 1,<br>
  "title": "متابعة",<br>
  "message": "مراجعة بعد أسبوع",<br>
  "alert_date": "2024-12-27",<br>
  "is_active": true<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>عرض التنبيهات</h2>
                <ul>
                    <li><strong>التنبيهات اليوم:</strong> جميع التنبيهات النشطة اليوم</li>
                    <li><strong>التنبيهات النشطة:</strong> جميع التنبيهات النشطة</li>
                    <li><strong>تنبيهات المريض:</strong> تنبيهات مريض محدد</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /api/alerts/today<br>
GET /api/alerts/active<br>
GET /api/alerts/patient/{patientId}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إدارة التنبيهات</h2>
                <ul>
                    <li>تعديل التنبيه</li>
                    <li>حذف التنبيه</li>
                    <li>إيقاف/تفعيل التنبيه</li>
                    <li>إيقاف جميع التنبيهات</li>
                    <li>حذف جميع التنبيهات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
PUT /api/alerts/{id}<br>
DELETE /api/alerts/{id}<br>
POST /api/alerts/dismiss<br>
POST /api/alerts/disable-all<br>
DELETE /api/alerts/delete-all
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>واجهة التنبيهات</h2>
                <p>واجهة مستخدم سهلة لإدارة التنبيهات:</p>
                <ul>
                    <li>عرض جميع التنبيهات</li>
                    <li>إنشاء تنبيه جديد</li>
                    <li>تعديل وحذف التنبيهات</li>
                    <li>فلترة حسب التاريخ</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>GET /doctor/alerts</code>
                </div>
            </section>
        </div>
    `;
}
