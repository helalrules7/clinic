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

    var api = {
        toAsciiDigits: toAsciiDigits,
        normalizeSearchQuery: normalizeSearchQuery,
        digitsOnly: digitsOnly,
        normalizePhone: normalizePhone,
        isPhoneNumberSearch: isPhoneNumberSearch,
        patientSearchUrl: patientSearchUrl,
        isDateQuery: function (query) {
            var trimmed = normalizeSearchQuery(query);
            if (trimmed.length < 6) return false;
            return /^\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}$/.test(trimmed)
                || /^\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2}$/.test(trimmed);
        }
    };

    global.DigitNormalizer = api;
    patchFetch();
})(typeof window !== 'undefined' ? window : this);
