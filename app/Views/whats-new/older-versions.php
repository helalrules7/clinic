<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Older Version Features - Roaya Clinic</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Discover all features from previous versions of HClinic / Roaya Clinic">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare, features, versions">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://roaya.hclinic.clinic/whats-new/older-versions">
    <meta property="og:title" content="Older Version Features - Roaya Clinic">
    <meta property="og:description" content="Discover all features from previous versions of HClinic / Roaya Clinic">
    <meta property="og:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://roaya.hclinic.clinic/whats-new/older-versions">
    <meta name="twitter:title" content="Older Version Features - Roaya Clinic">
    <meta name="twitter:description" content="Discover all features from previous versions of HClinic / Roaya Clinic">
    <meta name="twitter:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link id="favicon" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Light.png">
    <link id="favicon-dark" rel="icon" type="image/png" sizes="32x32" href="/assets/images/Dark.png" media="(prefers-color-scheme: dark)">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Light.png">
    
    <style>
        :root {
            --bg-light: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-dark: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            --card-light: #ffffff;
            --card-dark: #1e293b;
            --text-light: #2c3e50;
            --text-dark: #f8fafc;
            --subtitle-light: #6c757d;
            --subtitle-dark: #cbd5e1;
            --accent-light: #667eea;
            --accent-dark: #38bdf8;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-dark: rgba(0, 0, 0, 0.5);
            --border-light: #e5e7eb;
            --border-dark: #374151;
        }
        
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        body {
            background: var(--bg-light);
            min-height: 100vh;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 2rem 1rem;
            color: var(--text-light);
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background: var(--bg-dark);
                color: var(--text-dark);
            }
        }
        
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem 0;
        }
        
        .logo {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo img {
            max-width: 100px;
            height: auto;
            transition: transform 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }
        
        .logo img:hover {
            transform: scale(1.1);
        }
        
        @media (prefers-color-scheme: dark) {
            .logo img {
                filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5));
            }
        }
        
        body.dark .logo img {
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.5));
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .page-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .page-title {
            color: var(--text-dark);
        }
        
        .page-subtitle {
            font-size: 1.2rem;
            color: var(--subtitle-light);
            margin-bottom: 2rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .page-subtitle {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .page-subtitle {
            color: var(--subtitle-dark);
        }
        
        .card {
            background: var(--card-light);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px var(--shadow-light);
            border: 1px solid var(--border-light);
            margin-bottom: 2rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .card {
                background: var(--card-dark);
                box-shadow: 0 10px 30px var(--shadow-dark);
                border-color: var(--border-dark);
            }
        }
        
        body.dark .card {
            background: var(--card-dark);
            box-shadow: 0 10px 30px var(--shadow-dark);
            border-color: var(--border-dark);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 2px solid var(--border-light);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .card-header {
                border-bottom-color: var(--border-dark);
            }
        }
        
        body.dark .card-header {
            border-bottom-color: var(--border-dark);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin: 0;
        }
        
        @media (prefers-color-scheme: dark) {
            .card-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .card-title {
            color: var(--text-dark);
        }
        
        .version-section {
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .version-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        @media (prefers-color-scheme: dark) {
            .version-section {
                border-bottom-color: var(--border-dark);
            }
        }
        
        body.dark .version-section {
            border-bottom-color: var(--border-dark);
        }
        
        .version-title {
            font-size: 1.3rem;
            color: var(--text-light);
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .version-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .version-title {
            color: var(--text-dark);
        }
        
        .version-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .version-features li {
            padding: 0.6rem 0;
            padding-left: 2rem;
            position: relative;
            color: var(--subtitle-light);
            line-height: 1.6;
        }
        
        .version-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .version-features li {
                color: var(--subtitle-dark);
            }
            
            .version-features li::before {
                color: #34d399;
            }
        }
        
        body.dark .version-features li {
            color: var(--subtitle-dark);
        }
        
        body.dark .version-features li::before {
            color: #34d399;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 1000;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
            color: white;
        }
        
        @media (prefers-color-scheme: dark) {
            .back-button {
                background: rgba(0, 0, 0, 0.3);
                border-color: rgba(255, 255, 255, 0.2);
            }
            
            .back-button:hover {
                background: rgba(0, 0, 0, 0.5);
            }
        }
        
        body.dark .back-button {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        body.dark .back-button:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        
        .footer {
            text-align: center;
            margin-top: 3rem;
            padding: 2rem 0;
            border-top: 1px solid var(--border-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .footer {
                border-top-color: var(--border-dark);
            }
        }
        
        body.dark .footer {
            border-top-color: var(--border-dark);
        }
        
        .clinic-version {
            color: var(--subtitle-light);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-align: center;
            direction: ltr;
        }
        
        .clinic-version span {
            direction: rtl;
            display: inline-block;
        }
        
        @media (prefers-color-scheme: dark) {
            .clinic-version {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .clinic-version {
            color: var(--subtitle-dark);
        }
        
        .clinic-author {
            color: var(--subtitle-light);
            font-size: 0.85rem;
            margin-bottom: 1rem;
            text-align: center;
            direction: ltr;
        }
        
        @media (prefers-color-scheme: dark) {
            .clinic-author {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .clinic-author {
            color: var(--subtitle-dark);
        }
        
        .sidebar-footer-link {
            color: var(--accent-light) !important;
            transition: color 0.2s ease;
        }
        
        .sidebar-footer-link:hover {
            color: var(--accent-light) !important;
            opacity: 0.8;
        }
        
        @media (prefers-color-scheme: dark) {
            .sidebar-footer-link {
                color: var(--accent-dark) !important;
            }
            
            .sidebar-footer-link:hover {
                color: var(--accent-dark) !important;
            }
        }
        
        body.dark .sidebar-footer-link {
            color: var(--accent-dark) !important;
        }
        
        body.dark .sidebar-footer-link:hover {
            color: var(--accent-dark) !important;
        }
        
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .page-subtitle {
                font-size: 1rem;
            }
            
            .back-button {
                top: 10px;
                right: 10px;
                padding: 0.5rem 0.75rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="/whats-new" class="back-button">
        <i class="bi bi-arrow-left"></i>
        Back to What's New
    </a>
    
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="/assets/images/Dark.png" alt="Roaya Clinic Logo" id="clinicLogo" style="max-width: 100px; height: auto; transition: transform 0.3s ease;">
            </div>
            <h1 class="page-title">Older Version Features</h1>
            <p class="page-subtitle">Discover all features from previous versions</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    Previous Versions Features
                </h3>
            </div>
            <div class="card-body">
                <!-- Version 5.1 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-info me-2">v5.1</span>
                        Automatic Drug Database Update System
                    </h4>
                    <ul class="version-features">
                        <li>Complete automated drug database update with SQLite support</li>
                        <li>Real-time database synchronization with progress tracking</li>
                        <li>SQLite command-line integration with error handling</li>
                        <li>Enhanced error handling and logging system</li>
                    </ul>
                </div>

                <!-- Version 5.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-primary me-2">v5.0</span>
                        Dashboard & Financial Management
                    </h4>
                    <ul class="version-features">
                        <li>Daily financial management with opening balance and daily closure</li>
                        <li>Advanced Excel export with Arabic RTL support</li>
                        <li>Secretary management system with role-based permissions</li>
                        <li>Real-time financial dashboard with analytics</li>
                        <li>Appointment history timeline with attachments</li>
                        <li>Visual analytics dashboard with Chart.js</li>
                        <li>Missed appointments tracking and management</li>
                        <li>Advanced pagination and quantity control</li>
                    </ul>
                </div>

                <!-- Version 4.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-success me-2">v4.0</span>
                        Smart Drug Management & Security
                    </h4>
                    <ul class="version-features">
                        <li>Smart drug suggestions with usage analytics</li>
                        <li>Advanced drug autocomplete search</li>
                        <li>Comprehensive drug information system</li>
                        <li>Enhanced security with CSRF protection</li>
                        <li>Advanced clinic settings with branding</li>
                        <li>Logo and branding management system</li>
                        <li>Dynamic print templates</li>
                        <li>Visit cost management system</li>
                    </ul>
                </div>

                <!-- Version 3.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-warning me-2">v3.0</span>
                        Patient Management & Export
                    </h4>
                    <ul class="version-features">
                        <li>Patient data export to Word with embedded images</li>
                        <li>Glasses prescriptions management</li>
                        <li>Patient information editing interface</li>
                        <li>Enhanced image processing for exports</li>
                        <li>Improved user interface design</li>
                        <li>Doctor filter for patients</li>
                    </ul>
                </div>

                <!-- Version 2.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-danger me-2">v2.0</span>
                        Admin Dashboard & Reports
                    </h4>
                    <ul class="version-features">
                        <li>Complete admin dashboard system</li>
                        <li>Advanced user management with roles</li>
                        <li>Enhanced reporting system with charts</li>
                        <li>System settings panel</li>
                        <li>Enhanced dark/light mode support</li>
                        <li>Font Awesome integration</li>
                    </ul>
                </div>

                <!-- Version 1.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-secondary me-2">v1.0</span>
                        Core Features
                    </h4>
                    <ul class="version-features">
                        <li>Patient management system</li>
                        <li>Appointment scheduling</li>
                        <li>Medical records management</li>
                        <li>Payment management</li>
                        <li>User management with role-based access</li>
                        <li>Dark mode support</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p class="clinic-version" dir="ltr" style="text-align: center;">
                <span dir="rtl">Version</span> v6.0 
                <a href="/whats-new" class="text-decoration-none sidebar-footer-link" style="margin-right: 0.5rem; margin-left: 0.5rem;">What's New?</a>
            </p>
            <p class="clinic-author" dir="ltr" style="text-align: center;">
                HClinic / Roaya © 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none sidebar-footer-link">Ahmed Helal</a>
            </p>
        </div>
    </div>
    
    <script>
        // Auto-detect dark mode from system preference
        const body = document.body;
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (prefersDark) {
            body.classList.add('dark');
        }
        
        // Update favicon and logo based on theme
        function updateFavicon() {
            const favicon = document.getElementById('favicon');
            if (body.classList.contains('dark')) {
                favicon.setAttribute('href', '/assets/images/Dark.png');
            } else {
                favicon.setAttribute('href', '/assets/images/Light.png');
            }
        }
        
        function updateLogo() {
            const logo = document.getElementById('clinicLogo');
            if (body.classList.contains('dark')) {
                logo.src = '/assets/images/Dark.png';
            } else {
                logo.src = '/assets/images/Light.png';
            }
        }
        
        // Initialize favicon and logo
        updateFavicon();
        updateLogo();
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (e.matches) {
                body.classList.add('dark');
            } else {
                body.classList.remove('dark');
            }
            updateFavicon();
            updateLogo();
        });
        
        // Add smooth page transition
        document.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>

