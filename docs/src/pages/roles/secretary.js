export default async function SecretaryPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">📋 توثيق دور السكرتير</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لجميع ميزات ووظائف السكرتير في نظام عيادة رؤية
            </p>

            <!-- Dashboard -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📊 لوحة التحكم</h2>
                <p>لوحة تحكم شاملة للسكرتير:</p>
                <ul>
                    <li><strong>الإحصائيات اليومية:</strong> عدد المواعيد، المدفوعات، المرضى</li>
                    <li><strong>المواعيد اليوم:</strong> قائمة بمواعيد اليوم</li>
                    <li><strong>المدفوعات الأخيرة:</strong> آخر المدفوعات المسجلة</li>
                    <li><strong>التحديث التلقائي:</strong> تحديث كل 30 ثانية</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/dashboard<br>
GET /api/secretary/dashboard
                    </code>
                </div>
            </section>

            <!-- Bookings -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>📅 إدارة الحجوزات</h2>
                <p>إدارة شاملة للحجوزات والمواعيد:</p>
                <ul>
                    <li><strong>عرض الحجوزات:</strong> قائمة بجميع الحجوزات</li>
                    <li><strong>التقويم:</strong> عرض الحجوزات في تقويم</li>
                    <li><strong>إنشاء حجز:</strong> حجز موعد جديد</li>
                    <li><strong>تعديل الحجز:</strong> تحديث بيانات الحجز</li>
                    <li><strong>حذف الحجز:</strong> إلغاء الحجز</li>
                    <li><strong>تأكيد الحضور:</strong> تأكيد حضور المريض</li>
                    <li><strong>تفاصيل الحجز:</strong> عرض تفاصيل الحجز الكاملة</li>
                    <li><strong>طباعة الحجز:</strong> طباعة تفاصيل الحجز</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/bookings<br>
GET /secretary/bookings/calendar<br>
POST /secretary/bookings<br>
POST /secretary/bookings/{id}/update<br>
DELETE /secretary/bookings/{id}<br>
POST /secretary/bookings/{id}/confirm
                    </code>
                </div>
            </section>

            <!-- Patients -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>👥 إدارة المرضى</h2>
                <p>إدارة ملفات المرضى:</p>
                <ul>
                    <li><strong>قائمة المرضى:</strong> عرض جميع المرضى</li>
                    <li><strong>البحث:</strong> بحث متقدم عن المرضى</li>
                    <li><strong>مريض جديد:</strong> تسجيل مريض جديد</li>
                    <li><strong>عرض الملف:</strong> عرض ملف المريض الكامل</li>
                    <li><strong>طباعة الفاتورة:</strong> طباعة فاتورة المريض</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/patients<br>
GET /api/secretary/patients<br>
GET /secretary/patients/new<br>
POST /secretary/patients<br>
GET /secretary/patients/{id}
                    </code>
                </div>
            </section>

            <!-- Payments -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💰 إدارة المدفوعات</h2>
                <p>تسجيل وإدارة المدفوعات:</p>
                <ul>
                    <li><strong>عرض المدفوعات:</strong> قائمة بجميع المدفوعات</li>
                    <li><strong>تفاصيل الدفعة:</strong> عرض تفاصيل الدفعة</li>
                    <li><strong>طباعة الإيصال:</strong> طباعة إيصال الدفعة</li>
                    <li><strong>البحث:</strong> بحث في المدفوعات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/payments<br>
GET /secretary/payments/{id}<br>
GET /secretary/payments/{id}/receipt
                    </code>
                </div>
            </section>

            <!-- Expenses -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>💸 المصروفات</h2>
                <p>تسجيل وإدارة المصروفات:</p>
                <ul>
                    <li>عرض المصروفات</li>
                    <li>تسجيل مصروف جديد</li>
                    <li>عرض تفاصيل المصروف</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/expenses/{id}
                    </code>
                </div>
            </section>

            <!-- Invoices -->
            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>🧾 الفواتير</h2>
                <p>عرض وطباعة الفواتير:</p>
                <ul>
                    <li>عرض الفاتورة</li>
                    <li>طباعة الفاتورة</li>
                    <li>حساب التوازن</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/invoices/{id}
                    </code>
                </div>
            </section>

            <!-- Profile -->
            <section class="card scroll-reveal">
                <h2>👤 الملف الشخصي</h2>
                <p>إدارة الملف الشخصي:</p>
                <ul>
                    <li>عرض البيانات الشخصية</li>
                    <li>تحديث البيانات</li>
                    <li>تغيير كلمة المرور</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /secretary/profile<br>
POST /secretary/profile/update<br>
POST /secretary/profile/change-password
                    </code>
                </div>
            </section>
        </div>
    `;
}
