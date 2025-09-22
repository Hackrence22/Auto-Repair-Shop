<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Admin Login</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e3eafc 0%, #f4f6fa 100%);
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background: linear-gradient(270deg, #29527a, #4a90e2, #29527a);
            background-size: 400% 400%;
            animation: gradientMove 8s ease-in-out infinite;
            padding: 0.75rem 0;
            box-shadow: 0 2px 8px rgba(41,82,122,0.10);
        }
        @keyframes gradientMove {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            text-decoration: none;
            letter-spacing: 1px;
        }
        .navbar-brand i {
            font-size: 1.7rem;
        }
        .navbar-brand:hover {
            filter: brightness(1.2);
            text-decoration: underline;
            transition: all 0.2s;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 1rem;
            margin-top: -30px; /* compact like user side (desktop) */
        }
        .login-card {
            border: none;
            border-radius: 28px;
            max-width: 440px;
            width: 100%;
            background: rgba(255,255,255,0.85);
            box-shadow: 0 12px 48px 0 rgba(41, 82, 122, 0.18), 0 1.5px 8px 0 rgba(41,82,122,0.08);
            margin-top: 8px; /* reduce excessive top spacing */
            transition: box-shadow 0.3s, background 0.3s;
            backdrop-filter: blur(8px) saturate(120%);
            position: relative;
        }
        .login-card::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 28px;
            background: linear-gradient(120deg, #4a90e2 0%, #fff 100%);
            opacity: 0.07;
            z-index: 0;
        }
        .login-card:hover {
            box-shadow: 0 20px 64px 0 rgba(41, 82, 122, 0.22);
            background: rgba(255,255,255,0.93);
        }
        .login-header {
            border-radius: 28px 28px 0 0;
            background: linear-gradient(270deg, #29527a, #4a90e2, #29527a);
            background-size: 400% 400%;
            animation: gradientMove 8s ease-in-out infinite;
            color: #fff;
            padding: 2.2rem 1.5rem 1.5rem 1.5rem;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .login-header i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            animation: iconPulse 2.5s infinite;
        }
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.13); }
        }
        .login-header h2 {
            font-weight: 700;
            margin-bottom: 0;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 500;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }
        .form-control {
            border-left: none;
            font-size: 1.08rem;
            border-radius: 0 8px 8px 0;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-control:hover {
            box-shadow: 0 0 0 2px #4a90e233;
            border-color: #4a90e2;
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(90deg, #29527a 0%, #4a90e2 100%);
            border: none;
            font-weight: 700;
            font-size: 1.13rem;
            padding: 0.85rem;
            border-radius: 12px;
            letter-spacing: 1px;
            transition: 
                background 0.3s cubic-bezier(.4,2,.6,1), 
                transform 0.18s cubic-bezier(.4,2,.6,1), 
                box-shadow 0.25s;
            box-shadow: 0 2px 8px rgba(41,82,122,0.08);
            position: relative;
            overflow: hidden;
        }
        .btn-primary:focus, .btn-primary:hover {
            background: linear-gradient(270deg, #4a90e2 0%, #29527a 100%);
            background-size: 200% 200%;
            animation: btnGradientMove 1.2s linear infinite;
            transform: scale(1.06);
            box-shadow: 0 8px 24px 0 rgba(41, 82, 122, 0.18), 0 0 0 4px #4a90e244;
            outline: none;
        }
        @keyframes btnGradientMove {
            0% {background-position: 0% 50%;}
            100% {background-position: 100% 50%;}
        }
        .form-check-input:checked {
            background-color: #4a90e2;
            border-color: #4a90e2;
        }
        .alert {
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 1.01rem;
        }
        .btn-outline-secondary {
            border-color: #ced4da;
            color: #6c757d;
            transition: background 0.2s, color 0.2s;
            border-radius: 0 8px 8px 0;
        }
        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: #e3eaf3;
            color: #29527a;
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #888;
        }
        /* Focus ring for accessibility */
        .form-control:focus-visible, .btn:focus-visible {
            outline: 2px solid #4a90e2;
            outline-offset: 2px;
        }
        /* Mobile-specific adjustments */
        @media (max-width: 576px) {
            .navbar .container { padding-left: 0.75rem; padding-right: 0.75rem; }
            .navbar-brand { font-size: 1.1rem; }
            .navbar-brand i { font-size: 1.3rem; }
            .login-container { padding: 0.75rem; margin-top: -60px; } /* match user-end compact offset */
            .login-card { border-radius: 18px; margin-top: 6px; }
            .login-header { padding: 1.25rem 1rem 1rem 1rem; }
            .login-header i { font-size: 2rem; }
            .login-header h2 { font-size: 1.25rem; }
            .card-body { padding: 1rem; }
            .form-control { font-size: 1rem; }
            .btn-primary { font-size: 1rem; padding: 0.75rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-tools me-2"></i>
                <span>AUTO REPAIR SHOP</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <!-- No login/register links for admin login -->
                </ul>
            </div>
        </div>
    </nav>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h2 class="mb-0">Admin Login</h2>
                <div style="font-size:1rem;opacity:0.85;margin-top:0.5rem;">Welcome back, please sign in to continue</div>
            </div>
            <div class="card-body p-4">
                @if(session('login_success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('login_success') }}
                    <div class="mt-1 small">Redirecting in <span id="countdown">3</span> seconds...</div>
                </div>
                <script>
                    let count = 3;
                    const countdown = document.getElementById('countdown');
                    const interval = setInterval(() => {
                        count--;
                        if (countdown) countdown.textContent = count;
                        if (count <= 0) {
                            clearInterval(interval);
                            window.location.href = '/admin/dashboard';
                        }
                    }, 1000);
                </script>
                @else
                <form method="POST" action="{{ route('admin.login') }}" class="needs-validation" novalidate id="adminLoginForm">
                    @csrf
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
                    <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required autofocus
                                   placeholder="Enter your email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required
                                   placeholder="Enter your password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>LOGIN
                    </button>
                </div>
            </form>
                @endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('adminLoginForm');
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        // Password visibility toggle
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                togglePassword.querySelector('i').classList.toggle('fa-eye');
                togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        // Enhanced error handling - prevent input hiding
        if (form) {
            // Clear error states when user starts typing
            form.addEventListener('input', function(e) {
                const input = e.target;
                if (input && input.classList.contains('is-invalid')) {
                    input.classList.remove('is-invalid');
                    const errorDiv = input.parentNode.querySelector('.custom-error');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                }
            });

            // Clear error states when user focuses on input
            form.addEventListener('focusin', function(e) {
                const input = e.target;
                if (input && input.classList.contains('is-invalid')) {
                    input.classList.remove('is-invalid');
                    const errorDiv = input.parentNode.querySelector('.custom-error');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                }
            });

            // Form submission validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                // Clear previous error states
                requiredFields.forEach(field => {
                    field.classList.remove('is-invalid');
                    const errorDiv = field.parentNode.querySelector('.custom-error');
                    if (errorDiv) {
                        errorDiv.remove();
                    }
                });

                // Validate required fields
                requiredFields.forEach(field => {
                    const value = field.value ? field.value.trim() : '';
                    if (!value) {
                        field.classList.add('is-invalid');
                        isValid = false;
                        
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'custom-error invalid-feedback d-block';
                        errorDiv.textContent = 'This field is required.';
                        field.parentNode.appendChild(errorDiv);
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    
                    const firstError = form.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        }
    });
    </script>
</body>
</html> 