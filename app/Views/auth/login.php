<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Roaya Clinic</title>
    
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
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        
        body {
            background: var(--bg-gradient-light);
            color: var(--text);
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
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
        
        .login-container {
            width: 100%;
            max-width: 500px !important;
            padding: 20px;
        }
        
        .login-card {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.15) 0%, rgba(226, 232, 240, 0.15) 50%, rgba(241, 245, 249, 0.35) 100%) !important;
            border-radius: 20px !important;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            animation: fadeUp 0.5s ease both;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }
        
        .dark .login-card {
            background: linear-gradient(135deg, rgba(11, 18, 32, 0.15) 30%, rgba(22, 33, 62, 0.35) 100%) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7) !important;
        }
        
        .login-container {
            position: relative;
            z-index: 1;
        }
        
        .clinic-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .clinic-logo img {
            max-width: 80px !important;
            height: auto;
            transition: transform 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .clinic-logo img:hover {
            transform: scale(1.1);
        }
        
        .clinic-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.5rem;
        }
        
        .clinic-subtitle {
            color: var(--muted);
            font-size: 0.9rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
            font-size: 18px;
            padding: 7px 30px;
            width: 100%;
        }
        
        .form-floating::after {
            content: "";
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 100%;
            height: 1px;
            width: 85%;
            padding-top: 5px;
            border-top: 1px solid rgba(18, 18, 18, 0.6);
        }
        
        .dark .form-floating::after {
            border-top-color: rgba(255, 255, 255, 0.4);
        }
        
        .form-floating label {
            width: 100%;
            font-size: 17px;
            margin-bottom: 20px;
            line-height: 30px;
            color: var(--text);
            font-weight: 500;
            display: block;
        }
        
        .form-floating .field-icon {
            font-size: 22px;
            height: 30px;
            width: 30px;
            vertical-align: middle;
            text-align: left;
            position: absolute;
            left: 30px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            pointer-events: none;
            z-index: 1;
        }
        
        .form-floating .field-icon.username-icon {
            top: calc(50% + 10px);
        }
        
        .form-floating .field-icon.password-icon {
            top: calc(50% + 10px);
        }
        
        .form-control {
            border: 0;
            background: transparent;
            padding: 0 5px 0 40px;
            color: var(--text);
            font-family: inherit;
            font-size: inherit;
            height: 30px;
            width: calc(100% - 50px);
            vertical-align: middle;
            font-weight: 400;
        }
        
        .form-control:focus {
            outline: none;
            background: transparent;
            border: 0;
            box-shadow: none;
            color: var(--text);
        }
        
        .form-control::placeholder {
            color: rgba(18, 18, 18, 0.7) !important;
        }
        
        .dark .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .form-floating>.form-control, .form-floating>.form-control-plaintext{
            padding: 1rem 2rem !important;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--accent);
            font-weight: 600;
        }
        
        .password-toggle-container {
            position: relative;
        }
        
        .password-toggle-btn {
            position: absolute;
            right: 30px;
            top: calc(50% + 10px);
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            z-index: 10;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }
        
        .password-toggle-btn:hover {
            color: var(--accent);
        }
        
        .password-toggle-btn:focus {
            outline: none;
            color: var(--accent);
        }
        
        .password-toggle-container .form-control {
            padding-right: 50px;
        }
        
        .secure-login-text {
            color: var(--muted);
        }
        
        .dark .secure-login-text {
            color: #94a3b8;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, rgba(11, 18, 32, 0.75) 30%, rgba(22, 33, 62, 0.85) 100%) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            width: 85% !important;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .dark .btn-primary {
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(226, 232, 240, 0.45) 50%, rgba(241, 245, 249, 0.85) 100%) !important;
            backdrop-filter: blur(15px) !important;
            -webkit-backdrop-filter: blur(15px) !important;
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.3) !important;
            color: black !important;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
        }

        .form-check{
            color: rgba(18, 18, 18, 0.7) !important;

            padding-left: 3rem !important;
            padding-top: 1rem !important;
        }

        .dark .form-check{
            color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .form-check-input:checked {
            background-color: rgba(18, 18, 18, 0.7) !important;
            border-color: rgba(18, 18, 18, 0.7) !important;
        }
        .dark .form-check-input:checked {
            background-color: rgba(255, 255, 255, 0.5) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }

        .form-floating>.form-control, .form-floating>.form-control-plaintext, .form-floating>.form-select{
            width: 100% !important;
        }
        
        .btn-primary {
            max-width: 100%;
            width: 100%;
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
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 10px;
            }
            
            .login-card {
                padding: 1.5rem;
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
    <div class="theme-toggle">
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

    <div class="login-container">
        <div class="login-card">
            <div class="clinic-logo">
                <img src="/assets/images/Light.png" alt="Roaya Clinic Logo" id="clinicLogo" style="max-width: 120px; height: auto; transition: transform 0.3s ease;">
                <div class="clinic-name">Roaya Clinic</div>
                <div class="clinic-subtitle">Professional Eye Care Management</div>
            </div>

            <?php if (isset($_SESSION['session_expired']) && $_SESSION['session_expired']): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-clock-history me-2"></i>
                    <?= htmlspecialchars($_SESSION['expired_message'] ?? 'Your session has expired due to inactivity. Please log in again.') ?>
                </div>
                <?php 
                unset($_SESSION['session_expired']);
                unset($_SESSION['expired_message']);
                ?>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $this->url('/login') ?>">
                <?= $this->csrfField() ?>
                
                <div class="form-floating">
                    <i class='bx bx-user field-icon username-icon'></i>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Your username" value="<?= htmlspecialchars($username ?? '') ?>" spellcheck="false" required>
                </div>

                <div class="form-floating password-toggle-container">
                    <i class='bx bx-lock-alt field-icon password-icon'></i>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Your password" required>
                    <button type="button" class="password-toggle-btn" id="passwordToggle" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="passwordToggleIcon"></i>
                    </button>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100" style="margin-top: 25px !important;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button>
            </form>

            <div class="text-center mt-3" style="margin-top: 30px !important;">
                <small class="secure-login-text">
                    <i class="bi bi-shield-check me-1"></i>
                    Secure login system
                </small>
            </div>
            
            <!-- Demo Credentials -->
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme toggle functionality - synced with main layout using appTheme
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
        
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const passwordIcon = document.getElementById('passwordToggleIcon');
                
                if (passwordInput && passwordIcon) {
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        passwordIcon.className = 'fas fa-eye-slash';
                    } else {
                        passwordInput.type = 'password';
                        passwordIcon.className = 'fas fa-eye';
                    }
                }
            });
        }
        
        // Form validation
        const loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                
                if (!username || !password) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                    return false;
                }
                
                if (username.length < 3) {
                    e.preventDefault();
                    alert('Username must be at least 3 characters');
                    return false;
                }
            });
        }
        
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
                const isDark = document.documentElement.classList.contains('dark');
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

