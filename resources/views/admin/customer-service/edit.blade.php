@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="mx-auto" style="max-width: 760px;">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('admin.customer-service.show', $customerService) }}" class="btn btn-link p-0 me-2"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Edit Customer Service Request</h1>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
            <form action="{{ route('admin.customer-service.update', $customerService) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="open" {{ $customerService->status === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ $customerService->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $customerService->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="closed" {{ $customerService->status === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="assigned_admin_id" class="form-label">Assign To</label>
                    <select name="assigned_admin_id" id="assigned_admin_id" class="form-select">
                        <option value="">Unassigned</option>
                        @foreach(\App\Models\Admin::all() as $admin)
                            <option value="{{ $admin->id }}" {{ $customerService->assigned_admin_id == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }} ({{ $admin->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="admin_reply" class="form-label">Admin Response</label>
                    <textarea name="admin_reply" id="admin_reply" rows="6" class="form-control" placeholder="Provide a detailed response to the customer's request. Include any relevant information, solutions, or next steps.">{{ $customerService->admin_reply }}</textarea>
                    <div class="form-text">This response will be sent to the customer as a notification.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.customer-service.show', $customerService) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Request</button>
                </div>
            </form>
        </div>
                </div>
        <div class="mt-3">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h3 class="h6 mb-3">Request Summary</h3>
                    <div class="row g-2 small">
                        <div class="col-md-6"><span class="text-muted">Subject:</span> <span class="fw-semibold">{{ $customerService->subject }}</span></div>
                        <div class="col-md-6"><span class="text-muted">Customer:</span> <span class="fw-semibold">{{ $customerService->user->name }}</span></div>
                        <div class="col-md-6"><span class="text-muted">Shop:</span> <span class="fw-semibold">{{ $customerService->shop->name }}</span></div>
                        <div class="col-md-6"><span class="text-muted">Priority:</span> <span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }}</span></div>
                        <div class="col-md-6"><span class="text-muted">Created:</span> <span class="fw-semibold">{{ $customerService->created_at->format('M d, Y \a\t g:i A') }}</span></div>
                        <div class="col-md-6"><span class="text-muted">Current Status:</span> <span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span></div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
