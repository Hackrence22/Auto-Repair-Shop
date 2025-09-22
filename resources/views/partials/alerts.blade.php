@if(session('success') || session('login_success') || session('register_success') || session('logout_success'))
    <div class="alert alert-success alert-dismissible fade show auto-dismiss mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') ?? session('login_success') ?? session('register_success') ?? session('logout_success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show auto-dismiss mb-4" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show auto-dismiss mb-4" role="alert" id="validationErrors">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
            <div class="flex-grow-1">
                <h6 class="alert-title mb-2">Please correct the following errors:</h6>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
@endif

<style>
.alert {
    position: relative;
    width: 100%;
    border-radius: 10px;
    border: none;
    text-align: left;
    padding: 1rem 1.25rem;
    animation: fadeIn 0.3s ease-in-out;
}

.alert-success {
    background: var(--gradient-success, linear-gradient(135deg, #28a745, #20c997));
    color: white;
}

.alert-danger {
    background: var(--gradient-danger, linear-gradient(135deg, #dc3545, #c82333));
    color: white;
}

.alert .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
    transition: opacity 0.3s ease;
    padding: 1.25rem;
}

.alert .btn-close:hover {
    opacity: 1;
}

.alert ul {
    list-style-type: none;
    padding-left: 0;
    margin-bottom: 0;
}

.alert ul li {
    margin-bottom: 0.25rem;
}

.alert ul li:last-child {
    margin-bottom: 0;
}

.alert.fade {
    transition: opacity 0.3s linear;
}

.alert-title {
    font-weight: 600;
    font-size: 1rem;
}

/* Enhanced error styling for form inputs */
.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    display: block !important;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
    font-weight: 500;
}

.custom-error {
    display: block !important;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
    font-weight: 500;
}

/* Ensure input groups don't hide errors */
.input-group .is-invalid {
    z-index: 3;
}

.input-group .invalid-feedback {
    position: relative;
    z-index: 4;
}

/* Prevent form elements from being hidden */
.form-control:not(:placeholder-shown) {
    background-color: #fff;
}

.form-control:focus {
    background-color: #fff;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.auto-dismiss {
    animation: fadeIn 0.3s ease-in-out, fadeOut 0.3s ease-in-out 2.7s forwards;
}

@keyframes fadeOut {
    from { opacity: 1; transform: translateY(0); }
    to { opacity: 0; transform: translateY(-10px); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 3 seconds
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.remove();
        }, 3000);
    });
});
</script> 