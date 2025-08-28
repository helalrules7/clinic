<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Test - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
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
            font-size: 22px;
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
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 12px 0;
            color: white;
            border: 3px solid #3498db;
            padding: 8px;
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
            font-size: 12px;
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
            display: inline-block;
            width: 35%;
            min-width: fit-content;
        }
        
        .test-details {
            border: 2px solid #3498db;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            padding-bottom: 2cm;
        }
        
        .test-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            text-align: center;
            padding: 0 10px;
        }
        
        .test-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 12px;
            background: white;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
            width: 100%;
        }
        
        .test-type {
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            text-align: center;
            display: inline-block;
        }
        
        .test-status {
            padding: 3px 8px;
            border-radius: 3px;
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
            border-radius: 3px;
            font-size: 8px;
            color: white;
            text-align: center;
            display: inline-block;
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
            font-size: 11px;
        }
        
        .section-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 12px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
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
        <div class="document-title">Lab Test Report</div>
    </div>

    <div class="patient-info">
        <div class="patient-details">
            <h3>Patient Information</h3>
            <p><span class="info-label">Name:</span> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            <p><span class="info-label">Phone:</span> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><span class="info-label">Birth Date:</span> <?= date('d/m/Y', strtotime($patient['dob'])) ?></p>
        </div>
        <div class="appointment-details">
            <h3>Appointment Information</h3>
            <p><span class="info-label">Date:</span> <?= date('d/m/Y', strtotime($appointment['date'])) ?></p>
            <p><span class="info-label">Doctor:</span> <?= htmlspecialchars($doctor['display_name']) ?></p>
            <p><span class="info-label">Test Date:</span> <?= date('d/m/Y') ?></p>
        </div>
    </div>

    <div class="test-details">
        <div class="test-name"><?= htmlspecialchars($labTest['test_name']) ?></div>
        
        <div class="test-info">
            <div>
                <strong>Type:</strong> 
                <span class="test-type"><?= ucfirst($labTest['test_type']) ?></span>
            </div>
            <div>
                <strong>Category:</strong> 
                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $labTest['test_category']))) ?>
            </div>
            <div>
                <strong>Status:</strong> 
                <span class="test-status status-<?= $labTest['status'] ?>">
                    <?= ucfirst($labTest['status']) ?>
                </span>
            </div>
            <div>
                <strong>Priority:</strong> 
                <span class="priority priority-<?= $labTest['priority'] ?>">
                    <?= ucfirst($labTest['priority']) ?>
                </span>
            </div>
            <div>
                <strong>Ordered Date:</strong> 
                <?= date('d/m/Y', strtotime($labTest['ordered_date'])) ?>
            </div>
            <?php if (!empty($labTest['expected_date'])): ?>
            <div>
                <strong>Expected Date:</strong> 
                <?= date('d/m/Y', strtotime($labTest['expected_date'])) ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($labTest['notes'])): ?>
        <div class="notes-section">
            <div class="section-title">Notes:</div>
            <div><?= nl2br(htmlspecialchars($labTest['notes'])) ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($labTest['results'])): ?>
        <div class="results-section">
            <div class="section-title">Results:</div>
            <div><?= nl2br(htmlspecialchars($labTest['results'])) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div>Report Generated: <?= date('d/m/Y H:i') ?></div>
        <div><?= htmlspecialchars($clinic['name']) ?> - Lab Test Report</div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
