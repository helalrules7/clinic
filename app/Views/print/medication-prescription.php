<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Prescription - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.7cm;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
            background: white;
            width: 24.5cm;
            margin: 0 auto;
            padding: 0.7cm;
        }
        
        .prescription-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .clinic-logo {
            width: 55px;
            height: 55px;
            margin-bottom: 8px;
        }
        
        .clinic-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .clinic-name-ar {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }
        
        .clinic-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .prescription-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #2c3e50;
            border: 2px solid #3498db;
            padding: 8px;
            border-radius: 5px;
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
        
        .patient-details {
            flex: 1;
        }
        
        .patient-details h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .patient-details p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .appointment-details {
            flex: 1;
            text-align: left;
        }
        
        .appointment-details h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .appointment-details p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .prescription-content {
            margin: 15px 0;
        }
        
        .medication-item {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            background: white;
        }
        
        .medication-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #eee;
        }
        
        .drug-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .rx-symbol {
            font-size: 20px;
            color: #e74c3c;
            font-weight: bold;
        }
        
        .medication-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
        }
        
        .detail-group {
            display: flex;
            align-items: center;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            min-width: 70px;
            margin-left: 8px;
            font-size: 10px;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 10px;
        }
        
        .notes-section {
            margin-top: 8px;
            padding: 8px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
        }
        
        .notes-label {
            font-weight: bold;
            color: #856404;
            margin-bottom: 3px;
            font-size: 10px;
        }
        
        .notes-text {
            color: #856404;
            font-style: italic;
            font-size: 9px;
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
            width: 180px;
            height: 1px;
            background: #333;
            margin: 15px auto 3px;
        }
        
        .doctor-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 11px;
        }
        
        .doctor-title {
            color: #666;
            font-size: 9px;
        }
        
        .clinic-stamp {
            text-align: center;
            flex: 1;
        }
        
        .stamp-box {
            width: 80px;
            height: 80px;
            border: 2px solid #3498db;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            line-height: 1.1;
        }
        
        .date-section {
            text-align: center;
            flex: 1;
        }
        
        .date-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
            font-size: 10px;
        }
        
        .date-value {
            color: #666;
            font-size: 12px;
        }
        
        .prescription-number {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 10px;
            color: #999;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.05;
            pointer-events: none;
            z-index: -1;
        }
        
        .watermark img {
            width: 220px;
            height: auto;
        }
        
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0.4cm;
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
    <div class="prescription-number">Rx #<?= str_pad($appointment['id'], 6, '0', STR_PAD_LEFT) ?></div>
    <div class="watermark">
        <img src="<?= htmlspecialchars($clinic['logo_watermark']) ?>" alt="Watermark">
    </div>
    
    <!-- Header -->
    <div class="prescription-header">
        <div class="logo-section">
            <img src="<?= htmlspecialchars($clinic['logo_print']) ?>" alt="<?= htmlspecialchars($clinic['name']) ?> Logo" class="clinic-logo">
            <div class="clinic-name"><?= htmlspecialchars($clinic['name']) ?></div>
            <div class="clinic-name-ar"><?= htmlspecialchars($clinic['name_arabic']) ?></div>
        </div>
        <div class="clinic-info"><?= htmlspecialchars($clinic['address']) ?></div>
        <div class="clinic-info">هاتف: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
    </div>
    
    <!-- Title -->
    <div class="prescription-title">وصفة طبية - Medical Prescription</div>
    
    <!-- Patient and Appointment Info -->
    <div class="patient-info">
        <div class="patient-details">
            <h3>بيانات المريض - Patient Information</h3>
            <p><strong>الاسم:</strong> <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
            <p><strong>العمر:</strong> 
                <?php if ($patient['age_computed']): ?>
                    <?= $patient['age_computed'] ?> سنة
                <?php elseif ($patient['dob']): ?>
                    <?= date_diff(date_create($patient['dob']), date_create('now'))->y ?> سنة
                <?php else: ?>
                    غير محدد
                <?php endif; ?>
            </p>
            <p><strong>الجنس:</strong> <?= $patient['gender'] ?? 'N/A' ?></p>
            <p><strong>رقم الهاتف:</strong> <?= $patient['phone'] ?></p>
            <?php if ($patient['allergies']): ?>
                <p><strong>الحساسية:</strong> <?= $patient['allergies'] ?></p>
            <?php endif; ?>
        </div>
        
        <div class="appointment-details">
            <h3>تفاصيل الموعد - Appointment Details</h3>
            <p><strong>التاريخ:</strong> <?= date('d/m/Y', strtotime($appointment['date'])) ?></p>
            <p><strong>الوقت:</strong> <?= date('H:i', strtotime($appointment['start_time'])) ?></p>
            <p><strong>نوع الزيارة:</strong> <?= $appointment['visit_type'] ?></p>
            <p><strong>الطبيب:</strong> <?= $doctor['display_name'] ?></p>
        </div>
    </div>
    
    <!-- Prescription Content -->
    <div class="prescription-content">
        <?php foreach ($prescriptions as $prescription): ?>
        <div class="medication-item">
            <div class="medication-header">
                <div class="drug-name"><?= $prescription['drug_name'] ?></div>
                <div class="rx-symbol">℞</div>
            </div>
            
            <?php if ($prescription['notes']): ?>
                <div class="notes-section">
                    <div class="notes-label">ملاحظات خاصة - Special Instructions:</div>
                    <div class="notes-text"><?= $prescription['notes'] ?></div>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
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
            <div class="date-value"><?= date('d/m/Y') ?></div>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Wait a short moment for the page to fully render, then print
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Print button functionality
        function printPrescription() {
            window.print();
        }
    </script>
</body>
</html>
