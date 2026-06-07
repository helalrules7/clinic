/** layouts/global-search.js — command-palette search (secretary layout; doctor still in main.js) */
(function () {
    'use strict';
    var cfg = window.__GLOBAL_SEARCH_CONFIG__ || {};
    var isSecretary = cfg.mode === 'secretary';
    function mapResultUrl(result) {
        if (!result || !isSecretary) return result && result.url;
        var url = result.url || "";
        var id = result.id;
        var type = result.type;
        if (type === "patient" || url.indexOf("/doctor/patients/") === 0) return "/secretary/patients/" + id;
        if (type === "appointment" || url.indexOf("/doctor/appointments/") === 0) return "/secretary/bookings/" + id;
        if (type === "payment" || url.indexOf("/doctor/payments/") === 0) return "/secretary/payments/" + id;
        return url;
    }
    const searchInput = document.getElementById('globalSearchInput');
    const searchContainer = document.getElementById('globalSearchContainer');
    const searchResults = document.getElementById('globalSearchResults');
    const searchClear = document.getElementById('globalSearchClear');
    const searchToggle = document.getElementById('globalSearchToggle');
    const searchBackdrop = document.getElementById('globalSearchBackdrop');
    
    if (!searchInput || !searchContainer || !searchResults) return;
    
    let searchTimeout;
    let currentResults = [];
    let selectedIndex = -1;
    
    // Store original parent for restoration
    let originalParent = null;
    let isExpanding = false; // Flag to prevent immediate collapse when expanding
    let blurTimeout = null;
    
    function getSearchAnchorTop() {
        const noticeBar = document.querySelector('.notice-bar');
        const topBar = document.querySelector('.top-bar');
        let bottom = 0;

        if (noticeBar) {
            const rect = noticeBar.getBoundingClientRect();
            const style = window.getComputedStyle(noticeBar);
            const visible = rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
            if (visible) {
                bottom = Math.max(bottom, rect.bottom);
            }
        }

        if (topBar) {
            bottom = Math.max(bottom, topBar.getBoundingClientRect().bottom);
        }

        return Math.max(0, Math.round(bottom));
    }

    function applySearchAnchor() {
        searchContainer.style.setProperty('--global-search-anchor-top', getSearchAnchorTop() + 'px');
    }

    function onSearchOverlayLayout() {
        if (searchContainer.classList.contains('expanded')) {
            applySearchAnchor();
        }
    }

    window.addEventListener('resize', onSearchOverlayLayout);
    window.addEventListener('scroll', onSearchOverlayLayout, { passive: true });

    // Expand search on click/focus — unified mobile + desktop command palette
    function expandSearch(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }

        if (searchContainer.classList.contains('expanded')) {
            return;
        }

        isExpanding = true;
        applySearchAnchor();

        if (searchContainer.parentElement !== document.body) {
            originalParent = searchContainer.parentElement;
            document.body.appendChild(searchContainer);
        }

        searchContainer.classList.remove('collapsing', 'show');

        if (searchBackdrop) {
            searchBackdrop.style.display = 'block';
            searchBackdrop.style.visibility = 'visible';
            searchBackdrop.style.zIndex = '999998';
        }

        searchContainer.offsetHeight;

        searchContainer.classList.add('expanded');
        document.body.style.overflow = 'hidden';
        document.body.classList.add('global-search-open');

        if (blurTimeout) {
            clearTimeout(blurTimeout);
            blurTimeout = null;
        }

        setTimeout(() => {
            searchInput.focus();
            if (searchInput.value.trim().length >= 2 && currentResults.length > 0) {
                searchResults.classList.add('show');
            }
            setTimeout(() => {
                isExpanding = false;
            }, 300);
        }, 100);
    }
    
    // Collapse search - 3D perspective close animation
    function collapseSearch() {
        hideResults();
        searchInput.blur();

        if (!searchContainer.classList.contains('expanded')) {
            searchContainer.classList.remove('show');
            document.body.classList.remove('global-search-open');
            return;
        }

        // Force reflow to ensure CSS transition starts from expanded state
        searchContainer.offsetHeight;
        
        // Add collapsing class for 3D perspective close animation
        // Keep expanded class during transition so backdrop stays visible
        searchContainer.classList.add('collapsing');

        // Wait for CSS transition to complete (0.5s)
        setTimeout(() => {
            // Remove expanded and collapsing classes
            searchContainer.classList.remove('expanded', 'show', 'collapsing');

            document.body.style.overflow = '';
            document.body.classList.remove('global-search-open');
            
            // Remove backdrop completely to prevent interference with other elements
            if (searchBackdrop) {
                searchBackdrop.style.display = 'none';
                searchBackdrop.style.opacity = '0';
                searchBackdrop.style.pointerEvents = 'none';
                searchBackdrop.style.zIndex = '-1';
                searchBackdrop.style.visibility = 'hidden';
            }

            // Move container back to original parent if it was moved
            if (originalParent && searchContainer.parentElement === document.body) {
                // Use requestAnimationFrame for smooth DOM manipulation
                requestAnimationFrame(() => {
                    originalParent.appendChild(searchContainer);
                    originalParent = null;

                    // Ensure container is visible in original position
                    requestAnimationFrame(() => {
                        searchContainer.style.opacity = '';
                        searchContainer.style.pointerEvents = '';
                    });
                });
            }
            // Clear any pending blur timeout
            if (blurTimeout) {
                clearTimeout(blurTimeout);
                blurTimeout = null;
            }
        }, 520); // Match CSS transition duration (0.5s + buffer)
    }
    
    // Handle search input blur
    function handleSearchBlur() {
        // Don't collapse if we're expanding or if user is clicking inside
        if (isExpanding) {
            return;
        }
        
        // Delay collapse to allow click events to process first
        blurTimeout = setTimeout(() => {
            // Double check that we're not expanding
            if (!isExpanding && searchContainer.classList.contains('expanded')) {
                collapseSearch();
            }
        }, 200);
    }
    
    // Mobile toggle
    if (searchToggle) {
        searchToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (searchContainer.classList.contains('expanded')) {
                collapseSearch();
            } else {
                expandSearch();
            }
        });
    }
    
    // Expand on input click/focus
    searchInput.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!searchContainer.classList.contains('expanded')) {
            expandSearch(e);
        }
    });
    
    searchInput.addEventListener('focus', function(e) {
        e.stopPropagation();
        if (!searchContainer.classList.contains('expanded')) {
            expandSearch(e);
        }
        if (this.value.trim().length >= 2 && currentResults.length > 0) {
            searchResults.classList.add('show');
        }
    });
    
    // Handle blur on expanded search input
    searchInput.addEventListener('blur', function(e) {
        // Only handle blur if search is expanded
        if (searchContainer.classList.contains('expanded')) {
            handleSearchBlur();
        }
    });
    
    // Prevent top search input from closing when clicked
    searchInput.addEventListener('mousedown', function(e) {
        if (!searchContainer.classList.contains('expanded')) {
            e.stopPropagation();
        }
    });
    
    // Backdrop click to close
    if (searchBackdrop) {
        searchBackdrop.addEventListener('click', function(e) {
            e.stopPropagation();
            collapseSearch();
        });
    }
    
    // Prevent collapse if clicking inside the search wrapper
    searchContainer.addEventListener('click', function(e) {
        if (e.target.closest('.global-search-input-wrapper')) {
            if (blurTimeout) {
                clearTimeout(blurTimeout);
                blurTimeout = null;
            }
            e.stopPropagation();
        }
    });
    
    // Close when clicking outside
    document.addEventListener('click', function(e) {
        // Don't close if clicking on the search input or if expanding
        if (e.target === searchInput || searchInput.contains(e.target) || isExpanding) {
            return;
        }
        
        // Close if clicking outside the expanded search
        if (searchContainer.classList.contains('expanded') && !searchContainer.contains(e.target)) {
            collapseSearch();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && searchContainer.classList.contains('expanded')) {
            collapseSearch();
        }
    });
    
    // Clear button
    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.stopPropagation();
            searchInput.value = '';
            searchInput.focus();
            hideResults();
            searchClear.classList.add('d-none');
        });
    }
    
    // Check if query is a date (DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD, YYYY/MM/DD, etc.)
    function isDateQuery(query) {
        if (!query) {
            return false;
        }
        
        const trimmed = query.trim();
        
        // Must have at least 6 characters (e.g., "17/12/25")
        if (trimmed.length < 6) {
            return false;
        }
        
        // Check for common date patterns
        // Pattern 1: DD-MM-YYYY or DD/MM/YYYY (1-2 digits, separator, 1-2 digits, separator, 2-4 digits)
        // Examples: "17/12/2025", "17-12-2025", "7/1/25", "7-1-2025"
        const pattern1 = /^\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}$/;
        // Pattern 2: YYYY-MM-DD or YYYY/MM/DD (2-4 digits, separator, 1-2 digits, separator, 1-2 digits)
        // Examples: "2025-12-17", "2025/12/17", "25-1-7"
        const pattern2 = /^\d{2,4}[-\/]\d{1,2}[-\/]\d{1,2}$/;
        
        return pattern1.test(trimmed) || pattern2.test(trimmed);
    }
    
    // Search input handler
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        if (searchClear) {
            searchClear.classList.toggle('d-none', query.length === 0);
        }
        
        // For date queries, allow search with minimum 6 characters (e.g., "17/12/25")
        // For regular queries, require minimum 2 characters
        const isDate = isDateQuery(query);
        const minLength = isDate ? 6 : 2;
        
        if (query.length < minLength) {
            hideResults();
            return;
        }
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            collapseSearch();
            return;
        }
        
        if (!searchResults.classList.contains('show') || currentResults.length === 0) {
            if (e.key === 'Enter' && this.value.trim().length >= 2) {
                performSearch(this.value.trim());
            }
            return;
        }
        
        const items = searchResults.querySelectorAll('.global-search-result-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && items[selectedIndex]) {
                items[selectedIndex].click();
            }
        }
    });
    
    // Click outside to close (for mobile)
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!searchContainer.contains(e.target) && !searchToggle?.contains(e.target)) {
                searchContainer.classList.remove('show');
                hideResults();
            }
        }
    });
    
    function updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === selectedIndex);
            item.classList.toggle('selected', index === selectedIndex);
        });
    }
    
    function performSearch(query) {
        // Ensure search is expanded before showing results
        if (!searchContainer.classList.contains('expanded') && window.innerWidth > 768) {
            expandSearch();
        }
        
        // Check if query is a date - if so, search only appointments
        const trimmedQuery = query.trim();
        let searchUrl;
        const isDate = isDateQuery(trimmedQuery);
        
        if (isDate) {
            // Search only appointments by date
            searchUrl = `/api/appointments/search?q=${encodeURIComponent(trimmedQuery)}&limit=10`;
        } else {
            // Normal comprehensive search
            searchUrl = `/api/search/comprehensive?q=${encodeURIComponent(trimmedQuery)}&limit=10`;
        }
        
        fetch(searchUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Search failed');
            }
            return response.json();
        })
        .then(data => {
            // Handle appointment search response format
            if (data.ok && data.data && Array.isArray(data.data)) {
                // Convert appointment data to results format
                currentResults = data.data.map(apt => {
                    // Format date from YYYY-MM-DD to DD-MM-YYYY
                    let dateStr = '';
                    if (apt.date) {
                        try {
                            const dateParts = apt.date.split('-');
                            if (dateParts.length === 3) {
                                const day = dateParts[2].padStart(2, '0');
                                const month = dateParts[1].padStart(2, '0');
                                const year = dateParts[0];
                                dateStr = `${day}-${month}-${year}`;
                            } else {
                                dateStr = apt.date;
                            }
                        } catch (e) {
                            dateStr = apt.date;
                        }
                    }
                    const timeStr = apt.start_time ? apt.start_time.substring(0, 5) : '';
                    const patientName = (apt.patient_name || '').trim();
                    
                    return {
                        id: apt.id,
                        title: `Appointment #${apt.id}${patientName ? ' - ' + patientName : ''}`,
                        subtitle: `${dateStr}${timeStr ? ' at ' + timeStr : ''}${apt.status ? ' - ' + apt.status : ''}`,
                        type: 'appointment',
                        icon: 'bi-calendar3',
                        url: mapResultUrl({
                            id: apt.id,
                            type: 'appointment',
                            url: '/doctor/appointments/' + apt.id
                        })
                    };
                });
            } else {
                // Normal comprehensive search results
                currentResults = (data.results || []).map(function (r) {
                    return Object.assign({}, r, { url: mapResultUrl(r) });
                });
            }
            displayResults(currentResults, query);
        })
        .catch(error => {
            // Show error message
            searchResults.innerHTML = `
                <div class="global-search-results-empty">
                    <div class="global-search-empty-icon">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="global-search-empty-text">
                        <div class="global-search-empty-title">Search error</div>
                        <div class="global-search-empty-subtitle">Please try again</div>
                    </div>
                </div>
            `;
            searchResults.classList.add('show');
        });
    }
    
    function displayResults(results, query) {
        // Ensure container is visible
        if (!searchContainer.classList.contains('expanded') && window.innerWidth > 768) {
            expandSearch();
        }
        
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="global-search-results-empty">
                    <div class="global-search-empty-icon">
                        <i class="bi bi-search"></i>
                    </div>
                    <div class="global-search-empty-text">
                        <div class="global-search-empty-title">No results found</div>
                        <div class="global-search-empty-subtitle">No results found for "${escapeHtml(query)}"</div>
                    </div>
                </div>
            `;
            searchResults.classList.add('show');
            return;
        }
        
        const html = results.map((result, index) => {
            return `
                <div class="global-search-result-item" data-url="${escapeHtml(result.url)}" data-index="${index}">
                    <div class="global-search-result-icon">
                        <i class="bi ${escapeHtml(result.icon)}"></i>
                    </div>
                    <div class="global-search-result-content">
                        <div class="global-search-result-title">${escapeHtml(result.title)}</div>
                        <div class="global-search-result-subtitle">${escapeHtml(result.subtitle || '')}</div>
                    </div>
                    <span class="global-search-result-type">${escapeHtml(result.type)}</span>
                </div>
            `;
        }).join('');
        
        searchResults.innerHTML = html;
        searchResults.classList.add('show');
        selectedIndex = -1;
        
        // Force reflow to ensure visibility
        searchResults.offsetHeight;
        
        // Add click handlers
        searchResults.querySelectorAll('.global-search-result-item').forEach(function(item) {
            item.addEventListener('click', function() {
                const url = this.dataset.url;
                if (url) {
                    // Close search and cleanup backdrop before navigation
                    collapseSearch();
                    
                    // Immediately hide and disable backdrop
                    if (searchBackdrop) {
                        searchBackdrop.style.display = 'none';
                        searchBackdrop.style.opacity = '0';
                        searchBackdrop.style.pointerEvents = 'none';
                        searchBackdrop.style.zIndex = '-1';
                        searchBackdrop.style.visibility = 'hidden';
                    }
                    
                    // Remove any remaining backdrop elements (only modal-backdrop, not global-search-backdrop as it's part of container)
                    const modalBackdrops = document.querySelectorAll('.modal-backdrop');
                    modalBackdrops.forEach(function(backdrop) {
                        backdrop.remove();
                    });
                    
                    // Restore body overflow
                    document.body.style.overflow = '';
                    
                    // Small delay to ensure cleanup before navigation
                    setTimeout(function() {
                        window.location.href = url;
                    }, 50);
                }
            });
        });
    }
    
    function hideResults() {
        searchResults.classList.remove('show');
        selectedIndex = -1;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
