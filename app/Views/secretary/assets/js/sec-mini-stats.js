// ============================================
// Secretary mini-stat cards — shared sparklines + trend badges
// ============================================

(function (global) {
    'use strict';

    function generateSparklineSVG(data) {
        var width = 100;
        var height = 35;
        var padding = 2;
        if (!data || data.length < 2) {
            data = [0, 0];
        }
        var min = Math.min.apply(null, data);
        var max = Math.max.apply(null, data);
        var range = max - min || 1;
        var points = data.map(function (value, index) {
            var x = padding + (index / (data.length - 1)) * (width - padding * 2);
            var y = height - padding - ((value - min) / range) * (height - padding * 2);
            return x + ',' + y;
        });
        var linePath = 'M ' + points.join(' L ');
        var areaPath = 'M ' + padding + ',' + height + ' L ' + points.join(' L ') + ' L ' + (width - padding) + ',' + height + ' Z';
        return '<svg viewBox="0 0 ' + width + ' ' + height + '" preserveAspectRatio="none">' +
            '<path class="sparkline-area" d="' + areaPath + '"/>' +
            '<path class="sparkline-path" d="' + linePath + '"/>' +
            '</svg>';
    }

    function formatDiffValue(diff, format) {
        if (format === 'money' || format === 'money2') {
            var digits = format === 'money2' ? 2 : 0;
            var sign = diff > 0 ? '+' : '';
            return sign + Number(diff).toLocaleString('ar-EG', { minimumFractionDigits: digits, maximumFractionDigits: digits });
        }
        var sign = diff > 0 ? '+' : '';
        return sign + diff;
    }

    function formatTrendBadge(series, invert, todayValue, deltaOverride, neutralLabel, format) {
        neutralLabel = neutralLabel || 'اليوم';
        var diff;
        var hasDelta = deltaOverride !== undefined && deltaOverride !== null && !isNaN(Number(deltaOverride));
        if (hasDelta) {
            diff = Number(deltaOverride);
        } else if (!series || series.length < 2) {
            return { cls: 'trend-neutral', icon: 'bi-calendar-day', html: '<span class="arabic-text">' + neutralLabel + '</span>' };
        } else {
            var yesterday = Number(series[series.length - 2]) || 0;
            var today = todayValue !== undefined && todayValue !== null
                ? Number(todayValue) || 0
                : Number(series[series.length - 1]) || 0;
            diff = today - yesterday;
        }
        if (Math.abs(diff) < 0.005 && (format === 'money2' || format === 'money')) {
            diff = 0;
        }
        if (diff === 0) {
            return { cls: 'trend-neutral', icon: 'bi-dash-lg', html: '<span class="arabic-text">مثل أمس</span>' };
        }
        var up = diff > 0;
        if (invert) up = !up;
        return {
            cls: up ? 'trend-up' : 'trend-down',
            icon: up ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow',
            html: '<span dir="ltr" class="trend-diff-value">' + formatDiffValue(diff, format) + '</span> <span class="arabic-text">عن أمس</span>'
        };
    }

    function applyTrendBadge(elId, series, invert, todayValue, deltaOverride, neutralLabel, format) {
        var el = document.getElementById(elId);
        if (!el) return;
        var b = formatTrendBadge(series, invert, todayValue, deltaOverride, neutralLabel, format);
        el.className = 'mini-stat-trend ' + b.cls;
        el.innerHTML = '<i class="bi ' + b.icon + '"></i>' + b.html;
    }

    function readJsonScript(id) {
        var el = document.getElementById(id);
        if (!el) return null;
        try { return JSON.parse(el.textContent || '{}'); } catch (_) { return null; }
    }

    function computeDeltasForDate(dates, trends, dateStr, stats, cards) {
        var deltas = {};
        var idx = dates && dateStr ? dates.indexOf(dateStr) : -1;
        (cards || []).forEach(function (cfg) {
            if (cfg.staticToday) return;
            var series = (trends && trends[cfg.trendKey]) ? trends[cfg.trendKey] : [];
            var val = stats && cfg.statKey ? Number(stats[cfg.statKey]) || 0 : 0;
            if (idx >= 1) {
                deltas[cfg.trendKey] = val - (Number(series[idx - 1]) || 0);
            } else if (idx === 0) {
                deltas[cfg.trendKey] = val;
            } else if (series.length >= 2) {
                deltas[cfg.trendKey] = (Number(series[series.length - 1]) || 0) - (Number(series[series.length - 2]) || 0);
            } else {
                deltas[cfg.trendKey] = 0;
            }
        });
        return deltas;
    }

    function init(cards, options) {
        options = options || {};
        var trends = options.trends || readJsonScript(options.trendsId) || {};
        var stats = options.stats || readJsonScript(options.statsId) || {};
        var deltas = options.deltas || readJsonScript(options.deltasId) || {};
        var neutralLabel = options.neutralLabel || 'اليوم';

        (cards || []).forEach(function (cfg) {
            var chartEl = document.getElementById(cfg.chartId);
            if (!chartEl) return;
            var series = ((trends[cfg.trendKey] || []).slice());
            var todayVal = cfg.syncStatToSeries === false ? undefined : stats[cfg.statKey];
            if (todayVal !== undefined && series.length && options.syncLastPoint !== false && cfg.syncStatToSeries !== false) {
                series[series.length - 1] = Number(todayVal) || 0;
            }
            chartEl.innerHTML = generateSparklineSVG(series);
            if (cfg.staticToday) {
                var trendEl = document.getElementById(cfg.trendId);
                if (trendEl) {
                    trendEl.className = 'mini-stat-trend trend-neutral';
                    trendEl.innerHTML = '<i class="bi bi-calendar-day"></i><span class="arabic-text">' + neutralLabel + '</span>';
                }
                return;
            }
            applyTrendBadge(
                cfg.trendId,
                trends[cfg.trendKey] || [],
                !!cfg.invert,
                todayVal,
                deltas[cfg.trendKey],
                neutralLabel,
                cfg.format
            );
        });
    }

    function refresh(cards, trends, stats, deltas, options) {
        options = options || {};
        if (options.trendsId && trends) {
            var tEl = document.getElementById(options.trendsId);
            if (tEl) tEl.textContent = JSON.stringify(trends);
        }
        if (options.statsId && stats) {
            var sEl = document.getElementById(options.statsId);
            if (sEl) sEl.textContent = JSON.stringify(stats);
        }
        if (options.deltasId && deltas) {
            var dEl = document.getElementById(options.deltasId);
            if (dEl) dEl.textContent = JSON.stringify(deltas);
        }
        init(cards, {
            trends: trends,
            stats: stats,
            deltas: deltas,
            neutralLabel: options.neutralLabel,
            syncLastPoint: options.syncLastPoint
        });
    }

    function updateStatValues(cards, stats) {
        (cards || []).forEach(function (cfg) {
            if (!cfg.valueId || !stats) return;
            var el = document.getElementById(cfg.valueId);
            if (!el) return;
            var v = stats[cfg.statKey];
            if (cfg.format === 'money' || cfg.format === 'money2') {
                var digits = cfg.format === 'money2' ? 2 : 0;
                el.textContent = Number(v || 0).toLocaleString('ar-EG', { minimumFractionDigits: digits, maximumFractionDigits: digits });
            } else {
                el.textContent = v !== undefined && v !== null ? v : 0;
            }
        });
    }

    global.secMiniStats = {
        generateSparklineSVG: generateSparklineSVG,
        formatTrendBadge: formatTrendBadge,
        applyTrendBadge: applyTrendBadge,
        computeDeltasForDate: computeDeltasForDate,
        init: init,
        refresh: refresh,
        updateStatValues: updateStatValues,
        readJsonScript: readJsonScript
    };
})(typeof window !== 'undefined' ? window : this);
