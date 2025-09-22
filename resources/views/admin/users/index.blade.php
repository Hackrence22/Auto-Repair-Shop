@extends('layouts.admin')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">User Accounts</h1>
    </div>
    <div class="card">
        <div class="card-header bg-white border-0">
            <div class="d-flex align-items-stretch" style="gap:0.5rem;">
                <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex flex-grow-1" role="search">
                    <div class="input-group w-100" style="min-width:260px;">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, email, phone...">
                    </div>
                </form>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary flex-shrink-0" style="height:38px;">
                    <i class="fas fa-user-plus me-1"></i> Add User
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive table-scroll-y">
            <table class="table table-hover table-sticky">
                <thead>
                    <tr>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                @php
                                    $profilePic = $user->profile_picture_url;
                                @endphp
                                <img src="{{ $profilePic }}" alt="Profile" class="table-thumb" style="border-radius:50%;cursor:pointer;" onclick="showImageModal('{{ $profilePic }}')">
                            </td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @if(isset($users) && method_exists($users, 'links'))
        <div class="d-flex justify-content-center mt-3">
            {{ $users->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
        </div>
        <div class="text-center text-muted small mt-2">
            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
        </div>
    @endif
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