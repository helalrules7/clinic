<link href="/app/Views/doctor/assets/css/organizer.css?v=<?= file_exists(__DIR__ . '/assets/css/organizer.css') ? filemtime(__DIR__ . '/assets/css/organizer.css') : time() ?>" rel="stylesheet">
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Organizer</h4>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-outline-info" id="prevMonthBtn">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-info" id="currentMonthBtn">
                    <i class="bi bi-calendar3 me-1"></i>
                    <span id="currentMonthDisplay"></span>
                </button>
                <button type="button" class="btn btn-outline-info" id="nextMonthBtn">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Month/Year Header -->
<div class="row mb-4">
    <div class="col-12 text-center">
        <h1 id="monthYearHeader" class="display-4 fw-bold mb-0" style="color: var(--text);">
            <!-- Will be populated by JavaScript -->
        </h1>
    </div>
</div>

<!-- Mobile Navigation Buttons (visible on mobile only) -->
<div class="row mb-3 d-md-none">
    <div class="col-12">
        <div class="d-flex justify-content-center gap-2 align-items-center">
            <button type="button" class="btn btn-outline-info organizer-mobile-nav-btn" id="mobilePrevDayBtn" title="Previous day">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="btn btn-info organizer-mobile-nav-btn" id="mobileGoToDateBtn" title="Go to specific date">
                <i class="bi bi-calendar-event me-1"></i>
                Go to Date
            </button>
            <button type="button" class="btn btn-success organizer-mobile-nav-btn" id="mobileGoToTodayBtn" title="Go to today">
                <i class="bi bi-calendar-check me-1"></i>
                Today
            </button>
            <button type="button" class="btn btn-outline-info organizer-mobile-nav-btn" id="mobileNextDayBtn" title="Next day">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-3">
                <div id="organizerCalendar">
                    <!-- Calendar will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading calendar...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Fullscreen Navigation Overlay (for day name/date display during navigation only) -->
<div id="mobileNavigationOverlay" class="organizer-mobile-navigation-overlay d-md-none">
    <div class="organizer-mobile-navigation-overlay-content">
        <div id="mobileNavigationOverlayDayName" class="organizer-mobile-navigation-overlay-day-name"></div>
        <div id="mobileNavigationOverlayDate" class="organizer-mobile-navigation-overlay-date"></div>
    </div>
</div>

<!-- Date Picker Popover (Mobile only) -->
<div class="modal fade organizer-modal-glass" id="datePickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="datePickerYear" class="form-label">Year</label>
                    <select class="form-select" id="datePickerYear">
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="datePickerMonth" class="form-label">Month</label>
                    <select class="form-select" id="datePickerMonth">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="datePickerDay" class="form-label">Day</label>
                    <select class="form-select" id="datePickerDay">
                        <!-- Will be populated by JavaScript -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="datePickerGoBtn">Go</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/alert_modal.php'; ?>

<script src="/app/Views/doctor/assets/js/organizer.js?v=<?= file_exists(__DIR__ . '/assets/js/organizer.js') ? filemtime(__DIR__ . '/assets/js/organizer.js') : time() ?>"></script>