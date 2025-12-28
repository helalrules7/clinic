<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>What's New in v6.1 - Roaya Clinic</title>
    
    <!-- Meta Description -->
    <meta name="description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.1">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare, update, features">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://roaya.hclinic.clinic/whats-new">
    <meta property="og:title" content="What's New in v6.1 - Roaya Clinic">
    <meta property="og:description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.1">
    <meta property="og:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://roaya.hclinic.clinic/whats-new">
    <meta name="twitter:title" content="What's New in v6.1 - Roaya Clinic">
    <meta name="twitter:description" content="Discover the new features and improvements in HClinic / Roaya Clinic v6.1">
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
            --success-light: #10b981;
            --success-dark: #34d399;
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
            margin-bottom: 4rem;
            padding: 2rem 0;
        }
        
        .logo {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo img {
            max-width: 120px;
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
            font-size: 3rem;
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
            font-size: 1.3rem;
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
        
        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        @media (prefers-color-scheme: dark) {
            .version-badge {
                background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
                box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
            }
        }
        
        body.dark .version-badge {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .feature-card.full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 1200px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 900px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .feature-card.full-width {
                grid-column: 1;
            }
        }
        
        .feature-card {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px var(--shadow-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover::before {
            transform: scaleX(1);
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-card {
                background: var(--card-dark);
                box-shadow: 0 10px 30px var(--shadow-dark);
                border-color: rgba(255, 255, 255, 0.1);
            }
            
            .feature-card::before {
                background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
            }
        }
        
        body.dark .feature-card {
            background: var(--card-dark);
            box-shadow: 0 10px 30px var(--shadow-dark);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        body.dark .feature-card::before {
            background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%);
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px var(--shadow-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-card:hover {
                box-shadow: 0 15px 40px var(--shadow-dark);
            }
        }
        
        body.dark .feature-card:hover {
            box-shadow: 0 15px 40px var(--shadow-dark);
        }
        
        .feature-icon {
            font-size: 3.5rem;
            color: var(--accent-light);
            margin-bottom: 1rem;
            display: block;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-icon {
                color: var(--accent-dark);
            }
        }
        
        body.dark .feature-icon {
            color: var(--accent-dark);
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-light);
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-title {
                color: var(--text-dark);
            }
        }
        
        body.dark .feature-title {
            color: var(--text-dark);
        }
        
        .feature-description {
            color: var(--subtitle-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-description {
                color: var(--subtitle-dark);
            }
        }
        
        body.dark .feature-description {
            color: var(--subtitle-dark);
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .feature-list li {
            color: var(--subtitle-light);
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }
        
        .feature-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success-light);
            font-weight: bold;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-list li {
                color: var(--subtitle-dark);
            }
            
            .feature-list li::before {
                color: var(--success-dark);
            }
        }
        
        body.dark .feature-list li {
            color: var(--subtitle-dark);
        }
        
        body.dark .feature-list li::before {
            color: var(--success-dark);
        }
        
        .feature-mockup {
            width: 100%;
            border-radius: 12px;
            margin-top: 1rem;
            border: 2px solid var(--accent-light);
            position: relative;
            background: var(--card-light);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
        }
        
        .feature-mockup-inner {
            width: 100%;
            min-height: 400px;
            border-radius: 8px;
            overflow: visible;
            position: relative;
        }
        
        .feature-mockup-inner.toast-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-mockup {
                background: var(--card-dark);
                border-color: var(--accent-dark);
                box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
            }
        }
        
        body.dark .feature-mockup {
            background: var(--card-dark);
            border-color: var(--accent-dark);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        /* Mockup Styles */
        .mockup-header {
            background: rgba(102, 126, 234, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .mockup-header.scrolled {
            padding-top: 1.5rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-header {
                background: rgba(56, 189, 248, 0.1);
                border-color: rgba(56, 189, 248, 0.2);
            }
        }
        
        body.dark .mockup-header {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.2);
        }
        
        .mockup-patient-page {
            background: var(--card-light);
            border-radius: 12px;
            padding: 1.5rem;
            min-height: 400px;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-patient-page {
                background: var(--card-dark);
            }
        }
        
        body.dark .mockup-patient-page {
            background: var(--card-dark);
        }
        
        .mockup-alert-toast {
            background: rgba(102, 126, 234, 0.15);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(30, 144, 255, 0.6);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            animation: toastGlow 2s ease-in-out infinite;
        }
        
        @keyframes toastGlow {
            0%, 100% { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 0 rgba(30, 144, 255, 0); }
            50% { box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 20px rgba(30, 144, 255, 0.4); }
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-alert-toast {
                background: rgba(56, 189, 248, 0.15);
                border-color: rgba(30, 144, 255, 0.6);
            }
        }
        
        body.dark .mockup-alert-toast {
            background: rgba(56, 189, 248, 0.15);
            border-color: rgba(30, 144, 255, 0.6);
        }
        
        .mockup-timeline-marker {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: transform 0.2s ease;
            margin-right: 1rem;
        }
        
        .mockup-timeline-marker:hover {
            transform: scale(1.1);
        }
        
        .mockup-badge {
            text-align: center;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--accent-light);
            font-weight: 500;
            animation: fadeInUp 0.6s ease;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-badge {
                background: rgba(56, 189, 248, 0.1);
                border-color: rgba(56, 189, 248, 0.3);
                color: var(--accent-dark);
            }
        }
        
        body.dark .mockup-badge {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.3);
            color: var(--accent-dark);
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
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
        
        .whats-new-link {
            color: var(--accent-light) !important;
            font-weight: 500;
            text-decoration: underline !important;
            transition: color 0.2s ease;
        }
        
        .whats-new-link:hover {
            color: var(--accent-light) !important;
            opacity: 0.8;
        }
        
        @media (prefers-color-scheme: dark) {
            .whats-new-link {
                color: var(--accent-dark) !important;
            }
            
            .whats-new-link:hover {
                color: var(--accent-dark) !important;
            }
        }
        
        body.dark .whats-new-link {
            color: var(--accent-dark) !important;
        }
        
        body.dark .whats-new-link:hover {
            color: var(--accent-dark) !important;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .page-subtitle {
                font-size: 1rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .back-button {
                top: 10px;
                right: 10px;
                padding: 0.5rem 0.75rem;
                font-size: 1rem;
            }
            
            .mockup-alert-toast {
                max-width: 95%;
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature-card {
            animation: fadeInUp 0.6s ease both;
        }
        
        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="/" class="back-button">
        <i class="bi bi-arrow-left"></i>
        Back to Login
    </a>
    
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="/assets/images/Dark.png" alt="Roaya Clinic Logo" id="clinicLogo" style="max-width: 120px; height: auto; transition: transform 0.3s ease;">
            </div>
            <div class="version-badge">Version 6.1</div>
            <h1 class="page-title">What's New</h1>
            <p class="page-subtitle">Discover the amazing new features and improvements</p>
            <div class="mt-3 d-flex gap-3 justify-content-center flex-wrap">
                <a href="/whats-new/full-features" target="_blank" class="btn btn-light btn-lg" style="text-decoration: none;">
                    <i class="bi bi-stars me-2"></i>
                    View All Features
                </a>
                <a href="/whats-new/older-versions" target="_blank" class="btn btn-outline-light btn-lg" style="text-decoration: none;">
                    <i class="bi bi-clock-history me-2"></i>
                    View Older Version Features
                </a>
            </div>
        </div>
        
        <div class="features-grid">

                    <!-- Patient Alert System -->
                     <div class="feature-card full-width">
                <i class="bi bi-bell feature-icon"></i>
                <h3 class="feature-title">Patient Alert System</h3>
                <p class="feature-description">
                    A comprehensive alert and notification system to help doctors manage patient reminders and important notifications efficiently.
                </p>
                <ul class="feature-list">
                    <li><strong>Alert Creation:</strong> Create alerts with date, time, message, and repetition settings</li>
                    <li><strong>Patient-Specific Alerts:</strong> Link alerts to specific patients and appointments</li>
                    <li><strong>Real-time Toast Notifications:</strong> Beautiful glass-effect toast notifications with Dodger Blue glow</li>
                    <li><strong>Global Polling System:</strong> Automatic alert checking every 10 seconds without page refresh</li>
                    <li><strong>Alert Management:</strong> Dedicated alerts page with full CRUD operations</li>
                    <li><strong>Patient Alerts Card:</strong> View and manage alerts directly from patient profile</li>
                    <li><strong>Auto-Reactivation:</strong> Alerts automatically reactivate when edited to future dates</li>
                    <li><strong>Dark Mode Support:</strong> Full dark mode compatibility throughout</li>
                    <li><strong>Toast Actions:</strong> View Patient, Snooze, and Close buttons in toast notifications</li>
                    <li><strong>Alert Status:</strong> Visual distinction between active, dismissed, and inactive alerts</li>
                </ul>
                <div class="mockup-badge">
                    <i class="bi bi-info-circle me-2"></i>Complete alert management system
                </div>
                <div class="feature-mockup">
                    <div class="feature-mockup-inner toast-container">
                        <div class="mockup-alert-toast">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bell-fill text-warning" style="font-size: 1.5rem;"></i>
                                    <div>
                                        <h6 class="mb-0">Patient Alert</h6>
                                        <small class="text-muted" style="color: #ffffff !important;">Ahmed Mohamed - Follow-up appointment</small>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <p class="mb-2">Reminder: Patient follow-up scheduled for today at 2:00 PM</p>
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-sm btn-primary">
                                    <i class="bi bi-person me-1"></i>View Patient
                                </button>
                                <button class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-clock me-1"></i>Snooze
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Patient Page UI/UX Improvements -->
            <div class="feature-card">
                <i class="bi bi-person-badge feature-icon"></i>
                <h3 class="feature-title">Enhanced Patient Page UI/UX</h3>
                <p class="feature-description">
                    Comprehensive improvements to the patient profile page for better usability and visual appeal.
                </p>
                <ul class="feature-list">
                    <li>Reorganized action buttons next to Contact Information</li>
                    <li>Improved timeline markers with expand/collapse functionality</li>
                    <li>Enhanced visual hierarchy and spacing</li>
                    <li>Better mobile responsiveness</li>
                    <li>Improved card layouts and organization</li>
                    <li>Enhanced Dark Mode support throughout</li>
                    <li>Timeline markers now clickable for expand/collapse</li>
                    <li>Smooth animations and transitions</li>
                </ul>

            </div>
            
            <!-- Glass Header System -->
            <div class="feature-card">
                <i class="bi bi-window feature-icon"></i>
                <h3 class="feature-title">Glass Fixed Header System</h3>
                <p class="feature-description">
                    A beautiful glass-effect header that stays fixed while scrolling, providing easy access to navigation and improved visual appeal.
                </p>
                <ul class="feature-list">
                    <li>Semi-transparent glass effect with backdrop blur</li>
                    <li>Fixed position on scroll across all pages</li>
                    <li>Dynamic padding adjustment on scroll</li>
                    <li>Elegant shadow effects</li>
                    <li>Full Dark Mode support</li>
                    <li>Consistent design across doctor and secretary interfaces</li>
                    <li>Smooth transitions and animations</li>
                    <li>RTL layout support for Arabic interface</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p class="clinic-version" dir="ltr" style="text-align: center;">
                <span dir="rtl">Version</span> v6.1 
                <a href="/whats-new" class="text-decoration-none whats-new-link" style="margin-right: 0.5rem; margin-left: 0.5rem;">What's New?</a>
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
            
            // Mock header scroll effect
            const mockupHeader = document.getElementById('mockupHeader');
            const scrollContainer = mockupHeader?.parentElement?.querySelector('[style*="overflow-y"]');
            
            if (scrollContainer && mockupHeader) {
                scrollContainer.addEventListener('scroll', () => {
                    if (scrollContainer.scrollTop > 50) {
                        mockupHeader.classList.add('scrolled');
                    } else {
                        mockupHeader.classList.remove('scrolled');
                    }
                });
            }
        });
    </script>
</body>
</html>
