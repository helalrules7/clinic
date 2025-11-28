<link href="/app/Views/doctor/assets/css/dashboard.css?v=<?= file_exists(__DIR__ . '/assets/css/dashboard.css') ? filemtime(__DIR__ . '/assets/css/dashboard.css') : time() ?>" rel="stylesheet">
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Today
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['total'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar3 fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Completed
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['completed'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Missed Appointments
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['missed_appointments'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            In Progress
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['in_progress'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl col-lg-4 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Booked
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $stats['booked'] ?? 0 ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4 dashboard-card-row" data-card-id="quick-actions">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="quick-actions">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning me-2"></i>
                    Quick Actions
                </h6>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('quick-actions')" title="Move up">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('quick-actions')" title="Move down">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <div class="dashboard-card-drag-handle" title="Drag to reorder">
                        <i class="bi bi-grip-vertical text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <!-- Main 3 buttons + More Actions - equal sizes on mobile (4 buttons in one row) -->
                    <div class="col-3 col-md mb-2">
                        <a href="/doctor/patients" class="btn btn-outline-success quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-2 w-100">
                            <i class="bi bi-people mb-1"></i>
                            <span style="font-size: 0.7rem;">Patients</span>
                        </a>
                    </div>
                    <div class="col-3 col-md mb-2">
                        <a href="/doctor/drugs" class="btn btn-outline-success quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-2 w-100">
                            <i class="bi bi-capsule mb-1"></i>
                            <span style="font-size: 0.7rem;">Drugs</span>
                        </a>
                    </div>
                    <div class="col-3 col-md mb-2">
                        <a href="/doctor/calendar" class="btn btn-outline-primary quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-2 w-100">
                            <i class="bi bi-calendar3 mb-1"></i>
                            <span style="font-size: 0.7rem;">Calendar</span>
                        </a>
                    </div>
                    <!-- More Actions button - visible on mobile only, same size as other buttons -->
                    <div class="col-3 col-md mb-2 d-md-none">
                        <button type="button" class="btn btn-outline-secondary quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-2 w-100" data-bs-toggle="modal" data-bs-target="#moreActionsModal">
                            <i class="bi bi-three-dots mb-1"></i>
                            <span style="font-size: 0.7rem;">More</span>
                        </button>
                    </div>
                    <!-- Other buttons - hidden on mobile, visible on desktop -->
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/profile" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-person-circle mb-1"></i>
                            <span style="font-size: 0.75rem;">My Profile</span>
                        </a>
                    </div>
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/reports" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-graph-up mb-1"></i>
                            <span style="font-size: 0.75rem;">Reports</span>
                        </a>
                    </div>
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/alerts" class="btn btn-outline-danger quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-bell mb-1"></i>
                            <span style="font-size: 0.75rem;">Alerts</span>
                        </a>
                    </div>
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/notes" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-sticky mb-1"></i>
                            <span style="font-size: 0.75rem;">Notes</span>
                        </a>
                    </div>
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/medications" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-prescription mb-1"></i>
                            <span style="font-size: 0.75rem;">Prescriptions</span>
                        </a>
                    </div>
                    <div class="col-md mb-2 d-none d-md-block">
                        <a href="/doctor/glasses" class="btn btn-outline-primary quick-action-btn d-flex flex-column align-items-center justify-content-center px-3 py-2 w-100">
                            <i class="bi bi-eyeglasses mb-1"></i>
                            <span style="font-size: 0.75rem;">Glasses Prescriptions</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- More Actions Modal - Mobile Only -->
<div class="modal fade" id="moreActionsModal" tabindex="-1" aria-labelledby="moreActionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moreActionsModalLabel">
                    <i class="bi bi-lightning me-2"></i>
                    More Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4">
                        <a href="/doctor/profile" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <?php 
                            $currentUser = $this->getCurrentUser();
                            if (!empty($currentUser['profile_image'])): 
                                $profileImagePath = strpos($currentUser['profile_image'], '/public/') === 0 ? $currentUser['profile_image'] : '/public' . $currentUser['profile_image'];
                            ?>
                                <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                                     alt="Profile" 
                                     class="mb-2" 
                                     style="width: 2rem; height: 2rem; border-radius: 50%; object-fit: cover; border: 1px solid var(--accent);"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <i class="bi bi-person-circle mb-2" style="font-size: 2rem; display: none;"></i>
                            <?php else: ?>
                                <i class="bi bi-person-circle mb-2" style="font-size: 2rem;"></i>
                            <?php endif; ?>
                            <span style="font-size: 0.75rem !important; font-weight: 600 !important;">Profile</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/doctor/reports" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <i class="bi bi-graph-up mb-2" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 600;">Reports</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/doctor/alerts" class="btn btn-outline-danger quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <i class="bi bi-bell mb-2" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 600;">Alerts</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/doctor/notes" class="btn btn-outline-warning quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <i class="bi bi-sticky mb-2" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 600;">Notes</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/doctor/medications" class="btn btn-outline-info quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <i class="bi bi-prescription mb-2" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 600;">Prescriptions</span>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="/doctor/glasses" class="btn btn-outline-primary quick-action-btn d-flex flex-column align-items-center justify-content-center px-2 py-3 w-100" data-bs-dismiss="modal">
                            <i class="bi bi-eyeglasses mb-2" style="font-size: 1.5rem;"></i>
                            <span style="font-size: 0.7rem; font-weight: 600;">Glasses</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes Dashboard -->
<div class="row mb-4 dashboard-card-row" data-card-id="notes-dashboard">
    <div class="col-12">
        <div class="card shadow dashboard-card" id="notesDashboardCard" data-card-id="notes-dashboard">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-sticky me-2"></i>
                    Notes Board
                </h6>
                <div class="d-flex align-items-center gap-2">

                    <button class="btn btn-sm btn-success" id="dashboardAddNoteBtnHeader" onclick="dashboardAddNote()">
                        <i class="bi bi-plus-circle me-1"></i>Add Note
                    </button>
                    <a href="/doctor/notes" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>Notes Board
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('notes-dashboard')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('notes-dashboard')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" id="notesDashboardCardBody" style="position: relative; overflow: hidden;">
                <div id="dashboardNotesContainer" class="dashboard-notes-container">
                    <div class="text-center py-3" id="dashboardNotesLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="text-center py-4" id="dashboardNotesEmpty" style="display: none;">
                        <i class="bi bi-sticky text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3 mb-0">No notes yet. Click "Add Note" to create your first note.</p>
                    </div>
                </div>
                <div class="dashboard-notes-resize-handle" id="notesDashboardResizeHandle" title="Drag to resize"></div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Alerts -->
<div class="row mb-4 dashboard-card-row" data-card-id="today-alerts">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="today-alerts">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="bi bi-bell me-2"></i>
                    Today's Alerts
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <a href="/doctor/alerts" class="btn btn-sm btn-outline-warning manage-alerts-btn">
                        <i class="bi bi-gear me-1"></i>Manage Alerts
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('today-alerts')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('today-alerts')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="todayAlertsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 dashboard-card-row" data-card-id="upcoming-appointments">
    <!-- Upcoming Appointments -->
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="upcoming-appointments">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-calendar-event me-2"></i>
                    Upcoming Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">

                    <select class="form-select form-select-sm" id="upcomingPerPageSelect" style="width: auto;">
                        <option value="5">5 per page</option>
                        <option value="10" selected>10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                    <a href="/doctor/calendar" class="btn btn-sm btn-primary">
                        View All
                    </a>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('upcoming-appointments')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('upcoming-appointments')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="upcomingAppointmentsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="Upcoming Appointments Pagination" id="upcomingPaginationNav" style="display: none;">
                    <ul class="pagination justify-content-center mb-0" id="upcomingPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4 dashboard-card-row" data-card-id="missed-appointments">
    <!-- Missed Appointments -->
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="missed-appointments">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Missed Appointments
                </h6>
                <div class="d-flex align-items-center gap-2">

                    <select class="form-select form-select-sm" id="missedPerPageSelect" style="width: auto;">
                        <option value="5">5 per page</option>
                        <option value="10" selected>10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('missed-appointments')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('missed-appointments')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="missedAppointmentsContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="Missed Appointments Pagination" id="missedPaginationNav" style="display: none;">
                    <ul class="pagination justify-content-center mb-0" id="missedPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Visual Analytics -->
<div class="row mb-4 dashboard-card-row" data-card-id="visual-analytics">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="visual-analytics">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-graph-up me-2"></i>
                    Visual Analytics
                </h6>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('visual-analytics')" title="Move up">
                        <i class="bi bi-arrow-up"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('visual-analytics')" title="Move down">
                        <i class="bi bi-arrow-down"></i>
                    </button>
                    <div class="dashboard-card-drag-handle" title="Drag to reorder">
                        <i class="bi bi-grip-vertical text-muted"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Appointments Trend Line Chart -->
                    <div class="col-lg-6 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Trend</h6>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="appointmentsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Status Pie Chart -->
                    <div class="col-lg-3 mb-4">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h6 class="mb-0">Appointments Status</h6>
                            </div>
                            <div class="card-body chart-container">
                                <canvas id="appointmentsPieChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appointments Statistics Table -->
                    <div class="col-lg-3 mb-4">
                        <div class="card chart-card h-100 d-flex flex-column">
                            <div class="card-header">
                                <h6 class="mb-0">Statistics</h6>
                            </div>
                            <div class="card-body d-flex flex-column" style="background-color: var(--card); flex: 1;">
                                <table class="table table-sm table-borderless mb-0" id="appointmentsStatsTable" style="color: var(--text);">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">All Appointments:</td>
                                            <td class="text-end" style="color: var(--text) !important;" id="statsTotal">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">Completed:</td>
                                            <td class="text-end text-success" style="color: var(--success) !important;" id="statsCompleted">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted" style="color: var(--muted) !important;">Missed:</td>
                                            <td class="text-end text-danger" style="color: var(--danger) !important;" id="statsMissed">-</td>
                                        </tr>
                                        <tr class="border-top" style="border-top-color: var(--border) !important;">
                                            <td class="text-muted" style="color: var(--muted) !important;">Completion Ratio:</td>
                                            <td class="text-end text-primary" style="color: var(--accent) !important;" id="statsRatio">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-auto pt-3">
                                    <a href="/doctor/reports" class="btn btn-primary quick-action-btn w-100 d-flex align-items-center justify-content-center" style="font-size: 1rem; font-weight: 600;">
                                        <i class="bi bi-graph-up me-2" style="font-size: 1.2rem;"></i>
                                        <span>All Reports</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Timeline Events -->
<div class="row mb-4 dashboard-card-row" data-card-id="recent-activity">
    <div class="col-12">
        <div class="card shadow dashboard-card" data-card-id="recent-activity">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-activity me-2"></i>
                    Recent Activity
                </h6>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allActivitiesModal">
                        <i class="bi bi-list-ul me-1"></i>View All Activities
                    </button>
                    <div class="d-flex align-items-center gap-1 me-2">
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardUp('recent-activity')" title="Move up">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary dashboard-card-move-btn" onclick="moveCardDown('recent-activity')" title="Move down">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <div class="dashboard-card-drag-handle" title="Drag to reorder">
                            <i class="bi bi-grip-vertical text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="recentActivityContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Activities Modal - Extra Large -->
<div class="modal fade" id="allActivitiesModal" tabindex="-1" aria-labelledby="allActivitiesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allActivitiesModalLabel">
                    <i class="bi bi-activity me-2"></i>
                    All Activities
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-funnel"></i>
                            </span>
                            <input type="text" class="form-control" id="activitiesFilterInput" placeholder="Filter activities...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="modalPerPageSelect">
                            <option value="10" selected>10 per page</option>
                            <option value="20">20 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-outline-secondary" id="clearActivitiesFilter">
                            <i class="bi bi-x-circle me-1"></i>Clear Filter
                        </button>
                    </div>
                </div>
                <div id="allActivitiesContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <nav aria-label="All Activities Pagination" id="modalPaginationNav" style="display: none; margin-top: 1.5rem;">
                    <ul class="pagination justify-content-center mb-0" id="modalPaginationList">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/app/Views/doctor/assets/js/dashboard.js?v=<?= file_exists(__DIR__ . '/assets/js/dashboard.js') ? filemtime(__DIR__ . '/assets/js/dashboard.js') : time() ?>"></script>