export default async function MediaPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">🖼️ إدارة الوسائط</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام إدارة الوسائط والملفات
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظرة عامة</h2>
                <p>نظام شامل لإدارة الصور والملفات:</p>
                <ul>
                    <li>رفع الصور والملفات</li>
                    <li>ربط الوسائط بالمرضى</li>
                    <li>عرض وإدارة الوسائط</li>
                    <li>حذف الوسائط</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>عرض الوسائط</h2>
                <div class="code-block">
                    <code>
GET /doctor/media<br>
GET /api/media<br>
GET /api/media/patient?patient_id=1
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>رفع الملفات</h2>
                <p>رفع ملفات للمواعيد أو المرضى:</p>
                <div class="code-block">
                    <code>
POST /api/attachments/upload<br>
POST /api/patients/files/upload
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>عرض وتحميل الملفات</h2>
                <div class="code-block">
                    <code>
GET /api/attachments/view/{id}<br>
GET /api/attachments/download/{id}<br>
GET /api/patients/files/view/{id}<br>
GET /api/patients/files/download/{id}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>حذف الملفات</h2>
                <div class="code-block">
                    <code>
DELETE /api/attachments/{id}<br>
DELETE /api/patients/files/{id}
                    </code>
                </div>
            </section>
        </div>
    `;
}
