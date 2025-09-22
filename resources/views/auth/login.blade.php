@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0"><i class="fas fa-sign-in-alt me-2"></i>Login</h2>
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
                            @if(Auth::check() && Auth::user()->role === 'admin')
                                window.location.href = '/admin/dashboard';
                            @else
                                window.location.href = '/';
                            @endif
                        }
                    }, 1000);
                </script>
                @else
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf
                    @include('partials.alerts')
                    
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

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <!-- Social Login Section -->
                <div class="mt-4">
                    <div class="text-center mb-3">
                        <span class="text-muted">Or login with</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12">
                            <a href="{{ route('auth.google.login') }}" class="btn btn-outline-danger w-100">
                                <i class="fab fa-google me-2"></i>Google
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="text-center mt-4">
                    <p class="mb-0">Don't have an account? 
                        <a href="{{ route('register') }}" class="text-primary">
                            <i class="fas fa-user-plus me-1"></i>Register here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.btn-primary {
    padding: 0.8rem;
    font-weight: 500;
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.alert {
    border-radius: 10px;
    margin-bottom: 1.5rem;
}

.btn-outline-secondary {
    border-color: #ced4da;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    color: #495057;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
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
@endsection 