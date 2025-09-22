@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center mb-0"><i class="fas fa-user-plus me-2"></i>Register</h2>
            </div>
            <div class="card-body p-4">
                @if(session('register_success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('register_success') }}
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
                            window.location.href = '{{ route('home') }}';
                        }
                    }, 1000);
                </script>
                @else
                <form method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf
                    @include('partials.alerts')
                    
                    <div class="mb-4">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required autofocus
                                   placeholder="Enter your full name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required
                                   placeholder="Enter your email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}"
                                   placeholder="Enter your phone number">
                            @error('phone')
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
                                   placeholder="Create a password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">
                            Password must be at least 8 characters long
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required
                                   placeholder="Confirm your password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmation">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" 
                               id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="{{ route('terms') }}" class="text-primary">Terms and Conditions</a>
                        </label>
                        @error('terms')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </button>
                    </div>
                </form>
                
                <!-- Social Login Section -->
                <div class="mt-4">
                    <div class="text-center mb-3">
                        <span class="text-muted">Or register with</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12">
                            <a href="{{ route('auth.google.register') }}" class="btn btn-outline-danger w-100">
                                <i class="fab fa-google me-2"></i>Google
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="text-center mt-4">
                    <p class="mb-0">Already have an account? 
                        <a href="{{ route('login') }}" class="text-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login here
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

.form-text {
    font-size: 0.875rem;
    margin-top: 0.25rem;
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
    const form = document.getElementById('registerForm');
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');

    // Password visibility toggle
    function togglePasswordVisibility(button, input) {
        if (button && input) {
            button.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                button.querySelector('i').classList.toggle('fa-eye');
                button.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    }

    togglePasswordVisibility(togglePassword, password);
    togglePasswordVisibility(togglePasswordConfirmation, passwordConfirmation);

    // Enhanced error handling - prevent input hiding
    if (form) {
        // Clear error states when user starts typing
        form.addEventListener('input', function(e) {
            const input = e.target;
            if (input && input.classList.contains('is-invalid')) {
                input.classList.remove('is-invalid');
                // Remove any custom error messages
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
                    
                    // Add custom error message
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'custom-error invalid-feedback d-block';
                    errorDiv.textContent = 'This field is required.';
                    field.parentNode.appendChild(errorDiv);
                }
            });

            // Validate password confirmation
            if (password && passwordConfirmation) {
                if (password.value !== passwordConfirmation.value) {
                    passwordConfirmation.classList.add('is-invalid');
                    isValid = false;
                    
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'custom-error invalid-feedback d-block';
                    errorDiv.textContent = 'Passwords do not match.';
                    passwordConfirmation.parentNode.appendChild(errorDiv);
                }
            }

            if (!isValid) {
                e.preventDefault();
                
                // Scroll to first error
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