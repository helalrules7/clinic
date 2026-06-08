<?php
/**
 * Doctor sidebar animated icons — P1 core + P2 clinical nav (SVG + CSS).
 * Its Hover (MIT): https://www.itshover.com/icons
 * Calendar: Lucide-style paths (ISC) — animated via CSS.
 */
if (!function_exists('doc_nav_icon')) {
    function doc_nav_icon(string $key): string
    {
        static $icons = null;
        if ($icons === null) {
            $icons = [
                'dashboard' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--dashboard" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<rect class="rect-1" width="7" height="9" x="3" y="3" rx="1"/>
<rect class="rect-2" width="7" height="5" x="14" y="3" rx="1"/>
<rect class="rect-3" width="7" height="9" x="14" y="12" rx="1"/>
<rect class="rect-4" width="7" height="5" x="3" y="16" rx="1"/>
</svg>
</span>
SVG,
                'calendar' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--calendar" aria-hidden="true">
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
                'patients' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--patients" aria-hidden="true">
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
                'payments' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--payments" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="card-body" d="M3 5m0 3a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v8a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3z"/>
<path class="card-stripe" d="M3 10l18 0" opacity="0"/>
<path class="card-chip" d="M7 15l.01 0"/>
<path class="card-number" d="M11 15l2 0"/>
</svg>
</span>
SVG,
                'settings' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--settings" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square" stroke-miterlimit="10">
<g class="gear-rotator">
<circle class="gear-center" cx="16" cy="16" r="5"/>
<path class="gear-body" d="m30,17.5v-3l-3.388-1.355c-.25-.933-.617-1.815-1.089-2.633l1.436-3.351-2.121-2.121-3.351,1.436c-.817-.472-1.7-.838-2.633-1.089l-1.355-3.388h-3l-1.355,3.388c-.933.25-1.815.617-2.633,1.089l-3.351-1.436-2.121,2.121 1.436,3.351c-.472.817-.838,1.7-1.089,2.633l-3.388,1.355v3l3.388,1.355c.25.933.617,1.815,1.089,2.633l-1.436,3.351 2.121,2.121 3.351-1.436c.817.472 1.7.838 2.633,1.089l1.355,3.388h3l1.355-3.388c.933-.25 1.815-.617 2.633-1.089l3.351,1.436 2.121-2.121-1.436-3.351c.472-.817.838-1.7 1.089-2.633l3.388-1.355Z"/>
</g>
</svg>
</span>
SVG,
                'profile' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--profile" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="doc-icon-profile-figure">
<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/>
<path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
</g>
</svg>
</span>
SVG,
                'logout' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--logout" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="logout-door" d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
<path class="logout-arrow" d="M9 12h12"/>
<path class="logout-arrow-bottom" d="M18 15l3 -3l-3 -3"/>
</svg>
</span>
SVG,
                'board' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--board" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<rect class="kanban-frame" width="18" height="18" x="3" y="3" rx="2"/>
<path class="kanban-col kanban-col-1" d="M8 7v7"/>
<path class="kanban-col kanban-col-2" d="M12 7v4"/>
<path class="kanban-col kanban-col-3" d="M16 7v9"/>
</svg>
</span>
SVG,
                'drugs' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--drugs" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="pill-body">
<path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
<path class="pill-line" d="m8.5 8.5 7 7"/>
</g>
</svg>
</span>
SVG,
                'tags' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--tags" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="tag-shape">
<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
<circle class="tag-dot" cx="7.5" cy="7.5" r=".5" fill="currentColor" stroke="none"/>
</g>
</svg>
</span>
SVG,
                'reports' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--reports" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<path class="chart-base" d="M4 19l16 0"/>
<path class="chart-line" d="M4 15l4 -6l4 2l4 -5l4 4"/>
</svg>
</span>
SVG,
                'alerts' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--alerts" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" fill="currentColor" stroke="currentColor">
<path class="bell-clapper" d="M14.235 19c.865 0 1.322 1.024 .745 1.668a3.992 3.992 0 0 1 -2.98 1.332a3.992 3.992 0 0 1 -2.98 -1.332c-.552 -.616 -.158 -1.579 .634 -1.661l.11 -.006h4.471z"/>
<path class="bell-body" d="M12 2c1.358 0 2.506 .903 2.875 2.141l.046 .171l.008 .043a8.013 8.013 0 0 1 4.024 6.069l.028 .287l.019 .289v2.931l.021 .136a3 3 0 0 0 1.143 1.847l.167 .117l.162 .099c.86 .487 .56 1.766 -.377 1.864l-.116 .006h-16c-1.028 0 -1.387 -1.364 -.493 -1.87a3 3 0 0 0 1.472 -2.063l.021 -.143l.001 -2.97a8 8 0 0 1 3.821 -6.454l.248 -.146l.01 -.043a3.003 3.003 0 0 1 2.562 -2.29l.182 -.017l.176 -.004z"/>
</svg>
</span>
SVG,
                'notes' => <<<'SVG'
<span class="doc-nav-icon doc-nav-icon--notes" aria-hidden="true">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
<g class="note-sheet">
<path d="M16 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8Z"/>
<path class="note-fold" d="M15 3v4a2 2 0 0 0 2 2h4"/>
</g>
</svg>
</span>
SVG,
            ];
        }

        return $icons[$key] ?? '';
    }
}
