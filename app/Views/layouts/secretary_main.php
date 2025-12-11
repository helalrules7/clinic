<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HClinic / عيادة رؤية - السكرتارية' ?></title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/public/assets/fav/faicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/public/assets/fav/faicon-180x180.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/fav/faicon-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/public/assets/fav/faicon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/public/assets/fav/faicon-512x512.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Doctor Pages Consolidated CSS -->
    <link href="/app/Views/layouts/sec-style.css?v=<?= filemtime(__DIR__ . '/sec-style.css') ?>" rel="stylesheet">

    <!-- Prevent flash of wrong theme -->
    <script>
        (function() {
            const theme = localStorage.getItem('appTheme') || localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            }
            document.documentElement.classList.add('theme-loaded');
        })();
    </script>

    <style>
        /* Secretary specific styles - keep only what's not in sec-style.css */
        .secretary-badge {
            background: linear-gradient(135deg, var(--accent), var(--info));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        /* View As Banner Styles */
        .view-as-banner {
            animation: pulse 2s infinite;
            border: 3px solid #ffc107;
        }

        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
            50% { box-shadow: 0 4px 25px rgba(255, 193, 7, 0.6); }
            100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
        }

        .view-as-indicator {
            position: relative;
        }

        .view-as-indicator::before {
            content: "⚠️";
            position: absolute;
            top: -5px;
            left: -5px;
            font-size: 16px;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="clinic-logo">
                <img id="clinicLogo" src="/assets/images/Light.png" alt="HClinic / عيادة رؤية" style="width: 32px; height: 32px; margin-left: 0.75rem;">
                <div class="clinic-name">HClinic / عيادة رؤية</div>
            </div>
        </div>
        
        <div class="user-info">
            <div class="d-flex align-items-center">
                <div class="user-avatar">
                    <?= strtoupper(substr(($this->getCurrentUser()['name'] ?? 'س'), 0, 1)) ?>
                </div>
                <div class="user-details">
                    <h6><?= htmlspecialchars(($this->getCurrentUser()['name'] ?? 'المستخدم')) ?></h6>
                    <small class="secretary-badge">سكرتارية</small>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-item">
                <a href="/secretary/dashboard" class="nav-link <?= $this->isActiveRoute('/secretary/dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    لوحة التحكم
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/bookings" class="nav-link <?= $this->isActiveRoute('/secretary/bookings') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i>
                    الحجوزات
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/payments" class="nav-link <?= $this->isActiveRoute('/secretary/payments') ? 'active' : '' ?>">
                    <i class="bi bi-credit-card"></i>
                    المدفوعات
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/patients" class="nav-link <?= $this->isActiveRoute('/secretary/patients') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    المرضى
                </a>
            </div>
            <div class="nav-item">
                <a href="/secretary/profile" class="nav-link <?= $this->isActiveRoute('/secretary/profile') ? 'active' : '' ?>">
                    <i class="bi bi-person-circle"></i>
                    الملف الشخصي
                </a>
            </div>
            
            <?php 
            // Check if admin is in View As mode using session directly
            if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
            ?>
            <div class="nav-item mt-auto">
                <a href="/admin/stop-view-as" class="nav-link text-warning" style="font-weight: bold; background-color: rgba(255, 193, 7, 0.1);">
                    <i class="fas fa-arrow-left"></i>
                    الخروج من وضع المعاينة
                </a>
            </div>
            <?php endif; ?>
            
            <div class="nav-item mt-auto">
                <a href="/logout" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    تسجيل الخروج
                </a>
            </div>
            
            <!-- Version info -->
            <div class="sidebar-footer p-3 text-center border-top">
                <small class="text-muted">
                    <div class="mb-1">
                        HClinic / Roaya Clinic v7.0.0
                    </div>
                    <div>© 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none" style="color: var(--accent);">Ahmed Helal</a></div>
                </small>
            </div>
        </nav>
    </div>
    
    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>
    
    <!-- Main Content -->
    <div class="main-content">
        <?php 
        // View As Banner - Very visible indicator
        if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
        ?>
        <div class="view-as-banner" style="background: linear-gradient(135deg, #ffc107, #ff8f00); color: #000; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); text-align: center; position: relative;">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-0" style="font-weight: bold;">
                            <i class="fas fa-eye me-2"></i>
                            وضع المعاينة نشط - أنت تشاهد كـ: <strong><?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></strong>
                        </h4>
                        <small>أنت تشاهد حالياً واجهة <?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></small>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/stop-view-as" class="btn btn-dark btn-lg" style="font-weight: bold; padding: 10px 20px;">
                            <i class="fas fa-arrow-left me-2"></i>
                            الخروج من وضع المعاينة
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="top-bar">
            <button class="btn sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            
            <div class="page-title">
                <h1>
                    <?= $pageTitle ?? 'لوحة التحكم' ?>
                    <?php if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6em; animation: pulse 2s infinite;">
                            معاينة: <?= strtoupper($_SESSION['view_as_role'] ?? 'غير معروف') ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <small><?= $pageSubtitle ?? 'مرحباً بك في لوحة تحكم السكرتارية' ?></small>
            </div>
            
            <div class="top-actions">
                <?php 
                // Check if admin is in View As mode using session directly
                if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
                ?>
                    <div class="view-as-indicator me-3">
                        <div class="alert alert-warning d-flex align-items-center mb-0 py-2 px-3" style="font-size: 0.9rem; border: 2px solid #ffc107;">
                            <i class="fas fa-eye me-2"></i>
                            <span><strong>وضع المعاينة:</strong> <?= ucfirst($_SESSION['view_as_role'] ?? 'غير معروف') ?></span>
                            <a href="/admin/stop-view-as" class="btn btn-sm btn-outline-warning ms-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                خروج
                            </a>
                        </div>
                    </div>
                    
                    <!-- Additional Exit Button -->
                    <a href="/admin/stop-view-as" class="btn btn-warning me-2" title="الخروج من وضع المعاينة" style="font-weight: bold;">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        الخروج من المعاينة
                    </a>
                <?php endif; ?>
                
                <!-- Sun/Moon Theme Toggle Switch -->
                <label class="switch" title="تبديل المظهر">
                    <input type="checkbox" id="themeToggleInput">
                    <span class="slider round">
                        <span class="sun-moon">
                            <svg id="moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-1" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-2" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-3" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                        </span>
                        <svg class="cloud-light" id="cloud-1" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                        <svg class="cloud-dark" id="cloud-2" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                        <svg class="cloud-light" id="cloud-3" viewBox="0 0 100 100">
                            <path d="M50,92C78,92,100,71,100,47C100,23,78,2,50,2C22,2,0,23,0,47C0,71,22,92,50,92Z"></path>
                        </svg>
                    </span>
                </label>
            </div>
        </div>

        <!-- Page Content -->
        <?= $content ?>
    </div>    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="العودة للأعلى">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme toggle functionality - synced with main layout using appTheme
        function updateThemeUI(theme) {
            const isDark = theme === 'dark';
            document.documentElement.classList.toggle('dark', isDark);

            // Update logo
            const logo = document.getElementById('clinicLogo');
            if (logo) {
                logo.src = isDark ? '/assets/images/Dark.png' : '/assets/images/Light.png';
            }

            // Favicon is now static (faicon.ico) - no need to update

            // Update checkbox state
            const themeCheckbox = document.getElementById('themeToggleInput');
            if (themeCheckbox) {
                themeCheckbox.checked = isDark;
            }
        }
        
        // Function to save theme to database and localStorage
        async function saveThemeToDatabase(theme) {
            // Save to localStorage immediately (synchronous, no delay)
            localStorage.setItem('appTheme', theme);
            localStorage.setItem('theme', theme);
            
            try {
                const response = await fetch('/api/doctor/settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        theme: theme
                    })
                });
                
                if (!response.ok) {
                    console.error('Failed to save theme to database');
                }
            } catch (error) {
                console.error('Error saving theme to database:', error);
            }
        }
        
        // Function to load theme from database
        async function loadThemeFromDatabase() {
            try {
                const response = await fetch('/api/doctor/settings', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.settings && data.settings.theme) {
                        return data.settings.theme;
                    }
                }
            } catch (error) {
                console.error('Error loading theme from database:', error);
            }
            return null;
        }
        
        // Initialize theme - Priority to localStorage, sync with database
        (async function() {
            // Read theme from localStorage first (fast, synchronous)
            let savedTheme = localStorage.getItem('appTheme') || localStorage.getItem('theme');
            
            // Validate theme value
            if (savedTheme !== 'light' && savedTheme !== 'dark') {
                savedTheme = null;
            }
            
            // Load from database if no valid theme in localStorage
            let dbTheme = null;
            if (!savedTheme) {
                dbTheme = await loadThemeFromDatabase();
                if (dbTheme === 'light' || dbTheme === 'dark') {
                    savedTheme = dbTheme;
                    // Save to localStorage for next time
                    localStorage.setItem('appTheme', savedTheme);
                    localStorage.setItem('theme', savedTheme);
                }
            } else {
                // We have a theme in localStorage, check database in background
                dbTheme = await loadThemeFromDatabase();
            }
            
            // If still no theme, default to 'dark'
            if (!savedTheme || (savedTheme !== 'light' && savedTheme !== 'dark')) {
                savedTheme = 'dark';
                localStorage.setItem('appTheme', savedTheme);
                localStorage.setItem('theme', savedTheme);
            }
            
            // If localStorage theme differs from database, update database to match localStorage
            if (savedTheme && dbTheme && dbTheme !== savedTheme) {
                // localStorage has priority - save it to database
                await saveThemeToDatabase(savedTheme);
            } else if (savedTheme && !dbTheme) {
                // No theme in database but we have one in localStorage - save it
                await saveThemeToDatabase(savedTheme);
            }
            
            // Apply initial theme
            updateThemeUI(savedTheme);
            
            // Mark theme as loaded to remove flash prevention
            document.documentElement.classList.add('theme-loaded');
        })();

        // Theme toggle switch handler
        const themeCheckbox = document.getElementById('themeToggleInput');
        if (themeCheckbox) {
            themeCheckbox.addEventListener('change', async function() {
                const next = this.checked ? 'dark' : 'light';
                updateThemeUI(next);
                // Save to both keys for compatibility with main layout
                localStorage.setItem('appTheme', next);
                localStorage.setItem('theme', next);
                // Save to database
                await saveThemeToDatabase(next);
            });
        }

        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        
        // Ensure elements exist before adding event listeners
        if (sidebarToggle && sidebar && overlay) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
            
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
            
            // Close sidebar on window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            });
        } else {
            console.error('Sidebar toggle elements not found');
        }
        
        // Top-bar scroll effect
        const topBar = document.querySelector('.top-bar');
        if (topBar) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 0) {
                    topBar.classList.add('scrolled');
                } else {
                    topBar.classList.remove('scrolled');
                }
            });
        }
        
        // Scroll to Top Button
        const scrollToTopBtn = document.getElementById('scrollToTop');
        
        if (scrollToTopBtn) {
            // Show/hide button based on scroll position
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    scrollToTopBtn.classList.add('show');
                } else {
                    scrollToTopBtn.classList.remove('show');
                }
            });
            
            // Scroll to top when button is clicked
            scrollToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Make modals draggable globally
        function initializeDraggableModals() {
            const modals = document.querySelectorAll('.modal');
            
            modals.forEach(modal => {
                // Skip alertModal - it has its own draggable implementation
                if (modal.id === 'alertModal') {
                    return;
                }
                const modalDialog = modal.querySelector('.modal-dialog');
                if (!modalDialog) return;
                
                let isDragging = false;
                let currentX;
                let currentY;
                let initialX;
                let initialY;
                let xOffset = 0;
                let yOffset = 0;
                
                // Make modal header the drag handle
                const modalHeader = modal.querySelector('.modal-header');
                if (!modalHeader) return;
                
                modalHeader.style.cursor = 'move';
                
                modalHeader.addEventListener('mousedown', dragStart);
                
                function dragStart(e) {
                    // Don't drag if clicking on buttons or inputs
                    if (e.target.tagName === 'BUTTON' || e.target.tagName === 'INPUT' || e.target.closest('button') || e.target.closest('input') || e.target.closest('.btn-close')) {
                        return;
                    }
                    
                    // Only start dragging if clicking on header (not on title text)
                    if (e.target === modalHeader || (modalHeader.contains(e.target) && e.target.tagName !== 'H5' && !e.target.closest('h5'))) {
                        // Get current transform values
                        const transform = modalDialog.style.transform;
                        if (transform) {
                            const match = transform.match(/translate\(([^,]+)px,\s*([^)]+)px\)/);
                            if (match) {
                                xOffset = parseFloat(match[1]) || 0;
                                yOffset = parseFloat(match[2]) || 0;
                            }
                        }
                        
                        initialX = e.clientX - xOffset;
                        initialY = e.clientY - yOffset;
                        
                        // Store initial mouse position to detect if it's a drag or click
                        const startX = e.clientX;
                        const startY = e.clientY;
                        
                        // Set a flag to track if mouse moved
                        let hasMoved = false;
                        
                        function checkMove(moveEvent) {
                            const deltaX = Math.abs(moveEvent.clientX - startX);
                            const deltaY = Math.abs(moveEvent.clientY - startY);
                            if (deltaX > 5 || deltaY > 5) {
                                hasMoved = true;
                                isDragging = true;
                                modalDialog.style.transition = 'none';
                                moveEvent.preventDefault();
                                moveEvent.stopPropagation();
                            }
                        }
                        
                        function handleMove(moveEvent) {
                            if (hasMoved) {
                                drag(moveEvent);
                            } else {
                                checkMove(moveEvent);
                            }
                        }
                        
                        function handleEnd(endEvent) {
                            if (!hasMoved) {
                                // It was just a click, allow normal behavior
                                document.removeEventListener('mousemove', handleMove);
                                document.removeEventListener('mouseup', handleEnd);
                                return;
                            }
                            dragEnd(endEvent);
                            document.removeEventListener('mousemove', handleMove);
                            document.removeEventListener('mouseup', handleEnd);
                        }
                        
                        document.addEventListener('mousemove', handleMove);
                        document.addEventListener('mouseup', handleEnd);
                    }
                }
                
                function drag(e) {
                    if (isDragging) {
                        e.preventDefault();
                        e.stopPropagation(); // Prevent modal from closing
                        currentX = e.clientX - initialX;
                        currentY = e.clientY - initialY;
                        
                        xOffset = currentX;
                        yOffset = currentY;
                        
                        setTranslate(currentX, currentY, modalDialog);
                    }
                }
                
                function dragEnd(e) {
                    initialX = currentX;
                    initialY = currentY;
                    isDragging = false;
                    modalDialog.style.transition = '';
                }
                
                function setTranslate(xPos, yPos, el) {
                    // Get viewport dimensions
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    
                    // Get modal dimensions
                    const modalRect = el.getBoundingClientRect();
                    const modalWidth = modalRect.width;
                    const modalHeight = modalRect.height;
                    
                    // Get the original position (center of viewport)
                    const originalLeft = (viewportWidth - modalWidth) / 2;
                    const originalTop = 50; // Keep at least 50px from top
                    
                    // Calculate boundaries relative to original position
                    // Allow movement within viewport bounds
                    const minX = -(originalLeft - 20); // Allow 20px from left edge
                    const maxX = viewportWidth - modalWidth - originalLeft + 20; // Allow 20px from right edge
                    const minY = -(originalTop - 20); // Allow 20px from top
                    const maxY = viewportHeight - modalHeight - originalTop - 20; // Allow 20px from bottom
                    
                    // Constrain movement
                    const constrainedX = Math.max(minX, Math.min(maxX, xPos));
                    const constrainedY = Math.max(minY, Math.min(maxY, yPos));
                    
                    el.style.transform = `translate(${constrainedX}px, ${constrainedY}px)`;
                }
                
                // Reset position when modal is hidden
                modal.addEventListener('hidden.bs.modal', function() {
                    xOffset = 0;
                    yOffset = 0;
                    modalDialog.style.transform = '';
                });
            });
        }
        
        // Initialize draggable modals when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeDraggableModals);
        } else {
            initializeDraggableModals();
        }
        
        // Re-initialize draggable modals when new modals are added dynamically
        const modalObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && (node.classList && node.classList.contains('modal'))) {
                        // Small delay to ensure modal is fully initialized
                        setTimeout(initializeDraggableModals, 100);
                    }
                });
            });
        });
        
        modalObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    </script>
    
    <!-- Session Expiry Warning Modal -->
    <div class="modal fade" id="sessionExpiryModal" tabindex="-1" aria-labelledby="sessionExpiryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="sessionExpiryModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Session Expiring Soon
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <p class="mb-3">Your session will expire due to inactivity in:</p>
                    <div class="session-countdown mb-3">
                        <span id="sessionCountdown" class="display-4 fw-bold text-danger">30</span>
                        <span class="fs-5 text-muted ms-2">seconds</span>
                    </div>
                    <p class="text-muted">Click "Stay Logged In" to extend your session.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="stayLoggedInBtn">
                        <i class="bi bi-check-circle me-2"></i>
                        Stay Logged In
                    </button>
                    <button type="button" class="btn btn-secondary" id="logoutNowBtn">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout Now
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Session Expiry Warning Script -->
    <script>
        (function() {
            let sessionCheckInterval;
            let countdownInterval;
            let warningShown = false;
            const warningThreshold = 30; // Show warning 30 seconds before expiry
            const checkInterval = 5000; // Check every 5 seconds
            
            function checkSessionTime() {
                fetch('/api/auth/session-time')
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            // Session expired or not authenticated
                            clearIntervals();
                            window.location.href = '/login';
                            return;
                        }
                        
                        const remaining = Math.floor(data.remaining); // Ensure integer
                        
                        // Show warning modal if remaining time is less than threshold and not already shown
                        if (remaining <= warningThreshold && remaining > 0 && !warningShown) {
                            showWarningModal(remaining);
                        }
                        
                        // If session expired, redirect to login
                        if (remaining <= 0) {
                            clearIntervals();
                            window.location.href = '/login?expired=1';
                        }
                    })
                    .catch(error => {
                        console.error('Error checking session time:', error);
                    });
            }
            
            function showWarningModal(remainingSeconds) {
                warningShown = true;
                const modal = new bootstrap.Modal(document.getElementById('sessionExpiryModal'));
                const countdownElement = document.getElementById('sessionCountdown');
                let countdown = Math.min(remainingSeconds, warningThreshold);
                
                // Update countdown display
                countdownElement.textContent = countdown;
                
                // Start countdown
                countdownInterval = setInterval(() => {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        modal.hide();
                        window.location.href = '/login?expired=1';
                    } else {
                        countdownElement.textContent = countdown;
                    }
                }, 1000);
                
                // Show modal
                modal.show();
                
                // Handle "Stay Logged In" button
                document.getElementById('stayLoggedInBtn').addEventListener('click', function() {
                    clearInterval(countdownInterval);
                    modal.hide();
                    warningShown = false;
                    
                    // Make a request to refresh session
                    fetch('/api/auth/session-time')
                        .then(() => {
                            // Session refreshed, continue checking
                        })
                        .catch(error => {
                            console.error('Error refreshing session:', error);
                        });
                }, { once: true });
                
                // Handle "Logout Now" button
                document.getElementById('logoutNowBtn').addEventListener('click', function() {
                    clearInterval(countdownInterval);
                    modal.hide();
                    window.location.href = '/logout';
                }, { once: true });
            }
            
            function clearIntervals() {
                if (sessionCheckInterval) {
                    clearInterval(sessionCheckInterval);
                }
                if (countdownInterval) {
                    clearInterval(countdownInterval);
                }
            }
            
            // Start checking session time
            sessionCheckInterval = setInterval(checkSessionTime, checkInterval);
            
            // Initial check
            checkSessionTime();
            
            // Reset warning flag when user interacts with page
            let activityTimeout;
            ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
                document.addEventListener(event, function() {
                    clearTimeout(activityTimeout);
                    activityTimeout = setTimeout(() => {
                        warningShown = false;
                    }, 1000);
                });
            });
        })();
    </script>
</body>
</html>
