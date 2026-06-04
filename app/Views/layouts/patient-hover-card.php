<?php
/**
 * Patient Hover Card — v11.0.0
 *
 * Single recycled hover-card element. Included once at body level by
 * main.php / secretary_main.php. Activated by patient-hover.js which
 * delegates listeners on any [data-patient-id] trigger anywhere in the
 * document.
 *
 * Markup contract:
 *   - One <aside id="patientCard"> per document (recycled).
 *   - Two regions: .pc-skeleton (shown while fetching) and .pc-body
 *     (populated from /api/patients/:id/summary).
 *   - role="dialog" + aria-labelledby for accessibility.
 *   - hidden attribute by default; JS toggles display + animation class.
 */
?>
<aside
    class="patient-card"
    id="patientCard"
    role="dialog"
    aria-modal="false"
    aria-labelledby="patientCardName"
    aria-describedby="patientCardSummary"
    tabindex="-1"
    hidden
>
    <!-- Skeleton: visible while the summary request is in flight. -->
    <div class="pc-skeleton" data-pc-skeleton hidden>
        <div class="pc-skel-header">
            <span class="pc-skel pc-skel-avatar" aria-hidden="true"></span>
            <div class="pc-skel-lines">
                <span class="pc-skel pc-skel-line pc-skel-line-lg" aria-hidden="true"></span>
                <span class="pc-skel pc-skel-line pc-skel-line-sm" aria-hidden="true"></span>
            </div>
        </div>
        <div class="pc-skel-rows">
            <span class="pc-skel pc-skel-row" aria-hidden="true"></span>
            <span class="pc-skel pc-skel-row" aria-hidden="true"></span>
            <span class="pc-skel pc-skel-row" aria-hidden="true"></span>
        </div>
        <span class="pc-skel pc-skel-cta" aria-hidden="true"></span>
        <span class="visually-hidden">Loading patient summary…</span>
    </div>

    <!-- Body: hydrated by patient-hover.js. -->
    <div class="pc-body" data-pc-body hidden>
        <header class="pc-header">
            <span class="pc-avatar" data-pc-avatar aria-hidden="true">
                <span class="pc-avatar-initials" data-pc-initials>?</span>
            </span>
            <div class="pc-ident">
                <h3 class="pc-name" id="patientCardName" data-pc-name>—</h3>
                <p class="pc-meta" data-pc-meta>
                    <span class="pc-meta-age" data-pc-age></span>
                    <span class="pc-meta-sep" aria-hidden="true">·</span>
                    <span class="pc-meta-gender" data-pc-gender></span>
                    <span class="pc-meta-sep pc-meta-sep-phone" aria-hidden="true" hidden>·</span>
                    <span class="pc-meta-phone" data-pc-phone hidden></span>
                </p>
            </div>
        </header>

        <dl class="pc-stats" id="patientCardSummary">
            <div class="pc-stat" data-pc-row="last-visit">
                <dt class="pc-stat-label">
                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                    <span>Last visit</span>
                </dt>
                <dd class="pc-stat-value" data-pc-last-visit>—</dd>
            </div>
            <div class="pc-stat" data-pc-row="next-appt">
                <dt class="pc-stat-label">
                    <i class="bi bi-clock" aria-hidden="true"></i>
                    <span>Next appointment</span>
                </dt>
                <dd class="pc-stat-value" data-pc-next-appt>—</dd>
            </div>
            <div class="pc-stat pc-stat-alerts" data-pc-row="alerts" hidden>
                <dt class="pc-stat-label">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                    <span>Active alerts</span>
                </dt>
                <dd class="pc-stat-value">
                    <span class="pc-alerts-chip" data-pc-alerts>
                        <span class="pc-alerts-count" data-pc-alerts-count>0</span>
                        <span class="pc-alerts-label" data-pc-alerts-label>alerts</span>
                    </span>
                </dd>
            </div>
        </dl>

        <a class="pc-cta" href="#" data-pc-link>
            <span>Open profile</span>
            <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
        </a>
    </div>

    <!-- Error state: shown if the fetch fails. -->
    <div class="pc-error" data-pc-error hidden role="alert">
        <i class="bi bi-wifi-off" aria-hidden="true"></i>
        <span data-pc-error-msg>Couldn’t load patient summary.</span>
    </div>
</aside>
