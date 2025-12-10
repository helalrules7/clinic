<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HClinic / عيادة رؤية - السكرتارية' ?></title>
    
    <!-- Favicons -->
    <link id="favicon" rel="icon" type="image/x-icon" href="/assets/fav/Light.ico">
    <link id="favicon-dark" rel="icon" type="image/x-icon" href="/assets/fav/Dark.ico" media="(prefers-color-scheme: dark)">
    
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

            // Update favicon
            const favicon = document.getElementById('favicon');
            if (favicon) {
                favicon.href = isDark ? '/assets/fav/Dark.ico' : '/assets/fav/Light.ico';
            }

            // Update checkbox state
            const themeCheckbox = document.getElementById('themeToggleInput');
            if (themeCheckbox) {
                themeCheckbox.checked = isDark;
            }
        }

        // Get saved theme - check both keys for compatibility
        const saved = localStorage.getItem('appTheme') || localStorage.getItem('theme') ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

        // Apply initial theme
        updateThemeUI(saved);

        // Theme toggle switch handler
        const themeCheckbox = document.getElementById('themeToggleInput');
        if (themeCheckbox) {
            themeCheckbox.addEventListener('change', function() {
                const next = this.checked ? 'dark' : 'light';
                updateThemeUI(next);
                // Save to both keys for compatibility with main layout
                localStorage.setItem('appTheme', next);
                localStorage.setItem('theme', next);
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
    </script>
</body>
</html>
