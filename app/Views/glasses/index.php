<?php
/**
 * Glasses Prescriptions Gallery Page
 * Displays all glasses prescriptions grouped by patient
 */
?>

<link href="/app/Views/doctor/assets/css/glasses.css?v=<?= file_exists(__DIR__ . '/assets/css/glasses.css') ? filemtime(__DIR__ . '/assets/css/glasses.css') : time() ?>" rel="stylesheet">

<div class="container-fluid py-4">
    <!-- Page header -->
    <div class="mstore-header mb-4">
        <span class="mstore-header-icon"><i class="bi bi-eyeglasses"></i></span>
        <div class="mstore-header-text">
            <h4 class="mstore-header-title">Glasses Prescriptions</h4>
            <p class="mstore-header-sub">Browse glasses prescriptions grouped by patient</p>
        </div>
    </div>

    <!-- Patient Filter Section -->
    <div class="card mstore-filter mb-4">
        <div class="card-body">
            <label for="patientSearch" class="mstore-filter-label">
                <i class="bi bi-search me-2"></i>Filter by Patient Name
            </label>
            <div class="row align-items-center g-3">
                <div class="col-md-8 mstore-search-col">
                    <div class="mstore-search-field">
                        <i class="bi bi-search mstore-search-ic"></i>
                        <input type="text" id="patientSearch" class="form-control mstore-search-input"
                            placeholder="Type patient name to search..." autocomplete="off">
                        <div id="autocompleteResults" class="autocomplete-dropdown"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="filterActiveSection" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="mstore-filter-chip" id="selectedPatientBadge">
                                <i class="bi bi-person-fill"></i>
                                <span id="selectedPatientName"></span>
                            </span>
                            <button id="clearFilterBtn" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Glasses Prescriptions Gallery Grid -->
    <div id="glassesGallery" class="row g-4 mb-4">
        <!-- Prescription cards will be loaded here -->
    </div>

    <!-- Load More -->
    <div class="mstore-loadmore" id="loadMoreContainer">
        <button id="loadMoreBtn" class="btn btn-primary btn-lg" style="display: none;">
            <i class="bi bi-arrow-down-circle me-2"></i>
            Load More
        </button>
        <div id="loadingIndicator" class="spinner-border text-primary" role="status" style="display: none;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="mstore-empty" style="display: none;">
        <span class="mstore-empty-icon"><i class="bi bi-eyeglasses"></i></span>
        <h4 class="mstore-empty-title">No glasses prescriptions found</h4>
        <p class="mstore-empty-sub">No glasses prescriptions are available at this time.</p>
    </div>
</div>

<!-- Prescription Preview Modal -->
<div class="modal fade" id="prescriptionPreviewModal" tabindex="-1" aria-labelledby="prescriptionPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background: var(--card); border: 1px solid var(--border);">
            <div class="modal-header" style="border-bottom: 1px solid var(--border);">
                <h5 class="modal-title" id="prescriptionPreviewModalLabel" style="color: var(--text);">
                    <i class="bi bi-eyeglasses me-2"></i>
                    Glasses Prescription Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="prescriptionPreviewContent" style="color: var(--text);">
                <!-- Prescription preview will be loaded here -->
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border); background: var(--card);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="color: var(--text);">Close</button>
                <a href="#" id="viewAppointmentLink" class="btn btn-success" target="_blank" style="display: none;">
                    <i class="bi bi-calendar-check me-2"></i>
                    View Appointment
                </a>
                <a href="#" id="viewFullPrescriptionLink" class="btn btn-primary" target="_blank">
                    <i class="bi bi-printer me-2"></i>
                    Print Prescription
                </a>
                <a href="#" id="viewPatientLink" class="btn btn-info" target="_blank">
                    <i class="bi bi-person-fill me-2"></i>
                    View Patient
                </a>
            </div>
        </div>
    </div>
</div>
<script src="/app/Views/doctor/assets/js/glasses.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/glasses.js') ? filemtime(__DIR__ . '/../doctor/assets/js/glasses.js') : time() ?>"></script>
<style>
.dark .modal-content{
    background: var(--card) !important;
    }
    .modal-content{
    background: var(--card) !important;
    }
</style>