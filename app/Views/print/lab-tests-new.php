<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Tests - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
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
        
        .prescription-content {
            margin: 15px 0;
        }
        
        .tests-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .test-section {
            border: 2px solid #3498db;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8f9fa;
            page-break-inside: avoid;
            overflow: hidden;
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .test-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
            padding: 0 5px;
        }
        
        .test-type {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 8px;
            text-transform: uppercase;
        }
        
        .test-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 8px;
            background: white;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
            width: 100%;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 10px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: white;
            text-align: center;
        }
        
        .status-ordered { background: #f39c12; }
        .status-pending { background: #e67e22; }
        .status-completed { background: #27ae60; }
        .status-cancelled { background: #e74c3c; }
        
        .priority-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            color: white;
            text-align: center;
        }
        
        .priority-normal { background: #3498db; }
        .priority-high { background: #f39c12; }
        .priority-urgent { background: #e74c3c; }
        
        .notes-section, .results-section {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 5px;
            background: white;
            font-size: 10px;
        }
        
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
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
        
        .no-tests {
            text-align: center;
            padding: 40px;
            color: #666;
            border: 2px dashed #ddd;
            border-radius: 8px;
            background: #f8f9fa;
        }
        
        .no-tests h3 {
            color: #666;
            margin-bottom: 10px;
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
            .test-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-number">Lab Tests #<?= str_pad($appointment['id'] ?? '1', 6, '0', STR_PAD_LEFT) ?></div>
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
        <div class="clinic-info">Tel: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
    </div>
    
    <!-- Title -->
    <div class="prescription-title">تقرير التحاليل المخبرية - Lab Tests Report</div>
    
    <!-- Patient and Appointment Info -->
    <div class="patient-info">
        <div class="patient-details" dir="rtl">
            <h3>بيانات المريض - Patient Information</h3>
            <p><strong>الاسم:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            <p><strong>رقم الهاتف:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><strong>تاريخ الميلاد:</strong> <?= date('d/m/Y', strtotime($patient['dob'])) ?></p>
            <p><strong>العمر:</strong> <?= date('Y') - date('Y', strtotime($patient['dob'])) ?> سنة</p>
        </div>
        
        <div class="appointment-details" dir="rtl">
            <h3>تفاصيل الموعد - Appointment Details</h3>
            <p><strong>التاريخ:</strong> <?= date('d/m/Y', strtotime($appointment['date'])) ?></p>
            <p><strong>الطبيب:</strong> <?= htmlspecialchars($doctor['display_name']) ?></p>
            <p><strong>إجمالي التحاليل:</strong> <?= count($labTests) ?></p>
            <p><strong>تاريخ التقرير:</strong> <?= date('d/m/Y') ?></p>
        </div>
    </div>
    
    <!-- Lab Tests Content -->
    <div class="prescription-content">
        <?php if (!empty($labTests)): ?>
            <div class="tests-grid">
                <?php foreach ($labTests as $test): ?>
                <div class="test-section">
                    <div class="test-header">
                        <div class="test-name"><?= htmlspecialchars($test['test_name']) ?></div>
                        <span class="test-type"><?= ucfirst($test['test_type']) ?></span>
                    </div>
                    
                    <div class="test-details">
                        <div class="detail-item">
                            <span class="detail-label">التصنيف:</span>
                            <span><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $test['test_category']))) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">الحالة:</span>
                            <span class="status-badge status-<?= $test['status'] ?>">
                                <?= ucfirst($test['status']) ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">الأولوية:</span>
                            <span class="priority-badge priority-<?= $test['priority'] ?>">
                                <?= ucfirst($test['priority']) ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">تاريخ الطلب:</span>
                            <span><?= date('d/m/Y', strtotime($test['ordered_date'])) ?></span>
                        </div>
                        <?php if (!empty($test['expected_date'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">التاريخ المتوقع:</span>
                            <span><?= date('d/m/Y', strtotime($test['expected_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($test['notes'])): ?>
                    <div class="notes-section">
                        <div class="section-title">ملاحظات:</div>
                        <div><?= nl2br(htmlspecialchars($test['notes'])) ?></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($test['results'])): ?>
                    <div class="results-section">
                        <div class="section-title">النتائج:</div>
                        <div><?= nl2br(htmlspecialchars($test['results'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-tests">
                <h3>لم يتم العثور على تحاليل</h3>
                <p>لم يتم طلب أي تحاليل مخبرية لهذا الموعد.</p>
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
            <div class="date-value"><?= date('d/m/Y') ?></div>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>