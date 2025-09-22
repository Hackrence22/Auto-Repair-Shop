@extends('layouts.app')

@section('title', 'My Profile')

@php
@endphp

@php
    $profilePic = Auth::user()->profile_picture_url;
@endphp

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            @include('partials.alerts')
            
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-user-circle me-2"></i>My Profile</h2>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="profile-picture-container mx-auto" style="cursor:pointer;" onclick="showImageModal('{{ $profilePic }}')">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ $profilePic }}" alt="Profile Picture" class="profile-picture">
                            @elseif(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="Profile Picture" class="profile-picture">
                            @else
                                <div class="profile-picture-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <form action="{{ route('profile.update-picture') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="profile_picture" class="btn btn-outline-primary">
                                    <i class="fas fa-camera me-2"></i>Change Profile Picture
                                </label>
                                <input type="file" id="profile_picture" name="profile_picture" class="d-none" accept="image/*" onchange="showPreview(this)">
                                <small class="d-block text-muted mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Maximum file size: 100MB. Supported formats: JPEG, PNG, JPG, GIF
                                </small>
                            </div>
                            <div id="preview-container" class="mb-3 d-none">
                                <img id="preview-image" src="#" alt="Preview" class="preview-image">
                                <div class="mt-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Picture
                                    </button>
                                    <button type="button" class="btn btn-secondary ms-2" onclick="cancelPreview()">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </button>
                                </div>
                            </div>
                            @error('profile_picture')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </form>
                    </div>

                    <div class="profile-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-user me-2"></i>Name</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="profile-value">{{ $user->name }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-envelope me-2"></i>Email</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="profile-value">{{ $user->email }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-phone me-2"></i>Phone</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="profile-value">{{ $user->phone ?? 'Not provided' }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-map-marker-alt me-2"></i>Address</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="profile-value">{{ $user->address ?? 'Not provided' }}</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong><i class="fas fa-user-tag me-2"></i>Role</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-primary">
                                    <i class="fas fa-{{ $user->role == 'admin' ? 'crown' : 'user' }} me-1"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Password Change Card -->
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for image zoom -->
<div class="modal fade" id="profileImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 text-center">
        <img id="modalProfileImg" src="" alt="Profile" style="max-width:90vw;max-height:80vh;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.2);">
      </div>
    </div>
  </div>
</div>

<style>
.profile-picture-container {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.profile-picture-container:hover {
    transform: scale(1.05);
}

.profile-picture {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-picture-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary, linear-gradient(to right, #4a90e2, #2c5282));
    color: white;
    font-size: 3rem;
}

.preview-image {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.profile-value {
    font-size: 1.1rem;
    color: var(--primary-color);
    font-weight: 500;
}

.input-group-text {
    background: var(--gradient-primary);
    color: white;
    border: none;
}

.input-group .form-control {
    border-left: none;
}

.input-group .form-control:focus {
    border-left: none;
    border-color: #e9ecef;
}

.card {
    margin-bottom: 2rem;
}

.profile-info .row {
    border-bottom: 1px solid rgba(0,0,0,0.1);
    padding: 1.5rem 1rem;
}

.profile-info .row:last-child {
    border-bottom: none;
}

.profile-info strong i {
    color: var(--secondary-color);
}

.badge {
    font-size: 0.9rem;
    padding: 0.6rem 1.2rem;
}

.badge i {
    font-size: 0.8rem;
}

/* Clean Modal styles */
.modal-fullscreen-md-down {
    max-width: 95vw;
    margin: 1rem auto;
}

.modal-content.bg-transparent {
    background: transparent !important;
    box-shadow: none;
}

.btn-close-custom {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(0, 0, 0, 0.5);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 1050;
}

.btn-close-custom:hover {
    background: rgba(0, 0, 0, 0.7);
    transform: scale(1.1);
}

.modal-profile-image {
    max-height: 90vh;
    max-width: 100%;
    object-fit: contain;
    margin: 0 auto;
    display: block;
    border-radius: 10px;
}

.modal-profile-placeholder {
    width: 300px;
    height: 300px;
    margin: 2rem auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-primary);
    color: white;
    font-size: 6rem;
    border-radius: 10px;
}

.modal-backdrop.show {
    opacity: 0.9;
}

/* Remove any conflicting modal styles */
.modal-header,
.modal-footer {
    display: none;
}
</style>

<script>
function showPreview(input) {
    const maxSize = 100 * 1024 * 1024; // 100MB in bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Check file size
        if (file.size > maxSize) {
            alert('File is too large. Maximum size is 100MB.');
            input.value = '';
            return;
        }
        
        // Check file type
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Only JPEG, PNG, JPG and GIF images are allowed.');
            input.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('preview-image');
            const previewContainer = document.getElementById('preview-container');
            
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
    }
}

function cancelPreview() {
    const profilePicture = document.getElementById('profile_picture');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');
    
    profilePicture.value = '';
    previewContainer.classList.add('d-none');
    previewImage.src = '#';
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function showImageModal(src) {
    const modalImg = document.getElementById('modalProfileImg');
    modalImg.src = src;
    const modal = new bootstrap.Modal(document.getElementById('profileImageModal'));
    modal.show();
}
</script>
@endsection 