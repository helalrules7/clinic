
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