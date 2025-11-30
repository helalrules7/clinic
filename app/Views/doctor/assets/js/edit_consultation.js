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
            actionBar.style.top = topBarHeight + 'px';
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