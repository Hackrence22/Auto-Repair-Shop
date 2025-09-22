@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Profile</h1>
                <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @php
                                    $profilePic = $admin->profile_picture ? Storage::disk('public')->url($admin->profile_picture) : asset('images/default-profile.png');
                                @endphp
                                <img src="{{ $profilePic }}" 
                                     alt="Profile Picture" 
                                     class="rounded-circle img-thumbnail"
                                     style="width: 150px; height: 150px; object-fit: cover; cursor:pointer;"
                                     onclick="showImageModal('{{ $profilePic }}')">
                            </div>

                            <h5 class="mb-1">{{ $admin->name }}</h5>
                            <p class="text-muted mb-3">Administrator</p>

                            <form action="{{ route('admin.profile.picture') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" 
                                           id="profile_picture" name="profile_picture" accept="image/*">
                                    @error('profile_picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-upload"></i> Update Picture
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Change Password</h5>
                            <form action="{{ route('admin.profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Profile Information</h5>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <p class="mb-0 text-muted">Name</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="mb-0">{{ $admin->name }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <p class="mb-0 text-muted">Email</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="mb-0">{{ $admin->email }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <p class="mb-0 text-muted">Member Since</p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="mb-0">{{ $admin->created_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showImageModal(src) {
    const modalImg = document.getElementById('modalProfileImg');
    modalImg.src = src;
    const modal = new bootstrap.Modal(document.getElementById('profileImageModal'));
    modal.show();
}
</script>
@endsection

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