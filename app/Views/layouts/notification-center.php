<?php
/**
 * Notification Center Panel
 * Included by main.php and secretary_main.php
 * Opened by #notificationsToggle bell button.
 * Body is populated by /assets/js/notification-center.js
 */
$notifCenterContext = $notifCenterContext ?? 'doctor';
$notifCenterLang = $notifCenterLang ?? 'en';
$notifIsAr = ($notifCenterLang === 'ar');
$notifT = $notifIsAr ? [
    'title'       => 'الإشعارات',
    'close'       => 'إغلاق الإشعارات',
    'tab_view'    => 'عرض الإشعارات',
    'tab_notif'   => 'الإشعارات',
    'tab_activity'=> 'النشاط',
    'dock'        => 'إجراءات سريعة',
    'snooze_menu' => 'خيارات التأجيل',
    'snooze_hdr'  => 'تأجيل حتى',
    'snooze_1h'   => 'لمدة ساعة',
    'snooze_4h'   => 'لمدة 4 ساعات',
    'snooze_tmr'  => 'غداً 9:00 ص',
    'snooze_week' => 'الأسبوع القادم',
    'snooze_custom'=> 'مخصص…',
    'snooze_apply'=> 'تطبيق',
    'act_snooze'  => 'تأجيل',
    'act_pin'     => 'تثبيت',
    'act_read'    => 'تعليم كمقروء',
    'act_delete'  => 'حذف',
    'stack_tap'   => 'اضغط للتوسيع',
    'stack_expand'=> 'توسيع المجموعة',
] : [
    'title'       => 'Notifications',
    'close'       => 'Close notifications',
    'tab_view'    => 'Notification view',
    'tab_notif'   => 'Notifications',
    'tab_activity'=> 'Activity',
    'dock'        => 'Quick actions',
    'snooze_menu' => 'Snooze options',
    'snooze_hdr'  => 'Snooze until',
    'snooze_1h'   => 'For 1 hour',
    'snooze_4h'   => 'For 4 hours',
    'snooze_tmr'  => 'Tomorrow 9:00 AM',
    'snooze_week' => 'Next week',
    'snooze_custom'=> 'Custom…',
    'snooze_apply'=> 'Apply',
    'act_snooze'  => 'Snooze',
    'act_pin'     => 'Pin',
    'act_read'    => 'Mark as read',
    'act_delete'  => 'Delete',
    'stack_tap'   => 'Tap to expand',
    'stack_expand'=> 'Expand stack',
];
?>
<div class="notif-panel" id="notifPanel" role="dialog" aria-modal="false" aria-labelledby="notifPanelTitle" aria-hidden="true" hidden
     data-notif-context="<?= htmlspecialchars($notifCenterContext, ENT_QUOTES, 'UTF-8') ?>"
     data-notif-lang="<?= htmlspecialchars($notifCenterLang, ENT_QUOTES, 'UTF-8') ?>">
    <div class="notif-panel__inner">
        <!-- Header -->
        <header class="notif-header">
            <div class="notif-header__row">
                <h2 class="notif-title" id="notifPanelTitle"><?= $notifT['title'] ?></h2>
                <button type="button" class="notif-close" id="notifCloseBtn" aria-label="<?= $notifT['close'] ?>">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="notif-tabs" role="tablist" aria-label="<?= $notifT['tab_view'] ?>">
                <button type="button" class="notif-tab is-active" data-tab="notifications" role="tab" aria-selected="true" id="notifTabNotifications">
                    <i class="bi bi-bell" aria-hidden="true"></i>
                    <span><?= $notifT['tab_notif'] ?></span>
                    <span class="notif-tab__count" id="notifTabCount" hidden>0</span>
                </button>
                <button type="button" class="notif-tab" data-tab="activity" role="tab" aria-selected="false" id="notifTabActivity">
                    <i class="bi bi-activity" aria-hidden="true"></i>
                    <span><?= $notifT['tab_activity'] ?></span>
                </button>
                <span class="notif-tabs__indicator" aria-hidden="true"></span>
            </div>
        </header>

        <!-- Body -->
        <div class="notif-body" id="notifBody" role="tabpanel" aria-labelledby="notifTabNotifications">
            <!-- Initial skeleton -->
            <div class="notif-skeleton" aria-hidden="true">
                <div class="notif-skeleton__row"></div>
                <div class="notif-skeleton__row"></div>
                <div class="notif-skeleton__row"></div>
            </div>
        </div>

        <!-- Footer Dock -->
        <footer class="notif-dock" aria-label="<?= $notifT['dock'] ?>">
            <button type="button" class="qa-btn" data-action="new-patient" aria-label="New patient" title="New patient">
                <i class="bi bi-person-plus" aria-hidden="true"></i>
            </button>
            <button type="button" class="qa-btn" data-action="new-note" aria-label="New quick note" title="New quick note">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
            </button>
            <button type="button" class="qa-btn" data-action="notes-drawer" aria-label="Open notes" title="Open notes">
                <i class="bi bi-journal-text" aria-hidden="true"></i>
            </button>
            <button type="button" class="qa-btn" data-action="calendar" aria-label="Open calendar" title="Open calendar">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
            </button>
            <button type="button" class="qa-btn" data-action="boards" aria-label="Open boards" title="Open boards">
                <i class="bi bi-grid-3x3-gap-fill" aria-hidden="true"></i>
            </button>
            <button type="button" class="qa-btn" data-action="todo" aria-label="Open to-do drawer" title="Open to-do">
                <i class="bi bi-check2-square" aria-hidden="true"></i>
            </button>
        </footer>
    </div>

    <!-- Snooze popover template (cloned by JS) -->
    <template id="notifSnoozeTemplate">
        <div class="notif-snooze" role="menu" aria-label="<?= $notifT['snooze_menu'] ?>">
            <div class="notif-snooze__header">
                <i class="bi bi-moon-stars" aria-hidden="true"></i>
                <span><?= $notifT['snooze_hdr'] ?></span>
            </div>
            <button type="button" class="notif-snooze__opt" role="menuitem" data-snooze="1h">
                <i class="bi bi-clock" aria-hidden="true"></i>
                <span class="notif-snooze__label"><?= $notifT['snooze_1h'] ?></span>
                <span class="notif-snooze__hint" data-hint="1h"></span>
            </button>
            <button type="button" class="notif-snooze__opt" role="menuitem" data-snooze="4h">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                <span class="notif-snooze__label"><?= $notifT['snooze_4h'] ?></span>
                <span class="notif-snooze__hint" data-hint="4h"></span>
            </button>
            <button type="button" class="notif-snooze__opt" role="menuitem" data-snooze="tomorrow">
                <i class="bi bi-sunrise" aria-hidden="true"></i>
                <span class="notif-snooze__label"><?= $notifT['snooze_tmr'] ?></span>
                <span class="notif-snooze__hint" data-hint="tomorrow"></span>
            </button>
            <button type="button" class="notif-snooze__opt" role="menuitem" data-snooze="week">
                <i class="bi bi-calendar-week" aria-hidden="true"></i>
                <span class="notif-snooze__label"><?= $notifT['snooze_week'] ?></span>
                <span class="notif-snooze__hint" data-hint="week"></span>
            </button>
            <div class="notif-snooze__divider" aria-hidden="true"></div>
            <button type="button" class="notif-snooze__opt notif-snooze__opt--custom" role="menuitem" data-snooze="custom">
                <i class="bi bi-calendar-plus" aria-hidden="true"></i>
                <span class="notif-snooze__label"><?= $notifT['snooze_custom'] ?></span>
            </button>
            <div class="notif-snooze__custom" hidden>
                <input type="datetime-local" class="notif-snooze__input" aria-label="<?= $notifT['snooze_custom'] ?>">
                <button type="button" class="notif-snooze__apply"><?= $notifT['snooze_apply'] ?></button>
            </div>
        </div>
    </template>

    <!-- Notification row template -->
    <template id="notifRowTemplate">
        <article class="notif-row" role="listitem">
            <div class="notif-row__icon" data-tile></div>
            <div class="notif-row__content">
                <div class="notif-row__head">
                    <h3 class="notif-row__title" data-title></h3>
                    <span class="notif-row__time" data-time></span>
                </div>
                <p class="notif-row__body" data-body></p>
                <div class="notif-row__meta" data-meta hidden></div>
            </div>
            <div class="notif-row__actions" role="group" aria-label="Notification actions">
                <button type="button" class="notif-act" data-act="snooze" aria-label="<?= $notifT['act_snooze'] ?>" title="<?= $notifT['act_snooze'] ?>">
                    <i class="bi bi-moon-stars" aria-hidden="true"></i>
                </button>
                <button type="button" class="notif-act" data-act="pin" aria-label="<?= $notifT['act_pin'] ?>" title="<?= $notifT['act_pin'] ?>">
                    <i class="bi bi-pin-angle" aria-hidden="true"></i>
                </button>
                <button type="button" class="notif-act" data-act="read" aria-label="<?= $notifT['act_read'] ?>" title="<?= $notifT['act_read'] ?>">
                    <i class="bi bi-check2" aria-hidden="true"></i>
                </button>
                <button type="button" class="notif-act notif-act--danger" data-act="delete" aria-label="<?= $notifT['act_delete'] ?>" title="<?= $notifT['act_delete'] ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
            </div>
        </article>
    </template>

    <!-- Stack row template (collapsed group) -->
    <template id="notifStackTemplate">
        <article class="notif-row notif-row--stack" role="listitem" aria-expanded="false">
            <div class="notif-row__stack-layer" aria-hidden="true"></div>
            <div class="notif-row__stack-layer notif-row__stack-layer--2" aria-hidden="true"></div>
            <div class="notif-row__icon" data-tile></div>
            <div class="notif-row__content">
                <div class="notif-row__head">
                    <h3 class="notif-row__title" data-title></h3>
                    <span class="notif-row__time" data-time></span>
                </div>
                <p class="notif-row__body">
                    <span class="notif-stack__count" data-count></span>
                    <span class="notif-stack__hint"><?= $notifT['stack_tap'] ?></span>
                </p>
            </div>
            <button type="button" class="notif-row__expand" aria-label="<?= $notifT['stack_expand'] ?>">
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </button>
        </article>
    </template>

    <!-- Activity feed item template -->
    <template id="notifActivityTemplate">
        <article class="notif-activity" role="listitem">
            <span class="notif-activity__dot" data-dot></span>
            <div class="notif-activity__content">
                <p class="notif-activity__text" data-text></p>
                <span class="notif-activity__time" data-time></span>
            </div>
        </article>
    </template>

    <!-- Bucket header template -->
    <template id="notifBucketTemplate">
        <section class="notif-bucket">
            <header class="notif-bucket__head">
                <h4 class="notif-bucket__title" data-title></h4>
                <span class="notif-bucket__line" aria-hidden="true"></span>
            </header>
            <div class="notif-bucket__list" role="list" data-list></div>
        </section>
    </template>
</div>
