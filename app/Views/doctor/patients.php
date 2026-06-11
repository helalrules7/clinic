<?php
    // 2026-06-07: server-render the active clinics so the "Add New Patient"
    // modal's clinic dropdown ships its <option>/<li> items in the initial HTML
    // (mirrors calendar.php / secretary views). Fixes the undefined-$__calClinics
    // warning. Secretaries are pinned to their clinic; doctors/admins get all.
    try {
        $__cal_pdo  = \App\Config\Database::getInstance()->getConnection();
        $__cal_auth = new \App\Lib\Auth();
        $__cal_user = $__cal_auth->user();
        if (($__cal_user['role'] ?? null) === 'secretary' && !empty($__cal_user['clinic_id'])) {
            $__s = $__cal_pdo->prepare("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 AND id = ? ORDER BY sort_order, id");
            $__s->execute([(int)$__cal_user['clinic_id']]);
            $__calClinics = $__s->fetchAll(\PDO::FETCH_ASSOC);
        } else {
            $__calClinics = $__cal_pdo->query("SELECT id, code, name_ar, name_en FROM clinics WHERE is_active = 1 ORDER BY sort_order, id")->fetchAll(\PDO::FETCH_ASSOC);
        }
    } catch (\Throwable $__e) {
        $__calClinics = [];
    }
    $__clinicVisuals = [
        'riyadh' => ['icon' => 'bi-buildings-fill', 'color' => '#0d6efd'],
        'kfs'    => ['icon' => 'bi-hospital-fill',  'color' => '#10b981'],
    ];
?>
<link href="/app/Views/doctor/assets/css/patients.css?v=<?= file_exists(__DIR__ . '/assets/css/patients.css') ? filemtime(__DIR__ . '/assets/css/patients.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<link href="/app/Views/doctor/assets/css/filter-bar.css?v=<?= file_exists(__DIR__ . '/assets/css/filter-bar.css') ? filemtime(__DIR__ . '/assets/css/filter-bar.css') : time() ?>" rel="stylesheet">

<div class="patients-page">
<div class="row mb-4">
    <div class="col-md-8">
        <h4 class="text-primary">
            <i class="bi bi-people me-2"></i>
            Patient Records
        </h4>
        <p class="text-muted mb-0">Manage and view patient information</p>
        <div class="mt-2">
            <small class="text-muted">
                <i class="bi bi-keyboard me-1"></i>
                Shortcuts: 
                • Add Patient <kbd class="me-1">N</kbd> or <kbd class="me-1">ى</kbd> or <kbd class="me-1">Ctrl+N</kbd> 
                • Search <kbd class="me-1">F</kbd> or <kbd class="me-1">ب</kbd>
                <kbd>Esc</kbd> Close
            </small>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group header-action-buttons" role="group">
            <button class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addPatientModal" 
                    title="Use N or ى or Ctrl+N to add a new patient">
                <i class="bi bi-person-plus me-2"></i>
                <span class="d-none d-md-inline">Add Patient</span>
                <span class="d-md-none">Add</span>
                <span class="ms-2 d-none d-md-inline">
                    <kbd>N</kbd>
                    <span class="text-white-50 mx-1">/</span>
                    <kbd lang="ar">ى</kbd>
                </span>
            </button>
        <button class="btn btn-primary" 
                data-bs-toggle="modal" 
                data-bs-target="#searchModal" 
                title="Use F or ب to search for patients">
            <i class="bi bi-search me-2"></i>
            <span class="d-none d-md-inline">Search Patients</span>
            <span class="d-md-none">Search</span>
            <span class="ms-2 d-none d-md-inline">
                <kbd>F</kbd>
                <span class="text-white-50 mx-1">/</span>
                <kbd lang="ar">ب</kbd>
            </span>
        </button>
        </div>
    </div>
</div>

<!-- Patients Statistics Cards - Compact Design with Mini Charts -->
<?php
    // v12_perf: stats now come from a cheap aggregate query (DoctorController::getPatientStats),
    // not from iterating the full patient set (which is no longer inlined).
    $patientStats = $patientStats ?? [];
    $totalPatients = (int)($patientStats['total_patients'] ?? 0);
    $totalVisits = (int)($patientStats['total_visits'] ?? 0);
    $recentVisits = (int)($patientStats['recent_visits'] ?? 0);
    $newThisMonth = (int)($patientStats['new_this_month'] ?? 0);
    $newThisWeek = (int)($patientStats['new_this_week'] ?? 0);
    $maleCount = (int)($patientStats['male_count'] ?? 0);
    $femaleCount = (int)($patientStats['female_count'] ?? 0);
    $avgVisitsPerPatient = $totalPatients > 0 ? round($totalVisits / $totalPatients, 1) : 0;
    $activePatients = (int)($patientStats['active_patients'] ?? 0);
    $inactivePatients = $totalPatients - $activePatients;
?>
<div class="mini-stats-grid mb-4">
    <!-- Total Patients -->
    <div class="mini-stat-card mini-stat-primary">
        <div class="mini-stat-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsTotalPatients"><?= number_format($totalPatients) ?></span>
            <span class="mini-stat-label">Total Patients</span>
        </div>
        <div class="mini-stat-chart" id="chartTotalPatients"></div>
        <div class="mini-stat-trend trend-up">
            <i class="bi bi-graph-up-arrow"></i>
            <span>All Time</span>
        </div>
    </div>

    <!-- New This Week -->
    <div class="mini-stat-card mini-stat-success">
        <div class="mini-stat-icon">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsNewThisWeek"><?= number_format($newThisWeek) ?></span>
            <span class="mini-stat-label">New This Week</span>
        </div>
        <div class="mini-stat-chart" id="chartNewWeek"></div>
        <div class="mini-stat-trend trend-up">
            <i class="bi bi-calendar-week"></i>
            <span>Last 7 Days</span>
        </div>
    </div>

    <!-- New This Month -->
    <div class="mini-stat-card mini-stat-info">
        <div class="mini-stat-icon">
            <i class="bi bi-calendar-plus-fill"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsNewThisMonth"><?= number_format($newThisMonth) ?></span>
            <span class="mini-stat-label">New This Month</span>
        </div>
        <div class="mini-stat-chart" id="chartNewMonth"></div>
        <div class="mini-stat-trend trend-up">
            <i class="bi bi-calendar-month"></i>
            <span>Last 30 Days</span>
        </div>
    </div>

    <!-- Total Visits -->
    <div class="mini-stat-card mini-stat-warning">
        <div class="mini-stat-icon">
            <i class="bi bi-calendar-check-fill"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsTotalVisits"><?= number_format($totalVisits) ?></span>
            <span class="mini-stat-label">Total Visits</span>
        </div>
        <div class="mini-stat-chart" id="chartTotalVisits"></div>
        <div class="mini-stat-trend trend-neutral">
            <i class="bi bi-clipboard-check"></i>
            <span>All Appointments</span>
        </div>
    </div>

    <!-- Recent Visits -->
    <div class="mini-stat-card mini-stat-purple">
        <div class="mini-stat-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsRecentVisits"><?= number_format($recentVisits) ?></span>
            <span class="mini-stat-label">Recent Visits</span>
        </div>
        <div class="mini-stat-chart" id="chartRecentVisits"></div>
        <div class="mini-stat-trend trend-up">
            <i class="bi bi-activity"></i>
            <span>Last 7 Days</span>
        </div>
    </div>

    <!-- Active Patients -->
    <div class="mini-stat-card mini-stat-teal">
        <div class="mini-stat-icon">
            <i class="bi bi-heart-pulse-fill"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value" id="statsActivePatients"><?= number_format($activePatients) ?></span>
            <span class="mini-stat-label">Active Patients</span>
        </div>
        <div class="mini-stat-chart" id="chartActivePatients"></div>
        <div class="mini-stat-trend trend-up">
            <i class="bi bi-check-circle"></i>
            <span>Last 90 Days</span>
        </div>
    </div>

    <!-- Male Patients -->
    <div class="mini-stat-card mini-stat-male">
        <div class="mini-stat-icon">
            <i class="bi bi-gender-male"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value"><?= number_format($maleCount) ?></span>
            <span class="mini-stat-label">Male Patients</span>
        </div>
        <div class="mini-stat-chart" id="chartMale"></div>
        <div class="mini-stat-trend trend-neutral">
            <i class="bi bi-pie-chart"></i>
            <span><?= $totalPatients > 0 ? round(($maleCount / $totalPatients) * 100) : 0 ?>%</span>
        </div>
    </div>

    <!-- Female Patients -->
    <div class="mini-stat-card mini-stat-female">
        <div class="mini-stat-icon">
            <i class="bi bi-gender-female"></i>
        </div>
        <div class="mini-stat-content">
            <span class="mini-stat-value"><?= number_format($femaleCount) ?></span>
            <span class="mini-stat-label">Female Patients</span>
        </div>
        <div class="mini-stat-chart" id="chartFemale"></div>
        <div class="mini-stat-trend trend-neutral">
            <i class="bi bi-pie-chart"></i>
            <span><?= $totalPatients > 0 ? round(($femaleCount / $totalPatients) * 100) : 0 ?>%</span>
        </div>
    </div>
</div>

<!-- Unified Filter Bar -->
<div class="unified-filter-bar" id="unifiedFilterBar">
    <!-- Desktop View -->
    <div class="filter-bar-desktop">
        <!-- View Mode Toggle (Always Left) -->
        <div class="filter-bar-view-toggle">
            <div class="filter-bar-label">
                <i class="bi bi-layout-three-columns"></i>
                <span>Change View</span>
            </div>
            <div class="btn-group btn-group-sm" role="group" id="viewModeToggleUnified">
                <button type="button" 
                        class="btn btn-view-toggle" 
                        data-view="table"
                        onclick="switchViewMode('table')"
                        title="Table View">
                    <i class="bi bi-table"></i>
                    <span class="d-none d-md-inline ms-1">Table</span>
                </button>
                <button type="button" 
                        class="btn btn-view-toggle" 
                        data-view="cards"
                        onclick="switchViewMode('cards')"
                        title="Cards View">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span class="d-none d-md-inline ms-1">Cards</span>
                </button>
                <button type="button" 
                        class="btn btn-view-toggle" 
                        data-view="folders"
                        onclick="switchViewMode('folders')"
                        title="Folders View">
                    <i class="bi bi-folder"></i>
                    <span class="d-none d-md-inline ms-1">Folders</span>
                </button>
            </div>
        </div>

        <span class="filter-bar-separator" aria-hidden="true"></span>

        <div class="filter-bar-label filter-bar-label-filters">
            <i class="bi bi-funnel"></i>
            <span>Filters</span>
        </div>

        <div class="filter-chips">
            <!-- Doctor Filter Chip -->
            <div class="filter-chip" data-filter="doctor" id="doctorFilterChip">
                <span class="filter-chip-label">Doctor</span>
                <span class="filter-chip-value" id="doctorChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown filter-dropdown-scrollable" id="doctorDropdown">
                    <div class="filter-dropdown-content">
                        <!-- Doctor options will be rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Gender Filter Chip -->
            <div class="filter-chip" data-filter="gender" id="genderFilterChip">
                <span class="filter-chip-label">Gender</span>
                <span class="filter-chip-value" id="genderChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown" id="genderDropdown">
                    <div class="filter-dropdown-content">
                        <button class="filter-option selected" data-value="">All</button>
                        <button class="filter-option" data-value="Male">
                            <i class="bi bi-gender-male text-primary me-2"></i>Male
                        </button>
                        <button class="filter-option" data-value="Female">
                            <i class="bi bi-gender-female me-2" style="color: #ec4899;"></i>Female
                        </button>
                    </div>
                </div>
            </div>

            <!-- Age Filter Chip -->
            <div class="filter-chip" data-filter="age" id="ageFilterChip">
                <span class="filter-chip-label">Age</span>
                <span class="filter-chip-value" id="ageChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown filter-dropdown-wide" id="ageDropdown">
                    <div class="filter-range-inputs">
                        <input type="number" class="form-control form-control-sm"
                               id="ageFilterMin" placeholder="Min" min="0" max="150">
                        <span class="range-separator">to</span>
                        <input type="number" class="form-control form-control-sm"
                               id="ageFilterMax" placeholder="Max" min="0" max="150">
                    </div>
                    <div class="filter-dropdown-actions">
                        <button class="btn btn-sm btn-link" id="clearAgeFilter">Clear</button>
                        <button class="btn btn-sm btn-primary" id="applyAgeFilter">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Color Markers Filter Chip (All views) -->
            <div class="filter-chip filter-chip-colors" data-filter="colors" id="colorsFilterChip">
                <span class="filter-chip-label">Colors</span>
                <div class="color-dots" id="colorDotsPreview"></div>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown" id="colorsDropdown">
                    <div class="color-palette">
                        <!-- Color buttons will be rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Tags Filter Chip (All views) -->
            <div class="filter-chip" data-filter="tags" id="tagsFilterChip">
                <span class="filter-chip-label">Tags</span>
                <span class="filter-chip-value" id="tagsChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown filter-dropdown-scrollable" id="tagsDropdown">
                    <div class="tags-list">
                        <!-- Tags will be loaded via JS -->
                    </div>
                </div>
            </div>

            <!-- Date Created Filter Chip (Cards/Folders only) -->
            <div class="filter-chip" data-filter="dateCreated" id="dateCreatedFilterChip" data-views="cards,folders">
                <span class="filter-chip-label">Created</span>
                <span class="filter-chip-value" id="dateCreatedChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown filter-dropdown-wide" id="dateCreatedDropdown">
                    <div class="filter-date-inputs">
                        <div class="date-input-group">
                            <label>From</label>
                            <input type="date" class="form-control form-control-sm" id="dateCreatedFrom">
                        </div>
                        <div class="date-input-group">
                            <label>To</label>
                            <input type="date" class="form-control form-control-sm" id="dateCreatedTo">
                        </div>
                    </div>
                    <div class="filter-dropdown-actions">
                        <button class="btn btn-sm btn-link" id="clearDateCreatedFilter">Clear</button>
                        <button class="btn btn-sm btn-primary" id="applyDateCreatedFilter">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Last Visit Filter Chip (Table only) -->
            <div class="filter-chip" data-filter="lastVisit" id="lastVisitFilterChip" data-views="table">
                <span class="filter-chip-label">Last Visit</span>
                <span class="filter-chip-value" id="lastVisitChipValue">All</span>
                <i class="bi bi-chevron-down filter-chip-arrow"></i>
                <div class="filter-dropdown filter-dropdown-wide" id="lastVisitDropdown">
                    <div class="filter-date-inputs">
                        <div class="date-input-group">
                            <label>From</label>
                            <input type="date" class="form-control form-control-sm" id="lastVisitFrom">
                        </div>
                        <div class="date-input-group">
                            <label>To</label>
                            <input type="date" class="form-control form-control-sm" id="lastVisitTo">
                        </div>
                    </div>
                    <div class="filter-dropdown-actions">
                        <button class="btn btn-sm btn-link" id="clearLastVisitFilter">Clear</button>
                        <button class="btn btn-sm btn-primary" id="applyLastVisitFilter">Apply</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filters Indicator & Clear (Always Right) -->
        <div class="filter-bar-actions">
            <span class="active-filters-badge" id="activeFiltersBadge" style="display: none;">
                <span id="activeFiltersCount">0</span> active
            </span>
            <button class="btn btn-sm btn-link clear-all-btn" id="clearAllFilters" style="display: none;">
                <i class="bi bi-x-circle me-1"></i>Clear All
            </button>
        </div>
    </div>

    <!-- Mobile View -->
    <div class="filter-bar-mobile">
        <div class="d-flex align-items-center gap-2 flex-wrap w-100">
            <!-- View Mode Toggle (Mobile) -->
            <div class="filter-bar-view-toggle-mobile">
                <div class="filter-bar-label-mobile">
                    <i class="bi bi-layout-three-columns"></i>
                    <span>View</span>
                </div>
                <div class="btn-group btn-group-sm" role="group" id="viewModeToggleMobile">
                    <button type="button" 
                            class="btn btn-view-toggle" 
                            data-view="table"
                            onclick="switchViewMode('table')"
                            title="Table View">
                        <i class="bi bi-table"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-view-toggle" 
                            data-view="cards"
                            onclick="switchViewMode('cards')"
                            title="Cards View">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-view-toggle" 
                            data-view="folders"
                            onclick="switchViewMode('folders')"
                            title="Folders View">
                        <i class="bi bi-folder"></i>
                    </button>
                </div>
            </div>

            <span class="filter-bar-separator filter-bar-separator-mobile" aria-hidden="true"></span>
            
            <div class="d-flex align-items-center gap-2 ms-auto">
                <button class="filter-mobile-trigger" id="filterMobileTrigger" data-bs-toggle="modal" data-bs-target="#mobileFilterModal">
                    <i class="bi bi-funnel me-2"></i>
                    <span>Filters</span>
                    <span class="mobile-active-badge" id="mobileActiveBadge" style="display: none;">
                        <span id="mobileActiveCount">0</span>
                    </span>
                </button>
                <button class="btn btn-sm btn-link text-danger" id="mobileClearAll" style="display: none;">
                    Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Patients Table -->
<div class="card" id="patientsTableCard">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-table me-2"></i>
                    Patient List
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <!-- View Mode Toggle moved to unified filter bar -->
                    
                    <!-- Clear Filters and Sorting Buttons -->
                    <div class="d-flex gap-2 align-items-center me-3 d-none" id="clearFiltersGroup">
                        <button type="button" class="btn btn-sm btn-outline-danger clear-all-filters-btn" title="Clear all filters">
                            <i class="bi bi-funnel-x me-1"></i>
                            Clear Filters
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning clear-sorting-btn d-none" title="Clear sorting" id="clearSortingBtn">
                            <i class="bi bi-arrow-down-up me-1"></i>
                            Clear Sorting
                        </button>
                    </div>
                    
                    <!-- Quick Search + Per page (same row) -->
                    <div class="d-flex align-items-center gap-2 header-actions-row">
                        <div class="input-group input-group-sm quick-search-input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 border-end-0" 
                                   id="quickSearch" 
                                   placeholder="Quick search..."
                                   autocomplete="off"
                                   style="border-left: none; border-right: none;">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="clearQuickSearch" style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="paginationLimit" class="form-label mb-0 me-2 text-muted small per-page-label">View:</label>
                            <section class="field menu" style="min-width: 70px; width: auto;">
                                <div class="control">
                                    <select class="form-select form-select-sm d-none center-select" id="paginationLimit" style="width: auto;">
                                        <option value="10">10</option>
                                        <option value="20" selected>20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="all">All</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false" style="font-size: 0.875rem; padding: 0.75rem 2rem 0.75rem 0.75rem;">20</button>
                                    <menu>
                                        <li data-option="10" tabindex="0" role="button"><h3>10</h3></li>
                                        <li data-option="20" tabindex="0" role="button" class="selected"><h3>20</h3></li>
                                        <li data-option="30" tabindex="0" role="button"><h3>30</h3></li>
                                        <li data-option="50" tabindex="0" role="button"><h3>50</h3></li>
                                        <li data-option="all" tabindex="0" role="button"><h3>All</h3></li>
                                    </menu>
                                </div>
                            </section>
                        </div>
                        <div class="text-muted total-count-label">
                            <small>Total: <span id="totalPatientsCount"><?= number_format($totalPatients) ?></span> patients</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="first_name" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="first_name" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Patient Info</span>
                            </div>
                        </th>
                        <th>Contact</th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="gender" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="gender" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Gender</span>
                            </div>
                        </th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="age" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="age" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Age</span>
                            </div>
                        </th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="created_by_doctor_name" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="created_by_doctor_name" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Doctors</span>
                            </div>
                        </th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="last_visit" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="last_visit" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Last Visit</span>
                            </div>
                        </th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="last_clinic_name_ar" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="last_clinic_name_ar" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Last Clinic</span>
                            </div>
                        </th>
                        <th>
                            <div class="d-flex align-items-center gap-2">
                                <div class="sort-controls d-flex flex-column">
                                    <button class="sort-btn sort-asc" data-sort="total_appointments" data-order="asc" title="Sort ascending">
                                        <i class="bi bi-chevron-up"></i>
                                    </button>
                                    <button class="sort-btn sort-desc" data-sort="total_appointments" data-order="desc" title="Sort descending">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                                <span>Total Visits</span>
                            </div>
                        </th>
                        <th>Tags</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patientsTableBody">
                    <!-- Patients will be rendered here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination Controls -->
    <div class="card-footer" id="paginationContainer">
        <div class="row align-items-center justify-content-center">
            <div class="col-12 text-center">
                <div class="pagination-info text-muted mb-2">
                    <small>
                        View <span id="showingFrom">1</span> to <span id="showingTo">20</span> 
                        of <span id="totalPatients"><?= number_format($totalPatients) ?></span> patients
                    </small>
                </div>
                <nav aria-label="Patients pagination" class="d-flex justify-content-center">
                    <ul class="pagination pagination-sm justify-content-center mb-0" id="paginationNav">
                        <!-- Pagination items will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Patients Cards View -->
<div class="card" id="patientsCardsCard" style="display: none;">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-grid-3x3-gap me-2"></i>
                    Patient Cards
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <!-- View Mode Toggle moved to unified filter bar -->
                    
                    <!-- Quick Search + Per page (same row) -->
                    <div class="d-flex align-items-center gap-2 header-actions-row">
                        <div class="input-group input-group-sm quick-search-input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control border-start-0 border-end-0" 
                                   id="quickSearchCards" 
                                   placeholder="Quick search..."
                                   autocomplete="off"
                                   style="border-left: none; border-right: none;">
                            <button class="btn btn-outline-secondary border-start-0" type="button" id="clearQuickSearchCards" style="display: none;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="paginationLimitCards" class="form-label mb-0 me-2 text-muted small per-page-label">View:</label>
                            <section class="field menu" style="min-width: 70px; width: auto;">
                                <div class="control">
                                    <select class="form-select form-select-sm d-none center-select" id="paginationLimitCards" style="width: auto;">
                                        <option value="12">12</option>
                                        <option value="24" selected>24</option>
                                        <option value="36">36</option>
                                        <option value="48">48</option>
                                        <option value="all">All</option>
                                    </select>
                                    <button type="button" class="custom-select-toggle" aria-expanded="false" style="font-size: 0.875rem; padding: 0.75rem 2rem 0.75rem 0.75rem;">24</button>
                                    <menu>
                                        <li data-option="12" tabindex="0" role="button"><h3>12</h3></li>
                                        <li data-option="24" tabindex="0" role="button" class="selected"><h3>24</h3></li>
                                        <li data-option="36" tabindex="0" role="button"><h3>36</h3></li>
                                        <li data-option="48" tabindex="0" role="button"><h3>48</h3></li>
                                        <li data-option="all" tabindex="0" role="button"><h3>All</h3></li>
                                    </menu>
                                </div>
                            </section>
                        </div>
                        <div class="text-muted total-count-label">
                            <small>Total: <span id="totalPatientsCountCards">0</span> patients</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Single Quick Search is in card header (quickSearchCards) - no duplicate here -->
        
        <!-- Card Size Toggle for Cards View -->
        <div class="d-flex justify-content-end mb-3">
            <div class="btn-group btn-group-sm" role="group" id="cardSizeToggleCards" title="Card Size">
                <button type="button" 
                        class="btn btn-outline-secondary card-size-btn-cards" 
                        data-size="small"
                        onclick="setCardSizeCards('small')"
                        title="Large Cards">
                    <i class="bi bi-grid"></i>
                </button>
                <button type="button" 
                        class="btn btn-outline-secondary card-size-btn-cards" 
                        data-size="medium"
                        onclick="setCardSizeCards('medium')"
                        title="Medium Cards">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button type="button" 
                        class="btn btn-outline-secondary card-size-btn-cards active" 
                        data-size="large"
                        onclick="setCardSizeCards('large')"
                        title="Small Cards">
                    <i class="bi bi-grid-3x3"></i>
                </button>
            </div>
        </div>
        <div id="patientsCardsContainer" class="row g-3 patient-cards-grid">
            <!-- Cards will be rendered here by JavaScript -->
        </div>
    </div>
    <!-- Pagination Controls for Cards -->
    <div class="card-footer" id="paginationContainerCards">
        <div class="row align-items-center justify-content-center">
            <div class="col-12 text-center">
                <div class="pagination-info text-muted mb-2">
                    <small>
                        View <span id="showingFromCards">1</span> to <span id="showingToCards">24</span> 
                        of <span id="totalPatientsCards">0</span> patients
                    </small>
                </div>
                <nav aria-label="Patients pagination" class="d-flex justify-content-center">
                    <ul class="pagination pagination-sm justify-content-center mb-0" id="paginationNavCards">
                        <!-- Pagination items will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Patients Folders View -->
<div class="card" id="patientsFoldersCard" style="display: none;">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <i class="bi bi-folder me-2"></i>
                    Patient Folders
                </h5>
            </div>
            <div class="col-md-6 text-end">
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <!-- View Mode Toggle moved to unified filter bar -->
                    <button type="button"
                            class="btn btn-success btn-sm"
                            onclick="showCreateFolderModal()"
                            title="Create New Folder">
                        <i class="bi bi-folder-plus me-1"></i>
                        Create Folder
                    </button>
                    <!-- P17 (2026-06-07): multi-select folders at the root grid -->
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            onclick="toggleSelectionMode(); renderFoldersView(1, false);"
                            title="Select folders">
                        <i class="bi bi-check2-square me-1"></i>
                        <span id="rootSelectionModeLabel">Select</span>
                    </button>
                    <!-- Card Size Toggle -->
                    <div class="btn-group btn-group-sm" role="group" id="cardSizeToggle" title="Card Size">
                        <button type="button" 
                                class="btn btn-outline-secondary card-size-btn active" 
                                data-size="small"
                                onclick="setCardSize('small')"
                                title="Large Cards">
                            <i class="bi bi-grid"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-secondary card-size-btn" 
                                data-size="medium"
                                onclick="setCardSize('medium')"
                                title="Medium Cards">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button type="button" 
                                class="btn btn-outline-secondary card-size-btn" 
                                data-size="large"
                                onclick="setCardSize('large')"
                                title="Small Cards">
                            <i class="bi bi-grid-3x3"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="folders-view-layout d-flex">
            <!-- Sidebar - على اليسار -->
            <aside class="folders-sidebar" id="foldersSidebar">
                <div class="sidebar-header">
                    <h6 class="mb-0">
                        <i class="bi bi-diagram-3 me-2"></i>
                        Navigation
                    </h6>
                    <button class="btn btn-sm btn-link sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                </div>
                
                <!-- Treeview Container -->
                <div class="treeview-container" id="folderTreeview">
                    <!-- Treeview will be rendered here by JavaScript -->
                </div>
            </aside>
            
            <!-- Main Content Area - على اليمين -->
            <main class="folders-main-content flex-grow-1">
                <!-- Folders List (عندما لا يكون هناك مجلد مفتوح) -->
                <div id="patientsFoldersContainer">
                    <!-- Folders will be rendered here by JavaScript -->
                </div>
                
                <!-- Folder Content Area (عند فتح مجلد) -->
                <div id="folderContentArea" style="display: none;">
                    <!-- Breadcrumb -->
                    <div id="folderBreadcrumb" class="mb-3"></div>
                    
                    <!-- Search -->
                    <div id="folderSearchContainer" class="mb-3"></div>
                    
                    <!-- Sub-folders -->
                    <div id="subFoldersContainer" class="mb-3"></div>
                    
                    <!-- Per Page Selector -->
                    <div class="d-flex justify-content-end align-items-center mb-3" id="folderPerPageSelectorContainer">
                        <label for="folderItemsPerPageSelector" class="text-muted small me-2 mb-0">Per page:</label>
                        <select id="folderItemsPerPageSelector" 
                                class="form-select form-select-sm" 
                                style="width: auto;"
                                onchange="changeFolderItemsPerPage(this.value)">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="36" selected>36</option>
                            <option value="48">48</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                    
                    <!-- Patients -->
                    <div id="folderPatientsContainer" class="row g-3"></div>
                    
                    <!-- Pagination for Folders View -->
                    <div id="folderPaginationContainer" class="mt-3">
                        <div id="folderPaginationInfo" class="mb-2"></div>
                        <div id="folderPaginationNav"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-folder-plus me-2"></i>
                    Create New Folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createFolderForm">
                <div class="modal-body">
                    <div id="createFolderMessage" class="alert d-none" role="alert"></div>
                    <div class="mb-3">
                        <label for="folderName" class="form-label">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="folderName" 
                               name="folder_name" 
                               required 
                               maxlength="120"
                               placeholder="Enter folder name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-folder-plus me-1"></i>
                        Create Folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rename Folder Modal -->
<div class="modal fade" id="renameFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Rename Folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="renameFolderForm">
                <div class="modal-body">
                    <div id="renameFolderMessage" class="alert d-none" role="alert"></div>
                    <input type="hidden" id="renameFolderId" name="folder_id">
                    <div class="mb-3">
                        <label for="renameFolderName" class="form-label">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="renameFolderName" 
                               name="folder_name" 
                               required 
                               maxlength="120"
                               placeholder="Enter folder name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Move/Add Patient to Folder Modal -->
<div class="modal fade" id="movePatientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-folder me-2"></i>
                    <span id="movePatientModalTitle">Move Patient to Folder</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="movePatientMessage" class="alert d-none" role="alert"></div>
                <input type="hidden" id="movePatientId" name="patient_id">
                <div class="mb-3">
                    <label for="movePatientFolderSelect" class="form-label">Select Folder</label>
                    <select class="form-select" id="movePatientFolderSelect" name="folder_id">
                        <option value="">-- Select Folder --</option>
                        <!-- Folders will be populated by JavaScript -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="movePatientButton" onclick="confirmMovePatient()">
                    <i class="bi bi-check-lg me-1"></i>
                    <span id="movePatientButtonText">Move Patient</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create Sub-folder Modal -->
<div class="modal fade" id="createSubFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-folder-plus me-2"></i>
                    Create Sub-folder
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createSubFolderForm">
                <div class="modal-body">
                    <div id="createSubFolderMessage" class="alert d-none" role="alert"></div>
                    <input type="hidden" id="subFolderParentId" name="parent_id">
                    <input type="hidden" id="subFolderParentType" name="parent_type">
                    <div class="mb-3">
                        <label class="form-label">Parent Folder</label>
                        <div class="form-control bg-light" id="subFolderParentName" style="border: 1px solid var(--border);">
                            <!-- Parent folder name will be displayed here -->
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subFolderName" class="form-label">Sub-folder Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="subFolderName" 
                               name="sub_folder_name" 
                               required 
                               maxlength="120"
                               placeholder="Enter sub-folder name">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-folder-plus me-1"></i>
                        Create Sub-folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Folder Icon & Color Modal -->
<div class="modal fade" id="changeFolderIconModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-palette me-2"></i>
                    Change Folder Icon & Color
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeFolderIconForm">
                <div class="modal-body">
                    <div id="changeFolderIconMessage" class="alert d-none" role="alert"></div>
                    <input type="hidden" id="changeFolderIconId" name="folder_id">
                    
                    <!-- Icon Selection -->
                    <div class="mb-4">
                        <label class="form-label">Select Icon</label>
                        <div class="row g-2" id="iconSelectionGrid">
                            <!-- Bootstrap Icons will be populated by JavaScript -->
                        </div>
                        <input type="hidden" id="selectedIcon" name="icon" value="bi-folder">
                    </div>
                    
                    <!-- Gradient Color Selection -->
                    <div class="mb-4">
                        <label class="form-label">Select Gradient Color</label>
                        <div class="row g-2" id="gradientSelectionGrid">
                            <!-- Gradient presets will be populated by JavaScript -->
                        </div>
                        <input type="hidden" id="selectedGradient" name="gradient_color">
                    </div>
                    
                    <!-- D5 (2026-06-07): free-text gradient input removed (it was the
                         stored-XSS vector). Appearance is now preset-only. The hidden
                         field is kept so existing JS references stay valid. -->
                    <input type="hidden" id="customGradient">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="confirmModalMessage" style="margin: 0;">Are you sure?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmModalButton">
                    <i class="bi bi-check-lg me-1"></i>
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertModalTitle">
                    <i class="bi bi-info-circle me-2"></i>
                    Alert
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="alertModalMessage" style="margin: 0;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-search me-2"></i>
                    Search Patients
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="globalSearch" 
                               placeholder="Search by name, phone, or national ID..."
                               autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text d-flex justify-content-between align-items-center search-help-text">
                        <span class="search-instruction">
                            <i class="bi bi-info-circle me-1"></i>
                            Start typing to search automatically
                        </span>
                        <small class="search-shortcut">
                            <kbd>Ctrl</kbd>+<kbd>F</kbd> to focus search
                        </small>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults">
                    <!-- Loading State -->
                    <div id="searchLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Searching...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Searching patients...</p>
                    </div>

                    <!-- No Results -->
                    <div id="noResults" class="text-center py-4" style="display: none;">
                        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2">No patients found</h6>
                        <p class="text-muted mb-0">Try different search terms</p>
                    </div>

                    <!-- Results Container -->
                    <div id="searchResultsList" class="search-results-container">
                        <!-- Results will be populated here -->
                    </div>

                    <!-- Initial State -->
                    <div id="searchInitial" class="text-center py-4">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                        <h6 class="text-muted mt-2">Search Patients</h6>
                        <p class="text-muted mb-0">Enter name, phone number, or national ID to search</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header position-relative">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>
                    Add New Patient
                </h5>
                <div class="keyboard-hint">
                    <span>Press</span>
                    <kbd>Esc</kbd>
                    <span>to close</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <!-- Success/Error Messages -->
                    <div id="addPatientMessage" class="alert d-none" role="alert"></div>
                    
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>
                                Basic Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="age" class="form-label">Age (Years)</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" placeholder="Enter age in years">
                                <div class="form-text">Alternative: Enter age to automatically calculate date of birth</div>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="dob" name="dob">
                                <div class="form-text">Patient's date of birth (if empty, today's date will be used)</div>
                            </div>
                            
                            <div class="mb-3" style="display: none;">
                                <label for="nationalId" class="form-label">National ID</label>
                                <input type="text" class="form-control" id="nationalId" name="national_id" maxlength="20">
                                <div class="form-text">Government issued ID number (optional)</div>
                            </div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-telephone me-1"></i>
                                Contact Information
                            </h6>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required maxlength="20">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">Primary contact number</div>
                            </div>
                            
                            <div class="mb-3" style="display: none;">
                                <label for="altPhone" class="form-label">Alternative Phone</label>
                                <input type="tel" class="form-control" id="altPhone" name="alt_phone" maxlength="20">
                                <div class="form-text">Secondary contact number (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" maxlength="500"></textarea>
                                <div class="form-text">Home address (optional)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="gender" name="gender" required>
                                            <option value="Male" selected>Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false">Male</button>
                                        <menu>
                                            <li data-option="Male" tabindex="0" role="button" class="selected"><i class="bi bi-gender-male fs-5"></i><h3>Male</h3></li>
                                            <li data-option="Female" tabindex="0" role="button"><i class="bi bi-gender-female fs-5"></i><h3>Female</h3></li>
                                        </menu>
                                    </div>
                                </section>
                                <div class="invalid-feedback"></div>
                                <div class="form-text text-danger"><strong>Required:</strong> Change the gender if needed</div>
                            </div>

                            <div class="mb-3">
                                <label for="patientClinic" class="form-label">Clinic <span class="text-danger">*</span></label>
                                <section class="field menu" style="min-width: 100%;">
                                    <div class="control">
                                        <select class="form-select d-none" id="patientClinic" name="clinic_id" required>
                                            <option value="">اختر العيادة...</option>
                                            <?php foreach ($__calClinics as $__c): ?>
                                                <option value="<?= (int)$__c['id'] ?>"><?= htmlspecialchars($__c['name_ar'] ?: $__c['name_en']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="custom-select-toggle" aria-expanded="false"><i class="bi bi-building fs-5"></i> <h3>اختر العيادة...</h3></button>
                                        <menu>
                                            <li data-option="" tabindex="0" role="button" class="selected"><h3>اختر العيادة...</h3></li>
                                            <?php foreach ($__calClinics as $__c):
                                                $__v = $__clinicVisuals[$__c['code']] ?? ['icon' => 'bi-building', 'color' => '#6c757d'];
                                            ?>
                                                <li data-option="<?= (int)$__c['id'] ?>" tabindex="0" role="button"><i class="bi <?= $__v['icon'] ?> fs-5" style="color: <?= $__v['color'] ?>;"></i> <h3><?= htmlspecialchars($__c['name_ar'] ?: $__c['name_en']) ?></h3></li>
                                            <?php endforeach; ?>
                                        </menu>
                                    </div>
                                </section>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3" style="display: none;">
                                <label for="emergencyContact" class="form-label">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" maxlength="100">
                                <div class="form-text">Emergency contact person name</div>
                            </div>
                            
                            <div class="mb-3" style="display: none;">
                                <label for="emergencyPhone" class="form-label">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_phone" maxlength="20">
                                <div class="form-text">Emergency contact phone number</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="addPatientSubmit" title="Save patient - Press 'Ctrl+S'">
                        <i class="bi bi-person-plus me-1"></i>
                        <span class="btn-text">Add Patient</span>
                        <small class="ms-2 text-white-50">
                            <kbd style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); font-size: 0.7rem;">Ctrl+S</kbd>
                        </small>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Filter Modal (moved here so it appears above all elements on mobile, same as search/add modals) -->
<div class="modal fade" id="mobileFilterModal" tabindex="-1" aria-labelledby="mobileFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-bottom">
        <div class="modal-content filter-modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="mobileFilterModalLabel">
                    <i class="bi bi-funnel me-2"></i>Filters
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mobileFilterContent">
                <!-- Mobile filter options will be rendered by JS -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" id="mobileFilterClear">
                    Clear All
                </button>
                <button class="btn btn-primary" data-bs-dismiss="modal" id="mobileFilterApply">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Patient Warning Modal -->
<div class="modal fade" id="deletePatientModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Warining: Delete Patient
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-shield-exclamation fs-3 me-3"></i>
                    <div>
                        <h6 class="alert-heading mb-2">Important Warning!</h6>
                        <p class="mb-0">You are about to delete the patient permanently from the system. This action <strong>cannot be undone</strong>.</p>
                    </div>
                </div>
                
                <div class="patient-delete-info mb-4">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-person-x me-2"></i>
                        Patient Data to be Deleted:
                    </h6>
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3" id="deletePatientAvatar"></div>
                                <div>
                                    <h6 class="mb-1" id="deletePatientName"></h6>
                                    <small class="text-muted">Patient ID: #<span id="deletePatientId"></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="deletion-consequences">
                    <h6 class="text-danger mb-3">
                        <i class="bi bi-list-check me-2"></i>
                        The following data will be deleted permanently:
                    </h6>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-person text-danger me-2"></i>
                            <span>All patient personal data</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-calendar-event text-danger me-2"></i>
                            <span>All appointments and previous visits</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-medical text-danger me-2"></i>
                            <span>Medical history and diagnoses</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-receipt text-danger me-2"></i>
                            <span>All invoices and payments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-file-earmark text-danger me-2"></i>
                            <span>All files and attachments</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center">
                            <i class="bi bi-chat-left-text text-danger me-2"></i>
                            <span>All notes and reports</span>
                        </li>
                    </ul>
                </div>
                
                <div class="alert alert-warning mt-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> It is recommended to take a backup of important data before proceeding.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-warning" onclick="showDeleteConfirmation()">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    I understand the risks, proceed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Patient Confirmation Modal -->
<div class="modal fade" id="deletePatientConfirmModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-shield-exclamation me-2"></i>
                    Final Confirmation
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 4rem;"></i>
                    <h5 class="text-danger mt-3">Final Confirmation Required</h5>
                    <p class="text-muted">This is the final warning before the final deletion</p>
                </div>
                
                <div class="alert alert-danger" role="alert">
                    <strong>To proceed and delete finally:</strong><br>
                    Type the word <kbd>DELETE</kbd> or <kbd>DEL</kbd> in the field below
                </div>
                
                <div class="mb-3">
                    <label for="deleteConfirmationText" class="form-label">Confirmation Word:</label>
                    <input type="text" 
                           class="form-control form-control-lg text-center" 
                           id="deleteConfirmationText" 
                           placeholder="Type DELETE or DEL"
                           autocomplete="off">
                    <div class="form-text text-center delete-help-text">The confirmation word must be typed in uppercase English letters</div>
                </div>
                
                <div id="deleteConfirmationMessage" class="alert d-none" role="alert"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="backToDeleteWarning()">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </button>
                <button type="button" class="btn btn-danger" id="finalDeleteButton" onclick="confirmPatientDeletion()" disabled>
                    <i class="bi bi-trash me-1"></i>
                    <span class="btn-text">Final Delete</span>
                    <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    // Initialize PATIENTS_CONFIG with PHP variables
    <?php
    ?>

    window.PATIENTS_CONFIG = {
        patients: <?= json_encode($patients, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?>,
        doctors: <?= json_encode($doctors, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?>,
    };
</script>
<script defer src="/app/Views/doctor/assets/js/patients.js?v=<?= file_exists(__DIR__ . '/assets/js/patients.js') ? filemtime(__DIR__ . '/assets/js/patients.js') : time() ?>"></script>
<!-- Clinic dropdown is server-rendered now; no populate() needed. We just
     auto-lock the single-option case (secretary view) below. -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('patientClinic');
        if (!sel) return;
        const realOpts = Array.from(sel.options).filter(o => o.value !== '');
        if (realOpts.length === 1) {
            sel.value = realOpts[0].value;
            sel.disabled = true;
            const field = sel.closest('.field.menu');
            if (field) {
                field.classList.add('locked');
                const toggleBtn = field.querySelector('.custom-select-toggle');
                if (toggleBtn) {
                    toggleBtn.disabled = true;
                    toggleBtn.setAttribute('aria-disabled', 'true');
                    toggleBtn.style.pointerEvents = 'none';
                    toggleBtn.style.opacity = '0.85';
                    toggleBtn.style.cursor = 'not-allowed';
                }
                const h3 = field.querySelector('.custom-select-toggle h3');
                if (h3) h3.textContent = realOpts[0].textContent;
                const matchLi = field.querySelector(`menu li[data-option="${realOpts[0].value}"]`);
                if (matchLi) {
                    field.querySelectorAll('menu li').forEach(li => li.classList.remove('selected'));
                    matchLi.classList.add('selected');
                }
            }
        }
    });
</script>
<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
.dark .btn-outline-primary, .btn-outline-primary, .dark .btn-outline-success, .btn-outline-success, .dark .btn-outline-danger, .btn-outline-danger{
color: white !important;
margin-bottom: 5px !important;
}
</style>
</div><!-- /.patients-page -->