<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الحجز - Booking Details #<?= $booking['id'] ?></title>
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
            width: 21cm;
            margin: 0 auto;
            padding: 0.7cm;
        }
        
        .booking-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
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
        
        .booking-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #2c3e50;
            border: 2px solid #27ae60;
            padding: 8px;
            border-radius: 5px;
            background: #f0fff4;
        }
        
        .booking-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        
        .booking-info, .patient-info {
            flex: 1;
        }
        
        .booking-info h3, .patient-info h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px solid #27ae60;
            padding-bottom: 3px;
        }
        
        .booking-info p, .patient-info p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .patient-info {
            text-align: left;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-scheduled {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-checkedin {
            background: #d4edda;
            color: #155724;
        }
        
        .status-completed {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .visit-type-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .type-new {
            background: #d4edda;
            color: #155724;
        }
        
        .type-followup {
            background: #fff3cd;
            color: #856404;
        }
        
        .type-procedure {
            background: #f8d7da;
            color: #721c24;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 2px solid #ddd;
        }
        
        .payments-table th {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: right;
            font-weight: 600;
            font-size: 10px;
        }
        
        .payments-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        
        .payments-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .payments-table tr:hover {
            background: #e9ecef;
        }
        
        .payment-amount {
            text-align: center;
            font-weight: 600;
        }
        
        .payment-method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .method-cash {
            background: #d4edda;
            color: #155724;
        }
        
        .method-card {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .method-wallet {
            background: #fff3cd;
            color: #856404;
        }
        
        .method-transfer {
            background: #f8d7da;
            color: #721c24;
        }
        
        .totals-section {
            margin: 15px 0;
            border: 2px solid #27ae60;
            padding: 10px;
            border-radius: 5px;
            background: #f0fff4;
        }
        
        .totals-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #d4edda;
            font-size: 10px;
        }
        
        .total-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 12px;
            color: #27ae60;
        }
        
        .total-label {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .total-value {
            color: #27ae60;
            font-weight: 700;
        }
        
        .related-bookings {
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .related-bookings h3 {
            background: #2c3e50;
            color: white;
            padding: 8px;
            margin: 0;
            font-size: 12px;
        }
        
        .related-bookings table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .related-bookings th {
            background: #f8f9fa;
            color: #2c3e50;
            padding: 6px;
            text-align: right;
            font-weight: 600;
            font-size: 9px;
            border-bottom: 1px solid #ddd;
        }
        
        .related-bookings td {
            padding: 4px;
            border-bottom: 1px solid #eee;
            font-size: 8px;
        }
        
        .related-bookings tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .footer-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .clinic-stamp {
            text-align: center;
            flex: 1;
        }
        
        .stamp-box {
            width: 80px;
            height: 80px;
            border: 3px solid #27ae60;
            border-radius: 50%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #27ae60;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            line-height: 1.1;
            background: white;
        }
        
        .terms-section {
            flex: 2;
            text-align: center;
        }
        
        .terms-title {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 6px;
            font-size: 10px;
        }
        
        .terms-text {
            color: #666;
            font-size: 8px;
            line-height: 1.3;
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
            font-size: 10px;
        }
        
        .booking-number {
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
            
            .booking-header,
            .booking-details,
            .payments-table,
            .totals-section,
            .related-bookings {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="booking-number">Booking #<?= $booking['id'] ?></div>
    <div class="watermark">
        <img src="<?= htmlspecialchars($clinic['logo_watermark']) ?>" alt="Watermark">
    </div>
    
    <!-- Header -->
    <div class="booking-header">
        <div class="logo-section">
            <img src="<?= htmlspecialchars($clinic['logo_print']) ?>" alt="<?= htmlspecialchars($clinic['name']) ?> Logo" class="clinic-logo">
            <div class="clinic-name"><?= htmlspecialchars($clinic['name']) ?></div>
            <div class="clinic-name-ar"><?= htmlspecialchars($clinic['name_arabic']) ?></div>
        </div>
        <div class="clinic-info"><?= htmlspecialchars($clinic['address']) ?></div>
        <div class="clinic-info">هاتف: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
    </div>
    
    <!-- Title -->
    <div class="booking-title">تفاصيل الحجز - Booking Details</div>
    
    <!-- Booking and Patient Info -->
    <div class="booking-details">
        <div class="booking-info">
            <h3>تفاصيل الحجز - Booking Information</h3>
            <p><strong>رقم الحجز:</strong> #<?= $booking['id'] ?></p>
            <p><strong>تاريخ الموعد:</strong> <?= date('Y-m-d', strtotime($booking['date'])) ?></p>
            <p><strong>وقت الموعد:</strong> <?= date('H:i', strtotime($booking['start_time'])) ?> - <?= date('H:i', strtotime($booking['end_time'])) ?></p>
            <p><strong>نوع الزيارة:</strong> 
                <span class="visit-type-badge type-<?= strtolower($booking['visit_type']) ?>">
                    <?= $booking['visit_type'] ?>
                </span>
            </p>
            <p><strong>الحالة:</strong> 
                <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                    <?= $booking['status'] ?>
                </span>
            </p>
            <p><strong>المصدر:</strong> <?= $booking['source'] ?></p>
            <?php if ($booking['notes']): ?>
                <p><strong>ملاحظات:</strong> <?= htmlspecialchars($booking['notes']) ?></p>
            <?php endif; ?>
        </div>
        
        <div class="patient-info">
            <h3>بيانات المريض - Patient Information</h3>
            <p><strong>الاسم:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
            <p><strong>رقم الهاتف:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><strong>تاريخ الميلاد:</strong> <?= date('Y-m-d', strtotime($patient['dob'])) ?></p>
            <p><strong>العنوان:</strong> <?= htmlspecialchars($patient['address'] ?? 'N/A') ?></p>
            <p><strong>رقم الهوية:</strong> <?= htmlspecialchars($patient['national_id'] ?? 'N/A') ?></p>
        </div>
    </div>
    
    <!-- Doctor Information -->
    <div class="booking-details">
        <div class="booking-info">
            <h3>بيانات الطبيب - Doctor Information</h3>
            <p><strong>اسم الطبيب:</strong> <?= htmlspecialchars($doctor['name']) ?></p>
            <p><strong>البريد الإلكتروني:</strong> <?= htmlspecialchars($doctor['email']) ?></p>
        </div>
        
        <div class="patient-info">
            <h3>تفاصيل التكلفة - Cost Details</h3>
            <p><strong>تكلفة الزيارة:</strong> <?= number_format($booking['visit_cost'], 2) ?> EGP</p>
            <p><strong>المبلغ المدفوع:</strong> <?= number_format($booking['total_paid'], 2) ?> EGP</p>
            <p><strong>المتبقي:</strong> <?= number_format($booking['visit_cost'] - $booking['total_paid'], 2) ?> EGP</p>
        </div>
    </div>
    
    <!-- Payments Table -->
    <?php if (!empty($payments)): ?>
    <table class="payments-table">
        <thead>
            <tr>
                <th>التاريخ - Date</th>
                <th>نوع الدفع - Payment Type</th>
                <th>طريقة الدفع - Method</th>
                <th>المبلغ - Amount</th>
                <th>المستلم من - Received By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?></td>
                    <td><?= $payment['type'] ?></td>
                    <td>
                        <span class="payment-method method-<?= strtolower($payment['method']) ?>">
                            <?= $payment['method'] ?>
                        </span>
                    </td>
                    <td class="payment-amount"><?= number_format($payment['amount'], 2) ?> EGP</td>
                    <td><?= htmlspecialchars($payment['received_by_name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    
    <!-- Totals Section -->
    <div class="totals-section">
        <div class="totals-grid">
            <div>
                <div class="total-row">
                    <span class="total-label">تكلفة الزيارة - Visit Cost:</span>
                    <span class="total-value"><?= number_format($booking['visit_cost'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">المبلغ المدفوع - Paid Amount:</span>
                    <span class="total-value"><?= number_format($booking['total_paid'], 2) ?> EGP</span>
                </div>
            </div>
            <div>
                <div class="total-row">
                    <span class="total-label">المتبقي - Balance:</span>
                    <span class="total-value"><?= number_format($booking['visit_cost'] - $booking['total_paid'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">حالة الدفع - Payment Status:</span>
                    <span class="total-value">
                        <?php
                        $balance = $booking['visit_cost'] - $booking['total_paid'];
                        if ($balance <= 0) {
                            echo 'مدفوع بالكامل - Fully Paid';
                        } elseif ($booking['total_paid'] > 0) {
                            echo 'دفع جزئي - Partial Payment';
                        } else {
                            echo 'غير مدفوع - Unpaid';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Bookings -->
    <?php if (!empty($relatedBookings)): ?>
    <div class="related-bookings">
        <h3>الحجوزات السابقة - Previous Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>التاريخ - Date</th>
                    <th>الوقت - Time</th>
                    <th>النوع - Type</th>
                    <th>الحالة - Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($relatedBookings as $relatedBooking): ?>
                    <tr <?= $relatedBooking['id'] == $booking['id'] ? 'style="background: #e3f2fd;"' : '' ?>>
                        <td><?= date('Y-m-d', strtotime($relatedBooking['date'])) ?></td>
                        <td><?= date('H:i', strtotime($relatedBooking['start_time'])) ?></td>
                        <td><?= $relatedBooking['visit_type'] ?></td>
                        <td><?= $relatedBooking['status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- Footer -->
    <div class="footer-section">
        <div class="clinic-stamp">
            <div class="stamp-box">
                <?= $clinic['name'] ?>
            </div>
        </div>
        
        <div class="terms-section">
            <div class="terms-title">الشروط والأحكام - Terms & Conditions</div>
            <div class="terms-text">
                • يجب الحضور في الموعد المحدد
                <br>• Please arrive on time for your appointment
                <br>• للاستفسارات، يرجى الاتصال بنا
                <br>• For inquiries, please contact us
            </div>
        </div>
        
        <div class="date-section">
            <div class="date-label">تاريخ الطباعة - Print Date</div>
            <div class="date-value"><?= date('d/m/Y H:i') ?></div>
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
        function printBooking() {
            window.print();
        }
    </script>
</body>
</html>
