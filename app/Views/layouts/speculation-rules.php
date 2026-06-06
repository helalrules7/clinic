<?php
/**
 * Speculation Rules API — conservative browser-level prefetch.
 *
 * WHY prefetch (not prerender):
 * - prefetch only downloads HTML/CSS for a likely next navigation.
 * - prerender would execute page JS in a hidden context — unsafe for a
 *   medical app with session-bound patient data and form side-effects.
 *
 * SAFETY:
 * - Emitted only for authenticated users (this partial is included from
 *   layouts/main.php and layouts/secretary_main.php — never auth/login).
 * - Disabled during Admin "View As" impersonation.
 * - Strict URL allowlist derived from GET routes in public/index.php.
 * - Secondary not-block excludes action-like subpaths (edit, export, print…).
 *
 * @see ORTHO_V11_FEATURES.md §2.15
 */
if (!isset($_SESSION['user'])) {
    return;
}
if (!empty($_SESSION['view_as_mode'])) {
    return;
}

$speculationRules = [
    'prefetch' => [
        [
            'source' => 'document',
            'where' => [
                'and' => [
                    [
                        'or' => [
                            // Doctor — safe navigation indexes (read-only GET pages)
                            ['href_matches' => '/doctor/dashboard'],
                            ['href_matches' => '/doctor/board'],
                            ['href_matches' => '/doctor/calendar'],
                            ['href_matches' => '/doctor/organizer'],
                            ['href_matches' => '/doctor/patients'],
                            ['href_matches' => '/doctor/profile'],
                            ['href_matches' => '/doctor/drugs'],
                            ['href_matches' => '/doctor/payments'],
                            ['href_matches' => '/doctor/daily-closure'],
                            ['href_matches' => '/doctor/reports'],
                            ['href_matches' => '/doctor/settings'],
                            ['href_matches' => '/doctor/alerts'],
                            ['href_matches' => '/doctor/notes'],
                            ['href_matches' => '/doctor/todos'],
                            ['href_matches' => '/doctor/media'],
                            ['href_matches' => '/doctor/glasses'],
                            ['href_matches' => '/doctor/medications'],
                            // Doctor — dynamic read-only views (edit/* excluded below)
                            ['href_matches' => '/doctor/patients/*'],
                            ['href_matches' => '/doctor/appointments/*'],

                            // Secretary — safe navigation indexes
                            ['href_matches' => '/secretary/dashboard'],
                            ['href_matches' => '/secretary/bookings'],
                            ['href_matches' => '/secretary/payments'],
                            ['href_matches' => '/secretary/patients'],
                            ['href_matches' => '/secretary/profile'],
                            // Secretary — patient / booking detail views
                            ['href_matches' => '/secretary/patients/*'],
                            ['href_matches' => '/secretary/bookings/*'],

                            // Admin — safe navigation indexes (backup/view-as excluded below)
                            ['href_matches' => '/admin/dashboard'],
                            ['href_matches' => '/admin/users'],
                            ['href_matches' => '/admin/settings'],
                            ['href_matches' => '/admin/notifications'],
                            ['href_matches' => '/admin/media'],
                        ],
                    ],
                    [
                        'not' => [
                            'or' => [
                                // Auth / public
                                ['href_matches' => '/'],
                                ['href_matches' => '/login*'],
                                ['href_matches' => '/logout*'],
                                // API, AJAX, print, downloads
                                ['href_matches' => '/api/*'],
                                ['href_matches' => '/print/*'],
                                ['href_matches' => '*/download*'],
                                // Data-changing or action-like GET subpaths
                                ['href_matches' => '*/edit'],
                                ['href_matches' => '*/edit/*'],
                                ['href_matches' => '*/new'],
                                ['href_matches' => '*/new/*'],
                                ['href_matches' => '*/export*'],
                                ['href_matches' => '*/print*'],
                                ['href_matches' => '*/receipt*'],
                                ['href_matches' => '*/invoice*'],
                                ['href_matches' => '*/delete*'],
                                ['href_matches' => '*/remove*'],
                                ['href_matches' => '*/destroy*'],
                                ['href_matches' => '*/save*'],
                                ['href_matches' => '*/store*'],
                                ['href_matches' => '*/update*'],
                                ['href_matches' => '*/submit*'],
                                ['href_matches' => '*/cancel*'],
                                ['href_matches' => '*/approve*'],
                                ['href_matches' => '*/reject*'],
                                ['href_matches' => '*/archive*'],
                                ['href_matches' => '*/restore*'],
                                ['href_matches' => '*/status*'],
                                ['href_matches' => '*/mark*'],
                                ['href_matches' => '*/assign*'],
                                ['href_matches' => '*/generate*'],
                                ['href_matches' => '*/pdf*'],
                                ['href_matches' => '*/backup*'],
                                ['href_matches' => '*/details*'],
                                ['href_matches' => '*/calendar*'],
                                // Admin impersonation (session-mutating GET)
                                ['href_matches' => '/admin/view-as*'],
                                ['href_matches' => '/admin/stop-view-as*'],
                                // Report export (GET download)
                                ['href_matches' => '/doctor/reports/export*'],
                            ],
                        ],
                    ],
                ],
            ],
            'eagerness' => 'moderate',
        ],
    ],
];
?>
<script type="speculationrules">
<?= json_encode($speculationRules, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
</script>
