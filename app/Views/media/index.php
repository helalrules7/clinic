<?php
/**
 * Media Gallery Page
 * Displays all patient images grouped by patient
 */
?>

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
    <div class="modal-dialog modal-fullscreen">
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
                            <span class="d-none d-md-inline">View Patient</span>
                        </a>
                        <a href="#" id="viewSourceLink" class="btn btn-outline-light" target="_blank" style="display: none;">
                            <i class="bi bi-box-arrow-up-right me-2 me-md-2 me-0"></i>
                            <span id="sourceLinkText" class="d-none d-md-inline">View Appointment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Media Gallery Styles */
.media-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: var(--card);
    border: 1px solid var(--border);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    height: 100%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dark .media-card {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.media-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-color: var(--accent);
}

.dark .media-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.media-thumbnail {
    width: 100%;
    height: 250px;
    object-fit: cover;
    background: var(--bg);
    transition: transform 0.3s ease;
}

.media-card:hover .media-thumbnail {
    transform: scale(1.05);
}

.media-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.85) 100%);
    display: flex;
    align-items: flex-end;
    padding: 1rem;
    opacity: 1;
    transition: opacity 0.3s ease, background 0.3s ease;
    z-index: 1;
}

.dark .media-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.9) 100%);
}

.media-card:hover .media-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.95) 100%);
}

.dark .media-card:hover .media-overlay {
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.98) 100%);
}

.media-count-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.85);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    backdrop-filter: blur(10px);
    z-index: 2;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .media-count-badge {
    background: rgba(0, 0, 0, 0.9);
    border-color: rgba(255, 255, 255, 0.2);
}

.media-patient-name {
    color: white;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.media-image-count {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
    margin-top: 0.25rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.carousel-item {
    text-align: center;
    padding: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
    position: relative;
}

.carousel-item.active {
    display: flex !important;
}

.carousel-item img {
    max-height: calc(100vh - 200px);
    max-width: 100%;
    width: auto;
    height: auto;
    margin: 0 auto;
    object-fit: contain;
    display: block;
    transition: opacity 0.3s ease, transform 0.3s ease;
    will-change: transform, opacity;
}

.carousel-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    padding: 1rem;
    color: white;
    z-index: 5;
}

/* Ensure only active item is visible with smooth transitions */
.carousel-inner {
    position: relative;
    overflow: hidden;
}

.carousel-inner .carousel-item {
    transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-inner .carousel-item:not(.active) {
    display: none !important;
}

.carousel-inner .carousel-item.active {
    display: flex !important;
    opacity: 1;
    transform: scale(1);
    animation: fadeInScale 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.carousel-item.active img {
    transform: scale(1);
}

.carousel-item.active img:hover {
    transform: scale(1.02);
}

/* Carousel Controls Styling - Enhanced with smooth animations */
.carousel-control-prev,
.carousel-control-next {
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.7;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.carousel-control-prev {
    left: 20px;
}

.carousel-control-next {
    right: 20px;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(0, 0, 0, 0.9);
    opacity: 1;
    transform: translateY(-50%) scale(1.15);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}

.carousel-control-prev:active,
.carousel-control-next:active {
    transform: translateY(-50%) scale(1.05);
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 30px;
    height: 30px;
    background-size: 100% 100%;
    filter: brightness(0) invert(1);
    transition: transform 0.3s ease;
}

.carousel-control-prev:hover .carousel-control-prev-icon {
    transform: translateX(-2px);
}

.carousel-control-next:hover .carousel-control-next-icon {
    transform: translateX(2px);
}

.dark .carousel-control-prev-icon,
.dark .carousel-control-next-icon {
    filter: brightness(0) invert(1);
}

/* Ensure controls are visible */
#imageCarousel .carousel-control-prev,
#imageCarousel .carousel-control-next {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

/* Modal Footer Styling */
.modal-footer {
    background: rgba(0, 0, 0, 0.9) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
}

/* Dark Mode Support for Media Gallery Page */
.dark .container-fluid h2 {
    color: var(--text) !important;
}

.dark .container-fluid p {
    color: var(--muted) !important;
}

.dark #loadMoreBtn {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.dark #loadMoreBtn:hover {
    background: var(--success);
    border-color: var(--success);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
}

.dark #loadingIndicator {
    color: var(--accent) !important;
}

.dark #emptyState h4 {
    color: var(--text) !important;
}

.dark #emptyState p {
    color: var(--muted) !important;
}

.dark #emptyState .bi-images {
    color: var(--muted) !important;
}

/* Patient Search Autocomplete Styles */
#patientSearch {
    position: relative;
    z-index: 1;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    display: none;
    margin-top: -1px;
}

.dark .autocomplete-dropdown {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 9999 !important;
}

.dark .autocomplete-dropdown {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.autocomplete-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    transition: background-color 0.2s ease;
    color: var(--text);
}

.autocomplete-item:hover {
    background: var(--bg);
}

.autocomplete-item:last-child {
    border-bottom: none;
}

.autocomplete-item.active {
    background: var(--accent);
    color: white;
}

.autocomplete-item .patient-name {
    font-weight: 600;
    font-size: 0.95rem;
}

.autocomplete-item .patient-info {
    font-size: 0.85rem;
    color: var(--muted);
    margin-top: 0.25rem;
}

.autocomplete-item.active .patient-info {
    color: rgba(255, 255, 255, 0.8);
}

#filterActiveSection {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile responsive for modal footer buttons */
@media (max-width: 768px) {
    #viewPatientLink span,
    #viewSourceLink span {
        display: none !important;
    }
    
    #viewPatientLink i,
    #viewSourceLink i {
        margin: 0 !important;
    }
    
    #viewPatientLink,
    #viewSourceLink {
        min-width: 44px;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem !important;
    }
}
</style>

<script>
let currentPage = 1;
let isLoading = false;
let hasMore = true;
let currentPatientImages = [];
let currentPatientId = null;
let selectedPatientId = null;
let selectedPatientName = null;
let searchTimeout = null;
let autocompleteItems = [];
let selectedAutocompleteIndex = -1;

// Load initial media
document.addEventListener('DOMContentLoaded', function() {
    loadMedia();
    
    // Load more button
    document.getElementById('loadMoreBtn').addEventListener('click', function() {
        if (!isLoading && hasMore) {
            currentPage++;
            loadMedia();
        }
    });
    
    // Patient search autocomplete
    setupPatientSearch();
    
    // Clear filter button
    document.getElementById('clearFilterBtn').addEventListener('click', function() {
        clearFilter();
    });
    
    // Carousel event listener will be set up in renderCarousel function
});

function setupPatientSearch() {
    const searchInput = document.getElementById('patientSearch');
    const autocompleteResults = document.getElementById('autocompleteResults');
    
    if (!searchInput || !autocompleteResults) {
        console.error('Patient search elements not found');
        return;
    }
    
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideAutocomplete();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300);
    });
    
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            navigateAutocomplete(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            navigateAutocomplete(-1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            selectAutocompleteItem();
        } else if (e.key === 'Escape') {
            hideAutocomplete();
        }
    });
    
    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
            hideAutocomplete();
        }
    });
}

function searchPatients(query) {
    fetch(`/api/patients/search?q=${encodeURIComponent(query)}&limit=10`)
        .then(response => response.json())
        .then(data => {
            if ((data.ok || data.success) && data.data && data.data.length > 0) {
                displayAutocomplete(data.data);
            } else {
                hideAutocomplete();
            }
        })
        .catch(error => {
            console.error('Error searching patients:', error);
            hideAutocomplete();
        });
}

function displayAutocomplete(patients) {
    const autocompleteResults = document.getElementById('autocompleteResults');
    autocompleteItems = patients;
    selectedAutocompleteIndex = -1;
    
    autocompleteResults.innerHTML = '';
    
    patients.forEach((patient, index) => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.dataset.index = index;
        item.dataset.patientId = patient.id;
        
        const name = `${patient.first_name} ${patient.last_name}`;
        item.innerHTML = `
            <div class="patient-name">${name}</div>
            <div class="patient-info">
                ${patient.phone ? `📞 ${patient.phone}` : ''}
                ${patient.national_id ? ` | 🆔 ${patient.national_id}` : ''}
            </div>
        `;
        
        item.addEventListener('click', function() {
            selectPatient(patient.id, name);
        });
        
        item.addEventListener('mouseenter', function() {
            selectedAutocompleteIndex = index;
            updateAutocompleteSelection();
        });
        
        autocompleteResults.appendChild(item);
    });
    
    autocompleteResults.style.display = 'block';
}

function navigateAutocomplete(direction) {
    if (autocompleteItems.length === 0) return;
    
    selectedAutocompleteIndex += direction;
    
    if (selectedAutocompleteIndex < 0) {
        selectedAutocompleteIndex = autocompleteItems.length - 1;
    } else if (selectedAutocompleteIndex >= autocompleteItems.length) {
        selectedAutocompleteIndex = 0;
    }
    
    updateAutocompleteSelection();
}

function updateAutocompleteSelection() {
    const items = document.querySelectorAll('.autocomplete-item');
    items.forEach((item, index) => {
        if (index === selectedAutocompleteIndex) {
            item.classList.add('active');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('active');
        }
    });
}

function selectAutocompleteItem() {
    if (selectedAutocompleteIndex >= 0 && selectedAutocompleteIndex < autocompleteItems.length) {
        const patient = autocompleteItems[selectedAutocompleteIndex];
        const name = `${patient.first_name} ${patient.last_name}`;
        selectPatient(patient.id, name);
    }
}

function selectPatient(patientId, patientName) {
    selectedPatientId = patientId;
    selectedPatientName = patientName;
    
    // Update UI
    document.getElementById('patientSearch').value = patientName;
    document.getElementById('selectedPatientName').textContent = patientName;
    document.getElementById('filterActiveSection').style.display = 'block';
    
    hideAutocomplete();
    
    // Reset and reload media
    currentPage = 1;
    document.getElementById('mediaGallery').innerHTML = '';
    loadMedia();
}

function clearFilter() {
    selectedPatientId = null;
    selectedPatientName = null;
    
    // Update UI
    document.getElementById('patientSearch').value = '';
    document.getElementById('filterActiveSection').style.display = 'none';
    hideAutocomplete();
    
    // Reset and reload media
    currentPage = 1;
    document.getElementById('mediaGallery').innerHTML = '';
    loadMedia();
}

function hideAutocomplete() {
    document.getElementById('autocompleteResults').style.display = 'none';
    selectedAutocompleteIndex = -1;
}

function loadMedia() {
    if (isLoading) return;
    
    isLoading = true;
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('loadMoreBtn').style.display = 'none';
    
    // Add patient filter to URL if selected
    let url = `/api/media?page=${currentPage}`;
    if (selectedPatientId) {
        url += `&patient_id=${selectedPatientId}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
            
            if (data.success && data.data.length > 0) {
                renderMediaCards(data.data);
                
                // Update pagination
                hasMore = data.pagination.has_more;
                if (hasMore) {
                    document.getElementById('loadMoreBtn').style.display = 'block';
                }
                
                // Hide empty state
                document.getElementById('emptyState').style.display = 'none';
            } else {
                if (currentPage === 1) {
                    document.getElementById('emptyState').style.display = 'block';
                }
                hasMore = false;
            }
        })
        .catch(error => {
            console.error('Error loading media:', error);
            isLoading = false;
            document.getElementById('loadingIndicator').style.display = 'none';
        });
}

function renderMediaCards(patients) {
    const gallery = document.getElementById('mediaGallery');
    
    patients.forEach(patient => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-lg-3 col-sm-6';
        
        const card = document.createElement('div');
        card.className = 'media-card';
        card.onclick = () => openGallery(patient.patient_id, patient.patient_name);
        
        const thumbnail = document.createElement('img');
        thumbnail.src = patient.thumbnail_url || patient.view_url || '/assets/images/placeholder.png';
        thumbnail.className = 'media-thumbnail';
        thumbnail.alt = patient.patient_name;
        thumbnail.onerror = function() {
            this.src = '/assets/images/placeholder.png';
        };
        
        const overlay = document.createElement('div');
        overlay.className = 'media-overlay';
        
        const badge = document.createElement('div');
        badge.className = 'media-count-badge';
        badge.innerHTML = `<i class="bi bi-images me-1"></i>${patient.image_count}`;
        
        const info = document.createElement('div');
        info.innerHTML = `
            <div class="media-patient-name">${patient.patient_name}</div>
            <div class="media-image-count">${patient.image_count} ${patient.image_count === 1 ? 'image' : 'images'}</div>
        `;
        
        overlay.appendChild(info);
        card.appendChild(thumbnail);
        card.appendChild(badge);
        card.appendChild(overlay);
        col.appendChild(card);
        gallery.appendChild(col);
    });
}

function openGallery(patientId, patientName) {
    currentPatientId = patientId;
    
    // Show loading
    const modal = new bootstrap.Modal(document.getElementById('imageGalleryModal'));
    modal.show();
    
    document.getElementById('galleryModalTitle').textContent = `${patientName} - Image Gallery`;
    
    // Load patient images
    fetch(`/api/media/patient?patient_id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                currentPatientImages = data.data;
                renderCarousel(data.data);
            } else {
                alert('No images found for this patient');
                modal.hide();
            }
        })
        .catch(error => {
            console.error('Error loading patient images:', error);
            alert('Error loading images');
            modal.hide();
        });
}

function renderCarousel(images) {
    const carouselInner = document.getElementById('carouselInner');
    const carousel = document.getElementById('imageCarousel');
    
    // Destroy existing carousel instance if any
    const existingCarousel = bootstrap.Carousel.getInstance(carousel);
    if (existingCarousel) {
        existingCarousel.dispose();
    }
    
    // Clear existing content
    carouselInner.innerHTML = '';
    
    // Create carousel items
    images.forEach((image, index) => {
        const item = document.createElement('div');
        item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
        
        const img = document.createElement('img');
        const imageUrl = image.view_url || getImageUrl(image);
        img.src = imageUrl;
        img.className = 'img-fluid';
        img.alt = image.original_filename || 'Image';
        img.loading = 'lazy';
        
        img.onerror = function() {
            this.src = '/assets/images/placeholder.png';
        };
        
        const caption = document.createElement('div');
        caption.className = 'carousel-caption';
        caption.innerHTML = `
            <div class="text-start">
                <strong>${image.original_filename || 'Image'}</strong>
                <br>
                <small>${formatDate(image.created_at)}</small>
            </div>
        `;
        
        item.appendChild(img);
        item.appendChild(caption);
        carouselInner.appendChild(item);
    });
    
    // Wait for DOM to update, then initialize carousel
    setTimeout(() => {
        // Initialize new carousel instance
        const carouselInstance = new bootstrap.Carousel(carousel, {
            interval: false,
            wrap: true,
            keyboard: true,
            touch: true
        });
        
        // Remove any existing listeners and add new one
        carousel.removeEventListener('slid.bs.carousel', handleCarouselSlide);
        carousel.addEventListener('slid.bs.carousel', handleCarouselSlide);
        
        // Update counter and initial info
        updateImageCounter(0);
    }, 50);
}

// Separate function for carousel slide handler to allow removal
function handleCarouselSlide(event) {
    const carouselInner = document.getElementById('carouselInner');
    const items = carouselInner.querySelectorAll('.carousel-item');
    
    // Force show the active item with smooth transition
    const activeItem = items[event.to];
    if (activeItem) {
        activeItem.classList.add('active');
        activeItem.style.display = 'flex';
        
        // Hide all other items
        items.forEach((item, idx) => {
            if (idx !== event.to) {
                item.classList.remove('active');
                item.style.display = 'none';
            }
        });
    }
    
    updateImageCounter(event.to);
}

function updateImageCounter(index) {
    const total = currentPatientImages.length;
    document.getElementById('imageCounter').textContent = `${index + 1} / ${total}`;
    
    // Update link and filename for current image
    if (currentPatientImages[index]) {
        const currentImage = currentPatientImages[index];
        document.getElementById('currentImageName').textContent = currentImage.original_filename || '-';
        
        const viewSourceLink = document.getElementById('viewSourceLink');
        const sourceLinkText = document.getElementById('sourceLinkText');
        const viewPatientLink = document.getElementById('viewPatientLink');
        
        // Always show patient link if patient_id exists
        if (currentImage.patient_id) {
            viewPatientLink.href = '/doctor/patients/' + currentImage.patient_id;
            viewPatientLink.style.display = 'inline-block';
        } else {
            viewPatientLink.style.display = 'none';
        }
        
        // Show appointment link if available
        if (currentImage.source_link && currentImage.source_type === 'appointment') {
            viewSourceLink.href = currentImage.source_link;
            sourceLinkText.textContent = 'View Appointment';
            viewSourceLink.style.display = 'inline-block';
        } else {
            viewSourceLink.style.display = 'none';
        }
    }
}

function getImageUrl(image) {
    if (image.view_url) return image.view_url;
    if (image.file_path) {
        if (image.file_path.startsWith('storage/') || image.file_path.startsWith('uploads/')) {
            return '/' + image.file_path;
        }
        return image.file_path;
    }
    return '/assets/images/placeholder.png';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-EG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

