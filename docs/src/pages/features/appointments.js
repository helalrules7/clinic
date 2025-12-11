export default async function AppointmentsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">📅 إدارة المواعيد</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام إدارة المواعيد والحجوزات
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>التقويم التفاعلي</h2>
                <p>تقويم متقدم لإدارة المواعيد:</p>
                <ul>
                    <li><strong>عرض شهري:</strong> عرض جميع المواعيد في الشهر</li>
                    <li><strong>فترات زمنية:</strong> فترات مدتها 15 دقيقة</li>
                    <li><strong>ساعات العمل:</strong> 2:00 مساءً - 11:00 مساءً</li>
                    <li><strong>إغلاق الجمعة:</strong> يوم الجمعة مغلق تلقائياً</li>
                    <li><strong>التحديث التلقائي:</strong> تحديث كل 60 ثانية</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إنشاء المواعيد</h2>
                <p>إنشاء موعد جديد يتطلب:</p>
                <ul>
                    <li>اختيار المريض</li>
                    <li>اختيار التاريخ والوقت</li>
                    <li>اختيار الطبيب</li>
                    <li>نوع الزيارة (جديد، متابعة، إجراء)</li>
                    <li>رسوم الحجز</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/appointments<br>
{<br>
  "patient_id": 1,<br>
  "doctor_id": 1,<br>
  "date": "2024-12-20",<br>
  "start_time": "14:00",<br>
  "visit_type": "New"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>حالات المواعيد</h2>
                <p>المواعيد يمكن أن تكون في الحالات التالية:</p>
                <ul>
                    <li><span class="badge badge-primary">Booked</span> - محجوز</li>
                    <li><span class="badge badge-success">CheckedIn</span> - تم الحضور</li>
                    <li><span class="badge badge-warning">InProgress</span> - قيد المعالجة</li>
                    <li><span class="badge badge-success">Completed</span> - مكتمل</li>
                    <li><span class="badge">Cancelled</span> - ملغي</li>
                    <li><span class="badge">NoShow</span> - لم يحضر</li>
                    <li><span class="badge">Rescheduled</span> - أعيد الجدولة</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>API Endpoints</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>الوصف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/calendar</code></td>
                            <td>الحصول على بيانات التقويم</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/appointments/{id}</code></td>
                            <td>الحصول على موعد محدد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/appointments</code></td>
                            <td>إنشاء موعد جديد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">PUT</span></td>
                            <td><code>/api/appointments/{id}</code></td>
                            <td>تحديث موعد</td>
                        </tr>
                        <tr>
                            <td><span class="badge">DELETE</span></td>
                            <td><code>/api/appointments/{id}</code></td>
                            <td>حذف موعد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/appointments/{id}/reschedule</code></td>
                            <td>إعادة جدولة موعد</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    `;
}
