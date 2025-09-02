<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Test - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.8cm;
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
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            padding: 0.8cm;
        }
        
        .prescription-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 12px;
        }
        
        .logo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .clinic-logo {
            width: 60px;
            height: 60px;
            margin-bottom: 8px;
        }
        
        .clinic-name {
            font-size: 18px;
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }
        
        .clinic-name-ar {
            font-size: 16px;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }
        
        .clinic-info {
            font-size: 10px;
            font-family: 'Cairo', sans-serif;
            color: #666;
            margin-bottom: 3px;
        }
        
        .prescription-title {
            font-size: 18px;
            font-family: 'Cairo', sans-serif;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #2c3e50;
            border: 3px solid #3498db;
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            padding: 12px;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .patient-details, .appointment-details {
            flex: 1;
            text-align: right;
            direction: rtl;
        }
        
        .patient-details h3, .appointment-details h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            text-align: right;
        }
        
        .patient-details p, .appointment-details p {
            margin: 4px 0;
            font-size: 11px;
            text-align: right;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 35%;
            min-width: fit-content;
            font-size: 10px;
        }
        
        .test-details {
            border: 2px solid #3498db;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            page-break-inside: avoid;
        }
        
        .test-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
            padding: 8px;
            background: white;
            border-radius: 8px;
            border: 1px solid #3498db;
        }
        
        .test-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            width: 100%;
        }
        
        .test-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .test-info-label {
            font-weight: bold;
            color: #495057;
            font-size: 10px;
        }
        
        .test-info-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 10px;
        }
        
        .test-type {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            text-align: center;
            display: inline-block;
        }
        
        .test-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            color: white;
            text-align: center;
            display: inline-block;
        }
        
        .status-ordered { background: #f39c12; }
        .status-pending { background: #e67e22; }
        .status-completed { background: #27ae60; }
        .status-cancelled { background: #e74c3c; }
        
        .priority {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8px;
            color: white;
            text-align: center;
            display: inline-block;
        }
        
        .priority-normal { background: #3498db; }
        .priority-high { background: #f39c12; }
        .priority-urgent { background: #e74c3c; }
        
        .notes-section, .results-section {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            background: white;
            font-size: 11px;
        }
        
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
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
            width: 250px;
            height: auto;
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
            top: 25px;
            left: 25px;
            font-size: 11px;
            color: #999;
        }
        
        @media print {
            body {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0.5cm;
            }
            
            .prescription-header,
            .patient-info,
            .test-details {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-number">Lab Test #<?= str_pad($labTest['id'] ?? '1', 6, '0', STR_PAD_LEFT) ?></div>
    <div class="watermark">
        <img src="/assets/images/Light.png" alt="Watermark">
    </div>
    
    <!-- Header -->
    <div class="prescription-header">
        <div class="logo-section">
            <img src="/assets/images/Light.png" alt="Roaya Clinic Logo" class="clinic-logo">
            <div class="clinic-name">Roaya Ophthalmology Clinic</div>
            <div class="clinic-name-ar">رؤية لطب وجراحة العيون</div>
        </div>
        <div class="clinic-info"><?= htmlspecialchars($clinic['address']) ?></div>
        <div class="clinic-info">Tel: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
    </div>
    
    <!-- Title -->
    <div class="prescription-title">طلب تحليل مخبري - Lab Test Request</div>
    
    <!-- Patient and Appointment Info -->
    <div class="patient-info">
        <div class="patient-details" dir="rtl">
            <h3>بيانات المريض - Patient Details</h3>
            <p><span class="info-label">الاسم:</span> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            <p><span class="info-label">رقم الهاتف:</span> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><span class="info-label">تاريخ الميلاد:</span> <?= date('d/m/Y', strtotime($patient['dob'])) ?></p>
        </div>
        <div class="appointment-details" dir="rtl">
            <h3>معلومات الموعد - Appointment Details</h3>
            <p><span class="info-label">التاريخ:</span> <?= date('d/m/Y', strtotime($appointment['date'])) ?></p>
            <p><span class="info-label">الطبيب:</span> <?= htmlspecialchars($doctor['display_name']) ?></p>
            <p><span class="info-label">تاريخ التحليل:</span> <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <!-- Test Details -->
    <div class="test-details">
        <div class="test-name"><?= htmlspecialchars($labTest['test_name']) ?></div>
        
        <div class="test-info">
            <div class="test-info-item">
                <span class="test-info-label">النوع:</span>
                <span class="test-type"><?= ucfirst($labTest['test_type']) ?></span>
            </div>
            <div class="test-info-item">
                <span class="test-info-label">التصنيف:</span>
                <span class="test-info-value"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $labTest['test_category']))) ?></span>
            </div>
            <div class="test-info-item">
                <span class="test-info-label">الحالة:</span>
                <span class="test-status status-<?= $labTest['status'] ?>">
                    <?= ucfirst($labTest['status']) ?>
                </span>
            </div>
            <div class="test-info-item">
                <span class="test-info-label">الأولوية:</span>
                <span class="priority priority-<?= $labTest['priority'] ?>">
                    <?= ucfirst($labTest['priority']) ?>
                </span>
            </div>
            <div class="test-info-item">
                <span class="test-info-label">تاريخ الطلب:</span>
                <span class="test-info-value"><?= date('d/m/Y', strtotime($labTest['ordered_date'])) ?></span>
            </div>
            <?php if (!empty($labTest['expected_date'])): ?>
            <div class="test-info-item">
                <span class="test-info-label">التاريخ المتوقع:</span>
                <span class="test-info-value"><?= date('d/m/Y', strtotime($labTest['expected_date'])) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($labTest['notes'])): ?>
        <div class="notes-section">
            <div class="section-title">ملاحظات - Notes:</div>
            <div><?= nl2br(htmlspecialchars($labTest['notes'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($labTest['results'])): ?>
        <div class="results-section">
            <div class="section-title">النتائج - Results:</div>
            <div><?= nl2br(htmlspecialchars($labTest['results'])) ?></div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer -->
    <div class="footer-section">
        <div class="doctor-signature">
            <div class="signature-line"></div>
            <div class="doctor-name"><?= htmlspecialchars($doctor['display_name']) ?></div>
            <div class="doctor-title">طبيب عيون - Ophthalmologist</div>
            <div class="doctor-title"><?= htmlspecialchars($clinic['name']) ?></div>
        </div>
        
        <div class="clinic-stamp">
            <div class="stamp-box">
                <?= htmlspecialchars($clinic['name']) ?>
            </div>
        </div>
        
        <div class="date-section">
            <div class="date-label">تاريخ التقرير - Report Date</div>
            <div class="date-value"><?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>