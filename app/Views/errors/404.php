<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        /* Indigo palette (standalone page — no design-system tokens.css here) */
        :root {
            --bg: #F8FAFC; --card: #FFFFFF; --text: #0F172A; --muted: #64748B;
            --accent: #4F46E5; --accent-rgb: 79, 70, 229;
            --success: #10B981; --danger: #EF4444; --border: #E2E8F0;
        }

        [data-theme="dark"] {
            --bg: #070B14; --card: #131A29; --text: #F8FAFC; --muted: #94A3B8;
            --accent: #6366F1; --accent-rgb: 99, 102, 241;
            --success: #34D399; --danger: #FB7185; --border: #334155;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
        }
        
        .error-icon {
            font-size: 6rem;
            color: var(--danger);
            margin-bottom: 2rem;
        }
        
        .error-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: var(--muted);
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            background: var(--accent);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }
        
        .btn-outline-secondary {
            color: var(--muted);
            border-color: var(--border);
            padding: 0.75rem 2rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-outline-secondary:hover {
            background: var(--muted);
            border-color: var(--muted);
            color: var(--bg);
            transform: translateY(-1px);
        }
        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
            transition: all 0.2s ease;
        }
        
        .theme-toggle:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
            transform: scale(1.1);
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .btn-outline-secondary {
            color: var(--text);
            border-color: var(--border);
        }
        
        [data-theme="dark"] .btn-outline-secondary:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }
        
        [data-theme="dark"] .theme-toggle {
            background: var(--card);
            border-color: var(--border);
            color: var(--text);
        }
        
        [data-theme="dark"] .theme-toggle:hover {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
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
        
        .error-container {
            animation: fadeUp 0.5s ease both;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <button id="themeToggle" class="btn btn-outline-secondary theme-toggle">
        <i class="bi bi-moon"></i>
    </button>

    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        
        <h1 class="error-title">404</h1>
        
        <p class="error-message">
            Oops! The page you're looking for doesn't exist.
        </p>
        
        <div class="d-grid gap-2 d-md-block">
            <a href="/" class="btn btn-primary me-md-2">
                <i class="bi bi-house me-2"></i>
                Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Go Back
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted" style="var(--text) !important;">
                If you believe this is an error, please contact support.
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme toggle functionality
        const apply = mode => {
            document.documentElement.setAttribute('data-theme', mode);
        };
        
        const saved = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        apply(saved);
        
        document.getElementById('themeToggle').onclick = () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            apply(next);
            localStorage.setItem('theme', next);
            
            // Update icon with smooth transition
            const icon = document.querySelector('#themeToggle i');
            icon.style.transform = 'scale(0)';
            
            setTimeout(() => {
                icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
                icon.style.transform = 'scale(1)';
            }, 100);
        };
        
        // Update initial icon
        const icon = document.querySelector('#themeToggle i');
        icon.className = saved === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        icon.style.transition = 'transform 0.2s ease';
        
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

