<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Tests - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <style>
        @media print {
            @page {
                size: A5;
                margin: 1cm;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: white;
            width: 14.8cm;
            height: 21cm;
            margin: 0 auto;
            padding: 1cm;
        }
        
        .lab-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        
        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .clinic-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .lab-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #2c3e50;
            border: 2px solid #e74c3c;
            padding: 8px;
            border-radius: 5px;
            background: #fff5f5;
        }
        
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        
        .patient-details, .appointment-details {
            flex: 1;
        }
        
        .patient-details h3, .appointment-details h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 11px;
            border-bottom: 1px solid #e74c3c;
            padding-bottom: 3px;
        }
        
        .patient-details p, .appointment-details p {
            margin: 3px 0;
            font-size: 9px;
        }
        
        .appointment-details {
            text-align: left;
        }
        
        .lab-content {
            margin: 15px 0;
        }
        
        .test-section {
            border: 1px solid #e74c3c;
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 5px;
            background: white;
        }
        
        .test-title {
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 8px;
            font-size: 11px;
            text-align: center;
            border-bottom: 1px solid #e74c3c;
            padding-bottom: 5px;
        }
        
        .test-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .test-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            border-bottom: 1px solid #eee;
        }
        
        .test-label {
            font-weight: bold;
            color: #666;
            font-size: 9px;
        }
        
        .test-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 9px;
            text-align: center;
            min-width: 60px;
        }
        
        .diagnosis-section {
            margin: 15px 0;
            border: 1px solid #27ae60;
            padding: 10px;
            border-radius: 5px;
            background: #f0fff4;
        }
        
        .diagnosis-title {
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 8px;
            font-size: 11px;
            text-align: center;
        }
        
        .diagnosis-content {
            color: #27ae60;
            font-size: 9px;
            line-height: 1.4;
        }
        
        .plan-section {
            margin: 15px 0;
            border: 1px solid #f39c12;
            padding: 10px;
            border-radius: 5px;
            background: #fffbf0;
        }
        
        .plan-title {
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 8px;
            font-size: 11px;
            text-align: center;
        }
        
        .plan-content {
            color: #f39c12;
            font-size: 9px;
            line-height: 1.4;
        }
        
        .followup-section {
            margin: 15px 0;
            border: 1px solid #9b59b6;
            padding: 10px;
            border-radius: 5px;
            background: #f8f4fd;
        }
        
        .followup-title {
            font-weight: bold;
            color: #9b59b6;
            margin-bottom: 8px;
            font-size: 11px;
            text-align: center;
        }
        
        .followup-content {
            color: #9b59b6;
            font-size: 9px;
            text-align: center;
        }
        
        .footer-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .doctor-signature {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 120px;
            height: 1px;
            background: #333;
            margin: 15px auto 3px;
        }
        
        .doctor-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 10px;
        }
        
        .doctor-title {
            color: #666;
            font-size: 8px;
        }
        
        .clinic-stamp {
            text-align: center;
            flex: 1;
        }
        
        .stamp-box {
            width: 60px;
            height: 60px;
            border: 2px solid #e74c3c;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            font-weight: bold;
            font-size: 7px;
            text-align: center;
            line-height: 1.2;
            background: white;
        }
        
        .date-section {
            text-align: center;
            flex: 1;
        }
        
        .date-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 9px;
        }
        
        .date-value {
            color: #666;
            font-size: 11px;
        }
        
        .prescription-number {
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 8px;
            color: #999;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 32px;
            color: rgba(231, 76, 60, 0.08);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
        }
        
        .urgency-notice {
            margin: 15px 0;
            padding: 8px;
            background: #ffe6e6;
            border: 1px solid #e74c3c;
            border-radius: 5px;
            text-align: center;
        }
        
        .urgency-title {
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 3px;
            font-size: 9px;
        }
        
        .urgency-text {
            color: #e74c3c;
            font-size: 8px;
        }
        
        .test-notes {
            margin: 10px 0;
            padding: 8px;
            background: #f0f8ff;
            border: 1px solid #3498db;
            border-radius: 5px;
        }
        
        .notes-title {
            font-weight: bold;
            color: #3498db;
            margin-bottom: 5px;
            font-size: 9px;
        }
        
        .notes-text {
            color: #3498db;
            font-size: 8px;
            line-height: 1.3;
        }
        
        @media print {
            body {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0.5cm;
            }
            
            .lab-header,
            .patient-info,
            .test-section,
            .diagnosis-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-number">Lab #<?= str_pad($consultation['id'], 6, '0', STR_PAD_LEFT) ?></div>
    <div class="watermark">LAB TESTS</div>
    
    <!-- Header -->
    <div class="lab-header">
        <div class="clinic-name"><?= $clinic['name'] ?></div>
        <div class="clinic-info"><?= $clinic['address'] ?></div>
        <div class="clinic-info">هاتف: <?= $clinic['phone'] ?> | <?= $clinic['email'] ?></div>
        <div class="clinic-info"><?= $clinic['license'] ?> | <?= $clinic['tax_id'] ?></div>
    </div>
    
    <!-- Title -->
    <div class="lab-title">طلب فحوصات مخبرية - Laboratory Test Request</div>
    
    <!-- Patient and Appointment Info -->
    <div class="patient-info">
        <div class="patient-details">
            <h3>بيانات المريض - Patient Information</h3>
            <p><strong>الاسم:</strong> <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
            <p><strong>العمر:</strong> <?= $patient['age_computed'] ?? 'N/A' ?> سنة</p>
            <p><strong>الجنس:</strong> <?= $patient['gender'] ?? 'N/A' ?></p>
            <p><strong>رقم الهاتف:</strong> <?= $patient['phone'] ?></p>
            <?php if ($patient['allergies']): ?>
                <p><strong>الحساسية:</strong> <?= $patient['allergies'] ?></p>
            <?php endif; ?>
        </div>
        
        <div class="appointment-details">
            <h3>تفاصيل الموعد - Appointment Details</h3>
            <p><strong>التاريخ:</strong> <?= date('d/m/Y', strtotime($appointment['appointment_date'])) ?></p>
            <p><strong>الوقت:</strong> <?= date('H:i', strtotime($appointment['start_time'])) ?></p>
            <p><strong>نوع الزيارة:</strong> <?= $appointment['visit_type'] ?></p>
            <p><strong>الطبيب:</strong> <?= $doctor['display_name'] ?></p>
        </div>
    </div>
    
    <!-- Lab Tests Content -->
    <div class="lab-content">
        <!-- Visual Acuity Tests -->
        <div class="test-section">
            <div class="test-title">فحص حدة البصر - Visual Acuity Tests</div>
            <div class="test-grid">
                <div class="test-item">
                    <span class="test-label">العين اليمنى:</span>
                    <span class="test-value"><?= $consultation['visual_acuity_right'] ?? 'N/A' ?></span>
                </div>
                <div class="test-item">
                    <span class="test-label">العين اليسرى:</span>
                    <span class="test-value"><?= $consultation['visual_acuity_left'] ?? 'N/A' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Refraction Tests -->
        <div class="test-section">
            <div class="test-title">فحص الانكسار - Refraction Tests</div>
            <div class="test-grid">
                <div class="test-item">
                    <span class="test-label">العين اليمنى:</span>
                    <span class="test-value"><?= $consultation['refraction_right'] ?? 'N/A' ?></span>
                </div>
                <div class="test-item">
                    <span class="test-label">العين اليسرى:</span>
                    <span class="test-value"><?= $consultation['refraction_left'] ?? 'N/A' ?></span>
                </div>
            </div>
        </div>
        
        <!-- Intraocular Pressure Tests -->
        <div class="test-section">
            <div class="test-title">فحص ضغط العين - Intraocular Pressure (IOP)</div>
            <div class="test-grid">
                <div class="test-item">
                    <span class="test-label">العين اليمنى:</span>
                    <span class="test-value"><?= $consultation['IOP_right'] ?? 'N/A' ?> mmHg</span>
                </div>
                <div class="test-item">
                    <span class="test-label">العين اليسرى:</span>
                    <span class="test-value"><?= $consultation['IOP_left'] ?? 'N/A' ?> mmHg</span>
                </div>
            </div>
        </div>
        
        <!-- Slit Lamp Examination -->
        <?php if ($consultation['slit_lamp']): ?>
        <div class="test-section">
            <div class="test-title">فحص المصباح الشقي - Slit Lamp Examination</div>
            <div class="test-notes">
                <div class="notes-title">النتائج - Findings:</div>
                <div class="notes-text"><?= $consultation['slit_lamp'] ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Fundus Examination -->
        <?php if ($consultation['fundus']): ?>
        <div class="test-section">
            <div class="test-title">فحص قاع العين - Fundus Examination</div>
            <div class="test-notes">
                <div class="notes-title">النتائج - Findings:</div>
                <div class="notes-text"><?= $consultation['fundus'] ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Diagnosis -->
        <div class="diagnosis-section">
            <div class="diagnosis-title">التشخيص - Diagnosis</div>
            <div class="diagnosis-content">
                <?= $consultation['diagnosis'] ?>
                <?php if ($consultation['diagnosis_code']): ?>
                    <br><strong>رمز التشخيص:</strong> <?= $consultation['diagnosis_code'] ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Treatment Plan -->
        <div class="plan-section">
            <div class="plan-title">خطة العلاج - Treatment Plan</div>
            <div class="plan-content"><?= $consultation['plan'] ?></div>
        </div>
        
        <!-- Follow-up -->
        <?php if ($consultation['followup_days']): ?>
        <div class="followup-section">
            <div class="followup-title">موعد المتابعة - Follow-up Schedule</div>
            <div class="followup-content">
                بعد <?= $consultation['followup_days'] ?> يوم
                <br>After <?= $consultation['followup_days'] ?> days
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Urgency Notice -->
        <div class="urgency-notice">
            <div class="urgency-title">ملاحظة مهمة - Important Notice</div>
            <div class="urgency-text">
                يرجى إجراء هذه الفحوصات في أقرب وقت ممكن
                <br>Please perform these tests as soon as possible
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer-section">
        <div class="doctor-signature">
            <div class="signature-line"></div>
            <div class="doctor-name"><?= $doctor['display_name'] ?></div>
            <div class="doctor-title">طبيب عيون - Ophthalmologist</div>
            <div class="doctor-title"><?= $clinic['name'] ?></div>
        </div>
        
        <div class="clinic-stamp">
            <div class="stamp-box">
                <?= $clinic['name'] ?>
            </div>
        </div>
        
        <div class="date-section">
            <div class="date-label">تاريخ الطلب - Request Date</div>
            <div class="date-value"><?= date('d/m/Y', strtotime($consultation['created_at'] ?? 'now')) ?></div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            if (window.location.search.includes('print=1')) {
                window.print();
            }
        };
        
        // Print button functionality
        function printLabRequest() {
            window.print();
        }
    </script>
</body>
</html>
