@extends('layouts.admin')
@section('title', 'Feedback')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="gap:0.75rem;">
            <h4 class="mb-0">Customer Feedback</h4>
            <form method="GET" action="{{ route('admin.feedback.index') }}" class="d-flex" role="search">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, email, message...">
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive table-scroll-y">
            <table class="table table-hover table-sticky">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($feedbacks as $feedback)
                    <tr>
                        <td>
                            @php
                                $user = \App\Models\User::where('email', $feedback->email)->first();
                                $imgSrc = $user && $user->profile_picture ? asset('uploads/' . $user->profile_picture) : asset('images/default-avatar.png');
                            @endphp
                            <img src="{{ $imgSrc }}" alt="Avatar" class="table-thumb" style="border-radius:50%;margin-right:6px;vertical-align:middle;cursor:pointer;" onclick="showImageModal('{{ $imgSrc }}')">
                            {{ $feedback->name }}
                        </td>
                        <td>{{ $feedback->email }}</td>
                        <td>{{ Str::limit($feedback->message, 40) }}</td>
                        <td>{{ $feedback->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.feedback.show', $feedback) }}" class="btn btn-sm btn-info">View</a>
                            <form action="{{ route('admin.feedback.destroy', $feedback) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $feedbacks->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
            </div>
            <div class="text-center text-muted small mt-2">
                Showing {{ $feedbacks->firstItem() }} to {{ $feedbacks->lastItem() }} of {{ $feedbacks->total() }} results
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