<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HClinic / Roaya Clinic' ?></title>
    
    <!-- Meta Description -->
    <meta name="description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'HClinic / Roaya Clinic') ?>">
    <meta property="og:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta property="og:image" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?>/assets/images/Light.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    <meta property="og:locale" content="ar_EG">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?><?= $_SERVER['REQUEST_URI'] ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($title ?? 'HClinic / Roaya Clinic') ?>">
    <meta name="twitter:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta name="twitter:image" content="<?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ?>://<?= $_SERVER['HTTP_HOST'] ?>/assets/images/Light.png">
    
    <!-- Favicons -->
    <link id="favicon" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Light.png">
    <link id="favicon-dark" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Dark.png" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Light.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/Light.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/assets/images/Light.png">
    
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
    <link href="/app/Views/layouts/style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>" rel="stylesheet">
    
    <!-- Theme initialization script - Must run before CSS to prevent flash -->
    <script>
        (function() {
            // Read theme from localStorage immediately (before page renders)
            const savedTheme = localStorage.getItem('appTheme');
            
            if (savedTheme === 'light' || savedTheme === 'dark') {
                // Apply theme immediately
                document.documentElement.classList.toggle('dark', savedTheme === 'dark');
                document.documentElement.classList.add('theme-loaded');
            } else {
                // Default to dark if no saved theme
                document.documentElement.classList.add('dark');
                document.documentElement.classList.add('theme-loaded');
            }
        })();
    </script>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="clinic-logo">
                <img id="clinicLogo" src="/assets/images/Light.png" alt="HClinic / Roaya Clinic" style="width: 32px; height: 32px; margin-right: 0.75rem;">
                <div class="clinic-name">HClinic / Roaya</div>
            </div>
        </div>
        
        <div class="user-info">
            <div class="d-flex flex-column align-items-center text-center">
                <div class="user-avatar mb-2" id="sidebarUserAvatar">
                    <?php 
                    $currentUser = $this->getCurrentUser();
                    if (!empty($currentUser['profile_image'])): 
                        $profileImagePath = strpos($currentUser['profile_image'], '/public/') === 0 ? $currentUser['profile_image'] : '/public' . $currentUser['profile_image'];
                    ?>
                        <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                             alt="Profile" 
                             class="user-avatar-img"
                             data-profile-image="<?= htmlspecialchars($profileImagePath) ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="user-avatar-fallback" style="display: none;">
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="htooltip user-avatar-htooltip">
                            <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                                 alt="Profile" 
                                 class="user-avatar-preview-image">
                        </span>
                    <?php else: ?>
                        <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <a href="/doctor/profile" class="user-name-link">
                        <h6 class="mb-1"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></h6>
                    </a>
                    <small><?= ucfirst($currentUser['role'] ?? 'user') ?></small>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <?php if ($this->getCurrentUser()['role'] === 'doctor'): ?>
                <div class="nav-item">
                    <a href="/doctor/dashboard" class="nav-link <?= $this->isActiveRoute('/doctor/dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/calendar" class="nav-link <?= $this->isActiveRoute('/doctor/calendar') ? 'active' : '' ?>">
                        <i class="bi bi-calendar3"></i>
                        Calendar
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/organizer" class="nav-link <?= $this->isActiveRoute('/doctor/organizer') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-month"></i>
                        Organizer
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/patients" class="nav-link <?= $this->isActiveRoute('/doctor/patients') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Patients
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/forum" class="nav-link <?= $this->isActiveRoute('/doctor/forum') ? 'active' : '' ?>">
                        <i class="bi bi-chat-dots"></i>
                        Discussions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/drugs" class="nav-link <?= $this->isActiveRoute('/doctor/drugs') ? 'active' : '' ?>">
                        <i class="bi bi-capsule"></i>
                        Drugs Database
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/payments" class="nav-link <?= $this->isActiveRoute('/doctor/payments') ? 'active' : '' ?>">
                        <i class="bi bi-credit-card"></i>
                        Financial Management
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/reports" class="nav-link <?= $this->isActiveRoute('/doctor/reports') ? 'active' : '' ?>">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </div>
                <div class="nav-item has-submenu <?= ($this->isActiveRoute('/doctor/glasses') || $this->isActiveRoute('/doctor/medications')) ? 'active' : '' ?>">
                    <a href="#" class="nav-link nav-link-toggle">
                        <i class="bi bi-archive"></i>
                        Medical Storage
                        <i class="bi bi-chevron-down submenu-arrow"></i>
                    </a>
                    <div class="nav-submenu">
                        <a href="/doctor/medications" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/medications') ? 'active' : '' ?>">
                            <i class="bi bi-capsule"></i>
                            Medical Prescriptions
                        </a>
                        <a href="/doctor/glasses" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/glasses') ? 'active' : '' ?>">
                            <i class="bi bi-eyeglasses"></i>
                            Glasses Prescriptions
                        </a>
                        <a href="/doctor/media" class="nav-submenu-link <?= $this->isActiveRoute('/doctor/media') ? 'active' : '' ?>">
                            <i class="bi bi-images"></i>
                            Patients Media
                        </a>
                    </div>
                </div>
                <div class="nav-item">
                    <a href="/doctor/alerts" class="nav-link <?= $this->isActiveRoute('/doctor/alerts') ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i>
                        Alerts
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/notes" class="nav-link <?= $this->isActiveRoute('/doctor/notes') ? 'active' : '' ?>">
                        <i class="bi bi-sticky"></i>
                        Notes
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/settings" class="nav-link <?= $this->isActiveRoute('/doctor/settings') ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/profile" class="nav-link <?= $this->isActiveRoute('/doctor/profile') ? 'active' : '' ?>">
                        <i class="bi bi-person-circle"></i>
                        Profile
                    </a>
                </div>
            <?php elseif ($this->getCurrentUser()['role'] === 'secretary'): ?>
                <div class="nav-item">
                    <a href="/secretary/dashboard" class="nav-link <?= $this->isActiveRoute('/secretary/dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/bookings" class="nav-link <?= $this->isActiveRoute('/secretary/bookings') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-check"></i>
                        Bookings
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/payments" class="nav-link <?= $this->isActiveRoute('/secretary/payments') ? 'active' : '' ?>">
                        <i class="bi bi-credit-card"></i>
                        Payments
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/secretary/patients" class="nav-link <?= $this->isActiveRoute('/secretary/patients') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Patients
                    </a>
                </div>
            <?php elseif ($this->getCurrentUser()['role'] === 'admin'): ?>
                <div class="nav-item">
                    <a href="/admin/dashboard" class="nav-link <?= $this->isActiveRoute('/admin/dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/users" class="nav-link <?= $this->isActiveRoute('/admin/users') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Users
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/reports" class="nav-link <?= $this->isActiveRoute('/admin/reports') ? 'active' : '' ?>">
                        <i class="bi bi-graph-up"></i>
                        Reports
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/media" class="nav-link <?= $this->isActiveRoute('/doctor/media') ? 'active' : '' ?>">
                        <i class="bi bi-images"></i>
                        Media
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/glasses" class="nav-link <?= $this->isActiveRoute('/doctor/glasses') ? 'active' : '' ?>">
                        <i class="bi bi-eyeglasses"></i>
                        Glasses Prescriptions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/medications" class="nav-link <?= $this->isActiveRoute('/doctor/medications') ? 'active' : '' ?>">
                        <i class="bi bi-capsule"></i>
                        Prescriptions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/notifications" class="nav-link <?= $this->isActiveRoute('/admin/notifications') ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i>
                        System Notifications
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/admin/settings" class="nav-link <?= $this->isActiveRoute('/admin/settings') ? 'active' : '' ?>">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="nav-item mt-4">
                <a href="/about" class="nav-link <?= $this->isActiveRoute('/about') ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i>
                    About
                </a>
            </div>
            
            <?php 
            // Check if admin is in View As mode using session directly
            if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
            ?>
            <div class="nav-item mt-auto">
                <a href="/admin/stop-view-as" class="nav-link text-warning" style="font-weight: bold; background-color: rgba(255, 193, 7, 0.1);">
                    <i class="fas fa-arrow-left"></i>
                    Exit View As
                </a>
            </div>
            <?php endif; ?>
            
            <div class="nav-item mt-auto">
                <a href="/logout" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Logout
                </a>
            </div>
            
            <!-- Version info -->
            <div class="sidebar-footer p-3 text-center border-top">
                <small class="sidebar-footer-text">
                    <div class="mb-1">
                        HClinic / Roaya Clinic v6.1
                        <a href="/whats-new" class="text-decoration-none ms-2 whats-new-link">What's New?</a>
                        <a href="/whats-new/full-features" class="text-decoration-none ms-2 sidebar-footer-link">Full Features</a>
                    </div>
                    <div>© 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none sidebar-footer-link">Ahmed Helal</a></div>
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
                            VIEW AS MODE ACTIVE - You are viewing as: <strong><?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?></strong>
                        </h4>
                        <small>You are currently previewing the <?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?> interface</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/stop-view-as" class="btn btn-dark btn-lg" style="font-weight: bold; padding: 10px 20px;">
                            <i class="fas fa-arrow-left me-2"></i>
                            EXIT VIEW AS
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
                    <?= $pageTitle ?? 'Dashboard' ?>
                    <?php if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): ?>
                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.6em; animation: pulse 2s infinite;">
                            VIEW AS: <?= strtoupper($_SESSION['view_as_role'] ?? 'Unknown') ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <small><?= $pageSubtitle ?? 'Welcome to your dashboard' ?></small>
            </div>
            
            <div class="top-actions">
                <?php 
                // Check if admin is in View As mode using session directly
                if (isset($_SESSION['view_as_mode']) && $_SESSION['view_as_mode'] === true): 
                ?>
                    <div class="view-as-indicator me-3">
                        <div class="alert alert-warning d-flex align-items-center mb-0 py-2 px-3" style="font-size: 0.9rem; border: 2px solid #ffc107;">
                            <i class="fas fa-eye me-2"></i>
                            <span><strong>VIEW AS MODE:</strong> <?= ucfirst($_SESSION['view_as_role'] ?? 'Unknown') ?></span>
                            <a href="/admin/stop-view-as" class="btn btn-sm btn-outline-warning ms-2">
                                <i class="fas fa-arrow-left me-1"></i>
                                Exit
                            </a>
                        </div>
                    </div>
                    
                    <!-- Additional Exit Button -->
                    <a href="/admin/stop-view-as" class="btn btn-warning me-2" title="Exit View As Mode" style="font-weight: bold;">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Exit View As
                    </a>
                <?php endif; ?>
                
                <!-- Notifications Icon -->
                <button class="btn btn-outline-secondary notifications-toggle" id="notificationsToggle" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notifications-badge" id="notificationsBadge" style="display: none;">0</span>
                </button>
                
                <label class="switch" for="themeToggleInput">
                    <input id="themeToggleInput" type="checkbox" />
                    <div class="slider round">
                        <div class="sun-moon">
                            <svg id="moon-dot-1" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-2" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="moon-dot-3" class="moon-dot" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-1" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-2" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="light-ray-3" class="light-ray" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-1" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-2" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-3" class="cloud-dark" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-4" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-5" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                            <svg id="cloud-6" class="cloud-light" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50"></circle>
                            </svg>
                        </div>
                        <div class="stars">
                            <svg id="star-1" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-2" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-3" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                            <svg id="star-4" class="star" viewBox="0 0 20 20">
                                <path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path>
                            </svg>
                        </div>
                    </div>
                </label>
            </div>
        </div>
        
        <!-- Page Content -->
        <?= $content ?>
    </div>
    
    <!-- Forum Toast Container (Top Right) -->
    <div id="forumToastContainer"></div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>
    
    <!-- Notifications Panel -->
    <div class="notifications-panel-overlay" id="notificationsOverlay"></div>
    <div class="notifications-panel" id="notificationsPanel">
        <div class="notifications-panel-content">
            <div class="notifications-panel-header">
                <h5><i class="bi bi-bell me-2"></i>Notifications</h5>
                <div class="notifications-panel-header-actions">
                    <button class="btn btn-sm btn-outline-secondary" id="markAllReadBtn" title="Mark All Read">
                        <i class="bi bi-check-all"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="clearAllBtn" title="Clear All">
                        <i class="bi bi-trash"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" id="closeNotificationsBtn" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="notifications-panel-body" id="notificationsBody">
                <div class="notifications-empty">
                    <i class="bi bi-bell-slash"></i>
                    <p>No notifications</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Access Dock (Desktop Only) -->
    <div class="quick-access-dock" id="quickAccessDock">
        <div class="dock-container">
            <a href="/doctor/calendar" class="dock-item" title="View Calendar">
                <i class="bi bi-calendar3"></i>
                <span class="htooltip">View Calendar</span>
            </a>
            <a href="/doctor/organizer" class="dock-item" title="Organizer">
                <i class="bi bi-calendar-month"></i>
                <span class="htooltip">Organizer</span>
            </a>
            <a href="/doctor/patients" class="dock-item" title="Patient List">
                <i class="bi bi-people"></i>
                <span class="htooltip">Patient List</span>
            </a>
            <a href="/doctor/drugs" class="dock-item" title="Drugs">
                <i class="bi bi-capsule"></i>
                <span class="htooltip">Drugs</span>
            </a>
            <a href="/doctor/notes" class="dock-item" title="Notes">
                <i class="bi bi-sticky"></i>
                <span class="htooltip">Notes</span>
            </a>
            <a href="/doctor/forum" class="dock-item" title="Doctor Forum">
                <i class="bi bi-chat-dots"></i>
                <span class="htooltip">Doctor Forum</span>
            </a>
            <a href="/doctor/payments" class="dock-item" title="Financial">
                <i class="bi bi-credit-card"></i>
                <span class="htooltip">Financial</span>
            </a>
            <div class="dock-item dock-item-stack" id="medicalStorageDockItem" title="Medical Storage">
                <i class="bi bi-archive"></i>
                <span class="htooltip">Medical Storage</span>
                <div class="dock-stack-menu" id="medicalStorageStackMenu">
                    <a href="/doctor/medications" class="dock-stack-item">
                        <i class="bi bi-prescription"></i>
                        <span>Medical Prescriptions</span>
                    </a>
                    <a href="/doctor/glasses" class="dock-stack-item">
                        <i class="bi bi-eyeglasses"></i>
                        <span>Glasses Prescriptions</span>
                    </a>
                    <a href="/doctor/media" class="dock-stack-item">
                        <i class="bi bi-images"></i>
                        <span>Media</span>
                    </a>
                </div>
            </div>
            <a href="/doctor/alerts" class="dock-item" title="Alerts">
                <i class="bi bi-bell"></i>
                <span class="htooltip">Alerts</span>
            </a>
            <a href="/doctor/reports" class="dock-item" title="Reports">
                <i class="bi bi-graph-up"></i>
                <span class="htooltip">Reports</span>
            </a>
            <a href="/doctor/profile" class="dock-item" title="My Profile">
                <?php 
                $currentUser = $this->getCurrentUser();
                if (!empty($currentUser['profile_image'])): 
                    $profileImagePath = strpos($currentUser['profile_image'], '/public/') === 0 ? $currentUser['profile_image'] : '/public' . $currentUser['profile_image'];
                ?>
                    <img src="<?= htmlspecialchars($profileImagePath) ?>" 
                         alt="My Profile" 
                         class="dock-profile-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <i class="bi bi-person-circle dock-profile-fallback" style="display: none;"></i>
                <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                <?php endif; ?>
                <span class="htooltip">My Profile</span>
            </a>
            <a href="/doctor/settings" class="dock-item" title="Settings">
                <i class="bi bi-gear"></i>
                <span class="htooltip">Settings</span>
            </a>
            <div class="dock-divider"></div>
            <button class="dock-minimize-btn" id="dockMinimizeBtn" title="Minimize Dock">
                <i class="bi bi-fullscreen-exit"></i>
                <div class="minimized-icon">
                    <!-- Row 1: 3 squares (columns 1, 2, 3) -->
                    <span class="minimized-icon-rect rect-r1-c1"></span>
                    <span class="minimized-icon-rect rect-r1-c2"></span>
                    <span class="minimized-icon-rect rect-r1-c3"></span>
                    <!-- Row 2: 4 squares (columns 1, 2, 3, 4) -->
                    <span class="minimized-icon-rect rect-r2-c1"></span>
                    <span class="minimized-icon-rect rect-r2-c2"></span>
                    <span class="minimized-icon-rect rect-r2-c3"></span>
                    <span class="minimized-icon-rect rect-r2-c4"></span>
                    <!-- Row 3: 3 squares starting from column 2 (columns 2, 3, 4) -->
                    <span class="minimized-icon-rect rect-r3-c2"></span>
                    <span class="minimized-icon-rect rect-r3-c3"></span>
                    <span class="minimized-icon-rect rect-r3-c4"></span>
                </div>
                <span class="htooltip" id="dockMinimizeTooltip">Minimize Dock</span>
            </button>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme toggle functionality
        const apply = mode => document.documentElement.classList.toggle('dark', mode === 'dark');
        
        // Function to update UI elements based on theme
        function updateThemeUI(theme) {
            // Update checkbox state
            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.checked = theme === 'dark';
            }
            
            // Update logo
            const logo = document.getElementById('clinicLogo');
            if (logo) {
                logo.src = theme === 'dark' ? '/assets/images/Dark.png' : '/assets/images/Light.png';
            }
            
            // Update favicon
            const favicon = document.getElementById('favicon');
            if (favicon) {
                favicon.href = theme === 'dark' ? '/assets/fav/Dark.ico' : '/assets/fav/Light.ico';
            }
        }
        
        // Function to save theme to database and localStorage
        async function saveThemeToDatabase(theme) {
            // Save to localStorage immediately (synchronous, no delay)
            localStorage.setItem('appTheme', theme);
            
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
        
        // Initialize theme - Load from localStorage first, then sync with database
        (async function() {
            // Read theme from localStorage first (fast, synchronous)
            let savedTheme = localStorage.getItem('appTheme');
            
            // If no theme in localStorage, load from database
            if (!savedTheme || (savedTheme !== 'light' && savedTheme !== 'dark')) {
                savedTheme = await loadThemeFromDatabase();
                
                // If no theme in database, default to 'dark'
                if (!savedTheme) {
                    savedTheme = 'dark';
                    // Save default theme to database and localStorage
                    await saveThemeToDatabase(savedTheme);
                } else {
                    // Save to localStorage for next time
                    localStorage.setItem('appTheme', savedTheme);
                }
            }
            
            // Apply theme (should already be applied by inline script, but ensure it's correct)
            apply(savedTheme);
            
            // Update UI elements
            updateThemeUI(savedTheme);
            
            // Mark theme as loaded to remove flash prevention
            document.documentElement.classList.add('theme-loaded');
            
            // Sync with database in background (in case localStorage and database are out of sync)
            loadThemeFromDatabase().then(dbTheme => {
                if (dbTheme && dbTheme !== savedTheme) {
                    // Database has different theme, update localStorage and apply
                    localStorage.setItem('appTheme', dbTheme);
                    apply(dbTheme);
                    updateThemeUI(dbTheme);
                }
            });
            
            // Theme toggle checkbox change handler
            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.addEventListener('change', async function() {
                    const nextTheme = this.checked ? 'dark' : 'light';
                    
                    // Apply theme immediately
                    apply(nextTheme);
                    
                    // Update UI elements
                    updateThemeUI(nextTheme);
                    
                    // Save to localStorage and database
                    await saveThemeToDatabase(nextTheme);
                });
            }
        })();
        
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
        
        // Submenu toggle functionality
        (function() {
            const submenuToggles = document.querySelectorAll('.nav-link-toggle');
            
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const navItem = this.closest('.nav-item.has-submenu');
                    if (navItem) {
                        navItem.classList.toggle('expanded');
                    }
                });
            });
            
            // Auto-expand if any submenu item is active
            const activeSubmenuItems = document.querySelectorAll('.nav-submenu-link.active');
            activeSubmenuItems.forEach(item => {
                const navItem = item.closest('.nav-item.has-submenu');
                if (navItem) {
                    navItem.classList.add('expanded');
                }
            });
        })();
        
        // Dock Stack Menu functionality (macOS-style genie effect)
        (function() {
            const medicalStorageDockItem = document.getElementById('medicalStorageDockItem');
            const medicalStorageStackMenu = document.getElementById('medicalStorageStackMenu');
            
            if (medicalStorageDockItem && medicalStorageStackMenu) {
                // Toggle stack menu on click
                medicalStorageDockItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.toggle('active');
                });
                
                // Close stack menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!medicalStorageDockItem.contains(e.target)) {
                        medicalStorageDockItem.classList.remove('active');
                    }
                });
                
                // Close stack menu when clicking on a stack item
                const stackItems = medicalStorageStackMenu.querySelectorAll('.dock-stack-item');
                stackItems.forEach(item => {
                    item.addEventListener('click', function() {
                        medicalStorageDockItem.classList.remove('active');
                    });
                });
                
                // Prevent closing when clicking inside the stack menu
                medicalStorageStackMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        })();
        
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
        const mobileDock = document.getElementById('quickAccessDock');
        
        function updateMobileDockPosition() {
            if (!mobileDock || window.innerWidth > 768) return;
            
            if (scrollToTopBtn && scrollToTopBtn.classList.contains('show')) {
                // Back to top button is visible - move dock above it by 10px
                mobileDock.classList.add('dock-above-button');
            } else {
                // Back to top button is hidden - use its exact position
                mobileDock.classList.remove('dock-above-button');
            }
        }
        
        // Load and apply personal preferences
        async function loadAndApplyPersonalPreferences() {
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
                    if (data.success && data.settings) {
                        const settings = data.settings;
                        
                        // 1. Back to Top Display
                        if (scrollToTopBtn) {
                            const backToTopEnabled = settings.back_to_top_display !== false; // Default true
                            if (!backToTopEnabled) {
                                scrollToTopBtn.style.display = 'none';
                            } else {
                                scrollToTopBtn.style.display = '';
                                // Show/hide button based on scroll position
                                window.addEventListener('scroll', () => {
                                    if (window.pageYOffset > 300) {
                                        scrollToTopBtn.classList.add('show');
                                    } else {
                                        scrollToTopBtn.classList.remove('show');
                                    }
                                    // Update mobile dock position
                                    updateMobileDockPosition();
                                });
                                
                                // Scroll to top when button is clicked
                                scrollToTopBtn.addEventListener('click', () => {
                                    window.scrollTo({
                                        top: 0,
                                        behavior: 'smooth'
                                    });
                                });
                            }
                            // Initial position update
                            updateMobileDockPosition();
                        }
                        
                        // 2. Desktop Dock & Mobile Dock
                        const dock = document.getElementById('quickAccessDock');
                        if (dock) {
                            const dockEnabled = settings.desktop_dock_enabled !== false; // Default true
                            const mobileDockEnabled = settings.mobile_dock_enabled !== false; // Default true
                            
                            function updateDockVisibility() {
                                const isMobile = window.innerWidth <= 768;
                                
                                if (isMobile) {
                                    // Mobile: check mobile_dock_enabled
                                    // Control visibility using mobile-minimized class
                                    if (mobileDockEnabled !== false) {
                                        dock.style.display = '';
                                        // Ensure mobile-minimized class is present for mobile
                                        if (!dock.classList.contains('mobile-minimized') && !dock.classList.contains('mobile-expanded')) {
                                            dock.classList.add('mobile-minimized');
                                        }
                                    } else {
                                        dock.style.display = 'none';
                                    }
                                } else {
                                    // Desktop: check desktop_dock_enabled
                                    dock.style.display = dockEnabled !== false ? '' : 'none';
                                    // Remove mobile classes on desktop
                                    dock.classList.remove('mobile-minimized', 'mobile-expanded');
                                }
                            }
                            
                            // Initial update
                            updateDockVisibility();
                            
                            // Listen for window resize to update dock visibility
                            let resizeTimeout;
                            window.addEventListener('resize', function() {
                                clearTimeout(resizeTimeout);
                                resizeTimeout = setTimeout(updateDockVisibility, 100);
                            });
                        }
                        
                        // 3. Sidebar Items
                        applySidebarItems(settings.sidebar_items_enabled);
                    }
                }
            } catch (error) {
                console.error('Error loading personal preferences:', error);
            }
        }
        
        // Apply sidebar items visibility
        function applySidebarItems(enabledItems) {
            if (!enabledItems) {
                // Default: all items enabled
                return;
            }
            
            // Parse if string
            if (typeof enabledItems === 'string') {
                enabledItems = JSON.parse(enabledItems);
            }
            
            // Define sidebar item mappings
            const sidebarMappings = {
                'dashboard': '/doctor/dashboard',
                'calendar': '/doctor/calendar',
                'patients': '/doctor/patients',
                'organizer': '/doctor/organizer',
                'drugs': '/doctor/drugs',
                'payments': '/doctor/payments',
                'reports': '/doctor/reports',
                'media': '/doctor/media',
                'glasses': '/doctor/glasses',
                'medications': '/doctor/medications',
                'alerts': '/doctor/alerts',
                'notes': '/doctor/notes',
                'forum': '/doctor/forum',
                'settings': '/doctor/settings',
                'profile': '/doctor/profile',
                'about': '/about',
                'logout': '/logout'
            };
            
            // Hide/show sidebar items
            Object.keys(sidebarMappings).forEach(key => {
                const url = sidebarMappings[key];
                const navItems = document.querySelectorAll(`.nav-menu .nav-item a[href="${url}"]`);
                navItems.forEach(item => {
                    const navItem = item.closest('.nav-item');
                    if (navItem) {
                        if (enabledItems.includes(key)) {
                            navItem.style.display = '';
                        } else {
                            navItem.style.display = 'none';
                        }
                    }
                });
            });
        }
        
        // Callback for settings page to apply preferences
        window.applyPersonalPreferencesCallback = function(preferences) {
            if (preferences.back_to_top_display !== undefined) {
                const scrollToTopBtn = document.getElementById('scrollToTop');
                if (scrollToTopBtn) {
                    scrollToTopBtn.style.display = preferences.back_to_top_display !== false ? '' : 'none';
                }
            }
            
            if (preferences.desktop_dock_enabled !== undefined) {
                const dock = document.getElementById('quickAccessDock');
                if (dock) {
                    dock.style.display = preferences.desktop_dock_enabled !== false ? '' : 'none';
                }
            }
            
            if (preferences.sidebar_items_enabled) {
                applySidebarItems(preferences.sidebar_items_enabled);
            }
        };
        
        // Load preferences on page load
        loadAndApplyPersonalPreferences();
        
        // Alert System - Real-time notification system like chat
        (function() {
            let alertCheckInterval = null;
            let isChecking = false;
            let shownAlertIds = new Set(); // Track shown alerts in current session
            const POLLING_INTERVAL = 10000; // Check every 10 seconds (like chat system)
            const MIN_CHECK_INTERVAL = 2000; // Minimum 2 seconds between checks
            
            // Create toast container immediately
            function createToastContainer() {
                const container = document.getElementById('toastContainer');
                if (container) return container;
                
                const newContainer = document.createElement('div');
                newContainer.id = 'toastContainer';
                newContainer.className = 'toast-container position-fixed bottom-0 start-50 translate-middle-x p-3';
                newContainer.style.zIndex = '9999';
                document.body.appendChild(newContainer);
                return newContainer;
            }
            
            // Initialize toast container on page load
            createToastContainer();
            
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            function checkAlerts() {
                // Prevent multiple simultaneous checks
                if (isChecking) return;
                
                // Throttle checks - don't check too frequently
                const now = Date.now();
                if (now - (window.lastAlertCheckTime || 0) < MIN_CHECK_INTERVAL) {
                    return;
                }
                
                isChecking = true;
                window.lastAlertCheckTime = now;
                
                // Get current date and time for accurate checking
                const currentDate = new Date().toISOString().split('T')[0];
                const currentTime = new Date().toTimeString().split(' ')[0].substring(0, 5); // HH:mm format
                
                fetch(`/api/alerts/active?date=${currentDate}&time=${currentTime}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    cache: 'no-cache',
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.alerts && data.alerts.length > 0) {
                        // Show only new alerts that haven't been shown yet
                        data.alerts.forEach(alert => {
                            // Create unique key for this alert instance
                            const alertKey = `${alert.id}_${alert.alert_date}_${alert.alert_time}`;
                            
                            // Check if this exact alert instance was already shown
                            if (!shownAlertIds.has(alertKey)) {
                                playNotificationSound(); // Play sound for new alert
                                showAlertToast(alert);
                                shownAlertIds.add(alertKey);
                            }
                        });
                    }
                })
                .catch(error => {
                    // Silently fail - don't show errors for alert checking
                    console.debug('Alert check failed:', error);
                })
                .finally(() => {
                    isChecking = false;
                });
            }
            
            function showAlertToast(alert) {
                if (!alert || !alert.id) return;
                
                const toastContainer = createToastContainer();
                const uniqueId = `${alert.id}_${alert.alert_date}_${alert.alert_time}_${Date.now()}`;
                const toastId = 'alert-toast-' + uniqueId;
                
                // Check if toast already exists (prevent duplicates)
                if (document.getElementById(toastId)) {
                    return;
                }
                
                // Check if there's already a toast for this alert (by checking all existing toasts)
                const existingToasts = toastContainer.querySelectorAll('.toast[data-alert-unique-id]');
                let alreadyShown = false;
                existingToasts.forEach(existingToast => {
                    const existingAlertId = existingToast.getAttribute('data-alert-id');
                    const existingDate = existingToast.getAttribute('data-alert-date');
                    const existingTime = existingToast.getAttribute('data-alert-time');
                    if (existingAlertId == alert.id && existingDate == alert.alert_date && existingTime == alert.alert_time) {
                        alreadyShown = true;
                    }
                });
                
                if (alreadyShown) {
                    return;
                }
                
                const patientName = alert.patient_first_name && alert.patient_last_name 
                    ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                    : 'Patient';
                const patientLink = alert.patient_id ? `/doctor/patients/${alert.patient_id}` : '#';
                
                const toastHtml = `
                    <div id="${toastId}" class="toast alert-toast-glass align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false" data-alert-id="${alert.id}" data-alert-date="${alert.alert_date}" data-alert-time="${alert.alert_time}" data-alert-unique-id="${uniqueId}" style="min-width: 550px; max-width: 700px;">
                        <div class="d-flex align-items-center">
                            <div class="toast-body flex-grow-1">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem; margin-top: 2px;"></i>
                                    <div class="flex-grow-1">
                                        <div class="alert-message-content">${alert.message}</div>
                                        ${alert.patient_id ? `<br><small><i class="bi bi-person me-1"></i>${escapeHtml(patientName)}</small>` : ''}
                                        ${alert.alert_date && alert.alert_time ? `<br><small><i class="bi bi-clock me-1"></i>${escapeHtml(alert.alert_date)} ${escapeHtml(alert.alert_time)}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 me-2" style="flex-shrink: 0;">
                                ${alert.patient_id ? `
                                    <a href="${patientLink}" class="btn btn-sm btn-light alert-toast-btn" data-alert-id="${alert.id}" data-toast-id="${toastId}" style="white-space: nowrap;">
                                        <i class="bi bi-person me-1"></i>View Patient
                                    </a>
                                ` : ''}
                                <button type="button" class="btn btn-sm alert-toast-btn snooze-btn" data-alert-id="${alert.id}" data-toast-id="${toastId}" style="white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i>Snooze
                                </button>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close" data-alert-id="${alert.id}" data-toast-id="${toastId}"></button>
                            </div>
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    // Add event listeners for buttons
                    const viewPatientBtn = toastElement.querySelector('a[data-alert-id]');
                    const snoozeBtn = toastElement.querySelector('.snooze-btn');
                    const closeBtn = toastElement.querySelector('.btn-close');
                    
                    if (viewPatientBtn) {
                        viewPatientBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                            // Navigate to patient page after dismissing
                            setTimeout(() => {
                                window.location.href = patientLink;
                            }, 300);
                        });
                    }
                    
                    if (snoozeBtn) {
                        snoozeBtn.addEventListener('click', function() {
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                        });
                    }
                    
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            const alertId = parseInt(this.getAttribute('data-alert-id'));
                            const toastId = this.getAttribute('data-toast-id');
                            dismissAlert(alertId, toastId);
                        });
                    }
                    
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: false,
                        delay: 0
                    });
                    toast.show();
                    
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                    
                    // Send push notification if enabled
                    if (window.sendPushNotification) {
                        window.sendPushNotification(alert).catch(error => {
                            console.debug('Push notification failed:', error);
                        });
                    }
                }
            }
            
            function dismissAlert(alertId, toastId) {
                if (!alertId || !toastId) return;
                
                fetch('/api/alerts/dismiss', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ alert_id: alertId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const toastElement = document.getElementById(toastId);
                        if (toastElement) {
                            const toast = bootstrap.Toast.getInstance(toastElement);
                            if (toast) {
                                toast.hide();
                            } else {
                                toastElement.remove();
                            }
                        }
                    }
                })
                .catch(error => {
                    // Silently fail
                    console.debug('Dismiss alert failed:', error);
                    // Still try to hide the toast
                    const toastElement = document.getElementById(toastId);
                    if (toastElement) {
                        const toast = bootstrap.Toast.getInstance(toastElement);
                        if (toast) {
                            toast.hide();
                        } else {
                            toastElement.remove();
                        }
                    }
                });
            }
            
            // Make dismissAlert available globally
            window.dismissAlert = dismissAlert;
            
            // Start polling system - like real-time chat
            function startAlertPolling() {
                // Clear any existing interval
                if (alertCheckInterval) {
                    clearInterval(alertCheckInterval);
                }
                
                // Check immediately
                checkAlerts();
                
                // Set up continuous polling (like chat system)
                alertCheckInterval = setInterval(() => {
                    checkAlerts();
                }, POLLING_INTERVAL);
            }
            
            // Initialize polling system
            function initAlertSystem() {
                // Start polling immediately
                startAlertPolling();
                
                // Also check when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        setTimeout(() => {
                            checkAlerts();
                        }, 500);
                    });
                } else {
                    // DOM already ready, check immediately
                    setTimeout(() => {
                        checkAlerts();
                    }, 500);
                }
                
                // Check when page becomes visible (user switches tabs back)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        // Reset check time to allow immediate check
                        window.lastAlertCheckTime = 0;
                        setTimeout(() => {
                            checkAlerts();
                        }, 300);
                    }
                });
                
                // Check on window focus
                window.addEventListener('focus', function() {
                    window.lastAlertCheckTime = 0;
                    setTimeout(() => {
                        checkAlerts();
                    }, 300);
                });
                
                // Check when user interacts with page (like chat systems)
                ['click', 'keydown', 'mousemove'].forEach(eventType => {
                    document.addEventListener(eventType, function() {
                        // Reset check time occasionally to allow checks
                        if (Math.random() < 0.1) { // 10% chance
                            window.lastAlertCheckTime = 0;
                        }
                    }, { passive: true });
                });
                
                // Clean up on page unload
                window.addEventListener('beforeunload', function() {
                    if (alertCheckInterval) {
                        clearInterval(alertCheckInterval);
                    }
                });
            }
            
            // Start the alert system immediately
            initAlertSystem();
            
            // Make checkAlerts available globally for manual triggering
            window.checkAlerts = checkAlerts;
        })();
        
        // Push Notifications System
        (function() {
            let pushSubscription = null;
            let isPushEnabled = false;
            
            // Check if browser supports Push Notifications
            function isPushSupported() {
                return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
            }
            
            // Register Service Worker
            async function registerServiceWorker() {
                if (!isPushSupported()) {
                    return null;
                }
                
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js');
                    console.log('Service Worker registered:', registration);
                    return registration;
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                    return null;
                }
            }
            
            // Request notification permission
            async function requestNotificationPermission() {
                if (!('Notification' in window)) {
                    return 'denied';
                }
                
                if (Notification.permission === 'granted') {
                    return 'granted';
                }
                
                const permission = await Notification.requestPermission();
                return permission;
            }
            
            // Subscribe to Push Notifications
            async function subscribeToPush(registration) {
                try {
                    const subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(getVapidPublicKey())
                    });
                    
                    pushSubscription = subscription;
                    await savePushSubscription(subscription);
                    return subscription;
                } catch (error) {
                    console.error('Push subscription failed:', error);
                    return null;
                }
            }
            
            // Convert VAPID key from base64 URL to Uint8Array
            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');
                
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }
            
            // Get VAPID public key
            // To generate VAPID keys, use one of these methods:
            // 1. PHP script: php generate_vapid_keys.php (in project root)
            // 2. Node.js: npx web-push generate-vapid-keys
            // 3. Online tool: https://tools.reactpwa.com/vapid
            // 4. Python: pip install py-vapid && python -c "from py_vapid import Vapid01; v=Vapid01(); v.generate_keys(); print(v.public_key)"
            function getVapidPublicKey() {
                // VAPID Public Key - Generated using: php generate_vapid_keys.php
                return 'BM81HP8k4re4ObeiBgk2BSdC3FDx5Ke8-XbtPF_RbsEF5M6SC0OyHcygclxzQbPeiY8re_q6Hco16kLvol-4ozg';
            }
            
            // Save push subscription to database (supports multiple browsers)
            async function savePushSubscription(subscription) {
                try {
                    // Get current subscriptions array
                    const settings = await loadPushSettings();
                    let subscriptionsArray = [];
                    
                    // If there are existing subscriptions, load them
                    if (settings.subscription) {
                        // Check if it's an array or single subscription (for backward compatibility)
                        if (Array.isArray(settings.subscription)) {
                            subscriptionsArray = settings.subscription;
                        } else {
                            // Convert single subscription to array for backward compatibility
                            subscriptionsArray = [settings.subscription];
                        }
                    }
                    
                    // Check if this subscription already exists (by endpoint)
                    const subscriptionEndpoint = subscription.endpoint;
                    const existingIndex = subscriptionsArray.findIndex(sub => {
                        const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                        return subEndpoint === subscriptionEndpoint;
                    });
                    
                    // Convert subscription to object if needed
                    const subscriptionObj = typeof subscription === 'string' ? JSON.parse(subscription) : subscription;
                    
                    if (existingIndex >= 0) {
                        // Update existing subscription
                        subscriptionsArray[existingIndex] = subscriptionObj;
                    } else {
                        // Add new subscription
                        subscriptionsArray.push(subscriptionObj);
                    }
                    
                    // Save updated subscriptions array
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            push_notifications_enabled: true,
                            push_subscription: subscriptionsArray
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            isPushEnabled = true;
                            pushSubscription = subscriptionObj;
                            return true;
                        }
                    }
                    return false;
                } catch (error) {
                    console.error('Failed to save push subscription:', error);
                    return false;
                }
            }
            
            // Get browser identifier (unique for each browser)
            function getBrowserIdentifier() {
                // Use user agent + origin as browser identifier
                return navigator.userAgent + '|' + window.location.origin;
            }
            
            // Load push notification settings (supports multiple browsers)
            async function loadPushSettings() {
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
                        if (data.success && data.settings) {
                            isPushEnabled = data.settings.push_notifications_enabled === true || 
                                          data.settings.push_notifications_enabled === '1' ||
                                          data.settings.push_notifications_enabled === 1;
                            
                            let subscriptionData = null;
                            if (data.settings.push_subscription) {
                                // Parse subscription data
                                subscriptionData = typeof data.settings.push_subscription === 'string' 
                                    ? JSON.parse(data.settings.push_subscription)
                                    : data.settings.push_subscription;
                                
                                // Handle backward compatibility: if it's a single subscription, convert to array
                                if (!Array.isArray(subscriptionData) && subscriptionData.endpoint) {
                                    subscriptionData = [subscriptionData];
                                }
                            }
                            
                            // Get browsers that declined push notifications
                            let declinedBrowsers = [];
                            if (data.settings.dont_ask_push_notifications_browsers) {
                                declinedBrowsers = typeof data.settings.dont_ask_push_notifications_browsers === 'string' 
                                    ? JSON.parse(data.settings.dont_ask_push_notifications_browsers)
                                    : data.settings.dont_ask_push_notifications_browsers;
                                
                                // Handle backward compatibility: if it's not an array, convert to array
                                if (!Array.isArray(declinedBrowsers)) {
                                    declinedBrowsers = [];
                                }
                            }
                            
                            // Get remind later timestamp
                            let remindLaterTimestamp = null;
                            if (data.settings.push_notification_remind_later) {
                                remindLaterTimestamp = parseInt(data.settings.push_notification_remind_later) || null;
                            }
                            
                            // Check if current browser is in declined list
                            const currentBrowserId = getBrowserIdentifier();
                            const isDeclined = declinedBrowsers.includes(currentBrowserId);
                            
                            // Check if remind later is still active (24 hours)
                            const now = Date.now();
                            const oneDayInMs = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
                            const shouldRemind = !remindLaterTimestamp || (now - remindLaterTimestamp) >= oneDayInMs;
                            
                            return { 
                                enabled: isPushEnabled, 
                                subscription: subscriptionData, 
                                declinedBrowsers: declinedBrowsers, 
                                isDeclined: isDeclined,
                                remindLaterTimestamp: remindLaterTimestamp,
                                shouldRemind: shouldRemind
                            };
                        }
                    }
                } catch (error) {
                    console.error('Failed to load push settings:', error);
                }
                return { enabled: false, subscription: null, declinedBrowsers: [], isDeclined: false, remindLaterTimestamp: null, shouldRemind: true };
            }
            
            // Compare two subscriptions to check if they're the same
            function compareSubscriptions(sub1, sub2) {
                if (!sub1 || !sub2) return false;
                
                // Compare endpoint (unique identifier for each browser/device)
                const endpoint1 = sub1.endpoint || '';
                const endpoint2 = sub2.endpoint || '';
                
                return endpoint1 === endpoint2;
            }
            
            // Get current browser subscription
            async function getCurrentBrowserSubscription(registration) {
                try {
                    const subscription = await registration.pushManager.getSubscription();
                    return subscription;
                } catch (error) {
                    console.error('Failed to get current subscription:', error);
                    return null;
                }
            }
            
            // Find subscription in array by endpoint
            function findSubscriptionByEndpoint(subscriptionsArray, endpoint) {
                if (!subscriptionsArray || !Array.isArray(subscriptionsArray)) {
                    return null;
                }
                
                for (let i = 0; i < subscriptionsArray.length; i++) {
                    const sub = subscriptionsArray[i];
                    const subEndpoint = typeof sub === 'string' ? JSON.parse(sub).endpoint : sub.endpoint;
                    if (subEndpoint === endpoint) {
                        return typeof sub === 'string' ? JSON.parse(sub) : sub;
                    }
                }
                
                return null;
            }
            
            // Check if current browser has valid subscription (supports multiple browsers)
            async function checkBrowserSubscription(registration) {
                try {
                    // Get current browser's subscription
                    const currentSubscription = await getCurrentBrowserSubscription(registration);
                    
                    if (!currentSubscription) {
                        // No subscription in this browser
                        return { isValid: false, needsSetup: true, reason: 'no_subscription' };
                    }
                    
                    // Load saved subscriptions from database
                    const settings = await loadPushSettings();
                    const savedSubscriptions = settings.subscription;
                    
                    // If push is enabled but no saved subscriptions, show toast
                    if (settings.enabled && (!savedSubscriptions || (Array.isArray(savedSubscriptions) && savedSubscriptions.length === 0))) {
                        return { isValid: false, needsSetup: true, reason: 'no_saved_subscriptions' };
                    }
                    
                    // If push is enabled, check if current subscription exists in saved subscriptions
                    if (settings.enabled && savedSubscriptions) {
                        const subscriptionsArray = Array.isArray(savedSubscriptions) ? savedSubscriptions : [savedSubscriptions];
                        const currentEndpoint = currentSubscription.endpoint;
                        const foundSubscription = findSubscriptionByEndpoint(subscriptionsArray, currentEndpoint);
                        
                        if (foundSubscription) {
                            // Current browser subscription found - all good
                            pushSubscription = currentSubscription;
                            return { isValid: true, needsSetup: false };
                        } else {
                            // Different browser/device - needs new subscription
                            return { isValid: false, needsSetup: true, reason: 'different_browser' };
                        }
                    }
                    
                    // If push is not enabled
                    if (!settings.enabled) {
                        return { isValid: false, needsSetup: true, reason: 'not_enabled' };
                    }
                    
                    return { isValid: true, needsSetup: false };
                } catch (error) {
                    console.error('Failed to check browser subscription:', error);
                    return { isValid: false, needsSetup: true };
                }
            }
            
            // Save "Remind me later" setting (24 hours)
            async function saveRemindLater() {
                try {
                    const timestamp = Date.now(); // Current timestamp
                    
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            push_notification_remind_later: timestamp
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        return data.success;
                    }
                    return false;
                } catch (error) {
                    console.error('Failed to save remind later setting:', error);
                    return false;
                }
            }
            
            // Save "Don't ask for this browser" setting
            async function saveDontAskForThisBrowser() {
                try {
                    // Get current browser identifier
                    const currentBrowserId = getBrowserIdentifier();
                    
                    // Load current settings
                    const settings = await loadPushSettings();
                    let declinedBrowsers = settings.declinedBrowsers || [];
                    
                    // Add current browser to declined list if not already there
                    if (!declinedBrowsers.includes(currentBrowserId)) {
                        declinedBrowsers.push(currentBrowserId);
                    }
                    
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dont_ask_push_notifications_browsers: declinedBrowsers
                        })
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        return data.success;
                    }
                    return false;
                } catch (error) {
                    console.error('Failed to save dont ask for this browser setting:', error);
                    return false;
                }
            }
            
            // Show toast to enable push notifications
            async function showPushNotificationToast() {
                // Check if current browser has declined push notifications
                const settings = await loadPushSettings();
                if (settings.isDeclined) {
                    return; // Don't show toast if this browser declined
                }
                
                // Check if remind later is still active (24 hours)
                if (!settings.shouldRemind) {
                    return; // Don't show toast if remind later is still active
                }
                
                // Create separate toast container in the center of screen for push notifications
                let pushToastContainer = document.getElementById('pushToastContainer');
                if (!pushToastContainer) {
                    pushToastContainer = document.createElement('div');
                    pushToastContainer.id = 'pushToastContainer';
                    pushToastContainer.className = 'toast-container';
                    document.body.appendChild(pushToastContainer);
                }
                const toastId = 'push-notification-toast';
                
                // Check if toast already exists
                if (document.getElementById(toastId)) {
                    return;
                }
                
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white push-notification-toast border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
                        <div class="toast-header push-toast-header">
                            <div class="d-flex align-items-center flex-grow-1">
                                <i class="bi bi-bell-fill me-2" style="font-size: 1.5rem;"></i>
                                <strong class="me-auto">Enable Push Notifications</strong>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            <div>Get notified with your alerts, notes, appointments and more even when the browser is closed<br>You can disable this in your browser settings.</div>
                        </div>
                        <div class="toast-footer push-toast-footer">
                            <div class="d-flex align-items-center gap-2 w-100 flex-wrap">
                                <button type="button" class="btn btn-sm btn-light" id="enablePushBtn">
                                    <i class="bi bi-check-circle me-1"></i>Enable
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="remindMeLaterBtn" style="font-size: 0.75rem; white-space: nowrap;">
                                    <i class="bi bi-clock me-1"></i>Remind me later
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-light" id="dontAskForThisBrowserBtn" style="font-size: 0.75rem; white-space: nowrap;">
                                    <i class="bi bi-x-circle me-1"></i>Don't ask for this browser
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                
                pushToastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    const enableBtn = toastElement.querySelector('#enablePushBtn');
                    const closeBtn = toastElement.querySelector('.btn-close');
                    const remindMeLaterBtn = toastElement.querySelector('#remindMeLaterBtn');
                    const dontAskForThisBrowserBtn = toastElement.querySelector('#dontAskForThisBrowserBtn');
                    
                    if (enableBtn) {
                        enableBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enabling...';
                            
                            const permission = await requestNotificationPermission();
                            
                            if (permission === 'granted') {
                                const registration = await navigator.serviceWorker.ready;
                                const subscription = await subscribeToPush(registration);
                                
                                if (subscription) {
                                    // Add hiding class for exit animation
                                    toastElement.classList.add('hiding');
                                    const toast = bootstrap.Toast.getInstance(toastElement);
                                    if (toast) {
                                        // Wait for animation to complete before hiding
                                        setTimeout(() => {
                                            toast.hide();
                                        }, 300);
                                    }
                                    showToast('success', 'Push Notifications Enabled', 'You will now receive notifications even when the browser is closed.');
                                } else {
                                    this.disabled = false;
                                    this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Enable';
                                    showToast('error', 'Error', 'Failed to enable push notifications. Please try again.');
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-check-circle me-1"></i>Enable';
                                showToast('error', 'Permission Denied', 'Please allow notifications in your browser settings.');
                            }
                        });
                    }
                    
                    if (remindMeLaterBtn) {
                        remindMeLaterBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                            
                            const saved = await saveRemindLater();
                            
                            if (saved) {
                                // Add hiding class for exit animation
                                toastElement.classList.add('hiding');
                                const toast = bootstrap.Toast.getInstance(toastElement);
                                if (toast) {
                                    // Wait for animation to complete before hiding
                                    setTimeout(() => {
                                        toast.hide();
                                    }, 300);
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-clock me-1"></i>Remind me later';
                                showToast('error', 'Error', 'Failed to save preference. Please try again.');
                            }
                        });
                    }
                    
                    if (dontAskForThisBrowserBtn) {
                        dontAskForThisBrowserBtn.addEventListener('click', async function() {
                            this.disabled = true;
                            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                            
                            const saved = await saveDontAskForThisBrowser();
                            
                            if (saved) {
                                // Add hiding class for exit animation
                                toastElement.classList.add('hiding');
                                const toast = bootstrap.Toast.getInstance(toastElement);
                                if (toast) {
                                    // Wait for animation to complete before hiding
                                    setTimeout(() => {
                                        toast.hide();
                                    }, 300);
                                }
                            } else {
                                this.disabled = false;
                                this.innerHTML = '<i class="bi bi-x-circle me-1"></i>Don\'t ask for this browser';
                                showToast('error', 'Error', 'Failed to save preference. Please try again.');
                            }
                        });
                    }
                    
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function() {
                            // Add hiding class for exit animation
                            toastElement.classList.add('hiding');
                            const toast = bootstrap.Toast.getInstance(toastElement);
                            if (toast) {
                                // Wait for animation to complete before hiding
                                setTimeout(() => {
                                    toast.hide();
                                }, 300);
                            }
                        });
                    }
                    
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: false,
                        delay: 0
                    });
                    toast.show();
                    
                    // Add exit animation when toast is being hidden
                    toastElement.addEventListener('hide.bs.toast', function() {
                        if (!toastElement.classList.contains('hiding')) {
                            toastElement.classList.add('hiding');
                        }
                    });
                    
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                }
            }
            
            // Send push notification using Service Worker
            // NOTE: This shows a local notification. For true push notifications from server,
            // you need to implement server-side push sending with VAPID keys
            async function sendPushNotification(alert) {
                if (!isPushEnabled) {
                    return false;
                }
                
                try {
                    // Get service worker registration
                    const registration = await navigator.serviceWorker.ready;
                    
                    // Prepare notification data
                    const patientName = alert.patient_first_name && alert.patient_last_name 
                        ? `${alert.patient_first_name} ${alert.patient_last_name}` 
                        : '';
                    const notificationTitle = 'New Alert';
                    let notificationBody = alert.message || 'You have a new alert';
                    if (patientName) {
                        notificationBody += ` - ${patientName}`;
                    }
                    
                    const notificationData = {
                        title: notificationTitle,
                        body: notificationBody,
                        icon: '/assets/images/Light.png',
                        badge: '/assets/images/Light.png',
                        tag: `alert-${alert.id}-${alert.alert_date}-${alert.alert_time}`,
                        requireInteraction: false,
                        data: {
                            alert_id: alert.id,
                            patient_id: alert.patient_id,
                            url: alert.patient_id ? `/doctor/patients/${alert.patient_id}` : '/doctor/alerts'
                        },
                        actions: alert.patient_id ? [
                            {
                                action: 'view',
                                title: 'View Patient'
                            },
                            {
                                action: 'dismiss',
                                title: 'Dismiss'
                            }
                        ] : [
                            {
                                action: 'view',
                                title: 'View Alerts'
                            }
                        ]
                    };
                    
                    // Show notification using Service Worker
                    // This works even when the page is in background or closed
                    await registration.showNotification(notificationTitle, notificationData);
                    
                    return true;
                } catch (error) {
                    console.error('Failed to send push notification:', error);
                    return false;
                }
            }
            
            // Initialize push notifications system
            async function initPushNotifications() {
                if (!isPushSupported()) {
                    return;
                }
                
                // Check if current browser has declined push notifications
                const settings = await loadPushSettings();
                if (settings.isDeclined) {
                    return; // Don't show toast if this browser declined
                }
                
                // Check if remind later is still active (24 hours)
                if (!settings.shouldRemind) {
                    return; // Don't show toast if remind later is still active
                }
                
                // Register service worker
                const registration = await registerServiceWorker();
                if (!registration) {
                    return;
                }
                
                // Check current browser's subscription
                const subscriptionCheck = await checkBrowserSubscription(registration);
                
                if (subscriptionCheck.needsSetup) {
                    // New browser or subscription mismatch - show toast to enable
                    // Wait a bit before showing toast to avoid overwhelming user
                    setTimeout(() => {
                        showPushNotificationToast();
                    }, 3000);
                } else if (subscriptionCheck.isValid) {
                    // Subscription is valid - pushSubscription is already set in checkBrowserSubscription
                    isPushEnabled = true;
                } else {
                    // Push not enabled - show toast
                    setTimeout(() => {
                        showPushNotificationToast();
                    }, 3000);
                }
            }
            
            // Initialize on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPushNotifications);
            } else {
                initPushNotifications();
            }
            
            // Make sendPushNotification available globally
            window.sendPushNotification = sendPushNotification;
        })();
        
        // Quick Access Dock - Show only on desktop and Minimize functionality
        (function() {
            const dock = document.getElementById('quickAccessDock');
            const minimizeBtn = document.getElementById('dockMinimizeBtn');
            
            function isMobile() {
                return window.innerWidth <= 768;
            }
            
            function initMobileDock() {
                if (!dock) return;
                
                // On mobile, dock is minimized by default
                if (isMobile()) {
                    dock.classList.add('mobile-minimized');
                    dock.classList.remove('mobile-expanded');
                    
                    // Add click handler to dock container when minimized
                    const dockContainer = dock.querySelector('.dock-container');
                    if (dockContainer) {
                        // Remove existing listeners by cloning
                        const newContainer = dockContainer.cloneNode(true);
                        dockContainer.parentNode.replaceChild(newContainer, dockContainer);
                        
                        newContainer.addEventListener('click', function(e) {
                            // Don't trigger if clicking on a dock item
                            if (e.target.closest('.dock-item')) {
                                return;
                            }
                            
                            // Toggle expanded/minimized
                            if (dock.classList.contains('mobile-minimized')) {
                                dock.classList.remove('mobile-minimized');
                                dock.classList.add('mobile-expanded');
                            } else {
                                dock.classList.remove('mobile-expanded');
                                dock.classList.add('mobile-minimized');
                            }
                        });
                    }
                    
                    // Close dock when clicking on a dock item
                    const dockItems = dock.querySelectorAll('.dock-item');
                    dockItems.forEach(item => {
                        item.addEventListener('click', function() {
                            setTimeout(() => {
                                dock.classList.remove('mobile-expanded');
                                dock.classList.add('mobile-minimized');
                            }, 300);
                        });
                    });
                    
                    // Close dock when clicking minimize button
                    if (minimizeBtn) {
                        minimizeBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            dock.classList.remove('mobile-expanded');
                            dock.classList.add('mobile-minimized');
                        });
                    }
                } else {
                    // Desktop: remove mobile classes
                    dock.classList.remove('mobile-minimized', 'mobile-expanded');
                }
            }
            
            function updateDockVisibility() {
                if (!dock) return;
                
                // Handle mobile dock (<= 768px)
                if (isMobile()) {
                    // Remove desktop minimized class if exists
                    dock.classList.remove('minimized');
                    initMobileDock();
                    return;
                }
                
                // Desktop: show dock and remove mobile classes (>= 769px)
                if (window.innerWidth >= 769) {
                    dock.style.display = 'block';
                    dock.classList.remove('mobile-minimized', 'mobile-expanded');
                    // Load desktop dock state
                    loadDockState();
                } else {
                    dock.style.display = 'none';
                }
            }
            
            // Load dock minimized state from doctor settings
            async function loadDockState() {
                if (!dock) return;
                
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
                        if (data.success && data.settings && data.settings.dock_minimized) {
                            const isMinimized = data.settings.dock_minimized === '1' || data.settings.dock_minimized === true || data.settings.dock_minimized === 1;
                            if (isMinimized) {
                                dock.classList.add('minimized');
                            } else {
                                dock.classList.remove('minimized');
                            }
                        } else {
                            // Default: not minimized
                            dock.classList.remove('minimized');
                        }
                    }
                    
                    // Update button title after loading state
                    updateMinimizeButtonTitle();
                } catch (error) {
                    console.error('Error loading dock state:', error);
                    // Default: not minimized on error
                    dock.classList.remove('minimized');
                    updateMinimizeButtonTitle();
                }
            }
            
            // Save dock minimized state to doctor settings
            async function saveDockState(isMinimized) {
                try {
                    const response = await fetch('/api/doctor/settings', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            dock_minimized: isMinimized ? '1' : '0'
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Failed to save dock state');
                    }
                    
                    const data = await response.json();
                    if (!data.success) {
                        throw new Error('Failed to save dock state');
                    }
                } catch (error) {
                    console.error('Error saving dock state:', error);
                }
            }
            
            // Update minimize button title based on state
            function updateMinimizeButtonTitle() {
                if (!minimizeBtn || !dock) return;
                const isMinimized = dock.classList.contains('minimized');
                minimizeBtn.setAttribute('title', isMinimized ? 'Maximize Dock' : 'Minimize Dock');
                const htooltip = document.getElementById('dockMinimizeTooltip');
                if (htooltip) {
                    htooltip.textContent = isMinimized ? 'Maximize Dock' : 'Minimize Dock';
                }
            }
            
            // Toggle dock minimized state
            function toggleDockMinimize() {
                if (!dock) return;
                
                const isMinimized = dock.classList.contains('minimized');
                if (isMinimized) {
                    dock.classList.remove('minimized');
                    saveDockState(false);
                } else {
                    dock.classList.add('minimized');
                    saveDockState(true);
                }
                
                // Update button title
                updateMinimizeButtonTitle();
            }
            
            // Initialize
            if (minimizeBtn) {
                minimizeBtn.addEventListener('click', function(e) {
                    // On mobile, handle differently
                    if (isMobile()) {
                        e.preventDefault();
                        e.stopPropagation();
                        dock.classList.remove('mobile-expanded');
                        dock.classList.add('mobile-minimized');
                    } else {
                        toggleDockMinimize();
                    }
                });
            }
            
            // Load state on page load - wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    if (!isMobile()) {
                        loadDockState();
                    }
                    updateDockVisibility();
                });
            } else {
                // DOM is already ready
                if (!isMobile()) {
                    loadDockState();
                }
                updateDockVisibility();
            }
            
            // Update visibility on resize
            window.addEventListener('resize', function() {
                updateDockVisibility();
            });
        })();
        
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
        
        // Notifications System
        (function() {
            const notificationsToggle = document.getElementById('notificationsToggle');
            const notificationsPanel = document.getElementById('notificationsPanel');
            const notificationsOverlay = document.getElementById('notificationsOverlay');
            const notificationsBody = document.getElementById('notificationsBody');
            const notificationsBadge = document.getElementById('notificationsBadge');
            const closeNotificationsBtn = document.getElementById('closeNotificationsBtn');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const clearAllBtn = document.getElementById('clearAllBtn');
            const viewAllNotificationsBtn = document.getElementById('viewAllNotificationsBtn');
            
            let notificationsPollingInterval = null;
            let isNotificationsOpen = false;
            
            // Calculate panel height dynamically
            function calculatePanelHeight() {
                if (!notificationsBody) return;
                
                const panelHeader = document.querySelector('.notifications-panel-header');
                const headerHeight = panelHeader ? panelHeader.offsetHeight : 80;
                const buttonHeight = 60; // Height for "View All" button
                const padding = 32; // Top and bottom padding
                const availableHeight = window.innerHeight - headerHeight - buttonHeight - padding;
                
                // Set max-height for notifications panel body
                notificationsBody.style.maxHeight = Math.max(300, availableHeight) + 'px';
            }
            
            // Toggle notifications panel
            function toggleNotifications() {
                isNotificationsOpen = !isNotificationsOpen;
                if (isNotificationsOpen) {
                    notificationsPanel.classList.add('show');
                    notificationsOverlay.classList.add('show');
                    // Calculate and set panel height dynamically
                    setTimeout(() => {
                        calculatePanelHeight();
                    }, 100);
                    loadNotifications();
                    startNotificationsPolling();
                } else {
                    notificationsPanel.classList.remove('show');
                    notificationsOverlay.classList.remove('show');
                    stopNotificationsPolling();
                }
            }
            
            // Close notifications
            function closeNotifications() {
                isNotificationsOpen = false;
                notificationsPanel.classList.remove('show');
                notificationsOverlay.classList.remove('show');
                stopNotificationsPolling();
            }
            
            // Recalculate on window resize
            window.addEventListener('resize', function() {
                if (isNotificationsOpen) {
                    calculatePanelHeight();
                }
            });
            
            // Event listeners
            if (notificationsToggle) {
                notificationsToggle.addEventListener('click', toggleNotifications);
            }
            
            if (closeNotificationsBtn) {
                closeNotificationsBtn.addEventListener('click', closeNotifications);
            }
            
            if (notificationsOverlay) {
                notificationsOverlay.addEventListener('click', function(e) {
                    // Only close if clicking directly on overlay, not on panel content
                    if (e.target === notificationsOverlay) {
                        closeNotifications();
                    }
                });
            }
            
            // Load notifications
            async function loadNotifications() {
                try {
                    const response = await fetch('/api/notifications?limit=50');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        return;
                    }
                    
                    if (data.success) {
                        const previousUnreadCount = parseInt(notificationsBadge?.textContent || '0');
                        const currentUnreadCount = data.unread_count || 0;
                        
                        // Play notification sound if new unread notifications
                        if (currentUnreadCount > previousUnreadCount) {
                            playNotificationSound();
                        }
                        
                        console.log('Notifications loaded:', data.notifications);
                        console.log('Unread count:', data.unread_count);
                        renderNotifications(data.notifications);
                        updateBadge(data.unread_count);
                    } else {
                        console.error('API error:', data.message);
                        if (data.error) {
                            console.error('Error details:', data.error);
                        }
                    }
                } catch (error) {
                    console.error('Error loading notifications:', error);
                }
            }
            
            // Update unread count badge
            async function updateUnreadCount() {
                try {
                    const response = await fetch('/api/notifications/unread-count');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        return;
                    }
                    
                    if (data.success) {
                        updateBadge(data.unread_count);
                    }
                } catch (error) {
                    console.error('Error updating unread count:', error);
                }
            }
            
            // Update badge
            function updateBadge(count) {
                const previousCount = parseInt(notificationsBadge?.textContent || '0');
                
                if (notificationsBadge) {
                    if (count > 0) {
                        notificationsBadge.textContent = count > 99 ? '99+' : count;
                        notificationsBadge.style.display = 'flex';
                        
                        // Play notification sound if new unread notifications
                        if (count > previousCount) {
                            playNotificationSound();
                        }
                    } else {
                        notificationsBadge.style.display = 'none';
                    }
                }
            }
            
            // Play notification sound
            function playNotificationSound() {
                try {
                    // Create a simple notification sound using Web Audio API
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    // Simple pleasant notification tone
                    oscillator.frequency.value = 800; // Hz
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                } catch (error) {
                    console.debug('Could not play notification sound:', error);
                }
            }
            
            // Render notifications - calculate view height and show limited notifications
            function renderNotifications(notifications) {
                if (!notificationsBody) return;
                
                if (!notifications || notifications.length === 0) {
                    notificationsBody.innerHTML = `
                        <div class="notifications-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                        <button class="view-all-notifications-btn" onclick="showAllNotifications()">
                            <i class="bi bi-list-ul me-2"></i>View All Notifications
                        </button>
                    `;
                    return;
                }
                
                // Sort by date (newest first)
                const sortedNotifications = [...notifications].sort((a, b) => {
                    return new Date(b.created_at) - new Date(a.created_at);
                });
                
                // Calculate available height dynamically
                const panelHeader = document.querySelector('.notifications-panel-header');
                const headerHeight = panelHeader ? panelHeader.offsetHeight : 80;
                const buttonHeight = 60; // Height for "View All" button
                const padding = 32; // Top and bottom padding
                const availableHeight = window.innerHeight - headerHeight - buttonHeight - padding;
                
                // Set max-height for notifications panel body if not already set
                if (notificationsBody && !notificationsBody.style.maxHeight) {
                    notificationsBody.style.maxHeight = Math.max(300, availableHeight) + 'px';
                }
                
                // Estimate notification height (average ~80px per notification)
                const notificationHeight = 80;
                const maxVisible = Math.max(3, Math.floor(availableHeight / notificationHeight));
                
                // Get visible notifications
                const visibleNotifications = sortedNotifications.slice(0, maxVisible);
                const hasMore = sortedNotifications.length > maxVisible;
                
                let html = '';
                
                // Render visible notifications
                visibleNotifications.forEach(notif => {
                    html += renderNotificationItem(notif);
                });
                
                // Always add "View All" button at the bottom
                html += `<button class="view-all-notifications-btn" onclick="showAllNotifications()">
                    <i class="bi bi-list-ul me-2"></i>View All Notifications${hasMore ? ` (${sortedNotifications.length - maxVisible} more)` : ''}
                </button>`;
                
                notificationsBody.innerHTML = html;
                
                // Add event listeners to close buttons (now delete)
                notificationsBody.querySelectorAll('.notification-item-close').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        const notificationId = this.getAttribute('data-notification-id');
                        if (notificationId) {
                            deleteNotification(notificationId);
                        } else {
                            console.error('Notification ID not found on close button');
                        }
                    });
                });
                
                // Add hover listeners for notification items
                notificationsBody.querySelectorAll('.notification-item').forEach(item => {
                    // Add click listener for appointment-related notifications
                    const appointmentId = item.getAttribute('data-appointment-id');
                    if (appointmentId) {
                        item.addEventListener('click', function(e) {
                            // Don't trigger if clicking on buttons
                            if (e.target.closest('.notification-item-close') || 
                                e.target.closest('.notification-item-patient')) {
                                return;
                            }
                            
                            // Navigate to appointment page
                            window.location.href = `/doctor/appointments/${appointmentId}`;
                        });
                    }
                    
                    // Add swipe gesture for mobile
                    let touchStartX = 0;
                    let touchStartY = 0;
                    let touchEndX = 0;
                    let touchEndY = 0;
                    
                    item.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                        touchStartY = e.changedTouches[0].screenY;
                    }, { passive: true });
                    
                    item.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        touchEndY = e.changedTouches[0].screenY;
                        handleSwipe(item);
                    }, { passive: true });
                    
                    function handleSwipe(element) {
                        const deltaX = touchEndX - touchStartX;
                        const deltaY = touchEndY - touchStartY;
                        
                        // Check if it's a horizontal swipe (more horizontal than vertical)
                        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                            // Swipe right to delete (left to right swipe)
                            if (deltaX > 50) {
                                const notificationId = element.getAttribute('data-notification-id');
                                if (notificationId) {
                                    deleteNotification(notificationId);
                                }
                            }
                        }
                    }
                });
                
                // Add click listener for patient names
                notificationsBody.querySelectorAll('.notification-item-patient[data-patient-id]').forEach(patientElement => {
                    patientElement.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const patientId = this.getAttribute('data-patient-id');
                        if (patientId) {
                            window.location.href = `/doctor/patients/${patientId}`;
                        }
                    });
                });
            }
            
            // Get notification icon based on type
            function getNotificationIcon(type) {
                const icons = {
                    'appointment': '📅',
                    'alert': '🔔',
                    'system': '⚙️',
                    'default': '📬'
                };
                // Check title for login/logout
                if (type === 'system') {
                    // Will be determined by title in renderNotificationItem
                    return '⚙️';
                }
                return icons[type] || icons['default'];
            }
            
            // Get icon from notification title/type
            function getIconFromNotification(notif) {
                const title = (notif.title || '').toLowerCase();
                if (title.includes('login')) return '🔓';
                if (title.includes('logout')) return '🔒';
                if (notif.type === 'appointment') return '📅';
                if (notif.type === 'alert') return '🔔';
                if (notif.type === 'system') return '⚙️';
                return '📬';
            }
            
            // Shorten date and time in message
            function shortenDateTime(message) {
                if (!message) return message;
                
                // Replace "on YYYY-MM-DD at HH:MM:SS" with "on YYYY-MM-DD HH:MM"
                message = message.replace(/on (\d{4}-\d{2}-\d{2}) at (\d{2}):(\d{2}):\d{2}/g, 'on $1 $2:$3');
                
                // Replace "on YYYY-MM-DD HH:MM" with shorter format "on MM/DD HH:MM"
                message = message.replace(/on (\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/g, function(match, year, month, day, hour, minute) {
                    return `on ${month}/${day} ${hour}:${minute}`;
                });
                
                return message;
            }
            
            // Render single notification item
            function renderNotificationItem(notif) {
                const timeAgo = getTimeAgo(notif.created_at);
                const icon = getIconFromNotification(notif);
                const patientInfo = notif.patient_first_name && notif.patient_last_name 
                    ? `<div class="notification-item-patient" ${notif.patient_id ? `data-patient-id="${notif.patient_id}" style="cursor: pointer;"` : ''}>
                        <i class="bi bi-person me-1"></i>${escapeHtml(notif.patient_first_name + ' ' + notif.patient_last_name)}
                    </div>`
                    : '';
                
                // Check if notification is related to an appointment
                const isAppointmentRelated = notif.related_type === 'appointment' && notif.related_id;
                const appointmentId = isAppointmentRelated ? notif.related_id : null;
                
                // Shorten message if it contains long date/time
                let message = escapeHtml(notif.message);
                message = shortenDateTime(message);
                
                return `
                    <div class="notification-item ${notif.is_read ? 'read' : 'unread'}" 
                         data-notification-id="${notif.id}"
                         ${isAppointmentRelated ? `data-appointment-id="${appointmentId}" style="cursor: pointer;"` : ''}
                         data-related-type="${notif.related_type || ''}"
                         data-related-id="${notif.related_id || ''}"
                         data-patient-id="${notif.patient_id || ''}">
                        <button class="notification-item-close" data-notification-id="${notif.id}" title="Delete notification">
                            <i class="bi bi-x"></i>
                        </button>
                        <div class="notification-icon">${icon}</div>
                        <div class="notification-body">
                            <div class="notification-item-header">
                                <h6 class="notification-item-title">${escapeHtml(notif.title)}</h6>
                                <span class="notification-item-time">${timeAgo}</span>
                            </div>
                            <p class="notification-item-message">${message}</p>
                            ${patientInfo}
                        </div>
                    </div>
                `;
            }
            
            // Delete notification
            async function deleteNotification(notificationId) {
                if (!notificationId) {
                    console.error('deleteNotification: notificationId is missing');
                    return;
                }
                
                try {
                    const response = await fetch(`/api/notifications/${notificationId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        return;
                    }
                    
                    if (data.success) {
                        // Remove notification from DOM
                        const notificationItem = notificationsBody.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notificationItem) {
                            notificationItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            notificationItem.style.opacity = '0';
                            notificationItem.style.transform = 'translateX(100%)';
                            setTimeout(() => {
                                notificationItem.remove();
                                // Recalculate panel height after deletion
                                calculatePanelHeight();
                                // Always reload notifications to ensure sync with database
                                setTimeout(() => {
                                    loadNotifications();
                                }, 100);
                            }, 300);
                        } else {
                            // If item not found in DOM, reload to sync
                            loadNotifications();
                        }
                        updateUnreadCount();
                    } else {
                        console.error('Error deleting notification: ' + (data.message || 'Unknown error'));
                        // Reload notifications to restore state
                        loadNotifications();
                    }
                } catch (error) {
                    console.error('Error deleting notification:', error);
                    // Reload notifications to restore state on error
                    loadNotifications();
                }
            }
            
            // Mark as read and hide
            async function markAsReadAndHide(notificationId) {
                try {
                    const response = await fetch(`/api/notifications/${notificationId}/read`, {
                        method: 'PUT'
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        // Remove notification from DOM
                        const notifElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        if (notifElement) {
                            notifElement.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            notifElement.style.opacity = '0';
                            notifElement.style.transform = 'translateX(20px)';
                            setTimeout(() => {
                                notifElement.remove();
                                // Recalculate panel height after deletion
                                calculatePanelHeight();
                                // Reload if empty
                                if (notificationsBody.children.length === 0) {
                                    loadNotifications();
                                }
                            }, 300);
                        }
                        updateUnreadCount();
                    }
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            }
            
            // Mark all as read
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function() {
                    try {
                        const response = await fetch('/api/notifications/read-all', {
                            method: 'PUT'
                        });
                        const data = await response.json();
                        
                        if (data.success) {
                            loadNotifications();
                            updateUnreadCount();
                        }
                    } catch (error) {
                        console.error('Error marking all as read:', error);
                    }
                });
            }
            
            // Clear all
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', async function() {
                    try {
                        // Get all notification items
                        const notificationItems = notificationsBody.querySelectorAll('.notification-item');
                        
                        if (notificationItems.length === 0) {
                            return;
                        }
                        
                        // First, delete from server (same way as deleteNotification)
                        const response = await fetch('/api/notifications/clear-all', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const text = await response.text();
                        let data;
                        
                        try {
                            data = JSON.parse(text);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            console.error('Response text:', text);
                            return;
                        }
                        
                        if (data.success) {
                            // Apply delete animation to all notifications
                            notificationItems.forEach((item, index) => {
                                setTimeout(() => {
                                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                    item.style.opacity = '0';
                                    item.style.transform = 'translateX(100%)';
                                }, index * 50); // Stagger animations
                            });
                            
                            // Wait for animations to complete, then remove from DOM
                            setTimeout(() => {
                                // Remove all items from DOM
                                notificationItems.forEach(item => {
                                    item.remove();
                                });
                                
                                // Recalculate panel height
                                calculatePanelHeight();
                                
                                // Reload to show empty state and ensure sync with database
                                setTimeout(() => {
                                    loadNotifications();
                                    updateUnreadCount();
                                }, 100);
                            }, (notificationItems.length * 50) + 300); // Wait for all animations
                        } else {
                            console.error('Error clearing all notifications:', data);
                            console.error('Error message: ' + (data.message || 'Unknown error'));
                            // Reload to restore state
                            loadNotifications();
                        }
                    } catch (error) {
                        console.error('Error clearing all notifications:', error);
                        console.error('Error details:', error.message, error.stack);
                        // Reload to restore state on error
                        loadNotifications();
                    }
                });
            }
            
            
            // Show all notifications modal
            window.showAllNotifications = async function() {
                try {
                    const response = await fetch('/api/notifications?limit=1000');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        return;
                    }
                    
                    if (data.success && data.notifications) {
                        showAllNotificationsModal(data.notifications);
                    }
                } catch (error) {
                    console.error('Error loading all notifications:', error);
                }
            };
            
            // Show all notifications for specific patient
            window.showAllNotificationsForPatient = async function(patientId) {
                try {
                    const response = await fetch('/api/notifications?limit=1000');
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        return;
                    }
                    
                    if (data.success && data.notifications) {
                        const patientNotifs = data.notifications.filter(n => n.patient_id == patientId);
                        const patientName = patientNotifs.length > 0 && patientNotifs[0].patient_first_name 
                            ? `${patientNotifs[0].patient_first_name} ${patientNotifs[0].patient_last_name || ''}`.trim()
                            : 'Patient';
                        showAllNotificationsModal(patientNotifs, `Notifications for ${patientName}`);
                    }
                } catch (error) {
                    console.error('Error loading patient notifications:', error);
                }
            };
            
            // Show all notifications modal
            function showAllNotificationsModal(notifications, title = 'All Notifications') {
                // Close notifications panel immediately
                closeNotifications();
                
                let modalHtml = `
                    <div class="modal fade" id="allNotificationsModal" tabindex="-1" aria-labelledby="allNotificationsModalLabel" aria-hidden="true" style="z-index: 10000001;">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(40px) saturate(180%); border: 0.5px solid rgba(255, 255, 255, 0.15);">
                                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                    <h5 class="modal-title" id="allNotificationsModalLabel" style="color: white;">
                                        <i class="bi bi-bell me-2"></i>${escapeHtml(title)}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="allNotificationsModalBody" style="max-height: 70vh; overflow-y: auto;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span style="color: rgba(255, 255, 255, 0.8);">Total: ${notifications.length} notification${notifications.length !== 1 ? 's' : ''}</span>
                                        <button class="btn btn-sm btn-primary" onclick="markAllNotificationsAsRead()">
                                            <i class="bi bi-check-all me-1"></i>Mark All Read
                                        </button>
                                    </div>
                                    <div id="allNotificationsList" style="display: flex; flex-direction: column; gap: 12px;">
                `;
                
                if (notifications.length === 0) {
                    modalHtml += `
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; opacity: 0.3; color: rgba(255, 255, 255, 0.5);"></i>
                            <p class="mt-3" style="color: rgba(255, 255, 255, 0.7);">No notifications</p>
                        </div>
                    `;
                } else {
                    notifications.forEach(notif => {
                        modalHtml += renderNotificationItem(notif);
                    });
                }
                
                modalHtml += `
                                    </div>
                                </div>
                                <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('allNotificationsModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal backdrop with higher z-index
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.style.zIndex = '10000000';
                document.body.appendChild(backdrop);
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                
                // Initialize modal
                const modalElement = document.getElementById('allNotificationsModal');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true
                });
                modal.show();
                
                // Add event listeners to close buttons
                document.querySelectorAll('#allNotificationsModal .notification-item-close').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const notificationId = this.getAttribute('data-notification-id');
                        markAsReadAndHide(notificationId);
                    });
                });
                
                // Clean up on hide
                modalElement.addEventListener('hidden.bs.modal', function() {
                    this.remove();
                    if (backdrop && backdrop.parentNode) {
                        backdrop.parentNode.removeChild(backdrop);
                    }
                });
            }
            
            // Mark all notifications as read (from modal)
            window.markAllNotificationsAsRead = async function() {
                try {
                    const response = await fetch('/api/notifications/read-all', {
                        method: 'PUT'
                    });
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        return;
                    }
                    
                    if (data.success) {
                        // Reload notifications in modal
                        if (window.showAllNotifications) {
                            window.showAllNotifications();
                        }
                        // Reload main panel
                        loadNotifications();
                        updateUnreadCount();
                    }
                } catch (error) {
                    console.error('Error marking all as read:', error);
                }
            };
            
            // Get time ago
            function getTimeAgo(dateString) {
                const now = new Date();
                const date = new Date(dateString);
                const diff = Math.floor((now - date) / 1000);
                
                if (diff < 60) return 'Just now';
                if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
                if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
                return date.toLocaleDateString();
            }
            
            // Escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Start polling
            function startNotificationsPolling() {
                if (notificationsPollingInterval) return;
                
                notificationsPollingInterval = setInterval(() => {
                    if (isNotificationsOpen) {
                        loadNotifications();
                    } else {
                        updateUnreadCount();
                    }
                }, 10000); // Poll every 10 seconds
            }
            
            // Stop polling
            function stopNotificationsPolling() {
                if (notificationsPollingInterval) {
                    clearInterval(notificationsPollingInterval);
                    notificationsPollingInterval = null;
                }
            }
            
            // Initialize: Load unread count on page load
            updateUnreadCount();
            
            // Start polling for unread count (when panel is closed)
            setInterval(() => {
                if (!isNotificationsOpen) {
                    updateUnreadCount();
                }
            }, 30000); // Check every 30 seconds when panel is closed
        })();
        
        // Forum Notification Toast System
        (function() {
            const STORAGE_KEY = 'shownNotificationToasts';
            let shownNotificationIds = new Set();
            const POLLING_INTERVAL = 5000; // Check every 5 seconds
            
            // Load shown notification IDs from localStorage
            function loadShownNotificationIds() {
                try {
                    const stored = localStorage.getItem(STORAGE_KEY);
                    if (stored) {
                        const ids = JSON.parse(stored);
                        shownNotificationIds = new Set(ids);
                    }
                } catch (error) {
                    console.debug('Error loading shown notification IDs:', error);
                    shownNotificationIds = new Set();
                }
            }
            
            // Save shown notification IDs to localStorage
            function saveShownNotificationIds() {
                try {
                    const ids = Array.from(shownNotificationIds);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
                    
                    // Keep only last 1000 IDs to prevent localStorage from growing too large
                    if (ids.length > 1000) {
                        const recentIds = ids.slice(-1000);
                        shownNotificationIds = new Set(recentIds);
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(recentIds));
                    }
                } catch (error) {
                    console.debug('Error saving shown notification IDs:', error);
                }
            }
            
            // Load on initialization
            loadShownNotificationIds();
            
            // Create forum toast container
            function createForumToastContainer() {
                let container = document.getElementById('forumToastContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'forumToastContainer';
                    document.body.appendChild(container);
                }
                return container;
            }
            
            // Initialize container
            createForumToastContainer();
            
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Show forum notification toast
            function showForumToast(notification) {
                if (!notification || !notification.id) return;
                
                // Check if already shown
                if (shownNotificationIds.has(notification.id)) {
                    return;
                }
                
                const container = createForumToastContainer();
                const toastId = 'forum-toast-' + notification.id + '-' + Date.now();
                
                // Check if toast already exists
                if (document.getElementById(toastId)) {
                    return;
                }
                
                // Determine icon based on notification type
                let icon = '💬';
                if (notification.type === 'forum_topic') {
                    icon = '📝';
                } else if (notification.type === 'forum_post') {
                    icon = '💬';
                }
                
                // Create link to forum topic if available
                const topicLink = notification.related_id ? `/doctor/forum/topic/${notification.related_id}` : '/doctor/forum';
                
                const toastHtml = `
                    <div id="${toastId}" class="toast forum-toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                        <div class="toast-header">
                            <i class="bi bi-chat-dots me-2" style="font-size: 1.25rem;"></i>
                            <strong class="me-auto">${escapeHtml(notification.title || 'Forum Notification')}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${escapeHtml(notification.message || '')}
                            ${notification.related_id ? `
                                <div class="mt-2">
                                    <a href="${topicLink}" class="btn btn-sm btn-primary" style="text-decoration: none;">
                                        <i class="bi bi-arrow-right me-1"></i>View Topic
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: true,
                        delay: 5000
                    });
                    
                    toast.show();
                    
                    // Mark as shown and save to localStorage
                    shownNotificationIds.add(notification.id);
                    saveShownNotificationIds();
                    
                    // Play notification sound
                    playNotificationSound();
                    
                    // Remove from DOM after hiding
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                    
                    // Add click handler to view topic button
                    const viewTopicBtn = toastElement.querySelector('a[href]');
                    if (viewTopicBtn) {
                        viewTopicBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            toast.hide();
                            setTimeout(() => {
                                window.location.href = topicLink;
                            }, 300);
                        });
                    }
                }
            }
            
            // Show general notification toast (for non-forum notifications)
            function showGeneralNotificationToast(notification) {
                if (!notification || !notification.id) return;
                
                // Check if already shown
                if (shownNotificationIds.has(notification.id)) {
                    return;
                }
                
                const container = createForumToastContainer();
                const toastId = 'notification-toast-' + notification.id + '-' + Date.now();
                
                // Check if toast already exists
                if (document.getElementById(toastId)) {
                    return;
                }
                
                // Determine icon based on notification type
                let icon = '🔔';
                if (notification.type === 'appointment') {
                    icon = '📅';
                } else if (notification.type === 'alert') {
                    icon = '⚠️';
                } else if (notification.type === 'system') {
                    const title = (notification.title || '').toLowerCase();
                    if (title.includes('login')) icon = '🔓';
                    else if (title.includes('logout')) icon = '🔒';
                    else icon = '⚙️';
                }
                
                // Create link based on notification type
                let actionLink = null;
                let actionText = null;
                if (notification.related_type === 'appointment' && notification.related_id) {
                    actionLink = `/doctor/appointments/${notification.related_id}`;
                    actionText = 'View Appointment';
                } else if (notification.patient_id) {
                    actionLink = `/doctor/patients/${notification.patient_id}`;
                    actionText = 'View Patient';
                }
                
                const toastHtml = `
                    <div id="${toastId}" class="toast forum-toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
                        <div class="toast-header">
                            <i class="bi bi-bell me-2" style="font-size: 1.25rem;"></i>
                            <strong class="me-auto">${escapeHtml(notification.title || 'Notification')}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${escapeHtml(notification.message || '')}
                            ${actionLink ? `
                                <div class="mt-2">
                                    <a href="${actionLink}" class="btn btn-sm btn-primary" style="text-decoration: none;">
                                        <i class="bi bi-arrow-right me-1"></i>${actionText}
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                
                if (toastElement) {
                    const toast = new bootstrap.Toast(toastElement, {
                        autohide: true,
                        delay: 3000
                    });
                    
                    toast.show();
                    
                    // Mark as shown and save to localStorage
                    shownNotificationIds.add(notification.id);
                    saveShownNotificationIds();
                    
                    // Play notification sound (check if function exists)
                    if (typeof playNotificationSound === 'function') {
                        try {
                            playNotificationSound();
                        } catch (error) {
                            console.debug('Could not play notification sound:', error);
                        }
                    }
                    
                    // Remove from DOM after hiding
                    toastElement.addEventListener('hidden.bs.toast', function() {
                        toastElement.remove();
                    });
                    
                    // Add click handler to action button
                    const actionBtn = toastElement.querySelector('a[href]');
                    if (actionBtn) {
                        actionBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            toast.hide();
                            setTimeout(() => {
                                window.location.href = actionLink;
                            }, 300);
                        });
                    }
                }
            }
            
            // Check for new forum notifications
            async function checkForumNotifications() {
                try {
                    const response = await fetch('/api/notifications?limit=10');
                    
                    if (!response.ok) {
                        return;
                    }
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        return;
                    }
                    
                    if (data.success && data.notifications && data.notifications.length > 0) {
                        // Filter forum notifications
                        const forumNotifications = data.notifications.filter(n => 
                            (n.type === 'forum_topic' || n.type === 'forum_post') && 
                            !shownNotificationIds.has(n.id)
                        );
                        
                        // Show toast for each new forum notification
                        forumNotifications.forEach(notification => {
                            showForumToast(notification);
                        });
                        
                        // Filter general notifications (non-forum)
                        const generalNotifications = data.notifications.filter(n => 
                            n.type !== 'forum_topic' && 
                            n.type !== 'forum_post' && 
                            !shownNotificationIds.has(n.id)
                        );
                        
                        // Show toast for each new general notification
                        generalNotifications.forEach(notification => {
                            showGeneralNotificationToast(notification);
                        });
                    }
                } catch (error) {
                    // Silently fail
                    console.debug('Forum notification check failed:', error);
                }
            }
            
            // Start polling for forum notifications
            function startForumNotificationPolling() {
                // Check immediately
                checkForumNotifications();
                
                // Set up continuous polling
                setInterval(() => {
                    checkForumNotifications();
                }, POLLING_INTERVAL);
            }
            
            // Initialize on page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', startForumNotificationPolling);
            } else {
                startForumNotificationPolling();
            }
            
            // Check when page becomes visible
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setTimeout(() => {
                        checkForumNotifications();
                    }, 500);
                }
            });
            
            // Check on window focus
            window.addEventListener('focus', function() {
                setTimeout(() => {
                    checkForumNotifications();
                }, 500);
            });
        })();
    </script>
</body>
</html>

</html>