<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Tests - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
    <style>
        @media print {
            @page {
                size: A4;
                margin: 0.3cm 0.2cm 0.3cm 0.8cm;
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
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            padding: 0.3cm 0.2cm 0.3cm 0.8cm;
            direction: rtl;
        }
        
        .lab-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 10px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        
        .clinic-info {
            font-size: 10px;
            color: #666;
            margin-bottom: 4px;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: white;
            border: 3px solid #3498db;
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            padding: 8px 12px;
            border-radius: 8px;
            background: #f8f9fa;
            width: 100%;
        }
        
        .patient-details, .appointment-details {
            flex: 1;
            margin: 0 5px;
            min-width: 0;
        }
        
        .patient-details h3, .appointment-details h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 13px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .patient-details p, .appointment-details p {
            margin: 4px 0;
            font-size: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        
        .tests-container {
            margin-bottom: 20px;
            padding-bottom: 2cm;
        }
        
        .test-card {
            border: 2px solid #3498db;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            page-break-inside: avoid;
            overflow: hidden;
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .test-name {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            padding: 0 8px;
        }
        
        .test-type {
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .test-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 10px;
            background: white;
            padding: 6px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
            width: 100%;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 11px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #495057;
        }
        
        .status-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            text-align: center;
        }
        
        .status-ordered { background: #f39c12; }
        .status-pending { background: #e67e22; }
        .status-completed { background: #27ae60; }
        .status-cancelled { background: #e74c3c; }
        
        .priority-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
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
            margin-bottom: 8px;
            background: white;
            font-size: 10px;
        }
        
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 11px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }
        
        .section-content {
            line-height: 1.4;
            font-size: 10px;
        }
        
        .footer {
            position: fixed;
            bottom: 0.2cm;
            left: 0.8cm;
            right: 0.2cm;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            background: white;
        }
        
        .no-tests {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        @media print {
            body { print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="lab-header">
        <div class="clinic-name"><?= htmlspecialchars($clinic['name']) ?></div>
        <div class="clinic-info"><?= htmlspecialchars($clinic['address']) ?></div>
        <div class="clinic-info">Tel: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
        <div class="document-title">Lab Tests Report</div>
    </div>

    <div class="patient-info">
        <div class="patient-details">
            <h3>Patient Information - معلومات المريض</h3>
            <p><span class="info-label">Name - الاسم:</span> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            <p><span class="info-label">Phone - الهاتف:</span> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><span class="info-label">Date of Birth - تاريخ الميلاد:</span> <?= date('d/m/Y', strtotime($patient['dob'])) ?></p>
            <p><span class="info-label">Age - العمر:</span> <?= date('Y') - date('Y', strtotime($patient['dob'])) ?> years</p>
        </div>
        <div class="appointment-details">
            <h3>Appointment Information - معلومات الموعد</h3>
            <p><span class="info-label">Date - التاريخ:</span> <?= date('d/m/Y', strtotime($appointment['date'])) ?></p>
            <p><span class="info-label">Doctor - الطبيب:</span> <?= htmlspecialchars($doctor['display_name']) ?></p>
            <p><span class="info-label">Total Tests - إجمالي التحاليل:</span> <?= count($labTests) ?></p>
            <p><span class="info-label">Report Date - تاريخ التقرير:</span> <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <div class="tests-container">
        <?php if (!empty($labTests)): ?>
            <?php foreach ($labTests as $test): ?>
            <div class="test-card">
                <div class="test-header">
                    <div class="test-name"><?= htmlspecialchars($test['test_name']) ?></div>
                    <span class="test-type"><?= ucfirst($test['test_type']) ?></span>
                </div>
                
                <div class="test-details">
                    <div class="detail-item">
                        <span class="detail-label">Category:</span>
                        <span><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $test['test_category']))) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span class="status-badge status-<?= $test['status'] ?>">
                            <?= ucfirst($test['status']) ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Priority:</span>
                        <span class="priority-badge priority-<?= $test['priority'] ?>">
                            <?= ucfirst($test['priority']) ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ordered Date:</span>
                        <span><?= date('d/m/Y', strtotime($test['ordered_date'])) ?></span>
                    </div>
                    <?php if (!empty($test['expected_date'])): ?>
                    <div class="detail-item">
                        <span class="detail-label">Expected Date:</span>
                        <span><?= date('d/m/Y', strtotime($test['expected_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($test['notes'])): ?>
                <div class="notes-section">
                    <div class="section-title">Notes:</div>
                    <div class="section-content"><?= nl2br(htmlspecialchars($test['notes'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($test['results'])): ?>
                <div class="results-section">
                    <div class="section-title">Results:</div>
                    <div class="section-content"><?= nl2br(htmlspecialchars($test['results'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-tests">
                <h3>No Lab Tests Found</h3>
                <p>No lab tests have been ordered for this appointment.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div>Report Generated: <?= date('d/m/Y H:i') ?></div>
        <div><?= htmlspecialchars($clinic['name']) ?> - Lab Tests Report</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
