<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الموعد - <?= htmlspecialchars($appointment['patient_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #0066cc;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        
        .clinic-name {
            font-size: 24px;
            font-weight: 700;
            color: #0066cc;
            margin-bottom: 5px;
        }
        
        .clinic-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-top: 15px;
        }
        
        .patient-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .patient-details, .appointment-details {
            flex: 1;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #0066cc;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        
        .field {
            margin-bottom: 10px;
        }
        
        .field-label {
            font-weight: 600;
            color: #555;
            display: inline-block;
            width: 150px;
            vertical-align: top;
        }
        
        .field-value {
            display: inline-block;
            width: calc(100% - 160px);
            vertical-align: top;
        }
        
        .prescription-item {
            background: #f8f9fa;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }
        
        .prescription-name {
            font-weight: 600;
            color: #28a745;
            font-size: 15px;
            margin-bottom: 5px;
        }
        
        .prescription-details {
            font-size: 13px;
            color: #666;
        }
        
        .glasses-prescription {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #17a2b8;
        }
        
        .glasses-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .glasses-table th,
        .glasses-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        
        .glasses-table th {
            background: #e9ecef;
            font-weight: 600;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 50px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-name"><?= htmlspecialchars($clinic['name']) ?></div>
        <div class="clinic-info">
            <?= htmlspecialchars($clinic['address']) ?> | 
            <?= htmlspecialchars($clinic['phone']) ?> | 
            <?= htmlspecialchars($clinic['email']) ?>
        </div>
        <div class="clinic-info">
            <?= htmlspecialchars($clinic['license']) ?> | 
            <?= htmlspecialchars($clinic['tax_id']) ?>
        </div>
        <div class="report-title">تقرير الموعد الطبي</div>
    </div>

    <div class="patient-info">
        <div class="patient-details">
            <h3 style="margin-bottom: 10px; color: #0066cc;">معلومات المريض</h3>
            <div class="field">
                <span class="field-label">اسم المريض:</span>
                <span class="field-value"><?= htmlspecialchars($appointment['patient_name']) ?></span>
            </div>
            <?php if ($appointment['dob']): ?>
            <div class="field">
                <span class="field-label">تاريخ الميلاد:</span>
                <span class="field-value"><?= date('Y-m-d', strtotime($appointment['dob'])) ?></span>
            </div>
            <?php endif; ?>
            <div class="field">
                <span class="field-label">رقم الهاتف:</span>
                <span class="field-value"><?= htmlspecialchars($appointment['phone']) ?></span>
            </div>
            <?php if ($appointment['national_id']): ?>
            <div class="field">
                <span class="field-label">الرقم القومي:</span>
                <span class="field-value"><?= htmlspecialchars($appointment['national_id']) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="appointment-details">
            <h3 style="margin-bottom: 10px; color: #0066cc;">معلومات الموعد</h3>
            <div class="field">
                <span class="field-label">رقم الموعد:</span>
                <span class="field-value">#<?= $appointment['id'] ?></span>
            </div>
            <div class="field">
                <span class="field-label">التاريخ:</span>
                <span class="field-value"><?= date('Y-m-d', strtotime($appointment['date'])) ?></span>
            </div>
            <div class="field">
                <span class="field-label">الوقت:</span>
                <span class="field-value"><?= date('H:i', strtotime($appointment['start_time'])) ?> - <?= date('H:i', strtotime($appointment['end_time'])) ?></span>
            </div>
            <div class="field">
                <span class="field-label">الطبيب:</span>
                <span class="field-value"><?= htmlspecialchars($appointment['doctor_name']) ?></span>
            </div>
            <div class="field">
                <span class="field-label">نوع الزيارة:</span>
                <span class="field-value">
                    <?php 
                    $visitTypes = ['New' => 'زيارة جديدة', 'FollowUp' => 'متابعة', 'Procedure' => 'إجراء'];
                    echo $visitTypes[$appointment['visit_type']] ?? $appointment['visit_type'];
                    ?>
                </span>
            </div>
        </div>
    </div>

    <?php if ($consultationNotes): ?>
    <div class="section">
        <div class="section-title">ملاحظات الفحص</div>
        
        <?php if ($consultationNotes['chief_complaint']): ?>
        <div class="field">
            <span class="field-label">الشكوى الرئيسية:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['chief_complaint'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['hx_present_illness']): ?>
        <div class="field">
            <span class="field-label">تاريخ المرض الحالي:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['hx_present_illness'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['visual_acuity_right'] || $consultationNotes['visual_acuity_left']): ?>
        <div class="field">
            <span class="field-label">حدة البصر:</span>
            <span class="field-value">
                <?php if ($consultationNotes['visual_acuity_right']): ?>
                    العين اليمنى: <?= htmlspecialchars($consultationNotes['visual_acuity_right']) ?>
                <?php endif; ?>
                <?php if ($consultationNotes['visual_acuity_left']): ?>
                    <?= $consultationNotes['visual_acuity_right'] ? ' | ' : '' ?>العين اليسرى: <?= htmlspecialchars($consultationNotes['visual_acuity_left']) ?>
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['IOP_right'] || $consultationNotes['IOP_left']): ?>
        <div class="field">
            <span class="field-label">ضغط العين:</span>
            <span class="field-value">
                <?php if ($consultationNotes['IOP_right']): ?>
                    العين اليمنى: <?= $consultationNotes['IOP_right'] ?> mmHg
                <?php endif; ?>
                <?php if ($consultationNotes['IOP_left']): ?>
                    <?= $consultationNotes['IOP_right'] ? ' | ' : '' ?>العين اليسرى: <?= $consultationNotes['IOP_left'] ?> mmHg
                <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['slit_lamp']): ?>
        <div class="field">
            <span class="field-label">فحص المصباح الشقي:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['slit_lamp'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['fundus']): ?>
        <div class="field">
            <span class="field-label">فحص قاع العين:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['fundus'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['diagnosis']): ?>
        <div class="field">
            <span class="field-label">التشخيص:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['diagnosis'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['plan']): ?>
        <div class="field">
            <span class="field-label">خطة العلاج:</span>
            <span class="field-value"><?= nl2br(htmlspecialchars($consultationNotes['plan'])) ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($consultationNotes['followup_days']): ?>
        <div class="field">
            <span class="field-label">موعد المتابعة:</span>
            <span class="field-value">بعد <?= $consultationNotes['followup_days'] ?> يوم</span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($medications)): ?>
    <div class="section">
        <div class="section-title">الوصفة الطبية</div>
        <?php foreach ($medications as $med): ?>
        <div class="prescription-item">
            <div class="prescription-name"><?= htmlspecialchars($med['drug_name']) ?></div>
            <div class="prescription-details">
                <strong>الجرعة:</strong> <?= htmlspecialchars($med['dose']) ?> | 
                <strong>التكرار:</strong> <?= htmlspecialchars($med['frequency']) ?> | 
                <strong>المدة:</strong> <?= htmlspecialchars($med['duration']) ?> | 
                <strong>طريقة الإعطاء:</strong> <?= htmlspecialchars($med['route']) ?>
                <?php if ($med['notes']): ?>
                <br><strong>ملاحظات:</strong> <?= htmlspecialchars($med['notes']) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($glasses): ?>
    <div class="section">
        <div class="section-title">وصفة النظارة</div>
        <div class="glasses-prescription">
            <table class="glasses-table">
                <thead>
                    <tr>
                        <th>العين</th>
                        <th>المجال (Sphere)</th>
                        <th>الأسطوانة (Cylinder)</th>
                        <th>المحور (Axis)</th>
                        <th>القراءة (Add)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>اليمنى</td>
                        <td><?= $glasses['distance_sphere_r'] ?? '-' ?></td>
                        <td><?= $glasses['distance_cylinder_r'] ?? '-' ?></td>
                        <td><?= $glasses['distance_axis_r'] ?? '-' ?></td>
                        <td><?= $glasses['add_power_r'] ?? '-' ?></td>
                    </tr>
                    <tr>
                        <td>اليسرى</td>
                        <td><?= $glasses['distance_sphere_l'] ?? '-' ?></td>
                        <td><?= $glasses['distance_cylinder_l'] ?? '-' ?></td>
                        <td><?= $glasses['distance_axis_l'] ?? '-' ?></td>
                        <td><?= $glasses['add_power_l'] ?? '-' ?></td>
                    </tr>
                </tbody>
            </table>
            
            <?php if ($glasses['PD_DISTANCE'] || $glasses['PD_NEAR']): ?>
            <div style="margin-top: 10px;">
                <?php if ($glasses['PD_DISTANCE']): ?>
                <strong>المسافة بين البؤبؤين (بعيد):</strong> <?= $glasses['PD_DISTANCE'] ?> mm
                <?php endif; ?>
                <?php if ($glasses['PD_NEAR']): ?>
                <?= $glasses['PD_DISTANCE'] ? ' | ' : '' ?><strong>المسافة بين البؤبؤين (قريب):</strong> <?= $glasses['PD_NEAR'] ?> mm
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($glasses['lens_type']): ?>
            <div style="margin-top: 10px;">
                <strong>نوع العدسة:</strong> 
                <?php 
                $lensTypes = [
                    'Single Vision' => 'رؤية واحدة', 
                    'Bifocal' => 'ثنائية البؤرة', 
                    'Progressive' => 'متدرجة', 
                    'Reading' => 'قراءة'
                ];
                echo $lensTypes[$glasses['lens_type']] ?? $glasses['lens_type'];
                ?>
            </div>
            <?php endif; ?>
            
            <?php if ($glasses['comments']): ?>
            <div style="margin-top: 10px;">
                <strong>ملاحظات:</strong> <?= nl2br(htmlspecialchars($glasses['comments'])) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>توقيع الطبيب</div>
            <div style="font-size: 12px; margin-top: 5px;"><?= htmlspecialchars($appointment['doctor_name']) ?></div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>تاريخ الطباعة</div>
            <div style="font-size: 12px; margin-top: 5px;"><?= date('Y-m-d H:i') ?></div>
        </div>
    </div>

    <div class="footer">
        <p>هذا التقرير تم إنشاؤه تلقائياً بواسطة نظام إدارة العيادة</p>
        <p><?= htmlspecialchars($clinic['website']) ?></p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
