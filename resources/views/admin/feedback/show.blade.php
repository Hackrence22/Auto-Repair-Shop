@extends('layouts.admin')
@section('title', 'Feedback Details')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Feedback Details</h4>
        </div>
        <div class="card-body">
            @php
                $user = \App\Models\User::where('email', $feedback->email)->first();
                $imgSrc = $user && $user->profile_picture ? asset('uploads/' . $user->profile_picture) : asset('images/default-avatar.png');
            @endphp
            <p><strong>Name:</strong> <img src="{{ $imgSrc }}" alt="Avatar" style="width:28px;height:28px;object-fit:cover;border-radius:50%;margin-right:6px;vertical-align:middle;cursor:pointer;" onclick="showImageModal('{{ $imgSrc }}')">{{ $feedback->name }}</p>
            <p><strong>Email:</strong> {{ $feedback->email }}</p>
            <p><strong>Message:</strong><br>{{ $feedback->message }}</p>
            <p><strong>Date:</strong> {{ $feedback->created_at->format('Y-m-d H:i') }}</p>
            <a href="{{ route('admin.feedback.index') }}" class="btn btn-secondary">Back to Feedback</a>
            <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger ms-2">Delete</button>
            </form>
            <form action="{{ route('admin.feedback.reply', $feedback) }}" method="POST" class="mt-4">
                @csrf
                <div class="mb-3">
                    <label for="reply" class="form-label">Reply to User</label>
                    <textarea name="reply" id="reply" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send Reply</button>
            </form>
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