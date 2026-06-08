/**
 * layouts/digit-normalizer.js
 * Unicode digit equivalence — ASCII, Arabic-Indic (٠-٩), Extended (۰-۹).
 */
(function (global) {
    'use strict';

    var DIGIT_MAP = {
        '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4',
        '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9',
        '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4',
        '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9'
    };

    function toAsciiDigits(input) {
        if (input == null || input === '') return '';
        var s = String(input);
        var out = '';
        for (var i = 0; i < s.length; i++) {
            var ch = s.charAt(i);
            out += Object.prototype.hasOwnProperty.call(DIGIT_MAP, ch) ? DIGIT_MAP[ch] : ch;
        }
        return out;
    }

    function normalizeSearchQuery(query) {
        return toAsciiDigits(String(query || '').trim());
    }

    function digitsOnly(input) {
        return toAsciiDigits(input).replace(/\D/g, '');
    }

    function normalizePhone(phone) {
        var p = toAsciiDigits(String(phone || '').trim());
        p = p.replace(/^(\+20|0)/, '');
        return digitsOnly(p);
    }

    function isPhoneNumberSearch(query) {
        var clean = normalizePhone(query);
        return clean.length >= 9 && clean.length <= 11 && clean.charAt(0) === '1';
    }

    function patientSearchUrl(query) {
        return '/api/patients/search?q=' + encodeURIComponent(normalizeSearchQuery(query));
    }

    function normalizeUrlQueryParam(url, param) {
        try {
            var u = new URL(url, global.location.origin);
            if (!u.searchParams.has(param)) return url;
            var val = u.searchParams.get(param);
            if (val == null || val === '') return url;
            u.searchParams.set(param, normalizeSearchQuery(val));
            return u.pathname + u.search + u.hash;
        } catch (_) {
            return url;
        }
    }

    var SEARCH_Q_PATHS = [
        '/api/patients/search',
        '/api/search/palette',
        '/api/search/comprehensive',
        '/api/appointments/search'
    ];

    function shouldNormalizeSearchUrl(url) {
        if (!url || typeof url !== 'string') return false;
        var path = url.split('?')[0];
        for (var i = 0; i < SEARCH_Q_PATHS.length; i++) {
            if (path.indexOf(SEARCH_Q_PATHS[i]) !== -1) return true;
        }
        return false;
    }

    function patchFetch() {
        if (!global.fetch || global.__digitNormalizerFetchPatched) return;
        global.__digitNormalizerFetchPatched = true;
        var nativeFetch = global.fetch.bind(global);
        global.fetch = function (input, init) {
            if (typeof input === 'string' && shouldNormalizeSearchUrl(input)) {
                input = normalizeUrlQueryParam(input, 'q');
            } else if (input && typeof input === 'object' && input.url && shouldNormalizeSearchUrl(input.url)) {
                input = new Request(normalizeUrlQueryParam(input.url, 'q'), input);
            }
            return nativeFetch(input, init);
        };
    }

    /** Live input value — search keeps letters; phone keeps +()- space; digits strips non-digits. */
    function normalizeInputValue(value, mode) {
        if (value == null) return '';
        var s = String(value);
        if (mode === 'digits') return digitsOnly(s);
        if (mode === 'phone') return toAsciiDigits(s);
        return toAsciiDigits(s);
    }

    function bindInput(el, mode) {
        if (!el || el.nodeType !== 1 || el.dataset.digitNormalizeBound) return;
        mode = mode || 'search';
        el.dataset.digitNormalizeBound = mode;

        function apply() {
            if (el.readOnly || el.disabled) return;
            var val = el.value;
            var norm = normalizeInputValue(val, mode);
            if (norm !== val) {
                var start = el.selectionStart;
                var end = el.selectionEnd;
                el.value = norm;
                if (start != null && end != null) {
                    try {
                        el.setSelectionRange(start, end);
                    } catch (_) { /* type=number etc. */ }
                }
            }
        }

        el.addEventListener('input', apply);
        el.addEventListener('change', apply);
        el.addEventListener('paste', function () {
            setTimeout(apply, 0);
        });
    }

    var BIND_RULES = [
        {
            mode: 'phone',
            sel: [
                '#phone',
                'input[name="phone"]',
                'input[name="alt_phone"]',
                'input[name="emergency_phone"]',
                'input[type="tel"]:not([data-no-digit-normalize])'
            ].join(',')
        },
        {
            mode: 'digits',
            sel: [
                '#age',
                'input[name="age"]',
                '#national_id',
                'input[name="national_id"]'
            ].join(',')
        },
        {
            mode: 'search',
            sel: [
                '#patientSearch',
                '#editPatientSearch',
                '#alertPatientSearch',
                '.patient-search-input',
                '#search',
                '#quickSearch',
                '#quickSearchCards',
                '#globalSearch',
                '#globalSearchInput',
                '#cmdkInput'
            ].join(',')
        }
    ];

    function scanAndBind(root) {
        root = root || document;
        if (!root.querySelectorAll) return;
        var i, rule, nodes, j;
        for (i = 0; i < BIND_RULES.length; i++) {
            rule = BIND_RULES[i];
            nodes = root.querySelectorAll(rule.sel);
            for (j = 0; j < nodes.length; j++) {
                bindInput(nodes[j], rule.mode);
            }
        }
        nodes = root.querySelectorAll('[data-digit-normalize]');
        for (j = 0; j < nodes.length; j++) {
            bindInput(nodes[j], nodes[j].getAttribute('data-digit-normalize') || 'search');
        }
    }

    function initInputBindings() {
        if (global.__digitNormalizerInputsBound) return;
        global.__digitNormalizerInputsBound = true;
        scanAndBind(document);
        if (global.MutationObserver && document.body) {
            var mo = new MutationObserver(function (mutations) {
                var m, n, node;
                for (m = 0; m < mutations.length; m++) {
                    for (n = 0; n < mutations[m].addedNodes.length; n++) {
                        node = mutations[m].addedNodes[n];
                        if (node.nodeType === 1) scanAndBind(node);
                    }
                }
            });
            mo.observe(document.body, { childList: true, subtree: true });
        }
    }

    var api = {
        toAsciiDigits: toAsciiDigits,
        normalizeSearchQuery: normalizeSearchQuery,
        digitsOnly: digitsOnly,
        normalizePhone: normalizePhone,
        isPhoneNumberSearch: isPhoneNumberSearch,
        patientSearchUrl: patientSearchUrl,
        normalizeInputValue: normalizeInputValue,
        bindInput: bindInput,
        scanAndBind: scanAndBind,
        isDateQuery: function (query) {
            var trimmed = normalizeSearchQuery(query);
            if (trimmed.length < 6) return false;
            return /^\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}$/.test(trimmed)
                || /^\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2}$/.test(trimmed);
        }
    };

    global.DigitNormalizer = api;
    patchFetch();

    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initInputBindings);
        } else {
            initInputBindings();
        }
    }
})(typeof window !== 'undefined' ? window : this);
