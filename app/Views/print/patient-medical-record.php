<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Integrated Medical Report — <?= htmlspecialchars(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .pmr-no-print { display: none !important; }
            .pmr-preview-wrap { border: none !important; box-shadow: none !important; }
            #pmrPreview { height: 100vh !important; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', 'Cairo', sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .pmr-shell { max-width: 1100px; margin: 0 auto; padding: 16px; }

        .pmr-header {
            text-align: center;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 12px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }

        .pmr-logo { width: 52px; height: 52px; object-fit: contain; margin-bottom: 8px; }

        .pmr-clinic-name { font-size: 17px; font-weight: 700; color: #312e81; }
        .pmr-clinic-name-ar { font-size: 14px; font-weight: 600; color: #64748b; margin-top: 2px; }
        .pmr-clinic-meta { font-size: 11px; color: #64748b; margin-top: 6px; }

        .pmr-doc-title {
            margin-top: 14px;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            border: 2px solid #6366f1;
            border-radius: 8px;
            padding: 8px 12px;
            display: inline-block;
        }

        .pmr-patient-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 24px;
            justify-content: center;
            margin-top: 12px;
            font-size: 12px;
            color: #334155;
        }

        .pmr-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }

        .pmr-status {
            font-size: 12px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pmr-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #c7d2fe;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: pmr-spin .8s linear infinite;
        }

        @keyframes pmr-spin { to { transform: rotate(360deg); } }

        .pmr-actions { display: flex; flex-wrap: wrap; gap: 8px; }

        .pmr-btn {
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pmr-btn-primary { background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; }
        .pmr-btn-secondary { background: #e2e8f0; color: #334155; }
        .pmr-btn:disabled { opacity: 0.55; cursor: not-allowed; }

        .pmr-preview-wrap {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
            min-height: 70vh;
        }

        #pmrPreview {
            display: none;
            width: 100%;
            height: calc(100vh - 220px);
            min-height: 480px;
            border: 0;
        }

        .pmr-placeholder {
            padding: 48px 24px;
            text-align: center;
            color: #64748b;
        }

        .pmr-placeholder i { font-size: 2.5rem; color: #a5b4fc; display: block; margin-bottom: 12px; }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="pmr-shell">
        <header class="pmr-header">
            <img class="pmr-logo" src="<?= htmlspecialchars($clinic['logo_print'] ?? '/assets/images/Light.png') ?>" alt="Clinic logo">
            <div class="pmr-clinic-name"><?= htmlspecialchars($clinic['name'] ?? 'Medical Clinic') ?></div>
            <?php if (!empty($clinic['name_arabic'])): ?>
                <div class="pmr-clinic-name-ar" dir="rtl"><?= htmlspecialchars($clinic['name_arabic']) ?></div>
            <?php endif; ?>
            <div class="pmr-clinic-meta">
                <?= htmlspecialchars($clinic['phone'] ?? '') ?>
                <?php if (!empty($clinic['address'])): ?> · <?= htmlspecialchars($clinic['address']) ?><?php endif; ?>
            </div>
            <div class="pmr-doc-title"><i class="bi bi-file-earmark-medical me-1"></i> Complete Integrated Medical Report</div>
            <div class="pmr-patient-bar">
                <span><strong>Patient:</strong> <?= htmlspecialchars(trim(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? ''))) ?></span>
                <?php if (!empty($patient['phone'])): ?><span><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></span><?php endif; ?>
                <?php if (!empty($patient['dob'])): ?><span><strong>DOB:</strong> <?= htmlspecialchars($patient['dob']) ?></span><?php endif; ?>
                <?php if (!empty($patient['national_id'])): ?><span><strong>ID:</strong> <?= htmlspecialchars($patient['national_id']) ?></span><?php endif; ?>
            </div>
        </header>

        <div class="pmr-toolbar pmr-no-print">
            <div class="pmr-status" id="pmrStatus">
                <span class="pmr-spinner" id="pmrSpinner"></span>
                <span id="pmrStatusText">Preparing complete integrated report…</span>
            </div>
            <div class="pmr-actions">
                <button type="button" class="pmr-btn pmr-btn-primary" id="pmrPrintBtn" disabled>
                    <i class="bi bi-printer"></i> Print PDF
                </button>
                <button type="button" class="pmr-btn pmr-btn-secondary" id="pmrDownloadBtn" disabled>
                    <i class="bi bi-download"></i> Download
                </button>
                <button type="button" class="pmr-btn pmr-btn-secondary" onclick="window.close()">
                    <i class="bi bi-x-lg"></i> Close
                </button>
            </div>
        </div>

        <div class="pmr-preview-wrap">
            <div class="pmr-placeholder" id="pmrPlaceholder">
                <i class="bi bi-file-earmark-pdf"></i>
                <p>Building your complete integrated medical report…</p>
                <p style="font-size:11px;margin-top:6px;">Full chronology, charts, medications, labs, board notes, and images — same document as Export PDF.</p>
            </div>
            <iframe id="pmrPreview" title="Medical record PDF preview"></iframe>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="/app/Views/doctor/assets/js/patient-medical-record-pdf.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/patient-medical-record-pdf.js') ? filemtime(__DIR__ . '/../doctor/assets/js/patient-medical-record-pdf.js') : time() ?>"></script>
    <script>
    (function () {
        var PATIENT_ID = <?= (int) $patientId ?>;
        var statusText = document.getElementById('pmrStatusText');
        var spinner = document.getElementById('pmrSpinner');
        var iframe = document.getElementById('pmrPreview');
        var placeholder = document.getElementById('pmrPlaceholder');
        var printBtn = document.getElementById('pmrPrintBtn');
        var downloadBtn = document.getElementById('pmrDownloadBtn');
        var lastResult = null;

        function setReady() {
            if (spinner) spinner.style.display = 'none';
            printBtn.disabled = false;
            downloadBtn.disabled = false;
            if (placeholder) placeholder.style.display = 'none';
        }

        function setError(msg) {
            if (spinner) spinner.style.display = 'none';
            if (statusText) statusText.textContent = msg;
            if (placeholder) {
                placeholder.innerHTML = '<i class="bi bi-exclamation-triangle" style="color:#f59e0b"></i><p>' + msg + '</p>';
            }
        }

        printBtn.addEventListener('click', function () {
            if (!iframe || !iframe.contentWindow) return;
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                alert('Could not open print dialog. Use Download instead.');
            }
        });

        downloadBtn.addEventListener('click', function () {
            if (!lastResult || !lastResult.blobUrl) return;
            var a = document.createElement('a');
            a.href = lastResult.blobUrl;
            a.download = lastResult.filename || 'medical-record.pdf';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });

        if (typeof window.printPatientMedicalRecordPDF !== 'function') {
            setError('PDF module not loaded. Please refresh.');
            return;
        }

        window.printPatientMedicalRecordPDF(PATIENT_ID, {
            statusEl: statusText,
            iframe: iframe,
            autoPrint: <?= isset($_GET['autoprint']) && $_GET['autoprint'] === '0' ? 'false' : 'true' ?>
        }).then(function (result) {
            lastResult = result;
            setReady();
        }).catch(function (err) {
            console.error(err);
            setError(err.message || 'Failed to build medical record PDF.');
        });
    })();
    </script>
</body>
</html>
