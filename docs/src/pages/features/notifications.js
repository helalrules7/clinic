export default async function NotificationsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">🔔 الإشعارات (Notifications)</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام الإشعارات الفورية
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظرة عامة</h2>
                <p>نظام إشعارات فوري (Push Notifications):</p>
                <ul>
                    <li>إشعارات فورية في المتصفح</li>
                    <li>إشعارات النظام</li>
                    <li>إشعارات المواعيد</li>
                    <li>إشعارات المدفوعات</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الحصول على الإشعارات</h2>
                <div class="code-block">
                    <code>
GET /api/notifications<br>
GET /api/notifications/unread-count
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إدارة الإشعارات</h2>
                <ul>
                    <li>تحديد الإشعار كمقروء</li>
                    <li>تحديد الكل كمقروء</li>
                    <li>حذف الإشعار</li>
                    <li>حذف جميع الإشعارات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
PUT /api/notifications/{id}/read<br>
PUT /api/notifications/read-all<br>
DELETE /api/notifications/{id}<br>
DELETE /api/notifications/clear-all
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>إنشاء إشعار نظامي</h2>
                <p>للمدير فقط - إنشاء إشعار نظامي:</p>
                <div class="code-block">
                    <code>
POST /api/notifications/system<br>
{<br>
  "title": "عنوان الإشعار",<br>
  "message": "رسالة الإشعار",<br>
  "type": "info"<br>
}
                    </code>
                </div>
            </section>
        </div>
    `;
}
