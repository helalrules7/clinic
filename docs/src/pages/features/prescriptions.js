export default async function PrescriptionsPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">💊 الوصفات الطبية</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل لنظام الوصفات الطبية (أدوية ونظارات)
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>وصفات الأدوية</h2>
                <p>إنشاء وصفة دواء تتضمن:</p>
                <ul>
                    <li>اسم الدواء</li>
                    <li>الجرعة</li>
                    <li>التكرار (مرات في اليوم)</li>
                    <li>المدة (عدد الأيام)</li>
                    <li>التعليمات الخاصة</li>
                    <li>البحث في قاعدة بيانات الأدوية</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/prescriptions/meds<br>
{<br>
  "appointment_id": 1,<br>
  "drug_name": "دواء",<br>
  "dosage": "500mg",<br>
  "frequency": "مرتين يومياً",<br>
  "duration": "7 أيام"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>وصفات النظارات</h2>
                <p>إنشاء وصفة نظارات تتضمن:</p>
                <ul>
                    <li><strong>OD (العين اليمنى):</strong> SPH, CYL, AXIS</li>
                    <li><strong>OS (العين اليسرى):</strong> SPH, CYL, AXIS</li>
                    <li><strong>المسافة بين الحدقتين (PD):</strong> للعين اليمنى واليسرى</li>
                    <li><strong>نوع العدسة:</strong> Single Vision, Bifocal, Progressive, Reading</li>
                    <li><strong>ملاحظات إضافية</strong></li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/prescriptions/glasses<br>
{<br>
  "appointment_id": 1,<br>
  "od_sph": "-2.00",<br>
  "od_cyl": "-0.50",<br>
  "od_axis": "180",<br>
  "os_sph": "-2.00",<br>
  "lens_type": "Single Vision"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الفحوصات المخبرية</h2>
                <p>إنشاء طلب فحوصات مخبرية:</p>
                <ul>
                    <li>نوع الفحص</li>
                    <li>التشخيص</li>
                    <li>خطة العلاج</li>
                    <li>ملاحظات</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/lab-tests<br>
{<br>
  "appointment_id": 1,<br>
  "test_name": "فحص العين",<br>
  "diagnosis": "التشخيص",<br>
  "treatment_plan": "خطة العلاج"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>البحث في الأدوية</h2>
                <p>نظام بحث متقدم في قاعدة بيانات الأدوية:</p>
                <ul>
                    <li>البحث بالاسم</li>
                    <li>البحث التلقائي (Autocomplete)</li>
                    <li>الأدوية الأكثر استخداماً</li>
                    <li>التصفية حسب النوع</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
GET /api/searchDrugs?q={query}<br>
GET /api/searchDrugsAutocomplete?q={query}<br>
GET /api/getMostUsedDrugs
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>الطباعة</h2>
                <p>طباعة الوصفات بتصميم احترافي:</p>
                <ul>
                    <li><code>/print/prescription/{id}</code> - طباعة وصفة دواء</li>
                    <li><code>/print/glasses/{id}</code> - طباعة وصفة نظارات</li>
                    <li><code>/print/lab-tests/{id}</code> - طباعة طلب فحوصات</li>
                </ul>
            </section>
        </div>
    `;
}
