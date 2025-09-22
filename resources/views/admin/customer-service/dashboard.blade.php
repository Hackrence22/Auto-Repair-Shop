@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Customer Service Dashboard</h1>
        <a href="{{ route('admin.customer-service.index') }}" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> View All Requests
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="badge bg-primary p-3 me-3"><i class="fas fa-clipboard-list"></i></div>
                    <div>
                        <div class="text-muted small">Total Requests</div>
                        <div class="fs-4 fw-semibold">{{ $totalRequests }}</div>
                </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="badge bg-warning text-dark p-3 me-3"><i class="fas fa-hourglass-half"></i></div>
                    <div>
                        <div class="text-muted small">Open Requests</div>
                        <div class="fs-4 fw-semibold">{{ $openRequests }}</div>
                </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="badge bg-orange text-dark p-3 me-3" style="background:#ffd8a8;color:#7f4e00;">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div>
                        <div class="text-muted small">In Progress</div>
                        <div class="fs-4 fw-semibold">{{ $inProgressRequests }}</div>
                </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="badge bg-success p-3 me-3"><i class="fas fa-check"></i></div>
                    <div>
                        <div class="text-muted small">Resolved</div>
                        <div class="fs-4 fw-semibold">{{ $resolvedRequests }}</div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Breakdown -->
    <div class="row g-3 mb-3">
        @foreach(['booking', 'shop', 'payment', 'appointment', 'other'] as $category)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="badge bg-secondary p-3 me-3"><i class="fas fa-folder"></i></div>
                        <div>
                            <div class="text-muted small">{{ ucfirst($category) }}</div>
                            <div class="fs-4 fw-semibold">{{ $categoryBreakdown[$category] ?? 0 }}</div>
                    </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Priority Alerts -->
    <div class="row g-3 mb-3">
        @if($urgentRequests > 0)
        <div class="col-12 col-md-6">
            <div class="alert alert-danger h-100 mb-0">
                <div class="d-flex align-items-center">
                    <div class="badge bg-danger me-2"><i class="fas fa-triangle-exclamation"></i></div>
                    <div>
                        <div class="fw-semibold">Urgent Requests</div>
                        <div class="small">{{ $urgentRequests }} urgent requests require immediate attention</div>
                        <div class="mt-2"><a href="{{ route('admin.customer-service.index', ['priority' => 'urgent']) }}" class="link-light">View Urgent Requests →</a></div>
                </div>
            </div>
            </div>
        </div>
        @endif

        @if($highPriorityRequests > 0)
        <div class="col-12 col-md-6">
            <div class="alert alert-warning h-100 mb-0">
                <div class="d-flex align-items-center">
                    <div class="badge bg-warning text-dark me-2"><i class="fas fa-bolt"></i></div>
                    <div>
                        <div class="fw-semibold">High Priority</div>
                        <div class="small">{{ $highPriorityRequests }} high priority requests need attention</div>
                        <div class="mt-2"><a href="{{ route('admin.customer-service.index', ['priority' => 'high']) }}" class="link-dark">View High Priority Requests →</a></div>
                </div>
            </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Recent Requests -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">Recent Customer Service Requests</h2>
        </div>
        
        @if($recentRequests->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer & Request</th>
                            <th>Category</th>
                            <th>Shop</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentRequests as $customerService)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $customerService->user->name }}</div>
                                    <div class="text-muted small">{{ $customerService->subject }}</div>
                                </td>
                                <td><span class="badge {{ $customerService->category_color }}">{{ ucfirst($customerService->category) }}</span></td>
                                <td>{{ $customerService->shop->name }}</td>
                                <td><span class="badge {{ $customerService->priority_color }}">{{ ucfirst($customerService->priority) }}</span></td>
                                <td><span class="badge {{ $customerService->status_color }}">{{ ucfirst(str_replace('_', ' ', $customerService->status)) }}</span></td>
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
        @else
            <div class="card-body text-center py-5">
                <div class="text-muted mb-2">No recent customer service requests.</div>
                <div class="text-muted small">When customers submit service requests, they will appear here.</div>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="mt-3 card shadow-sm">
        <div class="card-body">
            <h2 class="h6 mb-3">Quick Actions</h2>
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <a href="{{ route('admin.customer-service.index', ['status' => 'open']) }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-inbox me-1"></i> View Open Requests
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('admin.customer-service.index', ['priority' => 'urgent']) }}" class="btn btn-outline-danger w-100">
                        <i class="fas fa-triangle-exclamation me-1"></i> Urgent Issues
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <a href="{{ route('admin.customer-service.index', ['status' => 'unassigned']) }}" class="btn btn-outline-warning w-100">
                        <i class="fas fa-user-plus me-1"></i> Unassigned
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
