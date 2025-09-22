@extends('layouts.admin')

@section('title', 'Add Payment Method')

@section('content')
<div class="container-fluid">
    <!-- Enhanced Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4" style="min-height: 110px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-plus text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Add Payment Method</h1>
                        <p class="mb-0 text-secondary">Create a new payment option for your customers</p>
                    </div>
                </div>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-lg px-4 py-2 text-white fw-semibold shadow-sm" style="background: linear-gradient(90deg,#6c757d 0%,#495057 100%); border-radius: 12px;">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-light border-0 py-3">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2 text-primary"></i>
                        Payment Method Details
                    </h3>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.payment-methods.store') }}" method="POST" enctype="multipart/form-data" id="paymentMethodForm">
                        @csrf
                        
                        <!-- Basic Information Section -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-primary bg-opacity-10 border-0">
                                <h5 class="mb-0 text-primary">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label fw-semibold">
                                                <i class="fas fa-tag me-1 text-primary"></i>
                                                Payment Method Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" 
                                                   placeholder="Enter payment method name" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="role_type" class="form-label fw-semibold">
                                                <i class="fas fa-mobile-alt me-1 text-primary"></i>
                                                Payment Type <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-lg @error('role_type') is-invalid @enderror" 
                                                    id="role_type" name="role_type" required>
                                                <option value="">Select Payment Type</option>
                                                <option value="gcash" {{ old('role_type') == 'gcash' ? 'selected' : '' }}>GCash</option>
                                                <option value="paymaya" {{ old('role_type') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
                                                <option value="cash" {{ old('role_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                                            </select>
                                            @error('role_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="shop_id" class="form-label fw-semibold">
                                                <i class="fas fa-store me-1 text-primary"></i>
                                                Shop <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-lg @error('shop_id') is-invalid @enderror" id="shop_id" name="shop_id" required>
                                                <option value="">Select Shop</option>
                                                @php
                                                    $shopsQuery = \App\Models\Shop::active()->ordered();
                                                    if (auth('admin')->user() && auth('admin')->user()->isOwner()) {
                                                        $ownerId = auth('admin')->id();
                                                        $ownerName = auth('admin')->user()->name;
                                                        $shopsQuery->where(function($q) use ($ownerId, $ownerName) {
                                                            $q->where('admin_id', $ownerId)
                                                              ->orWhere(function($qq) use ($ownerName) { $qq->whereNull('admin_id')->where('owner_name', $ownerName); });
                                                        });
                                                    }
                                                    $shopsList = $shopsQuery->get();
                                                @endphp
                                                @foreach($shopsList as $shop)
                                                    <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }} - {{ $shop->full_address }}</option>
                                                @endforeach
                                            </select>
                                            @error('shop_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Details Section -->
                        <div class="card mb-4 border-0 shadow-sm" id="accountDetailsSection">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <h5 class="mb-0 text-success">
                                    <i class="fas fa-user-circle me-2"></i>
                                    Account Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="account_name" class="form-label fw-semibold">
                                                <i class="fas fa-user me-1 text-success"></i>
                                                Account Name
                                            </label>
                                            <input type="text" class="form-control form-control-lg @error('account_name') is-invalid @enderror" 
                                                   id="account_name" name="account_name" value="{{ old('account_name') }}"
                                                   placeholder="Enter account holder name">
                                            @error('account_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <span id="accountNameHelp">Name of the account holder (for digital payments)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="account_number" class="form-label fw-semibold">
                                                <i class="fas fa-hashtag me-1 text-success"></i>
                                                Account Number
                                            </label>
                                            <input type="text" class="form-control form-control-lg @error('account_number') is-invalid @enderror" 
                                                   id="account_number" name="account_number" value="{{ old('account_number') }}"
                                                   placeholder="Enter phone number or account number">
                                            @error('account_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <span id="accountNumberHelp">Phone number or account number (for digital payments)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-warning bg-opacity-10 border-0">
                                <h5 class="mb-0 text-warning">
                                    <i class="fas fa-image me-2"></i>
                                    Payment Method Image
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image" class="form-label fw-semibold">
                                                <i class="fas fa-upload me-1 text-warning"></i>
                                                Upload Image
                                            </label>
                                            <input type="file" class="form-control form-control-lg @error('image') is-invalid @enderror" 
                                                   id="image" name="image" accept="image/*">
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Upload logo or icon (max 2MB, jpeg, png, jpg, gif)
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="sort_order" class="form-label fw-semibold">
                                                <i class="fas fa-sort-numeric-up me-1 text-info"></i>
                                                Sort Order
                                            </label>
                                            <input type="number" class="form-control form-control-lg @error('sort_order') is-invalid @enderror" 
                                                   id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                            @error('sort_order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Lower numbers appear first in the list
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Image Preview -->
                                <div class="mt-3" id="imagePreviewContainer" style="display: none;">
                                    <div class="card border-2 border-dashed border-secondary">
                                        <div class="card-body text-center">
                                            <h6 class="text-secondary mb-3">
                                                <i class="fas fa-eye me-2"></i>
                                                Image Preview
                                            </h6>
                                            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                                <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;">
                                                <div class="text-start">
                                                    <p class="mb-1"><strong>File:</strong> <span id="fileName"></span></p>
                                                    <p class="mb-1"><strong>Size:</strong> <span id="fileSize"></span></p>
                                                    <p class="mb-0"><strong>Type:</strong> <span id="fileType"></span></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-secondary bg-opacity-10 border-0">
                                <h5 class="mb-0 text-secondary">
                                    <i class="fas fa-align-left me-2"></i>
                                    Description
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-semibold">
                                        <i class="fas fa-edit me-1 text-secondary"></i>
                                        Payment Method Description
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="4" 
                                              placeholder="Provide details about this payment method">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Provide details about this payment method
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Section -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-success bg-opacity-10 border-0">
                                <h5 class="mb-0 text-success">
                                    <i class="fas fa-toggle-on me-2"></i>
                                    Status
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                           id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="is_active">
                                        <i class="fas fa-check-circle me-1 text-success"></i>
                                        Active Payment Method
                                    </label>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Enable this payment method for customer use
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-lg btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-lg btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>Create Payment Method
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-primary {
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(90deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.success-animation {
    animation: successPulse 0.5s ease-in-out;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleTypeSelect = document.getElementById('role_type');
    const accountNameField = document.getElementById('account_name');
    const accountNumberField = document.getElementById('account_number');
    const accountNameHelp = document.getElementById('accountNameHelp');
    const accountNumberHelp = document.getElementById('accountNumberHelp');
    const accountDetailsSection = document.getElementById('accountDetailsSection');
    const imageInput = document.getElementById('image');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const imagePreview = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const fileType = document.getElementById('fileType');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('paymentMethodForm');
    
    // Update field labels and help text based on role type
    function updateFieldLabels() {
        const selectedValue = roleTypeSelect.value;
        
        if (selectedValue === 'gcash' || selectedValue === 'paymaya') {
            accountNameField.placeholder = 'Enter account holder name';
            accountNumberField.placeholder = 'Enter phone number';
            accountNameHelp.textContent = 'Name of the account holder';
            accountNumberHelp.textContent = 'Phone number linked to the account';
            accountDetailsSection.style.display = 'block';
        } else if (selectedValue === 'cash') {
            accountNameField.placeholder = 'Not required for cash';
            accountNumberField.placeholder = 'Not required for cash';
            accountNameHelp.textContent = 'Not required for cash payments';
            accountNumberHelp.textContent = 'Not required for cash payments';
            accountDetailsSection.style.display = 'block';
        } else {
            accountNameField.placeholder = 'Enter account holder name';
            accountNumberField.placeholder = 'Enter phone number or account number';
            accountNameHelp.textContent = 'Name of the account holder (for digital payments)';
            accountNumberHelp.textContent = 'Phone number or account number (for digital payments)';
            accountDetailsSection.style.display = 'block';
        }
    }
    
    // Image preview functionality
    function handleImagePreview(event) {
        const file = event.target.files[0];
        
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                imageInput.value = '';
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, JPG, GIF)');
                imageInput.value = '';
                return;
            }
            
            // Display preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                fileName.textContent = file.name;
                fileSize.textContent = (file.size / 1024).toFixed(2) + ' KB';
                fileType.textContent = file.type;
                imagePreviewContainer.style.display = 'block';
                
                // Add success animation
                imagePreviewContainer.classList.add('success-animation');
                setTimeout(() => {
                    imagePreviewContainer.classList.remove('success-animation');
                }, 500);
            };
            reader.readAsDataURL(file);
        } else {
            imagePreviewContainer.style.display = 'none';
        }
    }
    
    // Form submission handling
    function handleFormSubmit(event) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
        
        // Re-enable after 5 seconds in case of error
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Create Payment Method';
        }, 5000);
    }
    
    // Event listeners
    roleTypeSelect.addEventListener('change', updateFieldLabels);
    imageInput.addEventListener('change', handleImagePreview);
    form.addEventListener('submit', handleFormSubmit);
    
    // Initialize on page load
    updateFieldLabels();
});
</script>
@endpush 