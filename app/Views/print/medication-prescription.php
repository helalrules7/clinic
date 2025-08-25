<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Prescription - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <style>
        @media print {
            @page {
                size: A4;
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
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: white;
            width: 24.5cm;
            margin: 0 auto;
            padding: 1cm;
        }
        
        .prescription-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .clinic-info {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .prescription-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            color: #2c3e50;
            border: 2px solid #3498db;
            padding: 10px;
            border-radius: 5px;
        }
        
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        
        .patient-details {
            flex: 1;
        }
        
        .patient-details h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .patient-details p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .appointment-details {
            flex: 1;
            text-align: left;
        }
        
        .appointment-details h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .appointment-details p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .prescription-content {
            margin: 20px 0;
        }
        
        .medication-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 5px;
            background: white;
        }
        
        .medication-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .drug-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .rx-symbol {
            font-size: 24px;
            color: #e74c3c;
            font-weight: bold;
        }
        
        .medication-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .detail-group {
            display: flex;
            align-items: center;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            min-width: 80px;
            margin-left: 10px;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .notes-section {
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
        
        .notes-label {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }
        
        .notes-text {
            color: #856404;
            font-style: italic;
        }
        
        .footer-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .doctor-signature {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 200px;
            height: 1px;
            background: #333;
            margin: 20px auto 5px;
        }
        
        .doctor-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .doctor-title {
            color: #666;
            font-size: 11px;
        }
        
        .clinic-stamp {
            text-align: center;
            flex: 1;
        }
        
        .stamp-box {
            width: 100px;
            height: 100px;
            border: 2px solid #3498db;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
            line-height: 1.2;
        }
        
        .date-section {
            text-align: center;
            flex: 1;
        }
        
        .date-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .date-value {
            color: #666;
            font-size: 14px;
        }
        
        .prescription-number {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 10px;
            color: #999;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 48px;
            color: rgba(52, 152, 219, 0.1);
            font-weight: bold;
            pointer-events: none;
            z-index: -1;
        }
        
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0.5cm;
            }
            
            .prescription-header,
            .patient-info,
            .medication-item {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-number">Rx #<?= str_pad($prescription['id'], 6, '0', STR_PAD_LEFT) ?></div>
    <div class="watermark"><?= $clinic['name'] ?></div>
    
    <!-- Header -->
    <div class="prescription-header">
        <div class="clinic-name"><?= $clinic['name'] ?></div>
        <div class="clinic-info"><?= $clinic['address'] ?></div>
        <div class="clinic-info">هاتف: <?= $clinic['phone'] ?> | <?= $clinic['email'] ?></div>
        <div class="clinic-info"><?= $clinic['license'] ?> | <?= $clinic['tax_id'] ?></div>
    </div>
    
    <!-- Title -->
    <div class="prescription-title">وصفة طبية - Medical Prescription</div>
    
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
    
    <!-- Prescription Content -->
    <div class="prescription-content">
        <div class="medication-item">
            <div class="medication-header">
                <div class="drug-name"><?= $prescription['drug_name'] ?></div>
                <div class="rx-symbol">℞</div>
            </div>
            
            <div class="medication-details">
                <div class="detail-group">
                    <span class="detail-label">الجرعة:</span>
                    <span class="detail-value"><?= $prescription['dose'] ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">التكرار:</span>
                    <span class="detail-value"><?= $prescription['frequency'] ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">المدة:</span>
                    <span class="detail-value"><?= $prescription['duration'] ?></span>
                </div>
                
                <div class="detail-group">
                    <span class="detail-label">طريقة الاستخدام:</span>
                    <span class="detail-value"><?= $prescription['route'] ?? 'Topical' ?></span>
                </div>
            </div>
            
            <?php if ($prescription['notes']): ?>
                <div class="notes-section">
                    <div class="notes-label">ملاحظات خاصة - Special Instructions:</div>
                    <div class="notes-text"><?= $prescription['notes'] ?></div>
                </div>
            <?php endif; ?>
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
            <div class="date-label">تاريخ الوصفة - Prescription Date</div>
            <div class="date-value"><?= date('d/m/Y', strtotime($prescription['created_at'] ?? 'now')) ?></div>
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
        function printPrescription() {
            window.print();
        }
    </script>
</body>
</html>
