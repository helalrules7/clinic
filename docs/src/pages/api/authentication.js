export default async function AuthenticationPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">🔐 Authentication</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل المصادقة والتفويض في النظام
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظام المصادقة</h2>
                <p>النظام يستخدم Session-based authentication:</p>
                <ul>
                    <li>تسجيل الدخول ينشئ session</li>
                    <li>Session يتم حفظه في cookies</li>
                    <li>جميع الطلبات تتطلب session صالح</li>
                    <li>تسجيل الخروج ينهي session</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تسجيل الدخول</h2>
                <div class="code-block">
                    <code>
POST /login<br>
Content-Type: application/x-www-form-urlencoded<br><br>
username=dr_ahmed<br>
password=password<br>
csrf_token=...
                    </code>
                </div>
                <p style="margin-top: 1rem;">Response:</p>
                <div class="code-block">
                    <code>
{<br>
  "success": true,<br>
  "redirect": "/doctor/dashboard"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>CSRF Protection</h2>
                <p>جميع طلبات POST/PUT/DELETE تتطلب CSRF token:</p>
                <ul>
                    <li>Token يتم إنشاؤه عند تحميل الصفحة</li>
                    <li>يجب إرسال token مع كل طلب</li>
                    <li>Token يتم التحقق منه في الخادم</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
// الحصول على CSRF token<br>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;<br><br>
// إرسال مع الطلب<br>
fetch('/api/endpoint', {<br>
  method: 'POST',<br>
  headers: {<br>
    'Content-Type': 'application/json',<br>
    'X-CSRF-Token': csrfToken<br>
  },<br>
  body: JSON.stringify(data)<br>
})
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الأدوار والصلاحيات</h2>
                <p>النظام يدعم ثلاثة أدوار:</p>
                <ul>
                    <li><strong>doctor:</strong> الطبيب - صلاحيات كاملة للمرضى والمواعيد</li>
                    <li><strong>secretary:</strong> السكرتير - إدارة الحجوزات والمدفوعات</li>
                    <li><strong>admin:</strong> المدير - صلاحيات كاملة للنظام</li>
                </ul>
                <p style="margin-top: 1rem;">كل endpoint يتحقق من صلاحيات المستخدم قبل التنفيذ.</p>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>تسجيل الخروج</h2>
                <div class="code-block">
                    <code>
GET /logout
                    </code>
                </div>
                <p style="margin-top: 1rem;">يتم إنهاء session وإعادة التوجيه لصفحة تسجيل الدخول.</p>
            </section>

            <section class="card scroll-reveal">
                <h2>Session Time</h2>
                <p>التحقق من وقت الجلسة:</p>
                <div class="code-block">
                    <code>
GET /api/auth/session-time<br><br>
Response:<br>
{<br>
  "ok": true,<br>
  "data": {<br>
    "remaining": 3600<br>
  }<br>
}
                    </code>
                </div>
            </section>
        </div>
    `;
}
