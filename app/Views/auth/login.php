<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Roaya Clinic</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
            background: var(--card);
            border: 2px solid var(--border);
            color: var(--text);
            font-weight: 500;
        }
        
        .form-control:focus {
            background: var(--card);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
            color: var(--text);
        }
        
        .form-floating label {
            color: var(--muted);
            font-weight: 500;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--accent);
            font-weight: 600;
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
                <div class="clinic-name">Roaya Clinic</div>
                <div class="clinic-subtitle">Professional Eye Care Management</div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= $this->url('/login') ?>">
                <?= $this->csrfField() ?>
                
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                    <label for="username">اسم المستخدم - Username</label>
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
            
            <!-- Demo Credentials -->
            <div class="mt-4 p-3" style="background: var(--bg); border: 1px solid #dee2e6; border-radius: 8px;">
                <h6 class="text-muted mb-2 text-center">أسماء المستخدمين التجريبية - Demo Usernames</h6>
                <div class="row text-sm">
                    <div class="col-6">
                        <strong style="color: var(--accent);">الأطباء - Doctors:</strong><br>
                        <code style="background: var(--accent); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">dr_ahmed</code><br>
                        <code style="background: var(--accent); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">dr_faramawy</code>
                    </div>
                    <div class="col-6">
                        <strong style="color: var(--success);">الموظفون - Staff:</strong><br>
                        <code style="background: var(--success); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">sec</code> (Secretary)<br>
                        <code style="background: var(--danger); color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">admin</code> (Admin)
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <strong>كلمة المرور لجميع الحسابات:</strong> <code style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 4px;">password</code>
                </div>
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
    </script>
</body>
</html>
