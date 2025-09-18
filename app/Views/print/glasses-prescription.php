<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glasses Prescription - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
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
        
        .glasses-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .eye-section {
            border: 2px solid #3498db;
            padding: 12px;
            border-radius: 8px;
            background: white;
            position: relative;
        }
        
        .eye-title {
            position: absolute;
            top: -12px;
            left: 20px;
            background: white;
            padding: 0 15px;
            font-weight: bold;
            color: #3498db;
            font-size: 14px;
        }
        
        .measurement-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }
        
        .measurement-label {
            font-weight: bold;
            color: #666;
            min-width: 100px;
        }
        
        .measurement-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            text-align: center;
            min-width: 80px;
        }
        
        .measurement-unit {
            color: #999;
            font-size: 11px;
            margin-right: 5px;
        }
        
        .lens-specs {
            margin-top: 12px;
            border: 2px solid #e74c3c;
            padding: 10px;
            border-radius: 8px;
            background: #fff5f5;
        }
        
        .lens-title {
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 6px;
            text-align: center;
            font-size: 12px;
        }
        
        .lens-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        
        .pd-section {
            margin: 12px 0;
            border: 2px solid #f39c12;
            padding: 10px;
            border-radius: 8px;
            background: #fffbf0;
        }
        
        .pd-title {
            font-weight: bold;
            color: #f39c12;
            margin-bottom: 6px;
            text-align: center;
            font-size: 12px;
        }
        
        .pd-values {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        
        .pd-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .pd-label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .pd-value {
            font-size: 16px;
            font-weight: bold;
            color: #f39c12;
        }
        
        .comments-section {
            margin: 12px 0;
            border: 2px solid #9b59b6;
            padding: 10px;
            border-radius: 8px;
            background: #f8f4fd;
        }
        
        .comments-title {
            font-weight: bold;
            color: #9b59b6;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .comments-text {
            color: #9b59b6;
            font-style: italic;
            line-height: 1.6;
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
            width: 220px;
            height: 2px;
            background: #333;
            margin: 15px auto 5px;
        }
        
        .doctor-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .doctor-title {
            color: #666;
            font-size: 10px;
        }
        
        .clinic-stamp {
            text-align: center;
            flex: 1;
        }
        
        .stamp-box {
            width: 100px;
            height: 100px;
            border: 3px solid #3498db;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-weight: bold;
            font-size: 9px;
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
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .date-value {
            color: #666;
            font-size: 14px;
        }
        
        .prescription-number {
            position: absolute;
            top: 25px;
            left: 25px;
            font-size: 11px;
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
            width: 250px;
            height: auto;
        }
        
        .validity-notice {
            margin: 12px 0;
            padding: 10px;
            background: #e8f5e8;
            border: 2px solid #27ae60;
            border-radius: 8px;
            text-align: center;
        }
        
        .validity-title {
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .validity-text {
            color: #27ae60;
            font-size: 10px;
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
            .glasses-grid,
            .lens-specs {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-number">Glasses Rx #<?= str_pad($prescription['id'], 6, '0', STR_PAD_LEFT) ?></div>
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
        <div class="clinic-info"><?= $clinic['address'] ?></div>
        <div class="clinic-info">هاتف: <?= $clinic['phone'] ?> | <?= $clinic['email'] ?></div>
    </div>
    
    <!-- Title -->
    <div class="prescription-title">وصفة نظارات - Glasses Prescription</div>
    
    <!-- Patient and Appointment Info -->
    <div class="patient-info">
        <div class="patient-details" dir="rtl">
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
        </div>
        
        <div class="appointment-details" dir="rtl">
            <h3>تفاصيل الموعد - Appointment Details</h3>
            <p><strong>التاريخ:</strong> 
                <?php 
                $appointmentDate = $prescription['appointment_date'] ?? $appointment['date'] ?? $appointment['appointment_date'] ?? null;
                echo $appointmentDate ? date('d/m/Y', strtotime($appointmentDate)) : '01/01/1970';
                ?>
            </p>
            <p><strong>الوقت:</strong> 
                <?php 
                $startTime = $prescription['start_time'] ?? $appointment['start_time'] ?? null;
                echo $startTime ? date('H:i', strtotime($startTime)) : '14:00';
                ?>
            </p>
            <p><strong>نوع الزيارة:</strong> 
                <?= $prescription['visit_type'] ?? $appointment['visit_type'] ?? 'New' ?>
            </p>
            <p><strong>الطبيب:</strong> <?= $doctor['display_name'] ?? 'Dr. Ahmed Abo AlKassem' ?></p>
        </div>
    </div>
    
    <!-- Glasses Prescription Content -->
    <div class="prescription-content">
        <div class="glasses-grid">
            <!-- Right Eye -->
            <div class="eye-section">
                <div class="eye-title">العين اليمنى - Right Eye (OD)</div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Sphere:</span>
                    <span class="measurement-value"><?= $prescription['distance_sphere_r'] ?? $prescription['sphere_r'] ?? '0.00' ?></span>
                    <span class="measurement-unit">D</span>
                </div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Cylinder:</span>
                    <span class="measurement-value"><?= $prescription['distance_cylinder_r'] ?? $prescription['cylinder_r'] ?? '0.00' ?></span>
                    <span class="measurement-unit">D</span>
                </div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Axis:</span>
                    <span class="measurement-value"><?= $prescription['distance_axis_r'] ?? $prescription['axis_r'] ?? '0' ?></span>
                    <span class="measurement-unit">°</span>
                </div>
                

            </div>
            
            <!-- Left Eye -->
            <div class="eye-section">
                <div class="eye-title">العين اليسرى - Left Eye (OS)</div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Sphere:</span>
                    <span class="measurement-value"><?= $prescription['distance_sphere_l'] ?? $prescription['sphere_l'] ?? '0.00' ?></span>
                    <span class="measurement-unit">D</span>
                </div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Cylinder:</span>
                    <span class="measurement-value"><?= $prescription['distance_cylinder_l'] ?? $prescription['cylinder_l'] ?? '0.00' ?></span>
                    <span class="measurement-unit">D</span>
                </div>
                
                <div class="measurement-row">
                    <span class="measurement-label">Axis:</span>
                    <span class="measurement-value"><?= $prescription['distance_axis_l'] ?? $prescription['axis_l'] ?? '0' ?></span>
                    <span class="measurement-unit">°</span>
                </div>
                

            </div>
        </div>
        
        <!-- Lens Type -->
        <div class="lens-specs">
            <div class="lens-title">نوع العدسة - Lens Type</div>
            <div class="lens-details">
                <div>
                    <strong>النوع:</strong> <?= $prescription['lens_type'] ?? 'Single Vision' ?>
                </div>
                <div>
                    <strong>التاريخ:</strong> <?= date('d/m/Y', strtotime($prescription['created_at'] ?? 'now')) ?>
                </div>
            </div>
        </div>
        
        <!-- Pupillary Distance -->
        <div class="pd-section">
            <div class="pd-title">المسافة بين الحدقتين - Pupillary Distance (PD)</div>
            <div class="pd-values">
                <div class="pd-item">
                    <span class="pd-label">للقراءة - Near</span>
                    <span class="pd-value"><?= $prescription['PD_NEAR'] ?? $prescription['pd_near'] ?? 'N/A' ?> mm</span>
                </div>
                <div class="pd-item">
                    <span class="pd-label">للبعد - Distance</span>
                    <span class="pd-value"><?= $prescription['PD_DISTANCE'] ?? $prescription['pd_distance'] ?? 'N/A' ?> mm</span>
                </div>
            </div>
        </div>
        
        <!-- Comments -->
        <?php if ($prescription['comments']): ?>
        <div class="comments-section">
            <div class="comments-title">ملاحظات خاصة - Special Instructions</div>
            <div class="comments-text"><?= $prescription['comments'] ?></div>
        </div>
        <?php endif; ?>
        
        <!-- Validity Notice -->
        <div class="validity-notice">
            <div class="validity-title">مدة صلاحية الوصفة - Prescription Validity</div>
            <div class="validity-text">هذه الوصفة صالحة لمدة سنة واحدة من تاريخ الإصدار - This prescription is valid for one year from the date of issue</div>
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
