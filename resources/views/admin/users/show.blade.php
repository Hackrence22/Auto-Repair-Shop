@extends('layouts.admin')

@section('title', 'User Profile')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">User Profile</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center"> 
                    @php
                        $profilePic = $user->profile_picture_url;
                    @endphp
                    <img src="{{ $profilePic }}" alt="Profile" class="rounded-circle img-thumbnail"
                         style="width:150px;height:150px;object-fit:cover;cursor:pointer;"
                         onclick="showImageModal('{{ $profilePic }}')">
                    <h5 class="mt-3 mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-0">Customer</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Account Information</h5>
                    <div class="row mb-3">
                        <div class="col-sm-3"><p class="mb-0 text-muted">Name</p></div>
                        <div class="col-sm-9"><p class="mb-0">{{ $user->name }}</p></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><p class="mb-0 text-muted">Email</p></div>
                        <div class="col-sm-9"><p class="mb-0">{{ $user->email }}</p></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3"><p class="mb-0 text-muted">Phone</p></div>
                        <div class="col-sm-9"><p class="mb-0">{{ $user->phone ?? 'N/A' }}</p></div>
                    </div>
                    <div class="row mb-0">
                        <div class="col-sm-3"><p class="mb-0 text-muted">Address</p></div>
                        <div class="col-sm-9"><p class="mb-0">{{ $user->address ?? 'N/A' }}</p></div>
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
