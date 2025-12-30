<?php
/**
 * Media Gallery Page
 * Displays all patient images grouped by patient
 */
?>
<link href="/app/Views/doctor/assets/css/media.css?v=<?= file_exists(__DIR__ . '/assets/css/media.css') ? filemtime(__DIR__ . '/assets/css/media.css') : time() ?>" rel="stylesheet">
<div class="container-fluid py-4">
    <!-- Patient Filter Section -->
    <div class="card mb-4" style="background: var(--card); border: 1px solid var(--border); position: relative; z-index: 100;">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-8" style="position: relative; z-index: 10000;">
                    <label for="patientSearch" class="form-label" style="color: var(--text); font-weight: 600;">
                        <i class="bi bi-search me-2"></i>Filter by Patient Name
                    </label>
                    <input 
                        type="text" 
                        id="patientSearch" 
                        class="form-control" 
                        placeholder="Type patient name to search..."
                        autocomplete="off"
                        style="background: var(--card); border: 2px solid var(--border); color: var(--text); position: relative; z-index: 1;"
                    >
                    <div id="autocompleteResults" class="autocomplete-dropdown" style="z-index: 9999 !important;"></div>
                </div>
                <div class="col-md-4">
                    <div id="filterActiveSection" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary" id="selectedPatientBadge" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                <i class="bi bi-person-fill me-1"></i>
                                <span id="selectedPatientName"></span>
                            </span>
                            <button id="clearFilterBtn" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle me-1"></i>Clear Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Gallery Grid -->
    <div id="mediaGallery" class="row g-4 mb-4">
        <!-- Media cards will be loaded here -->
    </div>

    <!-- Load More Button with Horizontal Line -->
    <div class="mt-5 mb-4" id="loadMoreContainer">
        <div class="d-flex align-items-center">
            <div class="flex-grow-1" style="height: 1px; background: var(--border);"></div>
            <div class="px-4">
                <button id="loadMoreBtn" class="btn btn-primary btn-lg" style="display: none;">
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    Load More
                </button>
                <div id="loadingIndicator" class="spinner-border text-primary" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="flex-grow-1" style="height: 1px; background: var(--border);"></div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5" style="display: none;">
        <i class="bi bi-images" style="font-size: 4rem; color: var(--muted);"></i>
        <h4 class="mt-3" style="color: var(--text);">No images found</h4>
        <p style="color: var(--muted);">No patient images are available at this time.</p>
    </div>
</div>

<!-- Image Gallery Modal -->
<div class="modal fade" id="imageGalleryModal" tabindex="-1" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-white" id="galleryModalTitle">
                    <i class="bi bi-images me-2"></i>
                    Image Gallery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 position-relative" style="overflow: hidden;">
                <div id="imageCarousel" class="carousel slide h-100" data-bs-ride="false" data-bs-interval="false" data-bs-wrap="true">
                    <div class="carousel-inner h-100" id="carouselInner" style="position: relative; overflow: hidden;">
                        <!-- Images will be loaded here -->
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-white" id="imageCounter">1 / 1</span>
                        <span class="text-white-50">|</span>
                        <span class="text-white-50" id="currentImageName">-</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="#" id="viewPatientLink" class="btn btn-outline-info" target="_blank" style="display: none;">
                            <i class="bi bi-person-fill me-2 me-md-2 me-0"></i>
                            <span class="d-none d-md-inline">Patient</span>
                        </a>
                        <a href="#" id="viewSourceLink" class="btn btn-outline-light" target="_blank" style="display: none;">
                            <i class="bi bi-box-arrow-up-right me-2 me-md-2 me-0"></i>
                            <span id="sourceLinkText" class="d-none d-md-inline">Appointment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/app/Views/doctor/assets/js/media.js?v=<?= file_exists(__DIR__ . '/../doctor/assets/js/media.js') ? filemtime(__DIR__ . '/../doctor/assets/js/media.js') : time() ?>"></script>

<style>
    .modal-backdrop.show{
        display: none !important;
    }
    body > div.modal-backdrop.fade.show{
        display: none !important;
    }
    .dark .modal-content{
    background: rgba(11, 18, 32, 0.8) !important;
    }
    .modal-content{
    background: rgba(248, 250, 252, 0.8) !important;
    }
</style>