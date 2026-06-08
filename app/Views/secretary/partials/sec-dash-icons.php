<?php
/**
 * Secretary dashboard animated icons — §32 ports + dashboard-specific SVG.
 */
if (!function_exists('sec_dash_icon_wrap')) {
    function sec_dash_icon_wrap(string $modifier, string $svg, string $size, string $extraClass = ''): string
    {
        $safeSize = preg_replace('/[^a-z0-9-]/', '', $size) ?: 'md';
        $class = 'sec-dash-icon sec-dash-icon--' . $modifier . ' sec-dash-icon--size-' . $safeSize;
        if ($extraClass !== '') {
            $class .= ' ' . trim($extraClass);
        }

        return '<span class="' . $class . '" aria-hidden="true">' . $svg . '</span>';
    }

    function sec_dash_icon_defs(): array
    {
        static $defs = null;
        if ($defs !== null) {
            return $defs;
        }

        $defs = [
            'hourglass' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="hg-top" d="M5 22h14"/><path class="hg-bottom" d="M5 2h14"/><path class="hg-glass" d="M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22"/><path class="hg-glass" d="M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"/></svg>',
            'user-check' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="uc-user"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></g><path class="uc-check" d="m16 11 2 2 4-4"/></svg>',
            'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="cc-ring" cx="12" cy="12" r="10"/><path class="cc-check" d="m9 12 2 2 4-4"/></svg>',
            'x-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="xc-ring" cx="12" cy="12" r="10"/><path class="xc-x" d="m15 9-6 6"/><path class="xc-x" d="m9 9 6 6"/></svg>',
            'calendar-plus' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="cp-cal"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></g><path class="cp-plus-v" d="M12 14v4"/><path class="cp-plus-h" d="M10 16h4"/></svg>',
            'patient-plus' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="pp-user"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></g><path class="pp-plus-v" d="M19 8v6"/><path class="pp-plus-h" d="M16 11h6"/></svg>',
            'lightbulb' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="lb-bulb" d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path class="lb-base" d="M9 18h6"/><path class="lb-base" d="M10 22h4"/></svg>',
            'calendar-event' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="ce-cal"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></g><rect class="ce-event" x="7" y="13" width="10" height="5" rx="1"/></svg>',
            'calendar-x' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="cx-cal"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></g><path class="cx-x" d="m10 16 4-4"/><path class="cx-x" d="m14 16-4-4"/></svg>',
            'calendar-check' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="ck-cal"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></g><path class="ck-check" d="m9 16 2 2 4-4"/></svg>',
            'pie-chart' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="pc-slice" d="M21 12a9 9 0 1 1-9-9"/><path class="pc-base" d="M12 3v9h9"/></svg>',
            'cash-coin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="cc-coin" cx="12" cy="12" r="10"/><path class="cc-s" d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path class="cc-line" d="M12 6v2"/><path class="cc-line" d="M12 16v2"/></svg>',
            'lightning' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="lt-bolt" d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
            'wallet' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="wl-body" d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path class="wl-flap" d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>',
            'trend-up' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="tu-line" d="m3 17 6-6 4 4 8-8"/><path class="tu-arrow" d="M14 7h7v7"/></svg>',
            'trend-down' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="td-line" d="m3 7 6 6 4-4 8 8"/><path class="td-arrow" d="M14 17h7v-7"/></svg>',
            'trend-flat' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="tf-line" d="M5 12h14"/></svg>',
            'calendar-day' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><g class="cd-cal"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></g><path class="cd-day" d="M8 14h.01"/><path class="cd-day" d="M12 14h.01"/><path class="cd-day" d="M16 14h.01"/></svg>',
            'eye' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="ey-shape" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle class="ey-pupil" cx="12" cy="12" r="3"/></svg>',
            'check' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="chk-ring" cx="12" cy="12" r="10"/><path class="chk-mark" d="m9 12 2 2 4-4"/></svg>',
            'stars' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="st-main" d="m12 3 1.9 5.8H20l-4.8 3.5 1.8 5.7L12 14.8 7 17.9l1.8-5.7L4 8.8h6.1z"/><path class="st-spark" d="M5 3v2"/><path class="st-spark" d="M19 17v2"/></svg>',
            'arrow-left' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="al-shaft" d="M19 12H5"/><path class="al-head" d="m12 19-7-7 7-7"/></svg>',
            'close' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="cl-x" d="M18 6 6 18"/><path class="cl-x" d="m6 6 12 12"/></svg>',
            'receipt' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="rc-body" d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path class="rc-line" d="M8 7h8"/><path class="rc-line" d="M8 11h8"/><path class="rc-line" d="M8 15h5"/></svg>',
            'clock' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="cl-face" cx="12" cy="12" r="10"/><path class="cl-hand" d="M12 6v6l4 2"/></svg>',
            'person-badge' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="pb-badge" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle class="pb-user" cx="9" cy="7" r="4"/><path class="pb-card" d="M22 11h-6"/><path class="pb-card" d="M19 8v6"/></svg>',
            'exclamation' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="ex-body" d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path class="ex-mark" d="M12 9v4"/><path class="ex-dot" d="M12 17h.01"/></svg>',
            'location' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="loc-pin" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle class="loc-dot" cx="12" cy="10" r="3"/></svg>',
            'info' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="inf-ring" cx="12" cy="12" r="10"/><path class="inf-mark" d="M12 16v-4"/><path class="inf-dot" d="M12 8h.01"/></svg>',
            'arrow-up-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="auc-ring" cx="12" cy="12" r="10"/><path class="auc-arrow" d="m16 12-4-4-4 4"/><path class="auc-shaft" d="M12 16V8"/></svg>',
            'arrow-down-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="adc-ring" cx="12" cy="12" r="10"/><path class="adc-arrow" d="m8 12 4 4 4-4"/><path class="adc-shaft" d="M12 8v8"/></svg>',
            'calculator' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect class="calc-body" width="16" height="20" x="4" y="2" rx="2"/><path class="calc-screen" d="M8 6h8"/><path class="calc-key" d="M8 10h.01"/><path class="calc-key" d="M12 10h.01"/><path class="calc-key" d="M16 10h.01"/><path class="calc-key" d="M8 14h.01"/><path class="calc-key" d="M12 14h.01"/><path class="calc-key" d="M16 14h.01"/></svg>',
            'search' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="srch-lens" cx="11" cy="11" r="8"/><path class="srch-handle" d="m21 21-4.3-4.3"/></svg>',
            'plus-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="pc-ring" cx="12" cy="12" r="10"/><path class="pc-plus-v" d="M12 8v8"/><path class="pc-plus-h" d="M8 12h8"/></svg>',
            'minus-circle' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle class="mc-ring" cx="12" cy="12" r="10"/><path class="mc-minus" d="M8 12h8"/></svg>',
            'lock' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect class="lk-body" width="18" height="11" x="3" y="11" rx="2"/><path class="lk-shackle" d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            'funnel' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="fn-body" d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>',
            'chevron-right' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="chv" d="m9 18 6-6-6-6"/></svg>',
            'chevron-left' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path class="chv" d="m15 18-6-6 6-6"/></svg>',
        ];

        return $defs;
    }

    function sec_dash_nav_key(string $key): ?string
    {
        return match ($key) {
            'calendar', 'bookings' => 'bookings',
            'payments', 'credit-card' => 'payments',
            'patients' => 'patients',
            default => null,
        };
    }

    function sec_dash_icon(string $key, string $size = 'md', string $extraClass = ''): string
    {
        $navKey = sec_dash_nav_key($key);
        if ($navKey !== null) {
            if (!function_exists('sec_nav_icon')) {
                require __DIR__ . '/sec-nav-icons.php';
            }
            $html = sec_nav_icon($navKey);
            if ($html === '') {
                return '';
            }
            $html = str_replace('sec-nav-icon', 'sec-dash-icon', $html);
            $safeSize = preg_replace('/[^a-z0-9-]/', '', $size) ?: 'md';
            $html = preg_replace(
                '/class="sec-dash-icon (sec-dash-icon--\w+)"/',
                'class="sec-dash-icon $1 sec-dash-icon--size-' . $safeSize . ($extraClass ? ' ' . trim($extraClass) : '') . '"',
                $html,
                1
            ) ?? $html;

            return $html;
        }

        $defs = sec_dash_icon_defs();
        if (!isset($defs[$key])) {
            return '';
        }

        return sec_dash_icon_wrap($key, $defs[$key], $size, $extraClass);
    }

    function sec_dash_icon_inline(string $key, string $size = 'md'): string
    {
        return sec_dash_icon($key, $size, 'sec-dash-icon--inline');
    }

    function sec_dash_icons_registry(): array
    {
        $keys = [
            'calendar', 'calendar-plus', 'hourglass', 'user-check', 'check-circle', 'x-circle',
            'payments', 'credit-card', 'patient-plus', 'lightbulb', 'calendar-event', 'calendar-x',
            'calendar-check', 'pie-chart', 'cash-coin', 'lightning', 'wallet',
            'trend-up', 'trend-down', 'trend-flat', 'calendar-day',
            'eye', 'check', 'check-circle', 'stars', 'arrow-left', 'close',
            'receipt', 'clock', 'person-badge', 'exclamation', 'location', 'info',
            'arrow-up-circle', 'arrow-down-circle', 'calculator', 'search',
            'plus-circle', 'minus-circle', 'lock', 'funnel', 'chevron-right', 'chevron-left',
        ];
        $sizes = ['sm', 'md', 'stat', 'tile', 'head', 'empty', 'page'];
        $registry = [];
        foreach (array_unique($keys) as $key) {
            foreach ($sizes as $size) {
                $registry[$key . ':' . $size] = sec_dash_icon($key, $size);
            }
        }

        return $registry;
    }
}
