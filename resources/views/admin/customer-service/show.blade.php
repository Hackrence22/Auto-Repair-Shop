@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="mx-auto" style="max-width: 980px;">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('admin.customer-service.index') }}" class="btn btn-link p-0 me-2"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Customer Service Request Details</h1>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                        <h2 class="h5 mb-1">{{ $customerService->subject }}</h2>
                        <div class="text-muted small">Shop: {{ $customerService->shop->name }} • Created: {{ $customerService->created_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }} Priority</span>
                        <span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span>
                </div>
            </div>

                <div class="border-top pt-3 mb-3">
                    <h3 class="h6 mb-3">Customer Information</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Name</div>
                            <div>{{ $customerService->user->name }}</div>
                    </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Email</div>
                            <div>{{ $customerService->user->email }}</div>
                    </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Phone</div>
                            <div>{{ $customerService->user->phone ?? 'Not provided' }}</div>
                    </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Member Since</div>
                            <div>{{ $customerService->user->created_at->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

                <div class="border-top pt-3">
                    <h3 class="h6 mb-3">Customer Message</h3>
                    <div class="alert alert-light border">{{ $customerService->message }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h3 class="h6 mb-3">Update Request</h3>
            <form action="{{ route('admin.customer-service.update', $customerService) }}" method="POST">
                @csrf
                @method('PUT')
                
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select" required>
                            <option value="open" {{ $customerService->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $customerService->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $customerService->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $customerService->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                        <div class="col-md-6">
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
                </div>

                    <div class="mb-3">
                        <label for="admin_reply" class="form-label">Admin Response</label>
                        <textarea name="admin_reply" id="admin_reply" rows="6" class="form-control" placeholder="Provide a detailed response to the customer's request. Include any relevant information, solutions, or next steps.">{{ $customerService->admin_reply }}</textarea>
                        <div class="form-text">This response will be sent to the customer as a notification.</div>
                </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.customer-service.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Request</button>
                </div>
            </form>
            </div>
        </div>

        @if($customerService->admin_reply)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge bg-success me-2"><i class="fas fa-check"></i></div>
                    <div>
                            <div class="fw-semibold">Current Admin Response</div>
                            <div class="text-muted small">
                            @if($customerService->assignedAdmin)
                                From {{ $customerService->assignedAdmin->name }}
                            @endif
                            @if($customerService->updated_at)
                                • {{ $customerService->updated_at->format('M d, Y \a\t g:i A') }}
                            @endif
                        </div>
                    </div>
                </div>
                    <div class="alert alert-success mb-0">{{ $customerService->admin_reply }}</div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <h3 class="h6 mb-3">Request Timeline</h3>
                <div class="mb-3 d-flex">
                    <div class="me-2"><span class="badge bg-primary"><i class="fas fa-clock"></i></span></div>
                    <div>
                        <div class="fw-semibold">Request Submitted</div>
                        <div class="text-muted small">{{ $customerService->created_at->format('M d, Y \a\t g:i A') }}</div>
                        <div class="text-muted small">by {{ $customerService->user->name }}</div>
                    </div>
                </div>
                @if($customerService->assignedAdmin)
                    <div class="mb-3 d-flex">
                        <div class="me-2"><span class="badge bg-warning text-dark"><i class="fas fa-user"></i></span></div>
                        <div>
                            <div class="fw-semibold">Assigned to Admin</div>
                            <div class="text-muted small">{{ $customerService->assignedAdmin->name }}</div>
                        </div>
                    </div>
                @endif
                @if($customerService->admin_reply)
                    <div class="mb-3 d-flex">
                        <div class="me-2"><span class="badge bg-success"><i class="fas fa-reply"></i></span></div>
                        <div>
                            <div class="fw-semibold">Admin Responded</div>
                            <div class="text-muted small">{{ $customerService->updated_at->format('M d, Y \a\t g:i A') }}</div>
                            @if($customerService->assignedAdmin)
                                <div class="text-muted small">by {{ $customerService->assignedAdmin->name }}</div>
                            @endif
                        </div>
                    </div>
                @endif
                @if($customerService->status === 'resolved' && $customerService->resolved_at)
                    <div class="d-flex">
                        <div class="me-2"><span class="badge bg-success"><i class="fas fa-check"></i></span></div>
                        <div>
                            <div class="fw-semibold">Request Resolved</div>
                            <div class="text-muted small">{{ $customerService->resolved_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
