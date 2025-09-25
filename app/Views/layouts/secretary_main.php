<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'عيادة رؤية - السكرتارية' ?></title>
    
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
    
    <link rel="stylesheet" href="/sec-style.css">

    <style>
        :root {
            --bg: #f8fafc;
            --text: #0f172a;
            --card: #ffffff;
            --muted: #475569;
            --accent: #0ea5e9;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --border: #e2e8f0;
            --sidebar-width: 280px;
            --bg-alt: #f1f5f9;
            --bg-dark: #ffffff;
            --accent-rgb: 14, 165, 233;
        }
        
        .dark {
            --bg: #0b1220;
            --text: #f8fafc;
            --card: #1e293b;
            --muted: #cbd5e1;
            --accent: #38bdf8;
            --success: #4ade80;
            --danger: #fb7185;
            --warning: #fbbf24;
            --info: #22d3ee;
            --border: #334155;
            --bg-alt: #0f172a;
            --bg-dark: #1e293b;
            --accent-rgb: 56, 189, 248;
        }
        
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            direction: rtl;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--card);
            border-left: 1px solid var(--border);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border);
            text-align: center;
        }
        
        .clinic-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .clinic-logo i {
            font-size: 2rem;
            color: var(--accent);
            margin-left: 0.75rem;
        }
        
        .clinic-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .user-info {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-left: 0.75rem;
        }
        
        .user-details h6 {
            margin: 0;
            color: var(--text);
            font-weight: 600;
        }
        
        .user-details small {
            color: var(--muted);
        }
        
        .nav-menu {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background: var(--bg);
            color: var(--accent);
            transform: translateX(-4px);
        }
        
        .nav-link.active {
            background: var(--accent);
            color: white;
        }
        
        .nav-link i {
            margin-left: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-right 0.3s ease;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }
        
        .page-title h1 {
            margin: 0;
            color: var(--text);
            font-weight: 600;
        }
        
        .page-title small {
            color: var(--muted);
        }
        
        .top-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .theme-toggle {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
        }
        
        .theme-toggle:hover {
            background: var(--bg);
            border-color: var(--accent);
        }
        
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            animation: fadeUp 0.35s ease both;
        }
        
        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .table {
            background: var(--card);
            color: var(--text);
        }
        
        .table th {
            background: var(--bg);
            border-color: var(--border);
            color: var(--text);
            font-weight: 600;
        }
        
        .table td {
            border-color: var(--border);
        }
        
        .badge {
            border-radius: 6px;
            font-weight: 500;
        }
        
        .form-control, .form-select {
            background: var(--card);
            border: 2px solid var(--border);
            color: var(--text);
            font-weight: 500;
        }
        
        .form-control:focus, .form-select:focus {
            background: var(--card);
            border-color: var(--accent);
            color: var(--text);
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
            font-weight: 600;
        }
        
        .form-control::placeholder {
            color: var(--muted);
            font-weight: 400;
        }
        
        .form-label {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .sidebar-toggle {
            display: none;
            background: var(--accent);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            margin-left: 1rem;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1001;
            position: relative;
            min-width: 44px;
            min-height: 44px;
            align-items: center;
            justify-content: center;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar.show ~ .main-content .sidebar-toggle {
                opacity: 0;
                pointer-events: none;
                transform: scale(0.8);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            
            .main-content {
                margin-right: 0;
                padding: 1rem;
            }
            
            .sidebar-toggle {
                display: flex !important;
                opacity: 1;
                pointer-events: auto;
                transform: scale(1);
            }
        }
        
        .sidebar-toggle:hover {
            background: var(--success);
            transform: scale(1.05);
        }
        
        .sidebar-toggle:active {
            transform: scale(0.95);
        }
        
        /* Ensure toggle button is visible on mobile devices */
        @media (max-width: 992px) {
            .sidebar-toggle {
                display: flex !important;
                opacity: 1;
                pointer-events: auto;
                transform: scale(1);
            }
            
            .sidebar {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar.show ~ .main-content .sidebar-toggle {
                opacity: 0;
                pointer-events: none;
                transform: scale(0.8);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            
            .main-content {
                margin-right: 0;
            }
        }
        
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }
        
        .overlay.show {
            display: block;
        }
        
        .sidebar-footer {
            border-top: 1px solid var(--border) !important;
        }
        
        .sidebar-footer a {
            color: var(--accent);
            transition: color 0.2s ease;
        }
        
        .sidebar-footer a:hover {
            color: var(--success);
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
        
        /* RTL specific adjustments */
        .me-2 { margin-left: 0.5rem !important; margin-right: 0 !important; }
        .me-3 { margin-left: 1rem !important; margin-right: 0 !important; }
        .ms-2 { margin-right: 0.5rem !important; margin-left: 0 !important; }
        .ms-3 { margin-right: 1rem !important; margin-left: 0 !important; }
        .text-start { text-align: right !important; }
        .text-end { text-align: left !important; }
        .justify-content-start { justify-content: flex-end !important; }
        .justify-content-end { justify-content: flex-start !important; }
        
        /* Arabic text styling */
        .arabic-text {
            font-family: 'Cairo', Arial, sans-serif;
            direction: rtl;
            text-align: right;
        }
        
        /* Secretary specific styles */
        .secretary-badge {
            background: linear-gradient(135deg, var(--accent), var(--info));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stat-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--text);
        }
        
        .stat-label {
            margin: 0;
            color: var(--muted);
            font-size: 0.875rem;
        }
        
        .avatar-sm {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--text);
            background: var(--bg);
        }
        
        .table td {
            vertical-align: middle;
            border-top: 1px solid var(--border);
        }
        
        .list-group-item {
            border: none;
            border-bottom: 1px solid var(--border);
            background: transparent;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
        
        .btn-group .btn {
            border-radius: 6px;
        }
        
        .btn-group .btn:not(:last-child) {
            border-left: 1px solid var(--border);
        }
        
        .quick-action-btn {
            transition: all 0.2s ease;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="clinic-logo">
                <img id="clinicLogo" src="/assets/images/Light.png" alt="رؤية" style="width: 32px; height: 32px; margin-left: 0.75rem;">
                <div class="clinic-name">عيادة رؤية</div>
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
            
            <div class="nav-item mt-4">
                <a href="/about" class="nav-link <?= $this->isActiveRoute('/about') ? 'active' : '' ?>">
                    <i class="bi bi-info-circle"></i>
                    حول النظام
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
                    <div class="mb-1">عيادة رؤية v4.0</div>
                    <div>© 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none">أحمد هلال</a></div>
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
                
                <button id="themeToggle" class="btn btn-outline-secondary theme-toggle">
                    <i class="bi bi-moon"></i>
                </button>
            </div>
        </div>
        
        <!-- Page Content -->
        <?= $content ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme toggle functionality
        const apply = mode => document.documentElement.classList.toggle('dark', mode === 'dark');
        const saved = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        apply(saved);
        
        document.getElementById('themeToggle').onclick = () => {
            const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            apply(next);
            localStorage.setItem('theme', next);
            
            // Update icon
            const icon = document.querySelector('#themeToggle i');
            icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
            
            // Update logo
            const logo = document.getElementById('clinicLogo');
            if (logo) {
                logo.src = next === 'dark' ? '/assets/images/Dark.png' : '/assets/images/Light.png';
            }
            
            // Update favicon
            const favicon = document.getElementById('favicon');
            if (favicon) {
                favicon.href = next === 'dark' ? '/assets/fav/Dark.ico' : '/assets/fav/Light.ico';
            }
        };
        
        // Update initial icon and logo
        const icon = document.querySelector('#themeToggle i');
        icon.className = saved === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        
        const logo = document.getElementById('clinicLogo');
        if (logo) {
            logo.src = saved === 'dark' ? '/assets/images/Dark.png' : '/assets/images/Light.png';
        }
        
        // Update initial favicon
        const favicon = document.getElementById('favicon');
        if (favicon) {
            favicon.href = saved === 'dark' ? '/assets/fav/Dark.ico' : '/assets/fav/Light.ico';
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
                console.log('Sidebar toggled'); // Debug log
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
    </script>
</body>
</html>
