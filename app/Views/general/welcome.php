<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام عيادة طب العيون - Roaya Clinic</title>
    
    <!-- Meta Description -->
    <meta name="description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta name="keywords" content="clinic, eye care, ophthalmology, medical, healthcare">
    <meta name="author" content="Ahmed Helal">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://roaya.hclinic.clinic/">
    <meta property="og:title" content="نظام عيادة طب العيون - Roaya Clinic">
    <meta property="og:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
    <meta property="og:image" content="https://roaya.hclinic.clinic/assets/images/Light.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="HClinic / Roaya Clinic">
    <meta property="og:locale" content="ar_EG">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://roaya.hclinic.clinic/">
    <meta name="twitter:title" content="نظام عيادة طب العيون - Roaya Clinic">
    <meta name="twitter:description" content="HClinic / Roaya Clinic - Advanced Eye Care Management System">
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
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/Light.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/assets/images/Light.png">
    
    <style>
        :root {
            --bg-gradient-light: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
            --bg-gradient-dark: linear-gradient(135deg, #0b1220 0%, #16213e 100%);
            /* Indigo palette (standalone page — no design-system tokens.css here) */
            --bg: #F8FAFC; --card: #FFFFFF; --text: #0F172A; --muted: #64748B;
            --accent: #4F46E5; --accent-rgb: 79, 70, 229;
            --success: #10B981; --danger: #EF4444; --border: #E2E8F0;
        }

        .dark {
            --bg: #070B14; --card: #131A29; --text: #F8FAFC; --muted: #94A3B8;
            --accent: #6366F1; --accent-rgb: 99, 102, 241;
            --success: #34D399; --danger: #FB7185; --border: #334155;
        }

        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        body {
            background: var(--bg-gradient-light);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        body.dark {
            background: var(--bg-gradient-dark);
        }
        
        #waveContainer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        
        #waveCanvas {
            width: 100%;
            height: 100%;
            display: block;
        }
        
        .welcome-card {
            position: relative;
            z-index: 1;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.15) 0%, rgba(226, 232, 240, 0.15) 50%, rgba(241, 245, 249, 0.35) 100%) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            color: var(--text);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            margin: 2rem;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        body.dark .welcome-card {
            background: linear-gradient(135deg, rgba(11, 18, 32, 0.15) 30%, rgba(22, 33, 62, 0.35) 100%) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7) !important;
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
        
        .clinic-name {
            font-size: 1.5rem !important;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .clinic-subtitle {
            font-size: 1.2rem;
            color: var(--muted);
            margin-bottom: 2rem;
        }
        
        .enter-btn {
            background: linear-gradient(135deg, rgba(11, 18, 32, 0.75) 30%, rgba(22, 33, 62, 0.85) 100%) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
            /* background: linear-gradient(to bottom right, #000000, #646773); */
            border: none;
            padding: 1rem 3rem;
            font-size: 1.2rem;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            /* box-shadow: 0 10px 20px rgba(14, 165, 233, 0.3); */
            font-weight: 600;
        }
        
        .enter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.4);
            color: white;
            background: linear-gradient(135deg, #667eea 0%, var(--accent) 100%);
        }
        
        body.dark .enter-btn {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(226, 232, 240, 0.45) 50%, rgba(241, 245, 249, 0.85) 100%) !important;
            backdrop-filter: blur(15px) !important;
            -webkit-backdrop-filter: blur(15px) !important;
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3) !important;
            color: black !important;
        }
        
        body.dark .enter-btn:hover {
            background: linear-gradient(135deg, #4F46E5 0%, var(--accent) 100%);
            box-shadow: 0 15px 30px rgba(56, 189, 248, 0.4);
        }
        
        .features {
            margin-top: 3rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .feature {
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            transition: background-color 0.3s ease;
        }
        
        .feature:hover {
            background: rgba(14, 165, 233, 0.1);
        }
        
        body.dark .feature:hover {
            background: rgba(56, 189, 248, 0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .feature-title {
            font-weight: bold;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .feature-desc {
            color: var(--muted);
            font-size: 0.9rem;
        }
        
        .system-info {
            background: var(--card);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: right;
            border: 1px solid var(--border);
        }
        
        .info-title {
            font-weight: bold;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .info-item {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            color: var(--muted);
        }
        
        /* Clinic Version and Author Styles */
        .clinic-version {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            text-align: center;
            direction: ltr;
        }
        
        .clinic-version span {
            direction: rtl;
            display: inline-block;
        }
        
        .clinic-author {
            color: var(--muted);
            font-size: 0.85rem;
            margin-bottom: 1rem;
            text-align: center;
            direction: ltr;
        }
        
        .whats-new-link {
            color: var(--accent) !important;
            font-weight: 500;
            text-decoration: underline !important;
            transition: color 0.2s ease;
        }
        
        .whats-new-link:hover {
            color: var(--accent) !important;
            opacity: 0.8;
        }
        
        .sidebar-footer-link {
            color: var(--accent) !important;
            transition: color 0.2s ease;
        }
        
        .sidebar-footer-link:hover {
            color: var(--accent) !important;
            opacity: 0.8;
        }
        
        /* Theme Toggle Switch - Same as top-bar */
        :root {
            --scale: 1;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: calc(var(--scale) * 60px);
            height: calc(var(--scale) * 34px);
        }
        
        .switch #themeToggleInput {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #2196f3;
            transition: 0.4s;
            z-index: 0;
            overflow: hidden;
        }
        
        .sun-moon {
            position: absolute;
            content: "";
            height: calc(var(--scale) * 26px);
            width: calc(var(--scale) * 26px);
            left: calc(var(--scale) * 4px);
            bottom: calc(var(--scale) * 4px);
            background-color: yellow;
            transition: 0.4s;
        }
        
        #themeToggleInput:checked + .slider {
            background-color: black;
        }
        
        #themeToggleInput:focus + .slider {
            box-shadow: 0 0 calc(var(--scale) * 1px) #2196f3;
        }
        
        #themeToggleInput:checked + .slider .sun-moon {
            transform: translateX(calc(var(--scale) * 26px));
            background-color: white;
            animation: rotate-center 0.6s ease-in-out both;
        }
        
        .moon-dot {
            opacity: 0;
            transition: 0.4s;
            fill: gray;
        }
        
        #themeToggleInput:checked + .slider .sun-moon .moon-dot {
            opacity: 1;
        }
        
        .slider.round {
            border-radius: calc(var(--scale) * 34px);
        }
        
        .slider.round .sun-moon {
            border-radius: 50%;
        }
        
        #moon-dot-1 {
            left: calc(var(--scale) * 10px);
            top: calc(var(--scale) * 3px);
            position: absolute;
            width: calc(var(--scale) * 6px);
            height: calc(var(--scale) * 6px);
            z-index: 4;
        }
        
        #moon-dot-2 {
            left: calc(var(--scale) * 2px);
            top: calc(var(--scale) * 10px);
            position: absolute;
            width: calc(var(--scale) * 10px);
            height: calc(var(--scale) * 10px);
            z-index: 4;
        }
        
        #moon-dot-3 {
            left: calc(var(--scale) * 16px);
            top: calc(var(--scale) * 18px);
            position: absolute;
            width: calc(var(--scale) * 3px);
            height: calc(var(--scale) * 3px);
            z-index: 4;
        }
        
        #light-ray-1,
        #light-ray-3,
        #light-ray-2 {
            position: absolute;
            z-index: -1;
            fill: white;
            opacity: 10%;
        }
        
        #light-ray-1 {
            left: calc(var(--scale) * -8px);
            top: calc(var(--scale) * -8px);
            width: calc(var(--scale) * 43px);
            height: calc(var(--scale) * 43px);
        }
        
        #light-ray-2 {
            left: -50%;
            top: -50%;
            width: calc(var(--scale) * 55px);
            height: calc(var(--scale) * 55px);
        }
        
        #light-ray-3 {
            left: calc(var(--scale) * -18px);
            top: calc(var(--scale) * -18px);
            width: calc(var(--scale) * 60px);
            height: calc(var(--scale) * 60px);
        }
        
        .cloud-light,
        .cloud-dark {
            position: absolute;
            animation-name: cloud-move;
            animation-duration: 6s;
            animation-iteration-count: infinite;
        }
        
        .cloud-light {
            fill: #eee;
        }
        
        .cloud-dark {
            fill: #ccc;
            animation-delay: 1s;
        }
        
        #cloud-1 {
            left: calc(var(--scale) * 30px);
            top: calc(var(--scale) * 15px);
            width: calc(var(--scale) * 40px);
        }
        
        #cloud-2 {
            left: calc(var(--scale) * 44px);
            top: calc(var(--scale) * 10px);
            width: calc(var(--scale) * 20px);
        }
        
        #cloud-3 {
            left: calc(var(--scale) * 18px);
            top: calc(var(--scale) * 24px);
            width: calc(var(--scale) * 30px);
        }
        
        #cloud-4 {
            left: calc(var(--scale) * 36px);
            top: calc(var(--scale) * 18px);
            width: calc(var(--scale) * 40px);
        }
        
        #cloud-5 {
            left: calc(var(--scale) * 48px);
            top: calc(var(--scale) * 14px);
            width: calc(var(--scale) * 20px);
        }
        
        #cloud-6 {
            left: calc(var(--scale) * 22px);
            top: calc(var(--scale) * 26px);
            width: calc(var(--scale) * 30px);
        }
        
        @keyframes cloud-move {
            0% {
                transform: translateX(0px);
            }
            40% {
                transform: translateX(calc(var(--scale) * 4px));
            }
            80% {
                transform: translateX(calc(var(--scale) * -4px));
            }
            100% {
                transform: translateX(0px);
            }
        }
        
        @keyframes rotate-center {
            0% {
                transform: translateX(calc(var(--scale) * 26px)) rotate(0);
            }
            100% {
                transform: translateX(calc(var(--scale) * 26px)) rotate(360deg);
            }
        }
        
        .stars {
            transform: translateY(calc(var(--scale) * -32px));
            opacity: 0;
            transition: 0.4s;
        }
        
        .star {
            fill: white;
            position: absolute;
            transition: 0.4s;
            animation-name: star-twinkle;
            animation-duration: 2s;
            animation-iteration-count: infinite;
        }
        
        #themeToggleInput:checked + .slider .stars {
            transform: translateY(0);
            opacity: 1;
        }
        
        #star-1 {
            width: calc(var(--scale) * 20px);
            top: calc(var(--scale) * 2px);
            left: calc(var(--scale) * 3px);
            animation-delay: 0.3s;
        }
        
        #star-2 {
            width: calc(var(--scale) * 6px);
            top: calc(var(--scale) * 16px);
            left: calc(var(--scale) * 3px);
        }
        
        #star-3 {
            width: calc(var(--scale) * 12px);
            top: calc(var(--scale) * 20px);
            left: calc(var(--scale) * 10px);
            animation-delay: 0.6s;
        }
        
        #star-4 {
            width: calc(var(--scale) * 18px);
            top: calc(var(--scale) * 0px);
            left: calc(var(--scale) * 18px);
            animation-delay: 1.3s;
        }
        
        @keyframes star-twinkle {
            0% {
                transform: scale(1);
            }
            40% {
                transform: scale(1.2);
            }
            80% {
                transform: scale(0.8);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-card {
                padding: 2rem;
                margin: 1rem;
            }
            
            .clinic-name {
                font-size: 2rem;
            }
            
            .clinic-subtitle {
                font-size: 1rem;
            }
            
            .enter-btn {
                padding: 0.875rem 2rem;
                font-size: 1rem;
            }
            
            .dark-mode-toggle {
                top: 10px;
                right: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Wave Animation Canvas -->
    <div id="waveContainer">
        <canvas id="waveCanvas"></canvas>
    </div>
    
    <!-- Theme Toggle Switch -->
    <div class="dark-mode-toggle">
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
    
    <div class="welcome-card">
        <div class="logo">
            <img src="/assets/images/Light.png" alt="Roaya Clinic Logo" id="clinicLogo" style="max-width: 120px; height: auto; transition: transform 0.3s ease;">
        </div>
        
        <h3 class="clinic-name">نظام إدارة عيادة رؤية لطب وجراحة العيون</h3>
        <p class="clinic-subtitle">Hclinic / Roaya Ophthalmology Clinic Management System</p>
        <p class="clinic-version" dir="ltr" style="text-align: center;">
            <span dir="rtl">Version</span> 7.2.8
            <a href="https://hclinic.clinic/docs/opth/" class="text-decoration-none whats-new-link" style="margin-right: 0.5rem; margin-left: 0.5rem;" target="_blank" rel="noopener noreferrer">What's New?</a>
        </p>
        <p class="clinic-author" dir="ltr" style="text-align: center;">
            HClinic / Roaya © 2025 <a href="https://ahmedhelal.dev" target="_blank" class="text-decoration-none sidebar-footer-link">Ahmed Helal</a>
        </p>

        <a href="/login" class="enter-btn">
            <i class="bi bi-arrow-left-circle me-2"></i>
            دخول النظام
        </a>
    </div>
    
    <script>
        // Theme toggle functionality - synced with main layout using appTheme
        const apply = mode => {
            const isDark = mode === 'dark';
            document.documentElement.classList.toggle('dark', isDark);
            document.body.classList.toggle('dark', isDark);
        };
        
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
        }
        
        // Get saved theme - check both keys for compatibility
        const saved = localStorage.getItem('appTheme') || localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply initial theme
        apply(saved);
        updateThemeUI(saved);
        
        // Theme toggle checkbox change handler
        const themeToggleInput = document.getElementById('themeToggleInput');
        if (themeToggleInput) {
            themeToggleInput.addEventListener('change', function() {
                const nextTheme = this.checked ? 'dark' : 'light';
                
                // Apply theme immediately
                apply(nextTheme);
                
                // Update UI elements
                updateThemeUI(nextTheme);
                
                // Save to both keys for compatibility
                localStorage.setItem('appTheme', nextTheme);
                localStorage.setItem('theme', nextTheme);
            });
        }
        
        // Update favicon based on theme
        function updateFavicon() {
            const favicon = document.getElementById('favicon');
            const faviconDark = document.getElementById('favicon-dark');
            
            if (document.body.classList.contains('dark')) {
                favicon.setAttribute('href', '/assets/images/Light.png');
            } else {
                favicon.setAttribute('href', '/assets/images/Dark.png');
            }
        }
        
        // Update favicon when theme changes
        if (themeToggleInput) {
            themeToggleInput.addEventListener('change', updateFavicon);
        }
        updateFavicon();
        
        // Wave Animation
        (function() {
            const c = document.getElementById('waveCanvas');
            if (!c) return;
            
            const $ = c.getContext('2d');
            let w, h;
            
            function resizeCanvas() {
                w = c.width = window.innerWidth;
                h = c.height = window.innerHeight;
            }
            
            resizeCanvas();
            
            function getWaveColors() {
                const isDark = document.body.classList.contains('dark') || document.documentElement.classList.contains('dark');
                return {
                    background: isDark ? 'rgba(11, 18, 32, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                    stroke: isDark ? 'hsla(210, 30%, 60%, 0.3)' : 'hsla(210, 15%, 40%, 0.3)'
                };
            }
            
            function draw(a, b, t) {
                const colors = getWaveColors();
                $.fillStyle = colors.background;
                $.fillRect(0, 0, w, h);
                
                for (var i = -60; i < 60; i += 1) {
                    $.strokeStyle = colors.stroke;
                    $.lineWidth = 0.3;
                    $.beginPath();
                    $.moveTo(0, h / 2);
                    for (var j = 0; j < w; j += 1) {
                        $.lineTo(
                            10 * Math.sin(i / 10) + j + 0.008 * j * j,
                            Math.floor(
                                h / 2 +
                                    (j / 2) * Math.sin(j / 50 - t / 50 - i / 80) +
                                    i * 0.9 * Math.sin(j / 25 - (i + t) / 95)
                            )
                        );
                    }
                    $.stroke();
                }
            }
            
            let t = 0;
            
            window.addEventListener('resize', function() {
                resizeCanvas();
            }, false);
            
            function run() {
                window.requestAnimationFrame(run);
                t += 1;
                draw(33, 52 * Math.sin(t / 2500), t);
            }
            
            run();
            
            // Update wave colors when theme changes
            const themeToggleInput = document.getElementById('themeToggleInput');
            if (themeToggleInput) {
                themeToggleInput.addEventListener('change', function() {
                    // Redraw immediately with new colors
                    draw(33, 52 * Math.sin(t / 2500), t);
                });
            }
        })();
    </script>
</body>
</html>

