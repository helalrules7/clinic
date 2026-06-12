<?php
/**
 * Public, patient-facing visit-documents page (no login).
 * Rendered by PublicShareController::visitDocuments(). All values are escaped.
 *
 * Vars: $patient, $appointment, $doctorName, $clinic, $meds, $glasses,
 *       $instructions, $labs, $radiology
 *
 * Print layout: the clinic header repeats on every page (table <thead>), and the
 * content is grouped so it paginates cleanly:
 *   Sheet 1 → الوصفة العلاجية (priority) + التعليمات الطبية
 *   Sheet 2 → النظارة الطبية + التحاليل + الأشعة
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmtDate = function ($d) { return $d ? date('Y/m/d', strtotime($d)) : ''; };
$dash = function ($v) { $v = trim((string) $v); return $v === '' ? '—' : $v; };

$clinicName = $clinic['name'] ?? 'مركز رؤية للعيون';
$clinicLogo = $clinic['logo'] ?? '';
$visitDate  = $fmtDate($appointment['date'] ?? '');
$docClean   = preg_replace('/^\s*(?:د|dr)\.?\s*/iu', '', trim((string) ($doctorName ?? '')));

$hasMeds    = !empty($meds);
$hasInstr   = !empty($instructions);
$hasGlasses = !empty($glasses);
$hasLabs    = !empty($labs);
$hasRad     = !empty($radiology);
$group1 = $hasMeds || $hasInstr;          // sheet 1
$group2 = $hasGlasses || $hasLabs || $hasRad; // sheet 2
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تقرير الزيارة — <?= $e($clinicName) ?></title>
    <?php $og = $og ?? []; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= $e($og['site'] ?? $clinicName) ?>">
    <meta property="og:title" content="<?= $e($og['title'] ?? 'تقرير الزيارة') ?>">
    <meta property="og:description" content="<?= $e($og['desc'] ?? 'تقرير زيارتك الطبي') ?>">
    <?php if (!empty($og['image'])): ?>
    <meta property="og:image" content="<?= $e($og['image']) ?>">
    <meta property="og:image:alt" content="<?= $e($clinicName) ?>">
    <?php endif; ?>
    <?php if (!empty($og['url'])): ?><meta property="og:url" content="<?= $e($og['url']) ?>"><?php endif; ?>
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= $e($og['title'] ?? 'تقرير الزيارة') ?>">
    <meta name="twitter:description" content="<?= $e($og['desc'] ?? 'تقرير زيارتك الطبي') ?>">
    <?php if (!empty($og['image'])): ?><meta name="twitter:image" content="<?= $e($og['image']) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#070B14; --bg-2:#0B1220; --card:#131A29; --card-2:#0F1626;
            --text:#F8FAFC; --muted:#94A3B8; --border:#334155;
            --indigo:#6366F1; --indigo-2:#818CF8; --indigo-soft:rgba(99,102,241,0.16);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; background: var(--bg); color: var(--text);
            font-family: "Cairo", "Segoe UI", Tahoma, sans-serif; line-height: 1.7;
            -webkit-text-size-adjust: 100%;
            background-image: radial-gradient(1200px 600px at 50% -10%, rgba(99,102,241,0.18), transparent 60%);
            background-attachment: fixed;
        }
        .wrap { max-width: 820px; margin: 0 auto; padding: 18px 14px 104px; }
        .card { background: var(--card); border: 1px solid var(--border); border-radius: 18px; overflow: hidden; box-shadow: 0 20px 56px rgba(0,0,0,.55); }
        table.sheet { width: 100%; border-collapse: collapse; }
        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #312E81 100%);
            padding: 20px 18px 16px; text-align: center;
            border-bottom: 1px solid rgba(129,140,248,.35);
        }
        .logo-chip { width: 64px; height: 64px; border-radius: 18px; background: #fff; display: inline-flex; align-items: center; justify-content: center; padding: 8px; box-shadow: 0 8px 22px rgba(0,0,0,.35); }
        .logo-chip img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .header h1 { margin: 8px 0 0; font-size: 20px; font-weight: 800; color: #fff; }
        .header .addr { margin-top: 6px; font-size: 12.5px; color: #E0E7FF; opacity: .95; }
        .header .addr .sep { opacity: .5; margin: 0 4px; }
        .report-badge { display: inline-block; margin-top: 10px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25); color: #fff; font-size: 12.5px; font-weight: 700; padding: 4px 15px; border-radius: 30px; }
        .body-cell { padding: 0; }
        /* v12: header (in the table <thead>) must never split across a page boundary */
        .sheet-header { break-inside: avoid; page-break-inside: avoid; }
        .meta { display: flex; flex-wrap: wrap; gap: 8px 24px; padding: 15px 18px; border-bottom: 1px solid var(--border); font-size: 14px; background: var(--card-2); }
        .meta b { color: var(--muted); font-weight: 600; }
        .section { padding: 18px; }
        .section + .section { border-top: 1px solid var(--border); }
        .section h2 { margin: 0 0 14px; font-size: 16px; color: var(--indigo-2); font-weight: 800; display: flex; align-items: center; gap: 9px; }
        .section h2 .tag { background: var(--indigo-soft); color: var(--indigo-2); font-size: 12px; border: 1px solid rgba(129,140,248,.35); border-radius: 20px; padding: 3px 11px; font-weight: 700; }
        .dtable { width: 100%; border-collapse: collapse; font-size: 14px; }
        .dtable th, .dtable td { padding: 10px; text-align: center; border: 1px solid var(--border); }
        .dtable th { background: var(--indigo-soft); color: var(--indigo-2); font-weight: 700; }
        .dtable td { color: var(--text); }
        .dtable td.rx-name, .dtable td.t-name { text-align: right; font-weight: 700; }
        .gl-eye { background: rgba(99,102,241,.10); font-weight: 700; color: var(--indigo-2); }
        .gl-extra { font-size: 13px; color: var(--muted); margin-top: 10px; }
        ul.instr { list-style: none; margin: 0; padding: 0; }
        ul.instr li { padding: 12px 14px; border: 1px solid var(--border); border-radius: 12px; margin-bottom: 9px; background: rgba(255,255,255,.025); }
        ul.instr li b { color: var(--indigo-2); display: block; margin-bottom: 3px; }
        .foot { text-align: center; color: var(--muted); font-size: 12px; padding: 12px 4px 0; }
        .actionbar { position: fixed; inset-block-end: 0; inset-inline: 0; background: var(--bg-2); border-top: 1px solid var(--border); padding: 12px 14px; display: flex; justify-content: center; gap: 10px; box-shadow: 0 -6px 24px rgba(0,0,0,.5); }
        .btn { appearance: none; border: 0; cursor: pointer; color: #fff; font-family: inherit; background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%); font-size: 15px; font-weight: 800; padding: 13px 14px; border-radius: 14px; flex: 1 1 0; max-width: 240px; box-shadow: 0 10px 26px rgba(79,70,229,.45); display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
        .btn.btn-print { background: transparent; color: var(--indigo-2); border: 2px solid var(--indigo-2); box-shadow: none; }
        .btn:active { transform: translateY(1px); }
        .btn[disabled] { opacity: .7; cursor: default; }

        /* Light theme used while rasterizing to PDF (html2pdf) — mirrors @media print */
        body.pdf-mode { background: #fff; color: #111; background-image: none; }
        body.pdf-mode .wrap { padding: 0; max-width: 100%; }
        body.pdf-mode .card { border: 0; box-shadow: none; background: #fff; }
        body.pdf-mode .header { background: #fff; border-bottom: 2px solid #4F46E5; }
        body.pdf-mode .header h1 { color: #312E81; }
        body.pdf-mode .header .addr { color: #444; }
        body.pdf-mode .report-badge { color: #312E81; border-color: #312E81; background: #fff; }
        body.pdf-mode .meta { background: #fff; color: #111; }
        body.pdf-mode .meta b { color: #555; }
        body.pdf-mode .section h2, body.pdf-mode .section h2 .tag, body.pdf-mode .gl-eye, body.pdf-mode .dtable td, body.pdf-mode ul.instr li b { color: #111; }
        body.pdf-mode .dtable th { background: #EEF2FF; color: #312E81; }
        body.pdf-mode ul.instr li { background: #fff; border-color: #ccc; }
        body.pdf-mode .group-2.has-prev { break-before: page; page-break-before: always; }
        body.pdf-mode .section, body.pdf-mode .dtable, body.pdf-mode ul.instr li { break-inside: avoid; page-break-inside: avoid; }
        body.pdf-mode .actionbar { display: none; }

        @media print {
            body { background: #fff !important; color: #111 !important; background-image: none; }
            .wrap { padding: 0; max-width: 100%; }
            .card { border: 0; box-shadow: none; border-radius: 0; background: #fff; }
            /* Repeat the clinic+patient header on EVERY printed page (native print) */
            thead.sheet-head { display: table-header-group; }
            .header { background: #fff !important; border-bottom: 2px solid #4F46E5; padding: 6px 8px 8px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .header h1 { color: #312E81; }
            .header .addr { color: #444; }
            .report-badge { color: #312E81; border-color: #312E81; background: #fff; }
            .meta { background: #fff; color: #111; }
            .meta b { color: #555; }
            .section h2, .section h2 .tag, .gl-eye, .dtable td, ul.instr li b { color: #111 !important; }
            .dtable th { background: #EEF2FF !important; color: #312E81 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            ul.instr li { background: #fff; border-color: #ccc; }
            /* Pagination: keep sections whole; start sheet 2 on a new page */
            .section, .dtable, .dtable tr, ul.instr li { page-break-inside: avoid; break-inside: avoid; }
            .group-2.has-prev { page-break-before: always; break-before: page; }
            .actionbar, .no-print { display: none !important; }
            @page { margin: 12mm 10mm; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <?php
            // v12: the clinic+patient header is a reusable block rendered at the top of EVERY
            // sheet. A table <thead> only repeats under native @media print — it does NOT repeat
            // in the html2pdf (html2canvas) export — so we emit the header per page-broken sheet.
            $renderSheetHeader = function () use ($e, $dash, $clinicLogo, $clinicName, $clinic, $patient, $docClean, $visitDate) {
                ob_start(); ?>
                <div class="sheet-header" id="reportHeader">
                    <div class="header">
                        <?php if (!empty($clinicLogo)): ?>
                            <div class="logo-chip"><img src="<?= $e($clinicLogo) ?>" alt="" onerror="this.parentElement.style.display='none'"></div>
                        <?php endif; ?>
                        <h1><?= $e($clinicName) ?></h1>
                        <?php if (!empty($clinic['address']) || !empty($clinic['phone'])): ?>
                            <div class="addr">
                                <?php if (!empty($clinic['address'])): ?><?= $e($clinic['address']) ?><?php endif; ?>
                                <?php if (!empty($clinic['address']) && !empty($clinic['phone'])): ?><span class="sep">•</span><?php endif; ?>
                                <?php if (!empty($clinic['phone'])): ?>📞 <?= $e($clinic['phone']) ?><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="report-badge">تقرير الزيارة</div>
                    </div>
                    <div class="meta">
                        <span><b>المريض:</b> <?= $e($dash($patient['full_name'] ?? '')) ?></span>
                        <?php if (!empty($patient['age'])): ?><span><b>السن:</b> <?= $e($patient['age']) ?> سنة</span><?php endif; ?>
                        <?php if (!empty($docClean)): ?><span><b>الطبيب المعالج:</b> د. <?= $e($docClean) ?></span><?php endif; ?>
                        <?php if ($visitDate): ?><span><b>تاريخ الزيارة:</b> <?= $e($visitDate) ?></span><?php endif; ?>
                    </div>
                </div>
                <?php return ob_get_clean();
            };
            ?>
            <table class="sheet">
                <thead class="sheet-head"><tr><td style="padding:0;"><?= $renderSheetHeader() ?></td></tr></thead>
                <tbody><tr><td class="body-cell">
            <div class="body-content" id="reportBody">

                        <?php /* Sections flow naturally; each avoids internal page breaks
                                 (break-inside:avoid) so a table is NEVER split across pages.
                                 Glasses / labs / radiology reflow up to fill the page when
                                 earlier sections are absent — no rigid page grouping. */ ?>
                            <?php if ($hasMeds): ?>
                            <div class="section">
                                <h2><span class="tag">Rx</span> الوصفة العلاجية</h2>
                                <table class="dtable">
                                    <thead><tr><th>الدواء</th><th>الجرعة</th><th>التكرار</th><th>المدة</th><th>ملاحظات</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($meds as $m): ?>
                                            <tr>
                                                <td class="rx-name"><?= $e($dash($m['drug_name'] ?? '')) ?></td>
                                                <td><?= $e($dash($m['dose'] ?? '')) ?></td>
                                                <td><?= $e($dash($m['frequency'] ?? '')) ?></td>
                                                <td><?= $e($dash($m['duration'] ?? '')) ?></td>
                                                <td><?= $e($dash($m['notes'] ?? '')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasInstr): ?>
                            <div class="section">
                                <h2><span class="tag">📋</span> التعليمات الطبية</h2>
                                <ul class="instr">
                                    <?php foreach ($instructions as $ins): ?>
                                        <li>
                                            <?php if (!empty($ins['title'])): ?><b><?= $e($ins['title']) ?></b><?php endif; ?>
                                            <?= nl2br($e($ins['body_ar'] ?? '')) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                            <?php if ($hasGlasses): ?>
                            <div class="section">
                                <h2><span class="tag">👓</span> مقاس النظارة الطبية</h2>
                                <table class="dtable">
                                    <thead><tr><th>المسافة</th><th>SPH</th><th>CYL</th><th>AXIS</th></tr></thead>
                                    <tbody>
                                        <tr>
                                            <td class="gl-eye">يمين (R)</td>
                                            <td><?= $e($dash($glasses['distance_sphere_r'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['distance_cylinder_r'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['distance_axis_r'] ?? '')) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="gl-eye">يسار (L)</td>
                                            <td><?= $e($dash($glasses['distance_sphere_l'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['distance_cylinder_l'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['distance_axis_l'] ?? '')) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php $hasNear = !empty($glasses['near_sphere_r']) || !empty($glasses['near_sphere_l']); ?>
                                <?php if ($hasNear): ?>
                                <table class="dtable" style="margin-top:10px;">
                                    <thead><tr><th>القراءة</th><th>SPH</th><th>CYL</th><th>AXIS</th></tr></thead>
                                    <tbody>
                                        <tr>
                                            <td class="gl-eye">يمين (R)</td>
                                            <td><?= $e($dash($glasses['near_sphere_r'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['near_cylinder_r'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['near_axis_r'] ?? '')) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="gl-eye">يسار (L)</td>
                                            <td><?= $e($dash($glasses['near_sphere_l'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['near_cylinder_l'] ?? '')) ?></td>
                                            <td><?= $e($dash($glasses['near_axis_l'] ?? '')) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php endif; ?>
                                <div class="gl-extra">
                                    <?php if (!empty($glasses['PD_DISTANCE'])): ?>PD (مسافة): <?= $e($glasses['PD_DISTANCE']) ?> &nbsp;<?php endif; ?>
                                    <?php if (!empty($glasses['PD_NEAR'])): ?>PD (قراءة): <?= $e($glasses['PD_NEAR']) ?> &nbsp;<?php endif; ?>
                                    <?php if (!empty($glasses['lens_type'])): ?><br>نوع العدسة: <?= $e($glasses['lens_type']) ?><?php endif; ?>
                                    <?php if (!empty($glasses['comments'])): ?><br>ملاحظات: <?= $e($glasses['comments']) ?><?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasLabs): ?>
                            <div class="section">
                                <h2><span class="tag">🧪</span> التحاليل المطلوبة</h2>
                                <table class="dtable">
                                    <thead><tr><th>الفحص</th><th>ملاحظات</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($labs as $lt): ?>
                                            <tr>
                                                <td class="t-name"><?= $e($dash($lt['test_name'] ?? '')) ?></td>
                                                <td><?= $e($dash($lt['notes'] ?? '')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <?php if ($hasRad): ?>
                            <div class="section">
                                <h2><span class="tag">🩻</span> الأشعة والفحوصات</h2>
                                <table class="dtable">
                                    <thead><tr><th>الفحص</th><th>ملاحظات</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($radiology as $rt): ?>
                                            <tr>
                                                <td class="t-name"><?= $e($dash($rt['test_name'] ?? '')) ?></td>
                                                <td><?= $e($dash($rt['notes'] ?? '')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

            </div>
                </td></tr></tbody>
            </table>
        </div>

        <div class="foot">هذا التقرير صادر من <?= $e($clinicName) ?> · للاستفسار يُرجى التواصل مع العيادة</div>
    </div>

    <div class="actionbar no-print">
        <button type="button" class="btn btn-download" id="btnDownload">⬇️ تحميل PDF</button>
        <button type="button" class="btn btn-print" onclick="window.print()">🖨️ طباعة</button>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        (function () {
            var btn = document.getElementById('btnDownload');
            var header = document.getElementById('reportHeader');
            var body = document.getElementById('reportBody');
            // Native print repeats the header automatically via the table <thead>
            // (display: table-header-group) — no JS needed there.

            // ── Download PDF (html2pdf): sections reflow and a table is NEVER cut across
            //    pages (pagebreak avoid-all); the header is rasterised once then stamped
            //    on EVERY page (image → keeps Arabic intact, unlike jsPDF.text). ──
            if (!btn) return;
            btn.addEventListener('click', function () {
                if (typeof html2pdf === 'undefined') { window.print(); return; }
                var label = btn.textContent;
                document.body.classList.add('pdf-mode');
                btn.disabled = true; btn.textContent = '... جاري التحضير';
                function done() { document.body.classList.remove('pdf-mode'); btn.disabled = false; btn.textContent = label; }
                var sideMm = 6, topMm = 6, gapMm = 4, imgWmm = 210 - sideMm * 2;
                var pagebreak = { mode: ['css', 'legacy', 'avoid-all'] };

                // Fallback when html2canvas isn't exposed → render the whole card (single
                // top header) but sections still avoid being cut.
                if (typeof html2canvas === 'undefined') {
                    html2pdf().set({
                        margin: [topMm, sideMm, 10, sideMm], filename: 'visit-report.pdf',
                        image: { type: 'jpeg', quality: 0.96 },
                        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }, pagebreak: pagebreak
                    }).from(document.querySelector('.card')).save().then(done).catch(function () { done(); window.print(); });
                    return;
                }

                html2canvas(header, { scale: 2, useCORS: true, backgroundColor: '#ffffff' }).then(function (hc) {
                    var headerImg = hc.toDataURL('image/jpeg', 0.96);
                    var headerHmm = (hc.height / hc.width) * imgWmm;
                    var reserveTop = topMm + headerHmm + gapMm;
                    return html2pdf().set({
                        margin: [reserveTop, sideMm, 10, sideMm],
                        filename: 'visit-report.pdf',
                        image: { type: 'jpeg', quality: 0.96 },
                        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                        pagebreak: pagebreak
                    }).from(body).toPdf().get('pdf').then(function (pdf) {
                        var n = pdf.internal.getNumberOfPages();
                        for (var i = 1; i <= n; i++) {
                            pdf.setPage(i);
                            pdf.addImage(headerImg, 'JPEG', sideMm, topMm, imgWmm, headerHmm);
                        }
                    }).save();
                }).then(done).catch(function () { done(); window.print(); });
            });
        })();
    </script>
</body>
</html>
