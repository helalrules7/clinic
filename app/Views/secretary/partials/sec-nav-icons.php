<?php
/**
 * Secretary sidebar animated icons — SVG ports inspired by Its Hover (MIT).
 * @see https://www.itshover.com/icons
 */
if (!function_exists('sec_nav_icon')) {
    function sec_nav_icon(string $key): string
    {
        static $icons = null;
        if ($icons === null) {
            $icons = [
                'dashboard' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--dashboard" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<rect class="rect-1" width="7" height="9" x="3" y="3" rx="1"/>
<rect class="rect-2" width="7" height="5" x="14" y="3" rx="1"/>
<rect class="rect-3" width="7" height="9" x="14" y="12" rx="1"/>
<rect class="rect-4" width="7" height="5" x="3" y="16" rx="1"/>
</svg>
</span>
SVG,
                'bookings' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--bookings" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="cal-header">
<path class="cal-body" d="M8 2v4"/>
<path class="cal-body" d="M16 2v4"/>
<rect class="cal-frame" width="18" height="18" x="3" y="4" rx="2"/>
<path class="cal-divider" d="M3 10h18"/>
</g>
<path class="cal-dot cal-dot-1" d="M8 14h.01"/>
<path class="cal-dot cal-dot-2" d="M12 14h.01"/>
<path class="cal-dot cal-dot-3" d="M16 14h.01"/>
</svg>
</span>
SVG,
                'payments' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--payments" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="card-body" d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
<path class="card-stripe" d="M3 10l18 0" opacity="0"/>
<path class="card-chip" d="M7 15l.01 0"/>
<path class="card-number" d="M11 15l2 0"/>
</svg>
</span>
SVG,
                'patients' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--patients" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="user-primary">
<path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
<path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
</g>
<g class="user-secondary">
<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
<path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
</g>
</svg>
</span>
SVG,
                'settings' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--settings" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<line class="path-1-left" x1="3" y1="5" x2="10" y2="5"/>
<line class="slider-1" x1="14" y1="3" x2="14" y2="7"/>
<line class="path-1-right" x1="14" y1="5" x2="21" y2="5"/>
<line class="path-2-left" x1="3" y1="12" x2="8" y2="12"/>
<line class="slider-2" x1="8" y1="10" x2="8" y2="14"/>
<line class="path-2-right" x1="12" y1="12" x2="21" y2="12"/>
<line class="path-3-left" x1="3" y1="19" x2="12" y2="19"/>
<line class="slider-3" x1="16" y1="17" x2="16" y2="21"/>
<line class="path-3-right" x1="16" y1="19" x2="21" y2="19"/>
</svg>
</span>
SVG,
                'profile' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--profile" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="sec-icon-profile-figure">
<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
<path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
</g>
</svg>
</span>
SVG,
                'logout' => <<<'SVG'
<span class="sec-nav-icon sec-nav-icon--logout" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="logout-door" d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
<path class="logout-arrow" d="M9 12h12"/>
<path class="logout-arrow-bottom" d="M18 15l3 -3l-3 -3"/>
</svg>
</span>
SVG,
            ];
        }

        return $icons[$key] ?? '';
    }
}
