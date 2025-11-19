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
            --user-info-bg: rgb(248, 250, 252);
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
            --user-info-bg: rgb(30, 41, 59);
        }
        
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Prevent flash of wrong theme - default to dark */
        html:not(.theme-loaded) {
            background: #0b1220;
            color: #f8fafc;
        }
        
        html:not(.theme-loaded) body {
            background: #0b1220;
            color: #f8fafc;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            /* Glass effect - similar to top-bar */
            background: rgba(248, 250, 252, 0.35);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid rgba(226, 232, 240, 0.3);
            box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
            z-index: 1000;
            transition: transform 0.3s ease, background 0.3s ease, border-color 0.3s ease;
            overflow-y: auto;
        }
        
        .dark .sidebar {
            background: rgba(11, 18, 32, 0.70);
            border-right: 1px solid rgba(51, 65, 85, 0.3);
            box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
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
            background: var(--user-info-bg);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            position: relative;
            overflow: visible;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), #10b981);
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        
        .user-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .user-avatar-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), #10b981);
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            font-weight: 600;
            margin-right: 0.75rem;
        }
        
        /* User Avatar Htooltip for Profile Image Preview */
        .user-avatar {
            position: relative;
        }
        
        .user-avatar-htooltip {
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: calc(100% + 15px);
            left: 50%;
            transform: translateX(-50%) translateY(-10px);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
            padding: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .user-avatar:hover .user-avatar-htooltip {
            visibility: visible;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
        
        .user-avatar-preview-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            display: block;
            border-radius: 12px;
            filter: none !important;
            -webkit-filter: none !important;
        }
        
        /* Light mode htooltip for avatar */
        .user-avatar-htooltip {
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(226, 232, 240, 0.5);
        }
        
        /* Dark mode htooltip for avatar */
        .dark .user-avatar-htooltip {
            background: rgba(11, 18, 32, 0.95);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(51, 65, 85, 0.5);
        }
        
        /* Arrow for avatar htooltip - pointing upward */
        .user-avatar-htooltip::after {
            content: "";
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 8px 8px 8px;
            border-color: transparent transparent rgba(248, 250, 252, 0.95) transparent;
        }
        
        .dark .user-avatar-htooltip::after {
            border-color: transparent transparent rgba(11, 18, 32, 0.95) transparent;
        }
        
        .user-details {
            width: 100%;
            text-align: center;
        }
        
        .user-details h6 {
            margin: 0;
            color: var(--text) !important;
            font-weight: 600;
        }
        
        .dark .user-details h6 {
            color: var(--text) !important;
        }
        
        .user-details small {
            color: var(--muted) !important;
            display: block;
        }
        
        .dark .user-details small {
            color: #94a3b8 !important;
        }
        
        .user-name-link {
            text-decoration: none;
            color: inherit;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        .user-name-link:hover {
            color: var(--accent) !important;
            transform: translateY(-1px);
        }
        
        .user-name-link h6 {
            transition: all 0.2s ease;
        }
        
        .user-name-link:hover h6 {
            color: var(--accent) !important;
        }
        
        .dark .user-name-link:hover h6 {
            color: var(--accent) !important;
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
            background: rgba(248, 250, 252, 0.35);
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
            background: rgba(11, 18, 32, 0.70);
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
        
        /* Light mode: moon icon should be black on hover */
        .theme-toggle:hover i.bi-moon {
            color: #000000 !important;
        }
        
        /* Dark mode: sun icon keeps normal color on hover */
        .dark .theme-toggle:hover i.bi-sun {
            color: var(--text) !important;
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
            background: var(--bg) !important;
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
            background: var(--bg) !important;
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
            
            /* Mobile Dock Ring - Minimized by default */
            .quick-access-dock {
                display: block !important;
                bottom: 1.5rem;
                right: 0.1rem;
                left: auto;
                top: auto;
                transform: none;
                z-index: 998;
                transition: all 0.3s ease;
            }
            
            /* When back to top button is hidden, use its exact position */
            .quick-access-dock.mobile-minimized {
                bottom: 1.5rem;
                right: 0.1rem;
                left: auto;
                top: auto;
            }
            
            /* When back to top button is visible, position dock above it with same right alignment */
            .quick-access-dock.mobile-minimized.dock-above-button {
                bottom: calc(1.5rem + 45px + 10px);
                right: 0.1rem;
                left: auto;
                top: auto;
            }
            
            .quick-access-dock.mobile-expanded {
                top: auto;
                bottom: calc(1.5rem + 45px + 10px);
                left: auto;
                right: 0.1rem;
                transform: none;
                width: auto;
                max-width: 280px;
                max-height: calc(100vh - 1.5rem - 45px - 10px - 1.5rem);
                overflow-y: auto;
            }
            
            /* When back to top is hidden and dock is expanded */
            .quick-access-dock.mobile-expanded:not(.dock-above-button) {
                bottom: 1.5rem;
                right: 0.1rem;
            }
            
            /* Ensure dock container aligns perfectly with scroll-to-top button */
            .quick-access-dock.mobile-minimized .dock-container {
                box-sizing: border-box;
                margin: 0;
                width: 45px;
                height: 45px;
                padding: 0;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                background: rgba(248, 250, 252, 0.25);
                backdrop-filter: blur(15px);
                -webkit-backdrop-filter: blur(15px);
                border: 1px solid rgba(226, 232, 240, 0.3);
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            }
            
            .dark .quick-access-dock.mobile-minimized .dock-container {
                background: rgba(11, 18, 32, 0.35);
                border: 1px solid rgba(51, 65, 85, 0.3);
            }
            
            .quick-access-dock.mobile-minimized .dock-item,
            .quick-access-dock.mobile-minimized .dock-divider,
            .quick-access-dock.mobile-minimized .dock-minimize-btn i {
                display: none;
            }
            
            .quick-access-dock.mobile-minimized .dock-minimize-btn {
                display: flex;
                width: 100%;
                height: 100%;
                align-items: center;
                justify-content: center;
                margin: 0;
                padding: 0;
                border-radius: 12px;
                background: transparent;
                border: none;
            }
            
            .quick-access-dock.mobile-minimized .dock-minimize-btn .minimized-icon {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(2, 1fr);
                gap: 2px;
                width: 20px;
                height: 20px;
            }
            
            .quick-access-dock.mobile-minimized .dock-minimize-btn .minimized-icon-rect {
                background: var(--text);
                border-radius: 1px;
            }
            
            .dark .quick-access-dock.mobile-minimized .dock-minimize-btn .minimized-icon-rect {
                background: #ffffff;
            }
            
            /* Hide all dock items on mobile except specific ones */
            .quick-access-dock.mobile-expanded .dock-item {
                display: none;
            }
            
            /* Show only specific items on mobile */
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/calendar"],
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/patients"],
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/drugs"],
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/profile"] {
                display: flex !important;
            }
            
            .quick-access-dock.mobile-expanded .dock-container {
                flex-direction: column;
                align-items: stretch;
                padding: 0.75rem;
                border-radius: 16px;
                max-height: 70vh;
                overflow-y: auto;
                background: rgba(248, 250, 252, 0.4);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(226, 232, 240, 0.3);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
                width: 100%;
                min-width: 200px;
                max-width: 100%;
                flex-shrink: 0;
            }
            
            .dark .quick-access-dock.mobile-expanded .dock-container {
                background: rgba(11, 18, 32, 0.95);
                border: 1px solid rgba(51, 65, 85, 0.3);
            }
            
            .quick-access-dock.mobile-expanded .dock-item {
                width: 100%;
                height: auto;
                min-height: 48px;
                border-radius: 12px;
                margin-bottom: 0.5rem;
                justify-content: flex-start;
                padding: 0.75rem;
                flex-direction: row;
                align-items: center;
            }
            
            .quick-access-dock.mobile-expanded .dock-item i,
            .quick-access-dock.mobile-expanded .dock-profile-image {
                margin-right: 0.75rem;
                flex-shrink: 0;
            }
            
            /* Hide all tooltips on mobile dock */
            .quick-access-dock.mobile-expanded .dock-item .htooltip,
            .quick-access-dock.mobile-minimized .htooltip,
            .quick-access-dock.mobile-expanded .dock-minimize-btn .htooltip {
                display: none !important;
            }
            
            /* Show text labels on mobile expanded dock */
            .quick-access-dock.mobile-expanded .dock-item::after {
                content: attr(title);
                display: block;
                color: var(--text);
                font-size: 0.875rem;
                font-weight: 500;
                white-space: nowrap;
                flex: 1;
            }
            
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/calendar"]::after {
                content: "Calendar";
            }
            
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/patients"]::after {
                content: "Patients";
            }
            
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/drugs"]::after {
                content: "Drugs";
            }
            
            .quick-access-dock.mobile-expanded .dock-item[href="/doctor/profile"]::after {
                content: "Profile";
            }
            
            .quick-access-dock.mobile-expanded .dock-divider {
                display: block;
                width: 100%;
                height: 1px;
                background: var(--border);
                margin: 0.5rem 0;
            }
            
            .quick-access-dock.mobile-expanded .dock-minimize-btn {
                display: flex;
                width: 100%;
                height: 48px;
                border-radius: 12px;
                justify-content: flex-start;
                align-items: center;
                padding: 0 0.75rem;
                margin-top: 0.25rem;
            }
            
            .quick-access-dock.mobile-expanded .dock-minimize-btn i {
                margin-right: 0.75rem;
            }
            
            .quick-access-dock.mobile-expanded .dock-minimize-btn::after {
                content: "Minimize";
                color: var(--text);
                font-size: 0.875rem;
                font-weight: 500;
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
        
        /* Global Modal Glass Effect - Same as sidebar */
        .modal-content {
            /* Glass effect - similar to sidebar */
            background: rgba(248, 250, 252, 0.35) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.3) !important;
            box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.08);
            color: var(--text) !important;
        }
        
        .dark .modal-content {
            background: rgba(11, 18, 32, 0.40) !important;
            border: 1px solid rgba(51, 65, 85, 0.3) !important;
            box-shadow: 2px 0 8px 0 rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            background: transparent !important;
            border-bottom-color: rgba(226, 232, 240, 0.3) !important;
            color: var(--text) !important;
        }
        
        .dark .modal-header {
            border-bottom-color: rgba(51, 65, 85, 0.3) !important;
        }
        
        /* Close button white in dark mode */
        .dark .modal-header .btn-close {
            filter: invert(1) brightness(2);
            opacity: 0.9;
        }
        
        .dark .modal-header .btn-close:hover {
            opacity: 1;
            filter: invert(1) brightness(2.5);
        }
        
        /* Enable dragging */
        .modal-content {
            cursor: move;
        }
        
        .modal-dialog {
            cursor: default;
            transition: transform 0.2s ease;
            margin: 1.75rem auto;
        }
        
        .modal-header {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }
        
        .modal-body {
            background: transparent !important;
            color: var(--text) !important;
        }
        
        .modal-footer {
            background: transparent !important;
            border-top-color: rgba(226, 232, 240, 0.3) !important;
        }
        
        .dark .modal-footer {
            border-top-color: rgba(51, 65, 85, 0.3) !important;
        }
        
        /* Quick Access Dock Styles */
        .quick-access-dock {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: none; /* Hidden by default */
        }
        
        .dock-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            /* Glass effect - maximum transparency */
            background: rgba(248, 250, 252, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(226, 232, 240, 0.2);
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .dark .dock-container {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(51, 65, 85, 0.2);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .dock-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            /* Prevent blur on hover - remove all filter effects */
            filter: none !important;
            -webkit-filter: none !important;
            /* Ensure no blur on text/content */
            will-change: transform;
        }
        
        .dock-item i {
            font-size: 1.5rem;
            transition: color 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--text);
            /* Prevent blur on icon - completely remove all filters */
            filter: none !important;
        }
        
        .dock-profile-image {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: none !important;
        }
        
        .dock-item:hover .dock-profile-image {
            transform: scale(1.6);
            border-color: #1e90ff;
        }
        
        .dock-profile-fallback {
            font-size: 1.5rem;
            transition: color 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--text);
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* Force hardware acceleration for crisp rendering */
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            will-change: color, transform;
        }
        
        /* New Tooltip Style - Top Only - Using htooltip class to avoid conflicts */
        .dock-item {
            position: relative;
            cursor: pointer;
        }
        
        .dock-item .htooltip {
            visibility: hidden;
            z-index: 1001;
            opacity: 0;
            width: auto;
            min-width: 80px;
            padding: 8px 16px;
            position: absolute;
            top: -140%;
            left: 50%;
            transform: translateX(-50%) translateY(9px);
            transition: all 0.3s ease-in-out;
            border-radius: 9px;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
            /* Ensure text is sharp - no blur on text */
            filter: none !important;
            -webkit-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Light mode htooltip - glass effect with light background */
        .dock-item .htooltip {
            background: rgba(248, 250, 252, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #1a1a1a;
            border: 1px solid rgba(226, 232, 240, 0.3);
        }
        
        /* Dark mode htooltip - glass effect with dark background */
        .dark .dock-item .htooltip {
            background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #ffffff;
            border: 1px solid rgba(51, 65, 85, 0.3);
        }
        
        /* Ensure text inside htooltip is always sharp */
        .dock-item .htooltip * {
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* htooltip arrow */
        .dock-item .htooltip::after {
            content: " ";
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 12px 12.5px 0 12.5px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -12px;
        }
        
        /* Light mode arrow */
        .dock-item .htooltip::after {
            border-color: rgba(248, 250, 252, 0.85) transparent transparent transparent;
        }
        
        /* Dark mode arrow */
        .dark .dock-item .htooltip::after {
            border-color: rgba(11, 18, 32, 0.85) transparent transparent transparent;
        }
        
        /* Show htooltip on hover */
        .dock-item:hover .htooltip {
            visibility: visible;
            transform: translateX(-50%) translateY(-10px);
            opacity: 1;
            transition: 0.3s linear;
            animation: tooltipBounce 1s ease-in-out infinite alternate;
        }
        
        @keyframes tooltipBounce {
            0% {
                transform: translateX(-50%) translateY(6px);
            }
            100% {
                transform: translateX(-50%) translateY(1px);
            }
        }
        
        .dock-item:hover {
            transform: translateY(-8px) scale(1.2);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            /* Prevent blur on hover - remove all filter effects */
            filter: none !important;
            -webkit-filter: none !important;
            /* Ensure crisp rendering */
            will-change: transform;
        }
        
        .dock-item:hover i {
            transform: scale(1.1) translateZ(0);
            -webkit-transform: scale(1.1) translateZ(0);
            color: #1e90ff !important; /* Dodger Blue */
            /* Prevent blur on icon hover - completely remove all filters */
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            /* Force hardware acceleration for crisp rendering */
            will-change: color, transform;
        }
        
        
        .dark .dock-item {
            background: rgba(0, 0, 0, 0.15);
            color: var(--text);
            /* Prevent blur in dark mode - remove all filter effects */
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dark .dock-item i {
            color: var(--text);
            /* Prevent blur on icon in dark mode - completely remove all filters */
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            will-change: color, transform;
        }
        
        .dark .dock-item:hover {
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            /* Prevent blur on hover in dark mode - remove all filter effects */
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dark .dock-item:hover i {
            transform: scale(1.1) translateZ(0);
            -webkit-transform: scale(1.1) translateZ(0);
            color: #1e90ff !important; /* Dodger Blue - same for dark mode */
            /* Prevent blur on icon hover in dark mode - completely remove all filters */
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            will-change: color, transform;
        }
        
        /* Show desktop dock only on screens larger than 768px */
        @media (min-width: 769px) {
            .quick-access-dock {
                display: block;
            }
            
            /* Hide mobile dock classes on desktop */
            .quick-access-dock.mobile-minimized,
            .quick-access-dock.mobile-expanded {
                display: none !important;
            }
        }
        
        /* Dock Divider */
        .dock-divider {
            width: 1px;
            height: 32px;
            background: rgba(226, 232, 240, 0.3);
            margin: 0 0.5rem;
        }
        
        .dark .dock-divider {
            background: rgba(51, 65, 85, 0.3);
        }
        
        /* Dock Minimize Button */
        .dock-minimize-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: none;
            cursor: pointer;
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dock-minimize-btn i {
            font-size: 1.5rem;
            transition: color 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: var(--text);
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            will-change: color, transform;
        }
        
        .dock-minimize-btn:hover {
            transform: translateY(-8px) scale(1.2);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dock-minimize-btn:hover i {
            transform: scale(1.1) translateZ(0);
            -webkit-transform: scale(1.1) translateZ(0);
            color: #1e90ff !important; /* Dodger Blue */
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            will-change: color, transform;
        }
        
        .dark .dock-minimize-btn {
            background: rgba(0, 0, 0, 0.15);
            color: var(--text);
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dark .dock-minimize-btn i {
            color: var(--text);
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            will-change: color, transform;
        }
        
        .dark .dock-minimize-btn:hover {
            background: rgba(0, 0, 0, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            filter: none !important;
            -webkit-filter: none !important;
            will-change: transform;
        }
        
        .dark .dock-minimize-btn:hover i {
            transform: scale(1.1) translateZ(0);
            -webkit-transform: scale(1.1) translateZ(0);
            color: #1e90ff !important; /* Dodger Blue */
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            will-change: color, transform;
        }
        
        /* Dock Minimized State */
        .quick-access-dock.minimized {
            left: auto;
            right: calc(2rem + 64px + 1rem); /* Position to the left of scroll to top button */
            transform: none;
            bottom: 2rem; /* Same level as scroll to top button */
        }
        
        .quick-access-dock.minimized .dock-container {
            padding: 0.75rem;
            width: 64px;
            height: 64px;
            justify-content: center;
            align-items: center;
            border-radius: 16px;
        }
        
        .quick-access-dock.minimized .dock-item,
        .quick-access-dock.minimized .dock-divider {
            display: none;
        }
        
        .quick-access-dock.minimized .dock-minimize-btn {
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
        }
        
        /* htooltip for minimize button */
        .dock-minimize-btn .htooltip {
            visibility: hidden;
            z-index: 1001;
            opacity: 0;
            width: auto;
            min-width: 100px;
            padding: 8px 16px;
            position: absolute;
            top: -140%;
            left: 50%;
            transform: translateX(-50%) translateY(9px);
            transition: all 0.3s ease-in-out;
            border-radius: 9px;
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
            pointer-events: none;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
            /* Ensure text is sharp - no blur on text */
            filter: none !important;
            -webkit-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Light mode htooltip for minimize button - glass effect */
        .dock-minimize-btn .htooltip {
            background: rgba(248, 250, 252, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #1a1a1a;
            border: 1px solid rgba(226, 232, 240, 0.3);
        }
        
        /* Dark mode htooltip for minimize button - glass effect */
        .dark .dock-minimize-btn .htooltip {
            background: rgba(11, 18, 32, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #ffffff;
            border: 1px solid rgba(51, 65, 85, 0.3);
        }
        
        /* Ensure text inside minimize button htooltip is always sharp */
        .dock-minimize-btn .htooltip * {
            filter: none !important;
            -webkit-filter: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* htooltip arrow for minimize button */
        .dock-minimize-btn .htooltip::after {
            content: " ";
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 12px 12.5px 0 12.5px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            bottom: -12px;
        }
        
        /* Light mode arrow for minimize button */
        .dock-minimize-btn .htooltip::after {
            border-color: rgba(248, 250, 252, 0.85) transparent transparent transparent;
        }
        
        /* Dark mode arrow for minimize button */
        .dark .dock-minimize-btn .htooltip::after {
            border-color: rgba(11, 18, 32, 0.85) transparent transparent transparent;
        }
        
        /* Show htooltip on hover for minimize button */
        .dock-minimize-btn:hover .htooltip {
            visibility: visible;
            transform: translateX(-50%) translateY(-10px);
            opacity: 1;
            transition: 0.3s linear;
            animation: tooltipBounce 1s ease-in-out infinite alternate;
        }
        
        .dark .quick-access-dock.minimized .dock-minimize-btn {
            background: rgba(0, 0, 0, 0.2);
        }
        
        .quick-access-dock.minimized .dock-minimize-btn i {
            font-size: 1.75rem;
        }
        
        /* Minimized Dock Icon - 4 Squares */
        .dock-minimize-btn .minimized-icon {
            display: none;
            width: 20px;
            height: 20px;
            position: relative;
        }
        
        .quick-access-dock.minimized .dock-minimize-btn .bi-dash {
            display: none;
        }
        
        .quick-access-dock.minimized .dock-minimize-btn .minimized-icon {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 2px;
        }
        
        .minimized-icon-rect {
            background: #000000;
            border-radius: 1px;
        }
        
        /* Dark mode - white squares */
        .dark .minimized-icon-rect {
            background: #ffffff;
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
                    <a href="/doctor/patients" class="nav-link <?= $this->isActiveRoute('/doctor/patients') ? 'active' : '' ?>">
                        <i class="bi bi-people"></i>
                        Patients
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
    
    <!-- Quick Access Dock (Desktop Only) -->
    <div class="quick-access-dock" id="quickAccessDock">
        <div class="dock-container">
            <a href="/doctor/calendar" class="dock-item" title="View Calendar">
                <i class="bi bi-calendar3"></i>
                <span class="htooltip">View Calendar</span>
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
            <a href="/doctor/medications" class="dock-item" title="Prescriptions">
                <i class="bi bi-prescription"></i>
                <span class="htooltip">Prescriptions</span>
            </a>
            <a href="/doctor/glasses" class="dock-item" title="Glasses Prescriptions">
                <i class="bi bi-eyeglasses"></i>
                <span class="htooltip">Glasses Prescriptions</span>
            </a>
            <a href="/doctor/payments" class="dock-item" title="Financial">
                <i class="bi bi-credit-card"></i>
                <span class="htooltip">Financial</span>
            </a>
            <a href="/doctor/media" class="dock-item" title="Media">
                <i class="bi bi-images"></i>
                <span class="htooltip">Media</span>
            </a>
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
                <i class="bi bi-dash"></i>
                <div class="minimized-icon">
                    <span class="minimized-icon-rect"></span>
                    <span class="minimized-icon-rect"></span>
                    <span class="minimized-icon-rect"></span>
                    <span class="minimized-icon-rect"></span>
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
            // Update icon
            const icon = document.querySelector('#themeToggle i');
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
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
        
        // Function to save theme to database
        async function saveThemeToDatabase(theme) {
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
        
        // Initialize theme - Dark by default
        (async function() {
            // Set default to dark immediately to prevent flash
            apply('dark');
            updateThemeUI('dark');
            
            // Load theme from database
            let savedTheme = await loadThemeFromDatabase();
            
            // If no theme in database, default to 'dark'
            if (!savedTheme) {
                savedTheme = 'dark';
                // Save default theme to database
                await saveThemeToDatabase(savedTheme);
            }
            
            // Apply theme from database (may override default)
            apply(savedTheme);
            
            // Update UI elements
            updateThemeUI(savedTheme);
            
            // Mark theme as loaded to remove flash prevention
            document.documentElement.classList.add('theme-loaded');
            
            // Theme toggle button click handler
            const themeToggleBtn = document.getElementById('themeToggle');
            if (themeToggleBtn) {
                themeToggleBtn.onclick = async () => {
                    const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    
                    // Apply theme
                    apply(nextTheme);
                    
                    // Update UI elements
                    updateThemeUI(nextTheme);
                    
                    // Save to database
                    await saveThemeToDatabase(nextTheme);
                };
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
        
        if (scrollToTopBtn) {
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
            
            // Initial position update
            updateMobileDockPosition();
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
    </script>
</body>
</html>
