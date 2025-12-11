export default async function ForumPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">💬 المنتدى الطبي</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                دليل شامل للمنتدى الطبي للتواصل والمناقشة
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>نظرة عامة</h2>
                <p>منتدى طبي للتواصل والمناقشة بين الأطباء:</p>
                <ul>
                    <li>إنشاء مواضيع للمناقشة</li>
                    <li>المشاركات والردود</li>
                    <li>نظام إعجاب</li>
                    <li>الصور والمرفقات</li>
                    <li>Tags والتصنيفات</li>
                </ul>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>المواضيع (Topics)</h2>
                <p>إنشاء موضوع جديد:</p>
                <div class="code-block">
                    <code>
POST /api/forum/topics<br>
{<br>
  "title": "عنوان الموضوع",<br>
  "content": "محتوى الموضوع",<br>
  "category": "عام",<br>
  "patient_id": 1,<br>
  "appointment_id": 1<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>المشاركات (Posts)</h2>
                <p>إضافة مشاركة أو رد:</p>
                <div class="code-block">
                    <code>
POST /api/forum/posts<br>
{<br>
  "topic_id": 1,<br>
  "content": "محتوى المشاركة"<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الإعجابات</h2>
                <p>نظام إعجاب للمواضيع والمشاركات:</p>
                <div class="code-block">
                    <code>
POST /api/forum/posts/{id}/like<br>
POST /api/forum/posts/{id}/dislike<br>
DELETE /api/forum/posts/{id}/like<br><br>
POST /api/forum/topics/{id}/like<br>
POST /api/forum/topics/{id}/dislike
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>الصور والمرفقات</h2>
                <p>إرفاق الصور والملفات:</p>
                <div class="code-block">
                    <code>
POST /api/forum/posts/{id}/images<br>
POST /api/forum/attachments/upload<br>
DELETE /api/forum/images/{id}<br>
DELETE /api/forum/attachments/{id}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>Tags والتصنيفات</h2>
                <p>إضافة tags للمواضيع:</p>
                <div class="code-block">
                    <code>
POST /api/forum/topics/{id}/tags<br>
DELETE /api/forum/topics/{id}/tags/{tagId}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>الميزات المتقدمة</h2>
                <ul>
                    <li><strong>الحلول المميزة:</strong> تمييز المواضيع المحلولة</li>
                    <li><strong>التثبيت:</strong> تثبيت مواضيع مهمة</li>
                    <li><strong>الإحصائيات:</strong> إحصائيات المنتدى</li>
                </ul>
                <div class="code-block" style="margin-top: 1rem;">
                    <code>
POST /api/forum/topics/{id}/toggle-resolved<br>
POST /api/forum/topics/{id}/toggle-pin<br>
GET /api/forum/stats/categories
                    </code>
                </div>
            </section>
        </div>
    `;
}
