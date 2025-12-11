export default async function APIOverviewPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">🔌 API Documentation</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لـ API الخاص بنظام عيادة رؤية
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظرة عامة</h2>
                <p>
                    جميع API endpoints تعيد استجابة بصيغة JSON مع الهيكل التالي:
                </p>
                <div class="code-block">
                    <code>
{<br>
  "ok": true,<br>
  "data": {...},<br>
  "error": null<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>Base URL</h2>
                <p>جميع الـ endpoints تبدأ بـ:</p>
                <div class="code-block">
                    <code>https://your-domain.com/api/</code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>Authentication</h2>
                <p>معظم الـ endpoints تتطلب مصادقة:</p>
                <ul>
                    <li>استخدام Session-based authentication</li>
                    <li>CSRF token مطلوب لـ POST/PUT/DELETE requests</li>
                    <li>التحقق من الصلاحيات حسب الدور</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>HTTP Methods</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>الاستخدام</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td>استرجاع البيانات</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td>إنشاء موارد جديدة</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">PUT</span></td>
                            <td>تحديث موارد موجودة</td>
                        </tr>
                        <tr>
                            <td><span class="badge">DELETE</span></td>
                            <td>حذف موارد</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>Error Handling</h2>
                <p>في حالة حدوث خطأ:</p>
                <div class="code-block">
                    <code>
{<br>
  "ok": false,<br>
  "data": null,<br>
  "error": "Error message"<br>
}
                    </code>
                </div>
                <p style="margin-top: 1rem;">HTTP Status Codes:</p>
                <ul>
                    <li><code>200</code> - نجاح</li>
                    <li><code>400</code> - طلب خاطئ</li>
                    <li><code>401</code> - غير مصرح</li>
                    <li><code>403</code> - محظور</li>
                    <li><code>404</code> - غير موجود</li>
                    <li><code>500</code> - خطأ في الخادم</li>
                </ul>
            </section>

            <section class="card scroll-reveal">
                <h2>Categories</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <div class="card hover-lift">
                        <h3>👥 Patients</h3>
                        <p>Endpoints لإدارة المرضى</p>
                        <a href="#/api/endpoints?category=patients">عرض →</a>
                    </div>
                    <div class="card hover-lift">
                        <h3>📅 Appointments</h3>
                        <p>Endpoints لإدارة المواعيد</p>
                        <a href="#/api/endpoints?category=appointments">عرض →</a>
                    </div>
                    <div class="card hover-lift">
                        <h3>💊 Prescriptions</h3>
                        <p>Endpoints للوصفات الطبية</p>
                        <a href="#/api/endpoints?category=prescriptions">عرض →</a>
                    </div>
                    <div class="card hover-lift">
                        <h3>💰 Payments</h3>
                        <p>Endpoints للمدفوعات</p>
                        <a href="#/api/endpoints?category=payments">عرض →</a>
                    </div>
                </div>
            </section>
        </div>
    `;
}
