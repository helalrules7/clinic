// Sticky Action Bar Management
document.addEventListener('DOMContentLoaded', function() {
    const actionBar = document.getElementById('actionBar');
    const actionBarContainer = document.getElementById('actionBarContainer');
    
    if (!actionBar || !actionBarContainer) return;
    
    // Get the offset position where sticky bar should appear
    let actionBarContainerTop = actionBarContainer.offsetTop;
    const topBar = document.querySelector('.top-bar');
    let topBarHeight = topBar ? topBar.offsetHeight : 70;
    
    let isSticky = false;
    
    function updateStickyPosition() {
        if (isSticky) {
            // Get current position and dimensions from container
            const rect = actionBarContainer.getBoundingClientRect();
            actionBar.style.top = (topBarHeight) + 'px';
            actionBar.style.left = rect.left + 'px';
            actionBar.style.width = rect.width + 'px';
        }
    }
    
    function handleScroll() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Make sticky when scrolled past the action bar container
        if (scrollTop >= actionBarContainerTop - 20) {
            if (!isSticky) {
                actionBar.classList.add('sticky-active');
                isSticky = true;
                updateStickyPosition();
            } else {
                // Update position on every scroll to maintain alignment
                updateStickyPosition();
            }
        } else {
            if (isSticky) {
                actionBar.classList.remove('sticky-active');
                actionBar.style.top = '';
                actionBar.style.left = '';
                actionBar.style.width = '';
                isSticky = false;
            }
        }
    }
    
    // Initial check
    handleScroll();
    
    // Listen to scroll events with throttling for better performance
    let scrollTimeout = null;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(handleScroll, 10);
    });
    
    // Also listen to resize events to recalculate position
    window.addEventListener('resize', function() {
        actionBarContainerTop = actionBarContainer.offsetTop;
        topBarHeight = topBar ? topBar.offsetHeight : 70;
        
        if (isSticky) {
            updateStickyPosition();
        }
        
        handleScroll();
    });
});

    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['chief_complaint', 'diagnosis'];
        let isValid = true;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        // Validate IOP fields - allow numeric values with + and - signs
        const iopFields = ['IOP_right', 'IOP_left'];
        iopFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && field.value.trim()) {
                const value = field.value.trim();
                // Allow empty, numeric values, or values with + and - at the beginning
                const iopPattern = /^[+-]?[0-9]*\.?[0-9]*$/;
                if (!iopPattern.test(value)) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    // Show error message
                    let errorDiv = field.parentNode.querySelector('.invalid-feedback');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        field.parentNode.appendChild(errorDiv);
                    }
                    errorDiv.textContent = 'Please enter a valid pressure value (e.g., 15.0, +2, -1)';
                } else {
                    field.classList.remove('is-invalid');
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please correct the errors before submitting the form.');
            return false;
        }
        
        // Add loading state to prevent double submission
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
        }
        
        return true;
    });


    // Real-time validation for IOP fields
    document.getElementById('IOP_right').addEventListener('input', function() {
        validateIOPField(this);
    });

    document.getElementById('IOP_left').addEventListener('input', function() {
        validateIOPField(this);
    });

    function validateIOPField(field) {
        const value = field.value.trim();
        const iopPattern = /^[+-]?[0-9]*\.?[0-9]*$/;
        
        if (value === '') {
            field.classList.remove('is-invalid', 'is-valid');
            return;
        }
        
        if (iopPattern.test(value)) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
        }
    }

// Common Complaints Modal Functions
(function() {
    const commonComplaintsBtn = document.getElementById('commonComplaintsBtn');
    const commonComplaintsModal = document.getElementById('commonComplaintsModal');
    
    if (commonComplaintsBtn && commonComplaintsModal) {
        commonComplaintsBtn.addEventListener('click', showCommonComplaintsModal);
    }
    
    function showCommonComplaintsModal() {
        const modal = new bootstrap.Modal(commonComplaintsModal);
        modal.show();
        
        // Show loading, hide content and error
        document.getElementById('commonComplaintsLoading').style.display = 'block';
        document.getElementById('commonComplaintsContent').style.display = 'none';
        document.getElementById('commonComplaintsError').style.display = 'none';
        
        // Fetch common complaints
        fetch('/api/consultation/common-complaints')
            .then(response => response.json())
            .then(data => {
                document.getElementById('commonComplaintsLoading').style.display = 'none';
                
                if (data.ok && data.data && data.data.complaints) {
                    displayCommonComplaints(data.data);
                    document.getElementById('commonComplaintsContent').style.display = 'block';
                } else {
                    throw new Error(data.error || 'Failed to load common complaints');
                }
            })
            .catch(error => {
                document.getElementById('commonComplaintsLoading').style.display = 'none';
                document.getElementById('commonComplaintsError').style.display = 'block';
                document.getElementById('errorMessage').textContent = error.message || 'An error occurred while loading common complaints.';
            });
    }
    
    function displayCommonComplaints(data) {
        const listContainer = document.getElementById('commonComplaintsList');
        const lastUpdated = document.getElementById('lastUpdated');
        
        if (data.last_updated) {
            lastUpdated.textContent = new Date(data.last_updated).toLocaleString('ar-EG');
        } else {
            lastUpdated.textContent = 'Never';
        }
        
        listContainer.innerHTML = '';
        
        if (data.complaints && data.complaints.length > 0) {
            data.complaints.forEach((item, index) => {
                const listItem = document.createElement('div');
                listItem.className = 'list-group-item list-group-item-action';
                listItem.style.cursor = 'pointer';
                listItem.innerHTML = `
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <span class="badge bg-primary me-2">${index + 1}</span>
                                ${escapeHtml(item.complaint)}
                            </h6>
                            ${item.diagnosis ? `<p class="mb-1 text-muted"><strong>Diagnosis:</strong> ${escapeHtml(item.diagnosis)}</p>` : ''}
                            ${item.plan ? `<p class="mb-0 text-muted"><strong>Plan:</strong> ${escapeHtml(item.plan)}</p>` : ''}
                        </div>
                        <small class="text-muted ms-2">
                            <i class="bi bi-clock-history me-1"></i>
                            Used ${item.count} ${item.count === 1 ? 'time' : 'times'}
                        </small>
                    </div>
                `;
                listItem.addEventListener('click', () => selectCommonComplaint(item));
                listContainer.appendChild(listItem);
            });
        } else {
            listContainer.innerHTML = '<div class="alert alert-info mb-0">No common complaints found.</div>';
        }
    }
    
    function selectCommonComplaint(item) {
        // Fill the form fields
        if (item.complaint) {
            const chiefComplaintField = document.getElementById('chief_complaint');
            if (chiefComplaintField) {
                chiefComplaintField.value = item.complaint;
                chiefComplaintField.dispatchEvent(new Event('input'));
            }
        }
        
        if (item.diagnosis) {
            const diagnosisField = document.getElementById('diagnosis');
            if (diagnosisField) {
                diagnosisField.value = item.diagnosis;
                diagnosisField.dispatchEvent(new Event('input'));
            }
        }
        
        if (item.plan) {
            const planField = document.getElementById('plan');
            if (planField) {
                planField.value = item.plan;
                planField.dispatchEvent(new Event('input'));
            }
        }
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(commonComplaintsModal);
        if (modal) {
            modal.hide();
        }
        
        // Scroll to form
        document.getElementById('chief_complaint').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();

// Autocomplete Functions
(function() {
    const autocompleteFields = ['chief_complaint', 'diagnosis', 'plan'];
    const debounceTimers = {};
    const suggestionContainers = {};
    
    autocompleteFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const suggestionsContainer = document.getElementById(fieldId + '_suggestions');
        
        if (field && suggestionsContainer) {
            suggestionContainers[fieldId] = suggestionsContainer;
            initAutocomplete(fieldId, field.getAttribute('data-field-type') || fieldId);
        }
    });
    
    function initAutocomplete(fieldId, fieldType) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        
        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!field.contains(e.target) && !suggestionContainers[fieldId].contains(e.target)) {
                suggestionContainers[fieldId].style.display = 'none';
            }
        });
        
        // Handle input with debounce
        field.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Clear previous timer
            if (debounceTimers[fieldId]) {
                clearTimeout(debounceTimers[fieldId]);
            }
            
            // Hide suggestions if query is too short
            if (query.length < 3) {
                suggestionContainers[fieldId].style.display = 'none';
                return;
            }
            
            // Set new timer
            debounceTimers[fieldId] = setTimeout(() => {
                fetchSuggestions(fieldType, query, fieldId);
            }, 300);
        });
        
        // Handle keyboard navigation
        field.addEventListener('keydown', function(e) {
            const suggestions = suggestionContainers[fieldId].querySelectorAll('.suggestion-item');
            const activeSuggestion = suggestionContainers[fieldId].querySelector('.suggestion-item.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (activeSuggestion) {
                    activeSuggestion.classList.remove('active');
                    const next = activeSuggestion.nextElementSibling;
                    if (next) {
                        next.classList.add('active');
                    } else {
                        suggestions[0]?.classList.add('active');
                    }
                } else {
                    suggestions[0]?.classList.add('active');
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (activeSuggestion) {
                    activeSuggestion.classList.remove('active');
                    const prev = activeSuggestion.previousElementSibling;
                    if (prev) {
                        prev.classList.add('active');
                    } else {
                        suggestions[suggestions.length - 1]?.classList.add('active');
                    }
                } else {
                    suggestions[suggestions.length - 1]?.classList.add('active');
                }
            } else if (e.key === 'Enter' && activeSuggestion) {
                e.preventDefault();
                activeSuggestion.click();
            } else if (e.key === 'Escape') {
                suggestionContainers[fieldId].style.display = 'none';
            }
        });
    }
    
    function fetchSuggestions(field, query, fieldId) {
        const url = `/api/consultation/suggestions?field=${encodeURIComponent(field)}&query=${encodeURIComponent(query)}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.ok && data.data) {
                    displaySuggestions(fieldId, data.data);
                } else {
                    suggestionContainers[fieldId].style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                suggestionContainers[fieldId].style.display = 'none';
            });
    }
    
    function displaySuggestions(fieldId, suggestions) {
        const container = suggestionContainers[fieldId];
        
        if (!suggestions || suggestions.length === 0) {
            container.style.display = 'none';
            return;
        }
        
        container.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            const item = document.createElement('div');
            item.className = 'suggestion-item';
            item.innerHTML = `
                <div class="suggestion-text">${escapeHtml(suggestion.text)}</div>
                <small class="suggestion-count">Used ${suggestion.count} ${suggestion.count === 1 ? 'time' : 'times'}</small>
            `;
            item.addEventListener('click', () => handleSuggestionClick(fieldId, suggestion.text));
            container.appendChild(item);
        });
        
        container.style.display = 'block';
    }
    
    function handleSuggestionClick(fieldId, suggestionText) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = suggestionText;
            field.dispatchEvent(new Event('input'));
            suggestionContainers[fieldId].style.display = 'none';
            
            // Trigger auto-resize for textarea
            if (field.tagName === 'TEXTAREA') {
                field.style.height = 'auto';
                field.style.height = field.scrollHeight + 'px';
            }
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();

// Fix modal z-index issues by moving them to body
function fixConsultationModalPlacement() {
    const modals = [
        'commonComplaintsModal',
        'deleteConsultationNoteModal' // This one is dynamic but let's be safe
    ];
    
    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    // Also catch any generic modals
    document.querySelectorAll('.modal').forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });
}

document.addEventListener('DOMContentLoaded', fixConsultationModalPlacement);