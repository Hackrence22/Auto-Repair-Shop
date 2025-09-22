@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Customer Service Management</h1>
        <a href="{{ route('admin.customer-service.dashboard') }}" class="btn btn-success">
            <i class="fas fa-chart-line me-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.customer-service.index') }}" class="row g-3">
                <div class="col-12 col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Subject, message, or user">
            </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
                <div class="col-6 col-md-2">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="">All</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
                <div class="col-6 col-md-2">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All</option>
                    <option value="booking" {{ request('category') == 'booking' ? 'selected' : '' }}>Booking</option>
                    <option value="shop" {{ request('category') == 'shop' ? 'selected' : '' }}>Shop</option>
                    <option value="payment" {{ request('category') == 'payment' ? 'selected' : '' }}>Payment</option>
                    <option value="appointment" {{ request('category') == 'appointment' ? 'selected' : '' }}>Appointment</option>
                    <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
                <div class="col-12 col-md-3 d-flex align-items-end justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Apply
                </button>
                    <a href="{{ route('admin.customer-service.index') }}" class="btn btn-secondary">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                </a>
            </div>
        </form>
        </div>
    </div>

    <!-- Customer Service Requests Table -->
    <div class="card shadow-sm">
        @if($customerServices->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Request Details</th>
                            <th>Category</th>
                            <th>Customer</th>
                            <th>Shop</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customerServices as $customerService)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $customerService->subject }}</div>
                                    <div class="text-muted small">{{ Str::limit($customerService->message, 60) }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $customerService->category_color }}">{{ ucfirst($customerService->category) }}</span>
                                </td>
                                <td>
                                    <div>{{ $customerService->user->name }}</div>
                                    <div class="text-muted small">{{ $customerService->user->email }}</div>
                                </td>
                                <td>{{ $customerService->shop->name }}</td>
                                <td>
                                    <span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span>
                                </td>
                                <td>
                                    <div>{{ $customerService->assignedAdmin ? $customerService->assignedAdmin->name : 'Unassigned' }}</div>
                                    @if(!$customerService->assignedAdmin)
                                        <form action="{{ route('admin.customer-service.assign-to-me', $customerService) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-user-plus me-1"></i> Assign to me
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $customerService->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.customer-service.show', $customerService) }}" class="btn btn-sm btn-outline-primary me-1">View</a>
                                    <a href="{{ route('admin.customer-service.edit', $customerService) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $customerServices->onEachSide(1)->links() }}
            </div>
        @else
            <div class="card-body text-center py-5">
                <div class="text-muted mb-2">No customer service requests found.</div>
                <div class="text-muted small">When customers submit service requests, they will appear here.</div>
            </div>
        @endif
    </div>
</div>
@endsection
