<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ophthalmology Clinic</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
        }
        
        .dark {
            --bg: #0b1220;
            --text: #e5e7eb;
            --card: #111827;
            --muted: #9aa4b2;
            --accent: #38bdf8;
            --success: #22c55e;
            --danger: #f87171;
            --border: #1f2937;
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
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            animation: fadeUp 0.5s ease both;
        }
        
        .clinic-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .clinic-logo i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
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
            margin-bottom: 1rem;
        }
        
        .form-control {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
        }
        
        .form-control:focus {
            background: var(--card);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(14, 165, 233, 0.25);
        }
        
        .form-floating label {
            color: var(--muted);
        }
        
        .btn-primary {
            background: var(--accent);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: #0284c7;
            transform: translateY(-1px);
        }
        
        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
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
    <!-- Theme Toggle -->
    <button id="themeToggle" class="btn btn-outline-secondary theme-toggle">
        <i class="bi bi-moon"></i>
    </button>

    <div class="login-container">
        <div class="login-card">
            <div class="clinic-logo">
                <i class="bi bi-eye"></i>
                <div class="clinic-name">Ophthalmology Clinic</div>
                <div class="clinic-subtitle">Professional Eye Care Management</div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <?= $this->csrfField() ?>
                
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="name@example.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    <label for="email">Email address</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required>
                    <label for="password">Password</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Sign In
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-shield-check me-1"></i>
                    Secure login system
                </small>
            </div>
        </div>
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
        };
        
        // Update initial icon
        const icon = document.querySelector('#themeToggle i');
        icon.className = saved === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
        });
    </script>
</body>
</html>
