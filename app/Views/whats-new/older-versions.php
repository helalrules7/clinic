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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
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
        
        /* Version 6.0 Mockup Styles */
        .version-mockup-container {
            margin-top: 2rem;
            padding: 1.5rem;
            background: var(--card-light);
            border-radius: 12px;
            border: 1px solid var(--border-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .version-mockup-container {
                background: var(--card-dark);
                border-color: var(--border-dark);
            }
        }
        
        body.dark .version-mockup-container {
            background: var(--card-dark);
            border-color: var(--border-dark);
        }
        
        .features-grid-v6 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 900px) {
            .features-grid-v6 {
                grid-template-columns: 1fr;
            }
        }
        
        .feature-card-v6 {
            background: var(--card-light);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px var(--shadow-light);
            border: 1px solid var(--border-light);
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-card-v6 {
                background: var(--card-dark);
                box-shadow: 0 5px 15px var(--shadow-dark);
                border-color: var(--border-dark);
            }
        }
        
        body.dark .feature-card-v6 {
            background: var(--card-dark);
            box-shadow: 0 5px 15px var(--shadow-dark);
            border-color: var(--border-dark);
        }
        
        .feature-mockup-v6 {
            width: 100%;
            border-radius: 8px;
            margin-top: 1rem;
            border: 2px solid var(--accent-light);
            background: var(--card-light);
            padding: 1rem;
            min-height: 300px;
        }
        
        @media (prefers-color-scheme: dark) {
            .feature-mockup-v6 {
                background: var(--card-dark);
                border-color: var(--accent-dark);
            }
        }
        
        body.dark .feature-mockup-v6 {
            background: var(--card-dark);
            border-color: var(--accent-dark);
        }
        
        /* Media Gallery Mockup */
        .mockup-media-v6 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 12px;
        }
        
        .mockup-media-item-v6 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            animation: pulse 2s ease-in-out infinite;
            cursor: pointer;
            aspect-ratio: 1;
            min-height: 60px;
        }
        
        .mockup-media-item-v6:nth-child(1) { animation-delay: 0s; }
        .mockup-media-item-v6:nth-child(2) { animation-delay: 0.3s; }
        .mockup-media-item-v6:nth-child(3) { animation-delay: 0.6s; }
        .mockup-media-item-v6:nth-child(4) { animation-delay: 0.9s; }
        .mockup-media-item-v6:nth-child(5) { animation-delay: 1.2s; }
        .mockup-media-item-v6:nth-child(6) { animation-delay: 1.5s; }
        
        .mockup-media-image-v6 {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .mockup-media-overlay-v6 {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), transparent);
            padding: 8px;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }
        
        .mockup-media-badge-v6 {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Glasses Mockup */
        .mockup-glasses-v6 {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mockup-glasses-card-v6 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: slideIn 1s ease-out;
            cursor: pointer;
        }
        
        .mockup-glasses-icon-v6 {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }
        
        .mockup-glasses-info-v6 {
            flex: 1;
            color: white;
            font-size: 9px;
        }
        
        /* Medication Mockup */
        .mockup-medications-v6 {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .mockup-medication-group-v6 {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 8px;
            padding: 10px;
            animation: expand 1.5s ease-out;
            cursor: pointer;
        }
        
        .mockup-medication-item-v6 {
            color: white;
            font-size: 10px;
            margin-top: 6px;
        }
        
        /* Ajax Mockup */
        .mockup-ajax-v6 {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .mockup-ajax-section-v6 {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 8px;
            padding: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .mockup-ajax-section-v6::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: ajaxLoad 2s infinite;
        }
        
        .mockup-ajax-content-v6 {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            min-height: 30px;
        }
        
        .mockup-ajax-item-v6 {
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            flex: 1;
            min-width: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .mockup-ajax-item-v6::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: ajaxShine 2s infinite;
        }
        
        /* Performance Charts */
        .performance-charts-v6 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem;
        }
        
        .performance-chart-container-v6 {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 1rem;
            min-height: 150px;
        }
        
        .performance-chart-title-v6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        @media (prefers-color-scheme: dark) {
            .performance-chart-title-v6 {
                color: var(--text-dark);
            }
        }
        
        body.dark .performance-chart-title-v6 {
            color: var(--text-dark);
        }
        
        .performance-chart-canvas-v6 {
            width: 100% !important;
            height: 120px !important;
        }
        
        /* Animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(0.98); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes expand {
            from { transform: scaleY(0); opacity: 0; }
            to { transform: scaleY(1); opacity: 1; }
        }
        
        @keyframes ajaxLoad {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        @keyframes ajaxShine {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .mockup-badge-v6 {
            text-align: center;
            margin-top: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--accent-light);
            font-weight: 500;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-badge-v6 {
                background: rgba(56, 189, 248, 0.1);
                border-color: rgba(56, 189, 248, 0.3);
                color: var(--accent-dark);
            }
        }
        
        body.dark .mockup-badge-v6 {
            background: rgba(56, 189, 248, 0.1);
            border-color: rgba(56, 189, 248, 0.3);
            color: var(--accent-dark);
        }
        
        /* Modals */
        .mockup-modal-v6 {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeInModal 0.3s ease;
        }
        
        .mockup-modal-v6.active {
            display: flex;
        }
        
        .mockup-modal-content-v6 {
            background: var(--card-light);
            border-radius: 20px;
            padding: 2rem;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: scaleInModal 0.3s ease;
        }
        
        @media (prefers-color-scheme: dark) {
            .mockup-modal-content-v6 {
                background: var(--card-dark);
            }
        }
        
        body.dark .mockup-modal-content-v6 {
            background: var(--card-dark);
        }
        
        .mockup-modal-close-v6 {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-light);
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .mockup-modal-close-v6:hover {
            background: rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
        }
        
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleInModal {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
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
                <!-- Version 6.0 -->
                <div class="version-section">
                    <h4 class="version-title">
                        <span class="badge bg-primary me-2">v6.0</span>
                        Media Gallery & Prescriptions Management
                    </h4>
                    <ul class="version-features">
                        <li>Comprehensive media management system organizing all patient images and attachments</li>
                        <li>Group images by patient with thumbnail preview and image count</li>
                        <li>Full-screen carousel viewer for media gallery</li>
                        <li>Patient name filtering with autocomplete</li>
                        <li>Load more pagination system</li>
                        <li>Direct links to appointments and visits</li>
                        <li>Dedicated glasses prescriptions gallery grouped by patient</li>
                        <li>Medication prescriptions organized by patient and appointment</li>
                        <li>Complete Ajax implementation for all operations without page reloads</li>
                        <li>Real-time UI updates with progress indicators</li>
                        <li>Significant performance improvements and optimizations</li>
                        <li>Faster page load times and optimized database queries</li>
                        <li>Efficient pagination and reduced server requests</li>
                        <li>Better caching strategies and improved response times</li>
                    </ul>
                    
                    <!-- Version 6.0 Mockups -->
                    <div class="version-mockup-container">
                        <div class="mockup-badge-v6">
                            <i class="bi bi-cursor me-2"></i>Interactive mockups - Click on items to see how they work
                        </div>
                        
                        <div class="features-grid-v6">
                            <!-- Media Gallery -->
                            <div class="feature-card-v6">
                                <h5 class="mb-3"><i class="bi bi-images me-2"></i>Media Gallery</h5>
                                <div class="feature-mockup-v6">
                                    <div class="mockup-media-v6">
                                        <div class="mockup-media-item-v6" data-image="1">
                                            <img src="https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=200&h=200&fit=crop" alt="Patient 1" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Ahmed Mohamed</div>
                                            <div class="mockup-media-badge-v6">12</div>
                                        </div>
                                        <div class="mockup-media-item-v6" data-image="2">
                                            <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=200&h=200&fit=crop" alt="Patient 2" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Sara Ali</div>
                                            <div class="mockup-media-badge-v6">8</div>
                                        </div>
                                        <div class="mockup-media-item-v6" data-image="3">
                                            <img src="https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop" alt="Patient 3" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Mohamed Hassan</div>
                                            <div class="mockup-media-badge-v6">5</div>
                                        </div>
                                        <div class="mockup-media-item-v6" data-image="4">
                                            <img src="https://images.unsplash.com/photo-1551601651-2a8555f1a136?w=200&h=200&fit=crop" alt="Patient 4" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Fatima Ibrahim</div>
                                            <div class="mockup-media-badge-v6">15</div>
                                        </div>
                                        <div class="mockup-media-item-v6" data-image="5">
                                            <img src="https://images.unsplash.com/photo-1559757175-0eb30cd8c063?w=200&h=200&fit=crop" alt="Patient 5" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Omar Khaled</div>
                                            <div class="mockup-media-badge-v6">3</div>
                                        </div>
                                        <div class="mockup-media-item-v6" data-image="6">
                                            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=200&h=200&fit=crop" alt="Patient 6" class="mockup-media-image-v6">
                                            <div class="mockup-media-overlay-v6">Layla Mahmoud</div>
                                            <div class="mockup-media-badge-v6">9</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Glasses Prescriptions -->
                            <div class="feature-card-v6">
                                <h5 class="mb-3"><i class="bi bi-eyeglasses me-2"></i>Glasses Prescriptions</h5>
                                <div class="feature-mockup-v6">
                                    <div class="mockup-glasses-v6">
                                        <div class="mockup-glasses-card-v6" data-prescription='{"patient":"Ahmed Mohamed","sphereR":"-2.50","sphereL":"-2.00","cylinderR":"-0.75","cylinderL":"-1.00","axisR":"180","axisL":"90","pdR":"32","pdL":"32","lensType":"Progressive"}'>
                                            <div class="mockup-glasses-icon-v6">👓</div>
                                            <div class="mockup-glasses-info-v6">
                                                <div style="font-weight: 600; margin-bottom: 4px;">Progressive Lens</div>
                                                <div>Sphere: R: -2.50 | L: -2.00</div>
                                                <div>Cylinder: R: -0.75 | L: -1.00</div>
                                            </div>
                                        </div>
                                        <div class="mockup-glasses-card-v6" style="animation-delay: 0.2s;" data-prescription='{"patient":"Sara Ali","sphereR":"+1.50","sphereL":"+1.75","cylinderR":"-0.50","cylinderL":"-0.50","axisR":"90","axisL":"90","pdR":"30","pdL":"30","lensType":"Single Vision"}'>
                                            <div class="mockup-glasses-icon-v6">👓</div>
                                            <div class="mockup-glasses-info-v6">
                                                <div style="font-weight: 600; margin-bottom: 4px;">Single Vision</div>
                                                <div>Sphere: R: +1.50 | L: +1.75</div>
                                                <div>Cylinder: R: -0.50 | L: -0.50</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Medication Prescriptions -->
                            <div class="feature-card-v6">
                                <h5 class="mb-3"><i class="bi bi-capsule me-2"></i>Medication Prescriptions</h5>
                                <div class="feature-mockup-v6">
                                    <div class="mockup-medications-v6">
                                        <div class="mockup-medication-group-v6" data-medication='{"patient":"Ahmed Mohamed","appointmentDate":"2025-01-15","appointmentTime":"10:00 AM","drugs":[{"name":"Paracetamol","dose":"500mg","frequency":"3 times daily","duration":"5 days","route":"Oral"},{"name":"Amoxicillin","dose":"250mg","frequency":"2 times daily","duration":"7 days","route":"Oral"}]}'>
                                            <div style="font-weight: 600; color: white; margin-bottom: 6px;">Appointment #123</div>
                                            <div class="mockup-medication-item-v6">Paracetamol - 500mg - 3x daily - 5 days</div>
                                            <div class="mockup-medication-item-v6">Amoxicillin - 250mg - 2x daily - 7 days</div>
                                        </div>
                                        <div class="mockup-medication-group-v6" style="opacity: 0.7;" data-medication='{"patient":"Sara Ali","appointmentDate":"2025-01-14","appointmentTime":"2:30 PM","drugs":[{"name":"Ibuprofen","dose":"400mg","frequency":"2 times daily","duration":"3 days","route":"Oral"}]}'>
                                            <div style="font-weight: 600; color: white; margin-bottom: 6px;">Appointment #122</div>
                                            <div class="mockup-medication-item-v6">Ibuprofen - 400mg - 2x daily - 3 days</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ajax Operations -->
                            <div class="feature-card-v6">
                                <h5 class="mb-3"><i class="bi bi-lightning-charge me-2"></i>Ajax Operations</h5>
                                <div class="feature-mockup-v6">
                                    <div class="mockup-ajax-v6">
                                        <div class="mockup-ajax-section-v6">
                                            <div style="color: white; font-weight: 600; margin-bottom: 8px;">📁 Files</div>
                                            <div class="mockup-ajax-content-v6" id="ajaxContent1V6">
                                                <div class="mockup-ajax-item-v6"></div>
                                                <div class="mockup-ajax-item-v6"></div>
                                            </div>
                                        </div>
                                        <div class="mockup-ajax-section-v6" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                            <div style="color: white; font-weight: 600; margin-bottom: 8px;">💊 Medications</div>
                                            <div class="mockup-ajax-content-v6" id="ajaxContent2V6">
                                                <div class="mockup-ajax-item-v6"></div>
                                            </div>
                                        </div>
                                        <div class="mockup-ajax-section-v6" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            <div style="color: white; font-weight: 600; margin-bottom: 8px;">👓 Glasses</div>
                                            <div class="mockup-ajax-content-v6" id="ajaxContent3V6">
                                                <div class="mockup-ajax-item-v6"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Performance Charts -->
                        <div class="feature-card-v6" style="margin-top: 1.5rem;">
                            <h5 class="mb-3"><i class="bi bi-speedometer2 me-2"></i>Performance & Speed</h5>
                            <div class="performance-charts-v6">
                                <div class="performance-chart-container-v6">
                                    <div class="performance-chart-title-v6">Page Load Times</div>
                                    <canvas id="pageLoadChartV6" class="performance-chart-canvas-v6"></canvas>
                                </div>
                                <div class="performance-chart-container-v6">
                                    <div class="performance-chart-title-v6">Database Queries</div>
                                    <canvas id="databaseChartV6" class="performance-chart-canvas-v6"></canvas>
                                </div>
                                <div class="performance-chart-container-v6">
                                    <div class="performance-chart-title-v6">Pagination Efficiency</div>
                                    <canvas id="paginationChartV6" class="performance-chart-canvas-v6"></canvas>
                                </div>
                                <div class="performance-chart-container-v6">
                                    <div class="performance-chart-title-v6">Server Requests</div>
                                    <canvas id="requestsChartV6" class="performance-chart-canvas-v6"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Media Modal -->
                    <div class="mockup-modal-v6" id="mockupMediaModalV6">
                        <div class="mockup-modal-content-v6">
                            <button class="mockup-modal-close-v6" id="mockupMediaModalCloseV6">
                                <i class="bi bi-x"></i>
                            </button>
                            <img src="" alt="Patient Image" class="img-fluid rounded" id="mockupMediaModalImageV6">
                        </div>
                    </div>
                    
                    <!-- Glasses Modal -->
                    <div class="mockup-modal-v6" id="mockupGlassesModalV6">
                        <div class="mockup-modal-content-v6">
                            <button class="mockup-modal-close-v6" id="mockupGlassesModalCloseV6">
                                <i class="bi bi-x"></i>
                            </button>
                            <h5 class="mb-3" id="mockupGlassesModalTitleV6">Glasses Prescription</h5>
                            <div id="mockupGlassesModalPrescriptionV6"></div>
                        </div>
                    </div>
                    
                    <!-- Medication Modal -->
                    <div class="mockup-modal-v6" id="mockupMedicationModalV6">
                        <div class="mockup-modal-content-v6">
                            <button class="mockup-modal-close-v6" id="mockupMedicationModalCloseV6">
                                <i class="bi bi-x"></i>
                            </button>
                            <h5 class="mb-3" id="mockupMedicationModalTitleV6">Medication Prescription</h5>
                            <div id="mockupMedicationModalContentV6"></div>
                        </div>
                    </div>
                </div>

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
                <span dir="rtl">Version</span> v6.1 
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
            
            // Version 6.0 Mockups Setup
            setupV6Mockups();
            setupV6PerformanceCharts();
            setupV6AjaxAnimation();
        });
        
        // Version 6.0 Media Modal
        function setupV6Mockups() {
            // Media Gallery Modal
            const mediaModal = document.getElementById('mockupMediaModalV6');
            const mediaModalImage = document.getElementById('mockupMediaModalImageV6');
            const mediaModalClose = document.getElementById('mockupMediaModalCloseV6');
            const mediaItems = document.querySelectorAll('.mockup-media-item-v6');
            
            if (mediaModal && mediaModalImage && mediaModalClose) {
                mediaItems.forEach((item) => {
                    item.addEventListener('click', function() {
                        const img = this.querySelector('.mockup-media-image-v6');
                        if (img && img.src) {
                            mediaModalImage.src = img.src;
                            mediaModalImage.alt = img.alt;
                            mediaModal.classList.add('active');
                            document.body.style.overflow = 'hidden';
                        }
                    });
                });
                
                mediaModalClose.addEventListener('click', () => {
                    mediaModal.classList.remove('active');
                    document.body.style.overflow = '';
                });
                
                mediaModal.addEventListener('click', (e) => {
                    if (e.target === mediaModal) {
                        mediaModal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
            
            // Glasses Modal
            const glassesModal = document.getElementById('mockupGlassesModalV6');
            const glassesModalTitle = document.getElementById('mockupGlassesModalTitleV6');
            const glassesModalPrescription = document.getElementById('mockupGlassesModalPrescriptionV6');
            const glassesModalClose = document.getElementById('mockupGlassesModalCloseV6');
            const glassesCards = document.querySelectorAll('.mockup-glasses-card-v6');
            
            if (glassesModal && glassesModalTitle && glassesModalPrescription && glassesModalClose) {
                glassesCards.forEach((card) => {
                    card.addEventListener('click', function() {
                        const prescriptionData = this.getAttribute('data-prescription');
                        if (prescriptionData) {
                            try {
                                const prescription = JSON.parse(prescriptionData);
                                glassesModalTitle.textContent = `${prescription.patient} - Glasses Prescription`;
                                glassesModalPrescription.innerHTML = `
                                    <div class="mb-2"><strong>Patient:</strong> ${prescription.patient}</div>
                                    <div class="mb-2"><strong>Lens Type:</strong> ${prescription.lensType}</div>
                                    <div class="mb-2"><strong>Sphere (R):</strong> ${prescription.sphereR} D</div>
                                    <div class="mb-2"><strong>Sphere (L):</strong> ${prescription.sphereL} D</div>
                                    <div class="mb-2"><strong>Cylinder (R):</strong> ${prescription.cylinderR} D</div>
                                    <div class="mb-2"><strong>Cylinder (L):</strong> ${prescription.cylinderL} D</div>
                                    <div class="mb-2"><strong>Axis (R):</strong> ${prescription.axisR}°</div>
                                    <div class="mb-2"><strong>Axis (L):</strong> ${prescription.axisL}°</div>
                                    <div class="mb-2"><strong>PD (R):</strong> ${prescription.pdR} mm</div>
                                    <div><strong>PD (L):</strong> ${prescription.pdL} mm</div>
                                `;
                                glassesModal.classList.add('active');
                                document.body.style.overflow = 'hidden';
                            } catch (e) {
                                console.error('Error parsing prescription data:', e);
                            }
                        }
                    });
                });
                
                glassesModalClose.addEventListener('click', () => {
                    glassesModal.classList.remove('active');
                    document.body.style.overflow = '';
                });
                
                glassesModal.addEventListener('click', (e) => {
                    if (e.target === glassesModal) {
                        glassesModal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
            
            // Medication Modal
            const medicationModal = document.getElementById('mockupMedicationModalV6');
            const medicationModalTitle = document.getElementById('mockupMedicationModalTitleV6');
            const medicationModalContent = document.getElementById('mockupMedicationModalContentV6');
            const medicationModalClose = document.getElementById('mockupMedicationModalCloseV6');
            const medicationGroups = document.querySelectorAll('.mockup-medication-group-v6');
            
            if (medicationModal && medicationModalTitle && medicationModalContent && medicationModalClose) {
                medicationGroups.forEach((group) => {
                    group.addEventListener('click', function() {
                        const medicationData = this.getAttribute('data-medication');
                        if (medicationData) {
                            try {
                                const medication = JSON.parse(medicationData);
                                medicationModalTitle.textContent = `${medication.patient} - Medication Prescription`;
                                let drugsHTML = '';
                                medication.drugs.forEach((drug) => {
                                    drugsHTML += `
                                        <div class="mb-3 p-3 border rounded">
                                            <div class="fw-bold mb-2">${drug.name}</div>
                                            <div><strong>Dose:</strong> ${drug.dose}</div>
                                            <div><strong>Frequency:</strong> ${drug.frequency}</div>
                                            <div><strong>Duration:</strong> ${drug.duration}</div>
                                            <div><strong>Route:</strong> ${drug.route}</div>
                                        </div>
                                    `;
                                });
                                medicationModalContent.innerHTML = `
                                    <div class="mb-3">
                                        <strong>Appointment:</strong> ${medication.appointmentDate} at ${medication.appointmentTime}
                                    </div>
                                    ${drugsHTML}
                                `;
                                medicationModal.classList.add('active');
                                document.body.style.overflow = 'hidden';
                            } catch (e) {
                                console.error('Error parsing medication data:', e);
                            }
                        }
                    });
                });
                
                medicationModalClose.addEventListener('click', () => {
                    medicationModal.classList.remove('active');
                    document.body.style.overflow = '';
                });
                
                medicationModal.addEventListener('click', (e) => {
                    if (e.target === medicationModal) {
                        medicationModal.classList.remove('active');
                        document.body.style.overflow = '';
                    }
                });
            }
        }
        
        // Version 6.0 Performance Charts
        function setupV6PerformanceCharts() {
            const isDark = document.body.classList.contains('dark') || window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDark ? '#e5e7eb' : '#2c3e50';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            const beforeColor = isDark ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.6)';
            const afterColor = isDark ? 'rgba(16, 185, 129, 0.8)' : 'rgba(16, 185, 129, 0.6)';
            
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: textColor,
                            font: { size: 10 },
                            padding: 6,
                            boxWidth: 10
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: isDark ? 'rgba(0, 0, 0, 0.8)' : 'rgba(0, 0, 0, 0.7)',
                        titleColor: textColor,
                        bodyColor: textColor
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: textColor,
                            font: { size: 10 }
                        },
                        grid: { color: gridColor }
                    },
                    x: {
                        ticks: {
                            color: textColor,
                            font: { size: 10 }
                        },
                        grid: { color: gridColor }
                    }
                }
            };
            
            // Page Load Times Chart
            const pageLoadCtx = document.getElementById('pageLoadChartV6');
            if (pageLoadCtx && typeof Chart !== 'undefined') {
                new Chart(pageLoadCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Load Time (ms)',
                            data: [3200, 850],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Database Queries Chart
            const databaseCtx = document.getElementById('databaseChartV6');
            if (databaseCtx && typeof Chart !== 'undefined') {
                new Chart(databaseCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Query Time (ms)',
                            data: [450, 120],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Pagination Efficiency Chart
            const paginationCtx = document.getElementById('paginationChartV6');
            if (paginationCtx && typeof Chart !== 'undefined') {
                new Chart(paginationCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Items per Request',
                            data: [10, 50],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
            
            // Server Requests Chart
            const requestsCtx = document.getElementById('requestsChartV6');
            if (requestsCtx && typeof Chart !== 'undefined') {
                new Chart(requestsCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Before', 'After'],
                        datasets: [{
                            label: 'Requests per Page',
                            data: [15, 3],
                            backgroundColor: [beforeColor, afterColor],
                            borderRadius: 4
                        }]
                    },
                    options: chartOptions
                });
            }
        }
        
        // Version 6.0 Ajax Animation
        function setupV6AjaxAnimation() {
            const section1 = document.getElementById('ajaxContent1V6');
            const section2 = document.getElementById('ajaxContent2V6');
            const section3 = document.getElementById('ajaxContent3V6');
            
            if (!section1 || !section2 || !section3) return;
            
            let currentStep = 0;
            let isAnimating = false;
            
            function createItem() {
                const item = document.createElement('div');
                item.className = 'mockup-ajax-item-v6';
                return item;
            }
            
            function resetSections() {
                section1.innerHTML = '';
                section2.innerHTML = '';
                section3.innerHTML = '';
            }
            
            function animateStep() {
                if (isAnimating) return;
                isAnimating = true;
                
                switch(currentStep) {
                    case 0:
                        const file1 = createItem();
                        section1.appendChild(file1);
                        break;
                    case 1:
                        const file2 = createItem();
                        section1.appendChild(file2);
                        break;
                    case 2:
                        const med1 = createItem();
                        section2.appendChild(med1);
                        break;
                    case 3:
                        const glass1 = createItem();
                        section3.appendChild(glass1);
                        break;
                    case 4:
                        const file3 = createItem();
                        section1.appendChild(file3);
                        break;
                    case 5:
                        const med2 = createItem();
                        section2.appendChild(med2);
                        break;
                    case 6:
                        if (section1.firstChild) {
                            section1.removeChild(section1.firstChild);
                        }
                        break;
                    case 7:
                        const glass2 = createItem();
                        section3.appendChild(glass2);
                        break;
                }
                
                setTimeout(() => {
                    isAnimating = false;
                    currentStep++;
                    
                    if (currentStep > 7) {
                        currentStep = 0;
                        setTimeout(() => {
                            resetSections();
                            setTimeout(() => animateStep(), 500);
                        }, 1000);
                    } else {
                        animateStep();
                    }
                }, 1500);
            }
            
            setTimeout(() => {
                animateStep();
            }, 2000);
        }
    </script>
</body>
</html>

