<?php
/**
 * Activities page — dedicated, filterable feed over /api/activity/page.
 * Shared by doctor (English) and secretary (Arabic). $activitiesLang = 'ar'|'en'.
 */
$__isAr = (($activitiesLang ?? 'en') === 'ar');
$T = $__isAr
    ? [
        'title' => 'سجل النشاط', 'sub' => 'كل أنشطة العيادة في مكان واحد',
        'type' => 'النوع', 'all' => 'الكل', 'appt' => 'المواعيد', 'note' => 'الملاحظات الطبية',
        'alert' => 'التنبيهات', 'todo' => 'المهام', 'from' => 'من', 'to' => 'إلى',
        'search' => 'بحث…', 'apply' => 'تطبيق', 'reset' => 'إعادة تعيين',
        'empty' => 'لا يوجد نشاط مطابق.', 'more' => 'تحميل المزيد',
      ]
    : [
        'title' => 'Activity Log', 'sub' => 'All clinic activity in one place',
        'type' => 'Type', 'all' => 'All', 'appt' => 'Appointments', 'note' => 'Consultation notes',
        'alert' => 'Alerts', 'todo' => 'Tasks', 'from' => 'From', 'to' => 'To',
        'search' => 'Search…', 'apply' => 'Apply', 'reset' => 'Reset',
        'empty' => 'No matching activity.', 'more' => 'Load more',
      ];
?>
<link href="/app/Views/doctor/assets/css/activities.css?v=<?= file_exists(__DIR__ . '/assets/css/activities.css') ? filemtime(__DIR__ . '/assets/css/activities.css') : time() ?>" rel="stylesheet">

<div class="container-fluid activities-page" data-lang="<?= $__isAr ? 'ar' : 'en' ?>">
    <div class="activities-toolbar">
        <div class="activities-toolbar-head">
            <span class="activities-toolbar-icon"><i class="bi bi-activity"></i></span>
            <div>
                <h5 class="mb-0"><?= $T['title'] ?></h5>
                <small class="text-muted"><?= $T['sub'] ?></small>
            </div>
        </div>
        <div class="activities-filters">
            <select id="actFilterType" class="form-select form-select-sm" aria-label="<?= $T['type'] ?>">
                <option value="all"><?= $T['all'] ?></option>
                <option value="appointment"><?= $T['appt'] ?></option>
                <option value="consultation_note"><?= $T['note'] ?></option>
                <option value="alert"><?= $T['alert'] ?></option>
                <option value="todo"><?= $T['todo'] ?></option>
            </select>
            <input type="date" id="actFilterFrom" class="form-control form-control-sm" aria-label="<?= $T['from'] ?>" title="<?= $T['from'] ?>">
            <input type="date" id="actFilterTo" class="form-control form-control-sm" aria-label="<?= $T['to'] ?>" title="<?= $T['to'] ?>">
            <input type="search" id="actFilterSearch" class="form-control form-control-sm activities-search" placeholder="<?= $T['search'] ?>">
            <button id="actFilterApply" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i><?= $T['apply'] ?></button>
            <button id="actFilterReset" class="btn btn-sm btn-outline-secondary"><?= $T['reset'] ?></button>
        </div>
    </div>

    <div class="activities-list" id="activitiesPageList" role="list"></div>
    <div class="activities-empty" id="activitiesEmpty" style="display:none;">
        <i class="bi bi-activity"></i>
        <p class="mb-0"><?= $T['empty'] ?></p>
    </div>
    <div class="text-center mt-3">
        <button id="activitiesLoadMore" class="btn btn-sm btn-outline-primary" style="display:none;">
            <i class="bi bi-arrow-down-circle me-1"></i><?= $T['more'] ?>
        </button>
    </div>
</div>

<script defer src="/app/Views/doctor/assets/js/activities.js?v=<?= file_exists(__DIR__ . '/assets/js/activities.js') ? filemtime(__DIR__ . '/assets/js/activities.js') : time() ?>"></script>
