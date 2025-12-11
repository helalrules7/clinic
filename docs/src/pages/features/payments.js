export default async function PaymentsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">💰 إدارة المدفوعات</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام إدارة المدفوعات والمصروفات
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تسجيل المدفوعات</h2>
                <p>تسجيل دفعة جديدة يتطلب:</p>
                <ul>
                    <li>المريض</li>
                    <li>نوع الدفعة (حجز، استشارة، متابعة، إجراء)</li>
                    <li>المبلغ</li>
                    <li>طريقة الدفع (نقدي، كارت، محفظة، تحويل)</li>
                    <li>ملاحظات (اختياري)</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/payments<br>
{<br>
  "patient_id": 1,<br>
  "appointment_id": 1,<br>
  "amount": 200.00,<br>
  "payment_type": "Consultation",<br>
  "payment_method": "Cash"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>أنواع المدفوعات</h2>
                <ul>
                    <li><strong>Booking:</strong> رسوم الحجز</li>
                    <li><strong>Consultation:</strong> رسوم الاستشارة</li>
                    <li><strong>FollowUp:</strong> رسوم المتابعة</li>
                    <li><strong>Procedure:</strong> رسوم الإجراء</li>
                    <li><strong>Other:</strong> مدفوعات أخرى</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>طرق الدفع</h2>
                <ul>
                    <li><strong>Cash:</strong> نقدي</li>
                    <li><strong>Card:</strong> كارت</li>
                    <li><strong>Wallet:</strong> محفظة</li>
                    <li><strong>Transfer:</strong> تحويل</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الخصومات والإعفاءات</h2>
                <p>النظام يدعم:</p>
                <ul>
                    <li>تطبيق خصم على المبلغ</li>
                    <li>إعفاء كامل من الرسوم</li>
                    <li>تسجيل سبب الخصم أو الإعفاء</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>المصروفات</h2>
                <p>تسجيل المصروفات:</p>
                <div class="code-block">
                    <code>
POST /api/expenses<br>
{<br>
  "description": "مصروف",<br>
  "amount": 100.00,<br>
  "category": "مكتبي",<br>
  "date": "2024-12-20"<br>
}
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
                            <td>جميع المعاملات المالية</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    `;
}
