@extends('layouts.app')

@section('content')
<div class="container py-4 customer-service-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Customer Service Requests</h1>
        <a href="{{ route('customer-service.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Submit New Request
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        @if($customerServices->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Subject</th>
                            <th>Category</th>
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
                                    <div class="text-muted small">{{ Str::limit($customerService->message, 50) }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $customerService->category_color }}">{{ ucfirst($customerService->category) }}</span>
                                </td>
                                <td>{{ $customerService->shop->name }}</td>
                                <td>
                                    <span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span>
                                </td>
                                <td>{{ $customerService->assignedAdmin ? $customerService->assignedAdmin->name : 'Unassigned' }}</td>
                                <td class="text-muted small">{{ $customerService->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('customer-service.show', $customerService) }}" class="btn btn-sm btn-outline-primary">View</a>
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
                <div class="text-muted mb-3">No customer service requests found.</div>
                <a href="{{ route('customer-service.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Submit Your First Request
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
