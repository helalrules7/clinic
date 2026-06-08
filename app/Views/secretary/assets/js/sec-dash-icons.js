/**
 * Secretary dashboard icon registry — reads PHP-exported SVG HTML.
 */
(function (global) {
    'use strict';

    var registry = {};

    function loadRegistry() {
        var el = document.getElementById('secDashIconRegistry');
        if (!el) return;
        try {
            registry = JSON.parse(el.textContent || '{}');
        } catch (_) {
            registry = {};
        }
    }

    function html(key, size) {
        size = size || 'md';
        return registry[key + ':' + size] || registry[key + ':md'] || registry[key + ':sm'] || '';
    }

    function trendHtml(iconKey) {
        return html(iconKey, 'sm');
    }

    global.SecDashIcons = {
        html: html,
        trendHtml: trendHtml,
        load: loadRegistry
    };

    loadRegistry();
})(typeof window !== 'undefined' ? window : this);
