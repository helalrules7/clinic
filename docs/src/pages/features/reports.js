export default async function ReportsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">📊 التقارير والإحصائيات</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام التقارير والإحصائيات
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>لوحات التحكم</h2>
                <p>لوحات تحكم تفاعلية تعرض:</p>
                <ul>
                    <li>الإحصائيات اليومية</li>
                    <li>المواعيد القادمة</li>
                    <li>المدفوعات الأخيرة</li>
                    <li>الرسوم البيانية</li>
                    <li>التحديث التلقائي</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>التقارير المالية</h2>
                <p>تقارير مالية شاملة:</p>
                <ul>
                    <li>ملخص الإيرادات</li>
                    <li>المصروفات</li>
                    <li>صافي الربح</li>
                    <li>التقارير اليومية</li>
                    <li>التقارير الشهرية</li>
                    <li>التقارير السنوية</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /api/financial-transactions<br>
GET /api/financial-transactions/export<br>
GET /api/dashboard-summary
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تقارير المواعيد</h2>
                <p>إحصائيات المواعيد:</p>
                <ul>
                    <li>عدد المواعيد</li>
                    <li>المواعيد المكتملة</li>
                    <li>المواعيد الملغاة</li>
                    <li>المواعيد الفائتة</li>
                    <li>إحصائيات حسب الطبيب</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تصدير البيانات</h2>
                <p>تصدير التقارير بصيغ مختلفة:</p>
                <ul>
                    <li><strong>CSV:</strong> للتحليل في Excel</li>
                    <li><strong>Excel:</strong> ملفات Excel جاهزة</li>
                    <li><strong>PDF:</strong> تقارير PDF (قريباً)</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /api/financial-transactions/export?format=csv<br>
GET /api/financial-transactions/export?format=excel<br>
GET /doctor/reports/export
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
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
                            <td><code>/api/dashboard-summary</code></td>
                            <td>ملخص لوحة التحكم</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/dashboard-charts</code></td>
                            <td>بيانات الرسوم البيانية</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/recent-activity</code></td>
                            <td>النشاط الأخير</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    `;
}
