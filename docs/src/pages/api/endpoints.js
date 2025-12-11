export default async function APIEndpointsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">📡 API Endpoints</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                قائمة شاملة بجميع API endpoints
            </p>

            <!-- Patients -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>👥 Patients Endpoints</h2>
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
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/patients/search</code></td>
                            <td>البحث عن المرضى</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/patients/{id}/timeline</code></td>
                            <td>Timeline المريض</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Appointments -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📅 Appointments Endpoints</h2>
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
                            <td>بيانات التقويم</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/appointments/{id}</code></td>
                            <td>الحصول على موعد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/appointments</code></td>
                            <td>إنشاء موعد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-warning">PUT</span></td>
                            <td><code>/api/appointments/{id}</code></td>
                            <td>تحديث موعد</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/appointments/{id}/reschedule</code></td>
                            <td>إعادة جدولة</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Prescriptions -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💊 Prescriptions Endpoints</h2>
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
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/prescriptions/meds</code></td>
                            <td>إنشاء وصفة دواء</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/prescriptions/glasses</code></td>
                            <td>إنشاء وصفة نظارات</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/lab-tests</code></td>
                            <td>إنشاء فحص مخبري</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/searchDrugs</code></td>
                            <td>البحث في الأدوية</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Payments -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💰 Payments Endpoints</h2>
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
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/payments</code></td>
                            <td>تسجيل دفعة</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/payments/{id}</code></td>
                            <td>الحصول على دفعة</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/financial-transactions</code></td>
                            <td>المعاملات المالية</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Alerts -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🔔 Alerts Endpoints</h2>
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
                            <td><code>/api/alerts</code></td>
                            <td>جميع التنبيهات</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-primary">GET</span></td>
                            <td><code>/api/alerts/today</code></td>
                            <td>التنبيهات اليوم</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/alerts</code></td>
                            <td>إنشاء تنبيه</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Forum -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💬 Forum Endpoints</h2>
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
                            <td><code>/api/forum/topics</code></td>
                            <td>جميع المواضيع</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/forum/topics</code></td>
                            <td>إنشاء موضوع</td>
                        </tr>
                        <tr>
                            <td><span class="badge badge-success">POST</span></td>
                            <td><code>/api/forum/posts</code></td>
                            <td>إنشاء مشاركة</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    `;
}
