export default async function APIExamplesPage() {
    return `
        <div class="page-content animate-fade-in">
            <h1 class="gradient-text">📝 API Examples</h1>
            <p class="lead text-secondary" style="font-size: 1.25rem; margin-bottom: 2rem;">
                أمثلة عملية لاستخدام API
            </p>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إنشاء موعد جديد</h2>
                <div class="code-block">
                    <code>
// JavaScript<br>
const response = await fetch('/api/appointments', {<br>
  method: 'POST',<br>
  headers: {<br>
    'Content-Type': 'application/json',<br>
    'X-CSRF-Token': csrfToken<br>
  },<br>
  body: JSON.stringify({<br>
    patient_id: 1,<br>
    doctor_id: 1,<br>
    date: '2024-12-20',<br>
    start_time: '14:00',<br>
    visit_type: 'New'<br>
  })<br>
});<br><br>
const data = await response.json();
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>البحث عن المرضى</h2>
                <div class="code-block">
                    <code>
// JavaScript<br>
const response = await fetch('/api/patients/search?q=أحمد');<br>
const data = await response.json();<br><br>
if (data.ok) {<br>
  console.log(data.data);<br>
}
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>إنشاء وصفة دواء</h2>
                <div class="code-block">
                    <code>
// JavaScript<br>
const response = await fetch('/api/prescriptions/meds', {<br>
  method: 'POST',<br>
  headers: {<br>
    'Content-Type': 'application/json',<br>
    'X-CSRF-Token': csrfToken<br>
  },<br>
  body: JSON.stringify({<br>
    appointment_id: 1,<br>
    drug_name: 'دواء',<br>
    dosage: '500mg',<br>
    frequency: 'مرتين يومياً',<br>
    duration: '7 أيام',<br>
    instructions: 'بعد الأكل'<br>
  })<br>
});
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal" style="margin-bottom: 2rem;">
                <h2>cURL Examples</h2>
                <h3 style="margin-top: 1rem;">الحصول على موعد</h3>
                <div class="code-block">
                    <code>
curl -X GET "https://your-domain.com/api/appointments/1" \<br>
  -H "Cookie: PHPSESSID=your_session_id"
                    </code>
                </div>

                <h3 style="margin-top: 1rem;">إنشاء مريض</h3>
                <div class="code-block">
                    <code>
curl -X POST "https://your-domain.com/api/patients" \<br>
  -H "Content-Type: application/json" \<br>
  -H "Cookie: PHPSESSID=your_session_id" \<br>
  -H "X-CSRF-Token: your_csrf_token" \<br>
  -d '{<br>
    "name": "أحمد محمد",<br>
    "phone": "01234567890",<br>
    "birth_date": "1990-01-01"<br>
  }'
                    </code>
                </div>
            </section>

            <section class="card scroll-reveal">
                <h2>Error Handling</h2>
                <div class="code-block">
                    <code>
try {<br>
  const response = await fetch('/api/endpoint', {<br>
    method: 'POST',<br>
    headers: {<br>
      'Content-Type': 'application/json',<br>
      'X-CSRF-Token': csrfToken<br>
    },<br>
    body: JSON.stringify(data)<br>
  });<br><br>
  const result = await response.json();<br><br>
  if (!result.ok) {<br>
    console.error('Error:', result.error);<br>
    return;<br>
  }<br><br>
  // Success<br>
  console.log('Success:', result.data);<br>
} catch (error) {<br>
  console.error('Network error:', error);<br>
}
                    </code>
                </div>
            </section>
        </div>
    `;
}
