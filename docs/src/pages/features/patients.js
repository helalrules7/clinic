export default async function PatientsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">👥 إدارة المرضى</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام إدارة المرضى في عيادة رؤية
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تسجيل المرضى الجدد</h2>
                <p>تسجيل مريض جديد يتطلب المعلومات التالية:</p>
                <ul>
                    <li>الاسم الكامل</li>
                    <li>رقم الهاتف</li>
                    <li>البريد الإلكتروني (اختياري)</li>
                    <li>تاريخ الميلاد</li>
                    <li>الجنس</li>
                    <li>العنوان</li>
                    <li>معلومات الاتصال في حالات الطوارئ</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>ملف المريض الشامل</h2>
                <p>كل مريض لديه ملف طبي شامل يتضمن:</p>
                <ul>
                    <li><strong>البيانات الأساسية:</strong> المعلومات الشخصية</li>
                    <li><strong>التاريخ الطبي:</strong> السجل الطبي الكامل</li>
                    <li><strong>المواعيد:</strong> جميع المواعيد السابقة والحالية</li>
                    <li><strong>الوصفات:</strong> وصفات الأدوية والنظارات</li>
                    <li><strong>الفحوصات:</strong> نتائج الفحوصات المخبرية</li>
                    <li><strong>الملفات:</strong> الصور والملفات المرفقة</li>
                    <li><strong>Timeline:</strong> الأحداث الزمنية</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>البحث المتقدم</h2>
                <p>نظام بحث متقدم للعثور على المرضى:</p>
                <ul>
                    <li>البحث بالاسم</li>
                    <li>البحث برقم الهاتف</li>
                    <li>البحث بالبريد الإلكتروني</li>
                    <li>البحث بالتاريخ</li>
                    <li>التصفية حسب المعايير</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /api/patients/search?q={query}<br>
GET /api/patients
                    </code>
                </div>
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
                            <td><code>/api/patients</code></td>
                            <td>الحصول على جميع المرضى</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/patients/{id}</code></td>
                            <td>الحصول على مريض محدد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/patients</code></td>
                            <td>إنشاء مريض جديد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">PUT</span></td>
                            <td><code>/api/patients/{id}</code></td>
                            <td>تحديث بيانات المريض</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/patients/{id}/timeline</code></td>
                            <td>الحصول على Timeline المريض</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/patients/{id}/export</code></td>
                            <td>تصدير بيانات المريض</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    `;
}
