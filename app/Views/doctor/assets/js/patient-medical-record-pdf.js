/* =====================================================================
   Patient Medical Record — PDF export (v11 §2.17)
   jsPDF + Chart.js · multi-volume · clinic watermark every page
   ===================================================================== */
(function (global) {
    'use strict';

    var VISITS_PER_PART = 25;
    var MARGIN = 14;
    var FOOTER_H = 12;
    var HEADER_H = 18;
    var COLORS = {
        primary: [79, 70, 229],
        muted: [100, 116, 139],
        border: [226, 232, 240],
        headerBg: [51, 51, 51],
    };

    var ARABIC_RE = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
    var ARABIC_FONT_URL = '/assets/fonts/Amiri-Regular.ttf';
    var _arabicFontReady = null;

    function containsArabic(s) {
        return ARABIC_RE.test(String(s || ''));
    }

    function mmToPx(mm) { return mm * 3.7795275591; }
    function ptToMm(pt) { return pt * 0.3527777778; }

    function ensureArabicFont() {
        if (_arabicFontReady) return _arabicFontReady;
        _arabicFontReady = (async function () {
            try {
                var face = new FontFace('PMR-Amiri', "url('" + ARABIC_FONT_URL + "')");
                await face.load();
                document.fonts.add(face);
            } catch (e) {
                console.warn('PMR: could not load Amiri font', e);
            }
            return true;
        })();
        return _arabicFontReady;
    }

    function wrapArabicLines(ctx, text, maxWidthPx) {
        var words = String(text).split(/\s+/).filter(Boolean);
        var lines = [];
        var line = '';
        words.forEach(function (w) {
            var test = line ? line + ' ' + w : w;
            if (ctx.measureText(test).width > maxWidthPx && line) {
                lines.push(line);
                line = w;
            } else {
                line = test;
            }
        });
        if (line) lines.push(line);
        return lines.length ? lines : [String(text)];
    }

    function renderArabicTextBlocks(text, fontSizePt, bold, maxWidthMm) {
        var fontSizePx = fontSizePt * 1.333;
        var maxWidthPx = mmToPx(maxWidthMm);
        var font = (bold ? 'bold ' : '') + fontSizePx + 'px PMR-Amiri, Amiri, "Noto Sans Arabic", sans-serif';
        var measure = document.createElement('canvas').getContext('2d');
        measure.font = font;
        measure.direction = 'rtl';
        var lines = wrapArabicLines(measure, text, maxWidthPx);
        var lineHeightMm = ptToMm(fontSizePt) * 1.38;
        var blocks = [];

        lines.forEach(function (line) {
            var wPx = Math.min(Math.ceil(measure.measureText(line).width) + 6, maxWidthPx);
            var hPx = Math.ceil(fontSizePx * 1.55);
            var c = document.createElement('canvas');
            c.width = wPx * 2;
            c.height = hPx * 2;
            var ctx = c.getContext('2d');
            ctx.scale(2, 2);
            ctx.font = font;
            ctx.fillStyle = '#111827';
            ctx.direction = 'rtl';
            ctx.textAlign = 'right';
            ctx.textBaseline = 'alphabetic';
            ctx.fillText(line, wPx - 3, fontSizePx * 1.15);
            blocks.push({
                dataUrl: c.toDataURL('image/png'),
                w: wPx / 3.7795275591,
                h: lineHeightMm,
            });
        });
        return blocks;
    }

    function fmtDate(d) {
        if (!d) return '—';
        try {
            return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        } catch (_) {
            return String(d);
        }
    }

    function formatGlassesRx(g) {
        var od = 'OD SPH ' + (g.distance_sphere_r || '—') + ', CYL ' + (g.distance_cylinder_r || '—') + ', AXIS ' + (g.distance_axis_r || '—');
        var os = 'OS SPH ' + (g.distance_sphere_l || '—') + ', CYL ' + (g.distance_cylinder_l || '—') + ', AXIS ' + (g.distance_axis_l || '—');
        var line = 'Glasses Rx — ' + od + ' | ' + os;
        if (g.near_sphere_r || g.near_sphere_l) {
            line += ' · Near OD ' + (g.near_sphere_r || '—') + ' / OS ' + (g.near_sphere_l || '—');
        }
        if (g.lens_type) line += ' · Lens: ' + g.lens_type;
        if (g.notes) line += ' · ' + g.notes;
        return line;
    }

    function countVisitAttachments(appointments) {
        var images = 0;
        var files = 0;
        (appointments || []).forEach(function (appt) {
            (appt.attachments || []).forEach(function (att) {
                if (att.is_image) images += 1;
                else files += 1;
            });
        });
        return { images: images, files: files };
    }

    function fmtDateTime(d) {
        if (!d) return '—';
        try {
            return new Date(d).toLocaleString('en-GB', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true,
            });
        } catch (_) {
            return String(d);
        }
    }

    function resolveAssetUrl(url) {
        if (!url) return '';
        if (url.indexOf('http') === 0 || url.indexOf('data:') === 0) return url;
        return window.location.origin + (url[0] === '/' ? url : '/' + url);
    }

    function dataUrlFormat(dataUrl) {
        if (!dataUrl) return 'JPEG';
        if (dataUrl.indexOf('image/png') !== -1) return 'PNG';
        if (dataUrl.indexOf('image/gif') !== -1) return 'GIF';
        return 'JPEG';
    }

    /** Fetch via authenticated API (same pattern as viewPatientAttachment in patient.js). */
    function loadImageDataUrl(url) {
        url = resolveAssetUrl(url);
        if (!url) return Promise.resolve(null);
        return fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                if (!res.ok) return null;
                return res.blob();
            })
            .then(function (blob) {
                if (!blob || !blob.size) return null;
                return new Promise(function (resolve) {
                    var reader = new FileReader();
                    reader.onload = function () { resolve(reader.result); };
                    reader.onerror = function () { resolve(null); };
                    reader.readAsDataURL(blob);
                });
            })
            .catch(function () { return null; });
    }

    function PdfBuilder(record, logoDataUrl) {
        var jsPDF = global.jspdf.jsPDF;
        this.pdf = new jsPDF('p', 'mm', 'a4');
        this.record = record;
        this.clinic = record.clinic || {};
        this.patient = record.patient || {};
        this.logoDataUrl = logoDataUrl;
        this.pageW = this.pdf.internal.pageSize.getWidth();
        this.pageH = this.pdf.internal.pageSize.getHeight();
        this.contentW = this.pageW - MARGIN * 2;
        this.y = MARGIN;
        this.pageNum = 1;
        this.totalPagesEstimate = 1;
        this.watermarkName = this.clinic.name || 'Medical Clinic';
    }

    PdfBuilder.prototype.applyPageChrome = function () {
        var pdf = this.pdf;
        var pageW = this.pageW;
        var pageH = this.pageH;

        pdf.setTextColor(200, 200, 210);
        pdf.setFontSize(28);
        pdf.setFont('helvetica', 'bold');
        if (typeof pdf.saveGraphicsState === 'function') pdf.saveGraphicsState();
        try {
            pdf.text(this.watermarkName, pageW / 2, pageH / 2, {
                align: 'center',
                angle: 35,
            });
        } catch (_) {
            pdf.text(this.watermarkName, pageW / 2, pageH / 2, { align: 'center' });
        }
        if (typeof pdf.restoreGraphicsState === 'function') pdf.restoreGraphicsState();

        if (this.logoDataUrl && this.pageNum === 1) {
            try {
                pdf.addImage(this.logoDataUrl, 'PNG', MARGIN, 8, 22, 12);
            } catch (_) {}
        }

        pdf.setDrawColor.apply(pdf, COLORS.border);
        pdf.setLineWidth(0.3);
        pdf.line(MARGIN, pageH - FOOTER_H, pageW - MARGIN, pageH - FOOTER_H);

        pdf.setFontSize(7);
        pdf.setTextColor.apply(pdf, COLORS.muted);
        pdf.setFont('helvetica', 'normal');
        var footer = 'Page ' + this.pageNum + ' · ' + this.watermarkName + ' · Exported ' + fmtDateTime(this.record.meta.exported_at);
        pdf.text(footer, pageW / 2, pageH - 5, { align: 'center' });
        pdf.setTextColor(0, 0, 0);
    };

    PdfBuilder.prototype._refreshFooter = function () {
        this.applyPageChrome();
    };

    PdfBuilder.prototype.newPage = function () {
        this.pdf.addPage();
        this.pageNum++;
        this.y = MARGIN + HEADER_H;
        this._refreshFooter();
    };

    PdfBuilder.prototype.checkBreak = function (need) {
        if (this.y + need > this.pageH - FOOTER_H - 4) {
            this.newPage();
            return true;
        }
        return false;
    };

    PdfBuilder.prototype.sectionTitle = function (title) {
        this.checkBreak(14);
        this.pdf.setFillColor.apply(this.pdf, COLORS.primary);
        this.pdf.rect(MARGIN, this.y, this.contentW, 8, 'F');
        this.pdf.setTextColor(255, 255, 255);
        this.pdf.setFontSize(11);
        this.pdf.setFont('helvetica', 'bold');
        this.pdf.text(title, MARGIN + 3, this.y + 5.5);
        this.pdf.setTextColor(0, 0, 0);
        this.y += 12;
    };

    PdfBuilder.prototype.printText = function (text, opts) {
        opts = opts || {};
        var size = opts.size || 9;
        var bold = !!opts.bold;
        var maxW = opts.maxWidth != null ? opts.maxWidth : this.contentW;
        var x = MARGIN + (opts.indent || 0);
        text = String(text || '—');

        if (!containsArabic(text)) {
            this.pdf.setFontSize(size);
            this.pdf.setFont('helvetica', bold ? 'bold' : 'normal');
            var lines = this.pdf.splitTextToSize(text, maxW);
            var lh = ptToMm(size) * 1.25;
            lines.forEach(function (line) {
                this.checkBreak(lh);
                this.pdf.text(line, x, this.y);
                this.y += lh;
            }.bind(this));
            return;
        }

        renderArabicTextBlocks(text, size, bold, maxW).forEach(function (b) {
            this.checkBreak(b.h + 1);
            try {
                this.pdf.addImage(b.dataUrl, 'PNG', x, this.y - b.h + 2, Math.min(b.w, maxW), b.h);
            } catch (_) {}
            this.y += b.h + 1.2;
        }.bind(this));
    };

    PdfBuilder.prototype.subTitle = function (text) {
        this.checkBreak(8);
        this.printText(text, { size: 10, bold: true });
        this.y += 1;
    };

    PdfBuilder.prototype.paragraph = function (text, size) {
        this.printText(text, { size: size || 9 });
        this.y += 2;
    };

    PdfBuilder.prototype.keyValue = function (label, value) {
        this.checkBreak(6);
        this.pdf.setFontSize(9);
        this.pdf.setFont('helvetica', 'bold');
        this.pdf.text(label + ':', MARGIN, this.y);
        var valueY = this.y;
        var savedY = this.y;
        this.y = valueY;
        if (containsArabic(value)) {
            renderArabicTextBlocks(String(value || '—'), 9, false, this.contentW - 38).forEach(function (b, idx) {
                if (idx > 0) this.y += 1;
                this.checkBreak(b.h + 1);
                try {
                    this.pdf.addImage(b.dataUrl, 'PNG', MARGIN + 36, this.y - b.h + 2, Math.min(b.w, this.contentW - 38), b.h);
                } catch (_) {}
                this.y += b.h + 0.5;
            }.bind(this));
        } else {
            this.pdf.setFont('helvetica', 'normal');
            var lines = this.pdf.splitTextToSize(String(value || '—'), this.contentW - 38);
            var cy = savedY;
            lines.forEach(function (line) {
                this.pdf.text(line, MARGIN + 36, cy);
                cy += 4.5;
            }.bind(this));
            this.y = lines.length > 1 ? cy - 4.5 : savedY;
        }
        this.y += 5;
    };

    PdfBuilder.prototype.tableCellText = function (text, x, colW) {
        text = String(text ?? '—');
        if (!containsArabic(text)) {
            this.pdf.text(text.substring(0, 42), x, this.y);
            return;
        }
        var blocks = renderArabicTextBlocks(text.substring(0, 80), 7.5, false, colW - 2);
        if (blocks[0]) {
            try {
                var b = blocks[0];
                this.pdf.addImage(b.dataUrl, 'PNG', x, this.y - b.h + 1.5, Math.min(b.w, colW - 2), b.h);
            } catch (_) {
                this.pdf.text('…', x, this.y);
            }
        }
    };

    PdfBuilder.prototype.table = function (headers, colWidths, rows) {
        var rowH = 6;
        var headerH = 7;
        var drawHeader = function () {
            this.pdf.setFillColor.apply(this.pdf, COLORS.headerBg);
            this.pdf.rect(MARGIN, this.y - 4, this.contentW, headerH, 'F');
            this.pdf.setTextColor(255, 255, 255);
            this.pdf.setFontSize(8);
            this.pdf.setFont('helvetica', 'bold');
            var x = MARGIN + 1;
            headers.forEach(function (h, i) {
                this.pdf.text(String(h), x, this.y);
                x += colWidths[i];
            }.bind(this));
            this.pdf.setTextColor(0, 0, 0);
            this.y += headerH;
        }.bind(this);

        this.checkBreak(headerH + rowH);
        drawHeader();
        this.pdf.setFontSize(7.5);
        this.pdf.setFont('helvetica', 'normal');

        rows.forEach(function (cells) {
            if (this.y + rowH > this.pageH - FOOTER_H - 2) {
                this.newPage();
                drawHeader();
                this.pdf.setFontSize(7.5);
                this.pdf.setFont('helvetica', 'normal');
            }
            var x = MARGIN + 1;
            cells.forEach(function (cell, i) {
                this.tableCellText(cell, x, colWidths[i]);
                x += colWidths[i];
            }.bind(this));
            this.y += rowH;
        }.bind(this));
        this.y += 4;
    };

    PdfBuilder.prototype.addChartImage = function (dataUrl, title, maxH) {
        if (!dataUrl) return;
        maxH = maxH || 55;
        var imgW = this.contentW;
        var imgH = maxH;
        this.checkBreak(imgH + 10);
        if (title) {
            this.pdf.setFontSize(9);
            this.pdf.setFont('helvetica', 'bold');
            this.pdf.text(title, MARGIN, this.y);
            this.y += 5;
        }
        try {
            this.pdf.addImage(dataUrl, 'PNG', MARGIN, this.y, imgW, imgH);
            this.y += imgH + 6;
        } catch (_) {}
    };

    PdfBuilder.prototype.buildCover = function () {
        var p = this.patient;
        this.pdf.setFillColor(248, 250, 252);
        this.pdf.rect(0, 0, this.pageW, 55, 'F');
        this.y = 28;

        if (this.logoDataUrl) {
            try {
                this.pdf.addImage(this.logoDataUrl, 'PNG', MARGIN, 12, 40, 22);
            } catch (_) {}
        }

        this.pdf.setFontSize(20);
        this.pdf.setFont('helvetica', 'bold');
        this.pdf.setTextColor.apply(this.pdf, COLORS.primary);
        this.pdf.text('Complete Integrated Medical Report', MARGIN, 50);
        this.pdf.setTextColor(0, 0, 0);

        this.y = 62;
        this.printText(p.full_name || 'Patient', { size: 14, bold: true });
        this.y += 2;
        this.keyValue('Patient ID', '#' + (p.id || ''));
        this.keyValue('Date of Birth', fmtDate(p.dob) + (p.age != null ? ' (' + p.age + ' yrs)' : ''));
        this.keyValue('Gender', p.gender || '—');
        this.keyValue('Phone', p.phone || '—');
        this.keyValue('Clinic', this.clinic.name || '—');
        this.keyValue('Export date', fmtDateTime(this.record.meta.exported_at));
        if (this.record.meta.exported_by && this.record.meta.exported_by.name) {
            this.keyValue('Exported by', this.record.meta.exported_by.name);
        }

        this.y += 4;
        this.pdf.setFontSize(8);
        this.pdf.setTextColor.apply(this.pdf, COLORS.muted);
        this.paragraph('CONFIDENTIAL — This document contains protected health information. Unauthorized disclosure is prohibited.');
        this.pdf.setTextColor(0, 0, 0);
        this._refreshFooter();
    };

    function renderChart(type, labels, datasets, opts) {
        return new Promise(function (resolve) {
            if (!global.Chart) { resolve(null); return; }
            var wrap = document.createElement('div');
            wrap.style.cssText = 'position:fixed;left:-9999px;top:0;width:700px;height:320px;';
            var canvas = document.createElement('canvas');
            canvas.width = 700;
            canvas.height = 320;
            wrap.appendChild(canvas);
            document.body.appendChild(wrap);
            try {
                var chart = new global.Chart(canvas.getContext('2d'), {
                    type: type,
                    data: { labels: labels, datasets: datasets },
                    options: Object.assign({
                        responsive: false,
                        animation: false,
                        plugins: { legend: { display: datasets.length > 1 } },
                    }, opts || {}),
                });
                setTimeout(function () {
                    var url = null;
                    try { url = chart.toBase64Image('image/png', 1); } catch (_) {}
                    chart.destroy();
                    document.body.removeChild(wrap);
                    resolve(url);
                }, 120);
            } catch (e) {
                document.body.removeChild(wrap);
                resolve(null);
            }
        });
    }

    function buildCharts(record) {
        var stats = record.statistics || {};
        var promises = [];

        var months = Object.keys(stats.visits_by_month || {}).sort();
        if (months.length) {
            promises.push(renderChart('bar',
                months,
                [{ label: 'Visits', data: months.map(function (m) { return stats.visits_by_month[m]; }), backgroundColor: 'rgba(99,102,241,0.7)' }],
                { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            ).then(function (u) { return { key: 'visits_month', url: u, title: 'Visits per Month' }; }));
        }

        var statuses = Object.keys(stats.status_breakdown || {});
        if (statuses.length) {
            promises.push(renderChart('doughnut',
                statuses,
                [{ data: statuses.map(function (s) { return stats.status_breakdown[s]; }), backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#94a3b8'] }],
                {}
            ).then(function (u) { return { key: 'status', url: u, title: 'Appointment Status Breakdown' }; }));
        }

        var iop = record.iop && record.iop.series;
        if (iop && (iop.OD.length || iop.OS.length)) {
            var allDates = [];
            iop.OD.forEach(function (p) { if (allDates.indexOf(p.date) < 0) allDates.push(p.date); });
            iop.OS.forEach(function (p) { if (allDates.indexOf(p.date) < 0) allDates.push(p.date); });
            allDates.sort();
            var odMap = {}, osMap = {};
            iop.OD.forEach(function (p) { odMap[p.date] = p.value; });
            iop.OS.forEach(function (p) { osMap[p.date] = p.value; });
            promises.push(renderChart('line',
                allDates.map(fmtDate),
                [
                    { label: 'OD', data: allDates.map(function (d) { return odMap[d] ?? null; }), borderColor: '#6366f1', tension: 0.3, spanGaps: true },
                    { label: 'OS', data: allDates.map(function (d) { return osMap[d] ?? null; }), borderColor: '#ec4899', tension: 0.3, spanGaps: true },
                ],
                { scales: { y: { beginAtZero: false, title: { display: true, text: 'mmHg' } } } }
            ).then(function (u) { return { key: 'iop', url: u, title: 'IOP Trend (OD / OS)' }; }));
        }

        var topMeds = stats.top_medications || {};
        var medNames = Object.keys(topMeds).slice(0, 10);
        if (medNames.length) {
            promises.push(renderChart('bar',
                medNames.map(function (n) { return n.length > 18 ? n.substring(0, 16) + '…' : n; }),
                [{ label: 'Prescriptions', data: medNames.map(function (n) { return topMeds[n]; }), backgroundColor: 'rgba(16,185,129,0.75)' }],
                { indexAxis: 'y', scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            ).then(function (u) { return { key: 'meds', url: u, title: 'Most Prescribed Medications' }; }));
        }

        return Promise.all(promises);
    }

    PdfBuilder.prototype.buildSummarySections = function (charts) {
        var r = this.record;
        var stats = r.statistics || {};

        this.newPage();
        this.sectionTitle('Integrated Medical Record');
        var visitAtt = countVisitAttachments(r.appointments || []);
        this.table(
            ['Metric', 'Value'],
            [70, this.contentW - 70],
            [
                ['Total appointments', stats.total_appointments],
                ['Medication lines', stats.total_medications],
                ['History entries', stats.total_history_entries],
                ['Active alerts', (r.alerts || []).filter(function (a) { return a.is_active == 1; }).length],
                ['Visit attachments (images)', visitAtt.images],
                ['Visit attachments (documents)', visitAtt.files],
                ['Profile images (appendix)', (r.images || []).length],
                ['Profile documents (appendix)', (r.profile_documents || []).length],
            ]
        );

        this.sectionTitle('Statistics & Charts');
        charts.forEach(function (ch) {
            if (ch && ch.url) this.addChartImage(ch.url, ch.title, 50);
        }.bind(this));

        this.sectionTitle('Medical History (dated)');
        var hist = r.medical_history || [];
        if (!hist.length) {
            this.paragraph('No medical history recorded.');
        } else {
            hist.forEach(function (h) {
                var date = h.diagnosis_date || h.created_at;
                var title = h.condition_name || h.category || 'History entry';
                this.subTitle(fmtDate(date) + ' — ' + title);
                if (h.status) this.paragraph('Status: ' + h.status);
                if (h.allergies) this.paragraph('Allergies: ' + h.allergies);
                if (h.medications) this.paragraph('Medications: ' + h.medications);
                if (h.systemic_history) this.paragraph('Systemic: ' + h.systemic_history);
                if (h.ocular_history) this.paragraph('Ocular: ' + h.ocular_history);
                if (h.prior_surgeries) this.paragraph('Surgeries: ' + h.prior_surgeries);
                if (h.family_history) this.paragraph('Family: ' + h.family_history);
                if (h.notes) this.paragraph(h.notes);
                this.y += 2;
            }.bind(this));
        }

        if ((r.alerts || []).length) {
            this.sectionTitle('Alerts');
            this.table(['Date', 'Message', 'Active'], [28, this.contentW - 48, 20],
                r.alerts.map(function (a) {
                    return [fmtDate(a.alert_date), (a.message || '').substring(0, 80), a.is_active == 1 ? 'Yes' : 'No'];
                })
            );
        }

        if ((r.tags || []).length) {
            this.subTitle('Tags: ' + r.tags.map(function (t) { return t.name; }).join(', '));
        }

        if ((r.patient_notes || []).length) {
            this.sectionTitle('Patient Notes');
            r.patient_notes.forEach(function (n) {
                this.subTitle((n.title || 'Note') + ' · ' + fmtDateTime(n.created_at));
                this.paragraph(n.content || '');
            }.bind(this));
        }

        if ((r.medication_register || []).length) {
            this.sectionTitle('Medication Register (all visits)');
            this.table(['Date', 'Drug', 'Dose', 'Frequency', 'Duration'],
                [24, 42, 22, 30, 28],
                r.medication_register.slice(0, 80).map(function (m) {
                    return [fmtDate(m.appointment_date), m.drug_name, m.dose, m.frequency, m.duration];
                })
            );
        }

    };

    PdfBuilder.prototype.buildBoardSection = function () {
        var self = this;
        var comments = (self.record.board_comments || []);
        if (!comments.length) return Promise.resolve();

        self.newPage();
        self.sectionTitle('Patient Board Notes');

        var chain = Promise.resolve();
        comments.forEach(function (c) {
            chain = chain.then(function () {
                var pin = c.pinned ? ' [Pinned]' : '';
                self.subTitle(fmtDateTime(c.created_at) + ' — ' + (c.author_name || 'Staff') + pin);
                if (c.body) self.paragraph(c.body, 8);

                var attChain = Promise.resolve();
                (c.attachments || []).forEach(function (att) {
                    attChain = attChain.then(function () {
                        var name = att.name || 'Board attachment';
                        if (att.is_image && att.view_url) {
                            return loadImageDataUrl(att.view_url).then(function (dataUrl) {
                                if (dataUrl && self.embedImageBlock(dataUrl, name, 50)) return;
                                self.paragraph(name + ' (image — could not embed)', 8);
                            });
                        }
                        if (att.kind === 'audio') {
                            self.paragraph('Audio: ' + name + (att.duration_ms ? ' (' + Math.round(att.duration_ms / 1000) + 's)' : ''), 8);
                            return Promise.resolve();
                        }
                        self.paragraph((att.kind === 'document' ? 'Document' : 'File') + ': ' + name, 8);
                        return Promise.resolve();
                    });
                });
                return attChain.then(function () { self.y += 2; });
            });
        });
        return chain;
    };

    PdfBuilder.prototype.embedImageBlock = function (dataUrl, title, maxH) {
        maxH = maxH || 55;
        if (title) this.subTitle(title);
        var maxW = this.contentW;
        this.checkBreak(maxH + 10);
        try {
            this.pdf.addImage(dataUrl, dataUrlFormat(dataUrl), MARGIN, this.y, maxW, maxH);
            this.y += maxH + 4;
            return true;
        } catch (_) {
            return false;
        }
    };

    PdfBuilder.prototype.buildVisitSection = function (appointments, partLabel, skipFirstNewPage) {
        var self = this;
        if (skipFirstNewPage) {
            self.y = MARGIN + HEADER_H;
            self._refreshFooter();
        } else {
            self.newPage();
        }
        self.sectionTitle('Visit Chronology' + (partLabel ? ' — ' + partLabel : ''));

        var chain = Promise.resolve();
        appointments.forEach(function (appt) {
            chain = chain.then(function () {
                self.checkBreak(20);
                self.subTitle(fmtDate(appt.date) + ' ' + (appt.start_time || '') + ' · ' + (appt.visit_type || '') + ' · ' + (appt.status || ''));
                self.keyValue('Doctor', appt.doctor_name);

                var cn = appt.consultation_note;
                if (cn) {
                    if (cn.chief_complaint) self.keyValue('Chief complaint', cn.chief_complaint);
                    if (cn.diagnosis) self.keyValue('Diagnosis', cn.diagnosis);
                    if (cn.diagnosis_code) self.keyValue('ICD', cn.diagnosis_code);
                    if (cn.IOP_right || cn.IOP_left) self.keyValue('IOP', 'OD ' + (cn.IOP_right || '—') + ' / OS ' + (cn.IOP_left || '—'));
                    if (cn.visual_acuity_right || cn.visual_acuity_left) {
                        self.keyValue('VA', 'OD ' + (cn.visual_acuity_right || '—') + ' / OS ' + (cn.visual_acuity_left || '—'));
                    }
                    if (cn.plan) self.keyValue('Plan', cn.plan);
                    if (cn.medication) self.keyValue('Medication notes', cn.medication);
                }

                (appt.medications || []).forEach(function (m) {
                    self.paragraph('Rx: ' + [m.drug_name, m.dose, m.frequency, m.duration].filter(Boolean).join(' · '), 8);
                });

                (appt.glasses || []).forEach(function (g) {
                    self.paragraph(formatGlassesRx(g), 8);
                });

                (appt.lab_tests || []).forEach(function (l) {
                    var kind = (l.test_type === 'radiology') ? 'Radiology' : 'Lab';
                    self.paragraph(kind + ': ' + (l.test_name || l.test_type || 'Test') + ' — ' + (l.status || '') + (l.results ? ' — ' + l.results : ''), 8);
                });

                (appt.medical_instructions || []).forEach(function (mi) {
                    self.paragraph('Instruction: ' + (mi.title || '') + ' — ' + (mi.body_en || mi.body_ar || ''), 8);
                });

                var attChain = Promise.resolve();
                (appt.attachments || []).forEach(function (att) {
                    attChain = attChain.then(function () {
                        var name = att.original_filename || att.filename || 'Attachment';
                        var url = att.view_url || (att.id ? '/api/attachments/view/' + att.id : null);
                        if (att.is_image && url) {
                            return loadImageDataUrl(url).then(function (dataUrl) {
                                if (dataUrl && self.embedImageBlock(dataUrl, 'Attachment: ' + name, 55)) {
                                    return;
                                }
                                self.paragraph('Attachment: ' + name + ' (could not embed)', 8);
                            });
                        }
                        self.paragraph('Attachment: ' + name, 8);
                        return Promise.resolve();
                    });
                });
                return attChain.then(function () {
                    self.y += 3;
                });
            });
        });
        return chain;
    };

    PdfBuilder.prototype.buildProfileAppendix = function (images, documents, onProgress, skipFirstNewPage) {
        var self = this;
        images = images || [];
        documents = documents || [];
        if (!images.length && !documents.length) return Promise.resolve();

        if (skipFirstNewPage) {
            self.y = MARGIN + HEADER_H;
            self._refreshFooter();
        } else {
            self.newPage();
        }
        self.sectionTitle('Patient Profile Files & Images');

        if (documents.length) {
            self.subTitle('Documents uploaded from patient profile');
            self.table(
                ['Date', 'File', 'Type'],
                [24, self.contentW - 54, 30],
                documents.map(function (doc) {
                    return [fmtDate(doc.date), doc.name || '—', doc.file_type || 'Document'];
                })
            );
            self.y += 2;
        }

        var chain = Promise.resolve();
        images.forEach(function (img, idx) {
            chain = chain.then(function () {
                if (onProgress) onProgress('Embedding profile image ' + (idx + 1) + ' of ' + images.length);
                return loadImageDataUrl(img.url).then(function (dataUrl) {
                    if (!dataUrl) {
                        self.paragraph(img.name + ' (' + fmtDate(img.date) + ') — could not embed');
                        return;
                    }
                    self.subTitle((img.name || 'Image') + ' · ' + fmtDate(img.date));
                    if (img.description) self.paragraph(img.description, 8);
                    var maxW = self.contentW;
                    var maxH = 90;
                    self.checkBreak(maxH + 12);
                    try {
                        self.pdf.addImage(dataUrl, dataUrlFormat(dataUrl), MARGIN, self.y, maxW, maxH);
                        self.y += maxH + 6;
                    } catch (_) {
                        self.paragraph('(image embed failed)');
                    }
                });
            });
        });
        return chain;
    };

    PdfBuilder.prototype.save = function (filename) {
        this.pdf.save(filename);
    };

    PdfBuilder.prototype.getBlob = function () {
        return this.pdf.output('blob');
    };

    PdfBuilder.prototype.download = function (filename) {
        this.pdf.save(filename);
    };

    /** Build one complete PDF (all visits + appendix) — used for print preview & small exports. */
    async function buildCompleteRecordPdf(record, onProgress) {
        var logoUrl = resolveAssetUrl((record.clinic && record.clinic.logo_print)
            ? record.clinic.logo_print : '/assets/images/Light.png');
        if (onProgress) onProgress('Rendering charts…');
        var charts = await buildCharts(record);
        if (onProgress) onProgress('Building PDF…');
        var logo = await loadImageDataUrl(logoUrl);
        var builder = new PdfBuilder(record, logo);
        builder.buildCover();
        builder.buildSummarySections(charts);
        await builder.buildBoardSection();
        var appointments = record.appointments || [];
        if (appointments.length) {
            await builder.buildVisitSection(appointments, null);
        }
        var profileImages = record.images || [];
        var profileDocuments = record.profile_documents || [];
        await builder.buildProfileAppendix(profileImages, profileDocuments, onProgress);
        return builder;
    }

    function chunkArray(arr, size) {
        var out = [];
        for (var i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
        return out;
    }

    function updateOverlay(overlay, msg) {
        if (!overlay) return;
        if (overlay.statusEl) {
            overlay.statusEl.textContent = msg;
            return;
        }
        var p = overlay.querySelector && overlay.querySelector('.pmr-export-status');
        if (p) p.textContent = msg;
    }

    async function fetchMedicalRecord(patientId) {
        var res = await fetch('/api/patients/' + patientId + '/medical-record', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to load medical record (' + res.status + ')');
        var json = await res.json();
        if (!json.success || !json.data) throw new Error(json.error || 'Invalid response');
        return json.data;
    }

    function baseFilename(record, patientId) {
        var dateSlug = new Date().toISOString().split('T')[0];
        return 'Patient_' + patientId + '_' + (record.patient.full_name || 'record').replace(/\s+/g, '_') + '_' + dateSlug;
    }

    /**
     * Print preview — always one PDF, embedded in iframe or new tab, optional auto-print.
     * @param {number|string} patientId
     * @param {object|HTMLElement|null} ui — { statusEl, iframe, autoPrint } or legacy overlay element
     */
    async function printPatientMedicalRecordPDF(patientId, ui) {
        if (!global.jspdf || !global.jspdf.jsPDF) {
            throw new Error('jsPDF library not loaded');
        }
        updateOverlay(ui, 'Loading fonts & patient record…');
        await ensureArabicFont();
        var record = await fetchMedicalRecord(patientId);
        var builder = await buildCompleteRecordPdf(record, function (m) { updateOverlay(ui, m); });
        var blob = builder.getBlob();
        var url = URL.createObjectURL(blob);
        var filename = baseFilename(record, patientId) + '.pdf';

        if (ui && ui.iframe) {
            ui.iframe.style.display = 'block';
            ui.iframe.src = url;
            ui.iframe.onload = function () {
                updateOverlay(ui, 'PDF ready — use Print or Download below.');
                if (ui.autoPrint !== false) {
                    setTimeout(function () {
                        try {
                            ui.iframe.contentWindow.focus();
                            ui.iframe.contentWindow.print();
                        } catch (_) {}
                    }, 700);
                }
            };
        } else {
            var win = window.open(url, '_blank');
            if (win) {
                updateOverlay(ui, 'PDF opened in a new tab.');
            }
        }

        return { blobUrl: url, filename: filename, parts: 1 };
    }

    async function exportPatientMedicalRecordPDF(patientId, overlay) {
        if (!global.jspdf || !global.jspdf.jsPDF) {
            throw new Error('jsPDF library not loaded');
        }

        updateOverlay(overlay, 'Loading fonts & patient record…');
        await ensureArabicFont();
        var record = await fetchMedicalRecord(patientId);

        var appointments = record.appointments || [];
        var visitChunks = chunkArray(appointments, VISITS_PER_PART);
        var profileImages = record.images || [];
        var profileDocuments = record.profile_documents || [];
        var appendixCount = profileImages.length + profileDocuments.length;
        var needsSplit = visitChunks.length > 1 || profileImages.length > 30;
        var baseName = baseFilename(record, patientId);
        var logoUrl = resolveAssetUrl((record.clinic && record.clinic.logo_print)
            ? record.clinic.logo_print : '/assets/images/Light.png');

        if (!needsSplit) {
            var single = await buildCompleteRecordPdf(record, function (m) { updateOverlay(overlay, m); });
            single.download(baseName + '.pdf');
            return { parts: 1 };
        }

        updateOverlay(overlay, 'Rendering charts…');
        var charts = await buildCharts(record);

        updateOverlay(overlay, 'Building Part 1 (complete record)…');
        var logo1 = await loadImageDataUrl(logoUrl);
        var part1 = new PdfBuilder(record, logo1);
        part1.buildCover();
        part1.buildSummarySections(charts);
        await part1.buildBoardSection();
        part1.save(baseName + '_Part1_CompleteRecord.pdf');

        for (var c = 0; c < visitChunks.length; c++) {
            updateOverlay(overlay, 'Building visits part ' + (c + 2) + '…');
            var logoV = await loadImageDataUrl(logoUrl);
            var vb = new PdfBuilder(record, logoV);
            await vb.buildVisitSection(visitChunks[c], 'Part ' + (c + 2) + ' of ' + (visitChunks.length + 1 + (appendixCount ? 1 : 0)), true);
            vb.save(baseName + '_Part' + (c + 2) + '_Visits.pdf');
        }

        if (appendixCount) {
            updateOverlay(overlay, 'Building profile files appendix…');
            var logoI = await loadImageDataUrl(logoUrl);
            var ib = new PdfBuilder(record, logoI);
            await ib.buildProfileAppendix(profileImages, profileDocuments, function (m) { updateOverlay(overlay, m); }, true);
            ib.save(baseName + '_Part' + (visitChunks.length + 2) + '_ProfileFiles.pdf');
        }

        return { parts: 1 + visitChunks.length + (appendixCount ? 1 : 0) };
    }

    global.exportPatientMedicalRecordPDF = exportPatientMedicalRecordPDF;
    global.printPatientMedicalRecordPDF = printPatientMedicalRecordPDF;
})(window);
