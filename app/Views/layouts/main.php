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
    
    <style>
        :root {
            --bg: #f8fafc;
            --text: #0f172a;
            --card: #ffffff;
            --muted: #475569;
            --accent: #0ea5e9;
            --success: #10b981;
            --danger: #ef4444;
            --border: #e2e8f0;
            --sidebar-width: 280px;
        }
        
        .dark {
            --bg: #0b1220;
            --text: #f8fafc;
            --card: #1e293b;
            --muted: #cbd5e1;
            --accent: #38bdf8;
            --success: #4ade80;
            --danger: #fb7185;
            --border: #334155;
        }
        
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--card);
            border-right: 1px solid var(--border);
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
            margin-right: 0.75rem;
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
            margin-right: 0.75rem;
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
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: var(--accent);
            color: white;
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
            /* Glass effect */
            background: rgba(248, 250, 252, 0.50);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.3);
            box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .top-bar.scrolled {
            padding-top: 1rem;
        }
        
        .dark .top-bar {
            background: rgba(11, 18, 32, 0.85);
            border-bottom: 1px solid rgba(51, 65, 85, 0.3);
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
        
        .form-label {
            color: var(--text);
            font-weight: 500;
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
            margin-right: 1rem;
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
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            /* Hide toggle button when sidebar is open */
            .sidebar.show ~ .main-content .sidebar-toggle {
                opacity: 0;
                pointer-events: none;
                transform: scale(0.8);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            
            .main-content {
                margin-left: 0;
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
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            /* Hide toggle button when sidebar is open */
            .sidebar.show ~ .main-content .sidebar-toggle {
                opacity: 0;
                pointer-events: none;
                transform: scale(0.8);
                transition: opacity 0.2s ease, transform 0.2s ease;
            }
            
            .main-content {
                margin-left: 0;
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
        
        .sidebar-footer-text {
            color: var(--text) !important;
        }
        
        .sidebar-footer a {
            color: var(--accent);
            transition: color 0.2s ease;
        }
        
        .sidebar-footer a:hover {
            color: var(--success);
        }
        
        .whats-new-link {
            color: var(--accent) !important;
            font-weight: 500;
            text-decoration: underline !important;
        }
        
        .whats-new-link:hover {
            color: var(--success) !important;
        }
        
        .sidebar-footer-link {
            color: var(--accent) !important;
        }
        
        .sidebar-footer-link:hover {
            color: var(--success) !important;
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
            right: -5px;
            font-size: 16px;
            animation: blink 1s infinite;
        }
        
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        
        /* Scroll to Top Button */
        .scroll-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 999;
            transition: all 0.3s ease;
            /* Glass effect - more transparent */
            background: rgba(248, 250, 252, 0.65);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.25);
            box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.15);
            color: var(--text);
        }
        
        .dark .scroll-to-top {
            background: rgba(11, 18, 32, 0.65);
            border: 1px solid rgba(51, 65, 85, 0.25);
        }
        
        .scroll-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.2);
        }
        
        .scroll-to-top.show {
            display: flex;
        }
        
        .scroll-to-top i {
            font-size: 1.25rem;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .scroll-to-top {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 45px;
                height: 45px;
            }
        }
        
        /* Alert Toast Glass Effect */
        .alert-toast-glass {
            /* Glass effect - similar to top-bar */
            background: rgba(248, 250, 252, 0.85) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(30, 144, 255, 0.6) !important;
            box-shadow: 0 4px 20px rgba(30, 144, 255, 0.4), 
                        0 0 0 1px rgba(30, 144, 255, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            color: var(--text) !important;
            animation: toastGlow 2s ease-in-out infinite;
            padding: 0.75rem 1rem !important;
        }
        
        .dark .alert-toast-glass {
            background: rgba(11, 18, 32, 0.90) !important;
            border: 2px solid rgba(30, 144, 255, 0.7) !important;
            box-shadow: 0 4px 20px rgba(30, 144, 255, 0.5), 
                        0 0 0 1px rgba(30, 144, 255, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }
        
        @keyframes toastGlow {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(30, 144, 255, 0.4), 
                            0 0 0 1px rgba(30, 144, 255, 0.2),
                            inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }
            50% {
                box-shadow: 0 4px 25px rgba(30, 144, 255, 0.6), 
                            0 0 0 1px rgba(30, 144, 255, 0.4),
                            inset 0 1px 0 rgba(255, 255, 255, 0.1);
            }
        }
        
        .dark .alert-toast-glass {
            animation: toastGlowDark 2s ease-in-out infinite;
        }
        
        @keyframes toastGlowDark {
            0%, 100% {
                box-shadow: 0 4px 20px rgba(30, 144, 255, 0.5), 
                            0 0 0 1px rgba(30, 144, 255, 0.3),
                            inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }
            50% {
                box-shadow: 0 4px 30px rgba(30, 144, 255, 0.7), 
                            0 0 0 1px rgba(30, 144, 255, 0.5),
                            inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }
        }
        
        .alert-toast-glass .toast-body {
            color: var(--text) !important;
        }
        
        .alert-toast-glass strong {
            color: var(--text) !important;
        }
        
        .alert-toast-glass small {
            color: var(--muted) !important;
        }
        
        .alert-toast-btn {
            transition: all 0.2s ease;
        }
        
        .alert-toast-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
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
            <div class="d-flex align-items-center">
                <div class="user-avatar">
                    <?= strtoupper(substr($this->getCurrentUser()['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="user-details">
                    <h6><?= htmlspecialchars($this->getCurrentUser()['name'] ?? 'User') ?></h6>
                    <small><?= ucfirst($this->getCurrentUser()['role'] ?? 'user') ?></small>
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
                    <a href="/doctor/patients" class="nav-link <?= $this->isActiveRoute('/doctor/patients') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Patients
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/drugs" class="nav-link <?= $this->isActiveRoute('/doctor/drugs') ? 'active' : '' ?>">
                        <i class="bi bi-capsule"></i>
                        Drug Search
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/payments" class="nav-link <?= $this->isActiveRoute('/doctor/payments') ? 'active' : '' ?>">
                        <i class="bi bi-credit-card"></i>
                        Financial Management
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/daily-closure" class="nav-link <?= $this->isActiveRoute('/doctor/daily-closure') ? 'active' : '' ?>">
                        <i class="bi bi-calendar-check"></i>
                        Daily Closure
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/reports" class="nav-link <?= $this->isActiveRoute('/doctor/reports') ? 'active' : '' ?>">
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
                        Medications Prescriptions
                    </a>
                </div>
                <div class="nav-item">
                    <a href="/doctor/alerts" class="nav-link <?= $this->isActiveRoute('/doctor/alerts') ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i>
                        Alerts
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
                        Medications Prescriptions
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
                
                <button id="themeToggle" class="btn btn-outline-secondary theme-toggle">
                    <i class="bi bi-moon"></i>
                </button>
            </div>
        </div>
        
        <!-- Page Content -->
        <?= $content ?>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" aria-label="Scroll to top">
        <i class="bi bi-arrow-up"></i>
    </button>

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
                                        <strong>${escapeHtml(alert.message)}</strong>
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
                                <button type="button" class="btn btn-sm btn-outline-light alert-toast-btn snooze-btn" data-alert-id="${alert.id}" data-toast-id="${toastId}" style="white-space: nowrap;">
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
    </script>
</body>
</html>
