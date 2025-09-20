<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></title>
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
        
        .invoice-header {
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
        
        .invoice-title {
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
        
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        
        .invoice-info, .patient-info {
            flex: 1;
        }
        
        .invoice-info h3, .patient-info h3 {
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 12px;
            border-bottom: 1px solid #27ae60;
            padding-bottom: 3px;
        }
        
        .invoice-info p, .patient-info p {
            margin: 3px 0;
            font-size: 10px;
        }
        
        .patient-info {
            text-align: left;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            border: 2px solid #ddd;
        }
        
        .invoice-table th {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: right;
            font-weight: 600;
            font-size: 10px;
        }
        
        .invoice-table td {
            padding: 6px;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        
        .invoice-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .invoice-table tr:hover {
            background: #e9ecef;
        }
        
        .item-description {
            text-align: right;
            font-weight: 500;
        }
        
        .item-amount {
            text-align: center;
            font-weight: 600;
        }
        
        .item-date {
            text-align: center;
            color: #666;
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
        
        .payment-status {
            margin: 15px 0;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        .status-partial {
            background: #d1ecf1;
            color: #0c5460;
            border: 2px solid #bee5eb;
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
        
        .invoice-number {
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
        
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0.4cm;
            }
            
            .invoice-header,
            .invoice-details,
            .invoice-table,
            .totals-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-number">Invoice #<?= $invoice['invoice_no'] ?></div>
    <div class="watermark">
        <img src="<?= htmlspecialchars($clinic['logo_watermark']) ?>" alt="Watermark">
    </div>
    
    <!-- Header -->
    <div class="invoice-header">
        <div class="logo-section">
            <img src="<?= htmlspecialchars($clinic['logo_print']) ?>" alt="<?= htmlspecialchars($clinic['name']) ?> Logo" class="clinic-logo">
            <div class="clinic-name"><?= htmlspecialchars($clinic['name']) ?></div>
            <div class="clinic-name-ar"><?= htmlspecialchars($clinic['name_arabic']) ?></div>
        </div>
        <div class="clinic-info"><?= htmlspecialchars($clinic['address']) ?></div>
        <div class="clinic-info">هاتف: <?= htmlspecialchars($clinic['phone']) ?> | <?= htmlspecialchars($clinic['email']) ?></div>
    </div>
    
    <!-- Title -->
    <div class="invoice-title">فاتورة - Invoice</div>
    
    <!-- Invoice and Patient Info -->
    <div class="invoice-details">
        <div class="invoice-info">
            <h3>تفاصيل الفاتورة - Invoice Details</h3>
            <p><strong>رقم الفاتورة:</strong> <?= $invoice['invoice_no'] ?></p>
            <p><strong>تاريخ الفاتورة:</strong> <?= date('d/m/Y', strtotime($invoice['created_at'])) ?></p>
            <p><strong>تاريخ الاستحقاق:</strong> <?= date('d/m/Y', strtotime($invoice['due_date'] ?? $invoice['created_at'])) ?></p>
            <p><strong>الحالة:</strong> <?= $invoice['status'] ?></p>
        </div>
        
        <div class="patient-info">
            <h3>بيانات المريض - Patient Information</h3>
            <p><strong>الاسم:</strong> <?= $patient['first_name'] . ' ' . $patient['last_name'] ?></p>
            <p><strong>رقم الهاتف:</strong> <?= $patient['phone'] ?></p>
            <p><strong>العنوان:</strong> <?= $patient['address'] ?? 'N/A' ?></p>
            <p><strong>رقم الهوية:</strong> <?= $patient['national_id'] ?? 'N/A' ?></p>
        </div>
    </div>
    
    <!-- Invoice Items Table -->
    <table class="invoice-table">
        <thead>
            <tr>
                <th>التاريخ - Date</th>
                <th>الوصف - Description</th>
                <th>طريقة الدفع - Payment Method</th>
                <th>المبلغ - Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td class="item-date"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                    <td class="item-description">
                        <?= $item['type'] ?>
                        <?php if ($item['appointment_id']): ?>
                            - Appointment #<?= $item['appointment_id'] ?>
                        <?php endif; ?>
                        <?php if ($item['discount_amount'] > 0): ?>
                            <br><small style="color: #e74c3c;">Discount: <?= $item['discount_amount'] ?> EGP</small>
                        <?php endif; ?>
                        <?php if ($item['is_exempt']): ?>
                            <br><small style="color: #e74c3c;">Exempt</small>
                        <?php endif; ?>
                    </td>
                    <td class="item-amount">
                        <span class="payment-method method-<?= strtolower($item['method']) ?>">
                            <?= $item['method'] ?>
                        </span>
                    </td>
                    <td class="item-amount"><?= number_format($item['amount'], 2) ?> EGP</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Totals Section -->
    <div class="totals-section">
        <div class="totals-grid">
            <div>
                <div class="total-row">
                    <span class="total-label">إجمالي المدفوعات - Total Payments:</span>
                    <span class="total-value"><?= number_format($invoice['total_payments'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">إجمالي الخصومات - Total Discounts:</span>
                    <span class="total-value"><?= number_format($invoice['total_discounts'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">إجمالي الإعفاءات - Total Exemptions:</span>
                    <span class="total-value"><?= number_format($invoice['total_exemptions'], 2) ?> EGP</span>
                </div>
            </div>
            <div>
                <div class="total-row">
                    <span class="total-label">إجمالي الفاتورة - Invoice Total:</span>
                    <span class="total-value"><?= number_format($invoice['total_amount'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">المدفوع - Paid:</span>
                    <span class="total-value"><?= number_format($invoice['paid_amount'], 2) ?> EGP</span>
                </div>
                <div class="total-row">
                    <span class="total-label">المتبقي - Balance:</span>
                    <span class="total-value"><?= number_format($invoice['balance'], 2) ?> EGP</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Status -->
    <div class="payment-status status-<?= strtolower($invoice['status']) ?>">
        <?php
        $statusText = '';
        $statusClass = '';
        switch ($invoice['status']) {
            case 'Paid':
                $statusText = 'تم الدفع بالكامل - Fully Paid';
                break;
            case 'Pending':
                $statusText = 'في انتظار الدفع - Payment Pending';
                break;
            case 'Partial':
                $statusText = 'دفع جزئي - Partial Payment';
                break;
            default:
                $statusText = $invoice['status'];
        }
        ?>
        <?= $statusText ?>
    </div>
    
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
                • يجب سداد جميع المبالغ المستحقة في المواعيد المحددة
                <br>• All outstanding amounts must be paid by the due dates
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
        function printInvoice() {
            window.print();
        }
    </script>
</body>
</html>
