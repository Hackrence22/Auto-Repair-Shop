@extends('layouts.admin')

@section('title', 'Shop Management')

@section('breadcrumb')
<li class="breadcrumb-item active">Shops</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
                <h1 class="h3 mb-0">Shop Management</h1>
                <div class="d-flex align-items-center" style="gap:0.5rem;">
                    <form method="GET" action="{{ route('admin.shops.index') }}" class="d-flex" role="search">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search shop, owner, city, email...">
                        </div>
                    </form>
                    <a href="{{ route('admin.shops.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Shop
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                @forelse($shops as $shop)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 shop-card position-relative">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-2 border-white" style="width: 48px; height: 48px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                    <div>
                                        <div class="fw-bold fs-5">{{ $shop->name }}</div>
                                        <div class="small text-light">{{ $shop->city }}</div>
                                    </div>
                                </div>
                                <span class="badge {{ $shop->is_active ? 'bg-success' : 'bg-danger' }} ms-2 text-white" style="font-size: 0.95em;">{{ $shop->is_active ? 'Active' : 'Inactive' }}</span>
                            </div>
                            <div class="card-body pb-2">
                                <div class="mb-2">
                                    <span class="fw-bold text-muted">Owner:</span> {{ $shop->owner_name }}<br>
                                    <span class="fw-bold text-muted">Phone:</span> <a href="tel:{{ $shop->phone }}" class="text-decoration-none">{{ $shop->phone }}</a>
                                </div>
                                <div class="mb-2">
                                    <span class="fw-bold text-muted">Address:</span> <span class="text-muted">{{ $shop->full_address }}</span>
                                </div>
                                <div class="mb-2">
                                    <span class="fw-bold text-muted">Hours:</span> {{ $shop->operating_hours }}<br>
                                    <span class="fw-bold text-muted">Days:</span> {{ $shop->working_days_text }}
                                </div>
                                @if($shop->description)
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">About:</span> <span class="text-muted">{{ Str::limit($shop->description, 60) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex gap-2 mt-3 flex-wrap">
                                    <span class="badge bg-primary text-white"><i class="fas fa-tools me-1"></i> {{ $shop->services->count() }} Services</span>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ $shop->slotSettings->count() }} Slots</span>
                                    <span class="badge bg-success text-white"><i class="fas fa-calendar-check me-1"></i> {{ $shop->appointments->count() }} Appointments</span>
                                    <span class="badge bg-secondary text-white"><i class="fas fa-star me-1" style="color:#ffc107;"></i> {{ $shop->average_rating ? number_format($shop->average_rating, 1) : 'No ratings' }} ({{ $shop->ratings_count ?? 0 }})</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pt-2 pb-3">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.shops.show', $shop) }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.shops.edit', $shop) }}" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Edit Shop"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.shops.toggle-status', $shop) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-{{ $shop->is_active ? 'secondary' : 'success' }} btn-sm" data-bs-toggle="tooltip" title="{{ $shop->is_active ? 'Deactivate' : 'Activate' }}" onclick="return confirm('Are you sure you want to {{ $shop->is_active ? 'deactivate' : 'activate' }} this shop?')">
                                            <i class="fas fa-{{ $shop->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" title="Delete Shop" onclick="confirmDelete({{ $shop->id }}, '{{ $shop->name }}')"><i class="fas fa-trash"></i></button>
                                </div>
                                <div>
                                    @if($shop->is_active)
                                        @if($shop->isCurrentlyOpen())
                                            <span class="badge bg-success">Open Now</span>
                                        @else
                                            @php
                                                $now = \Carbon\Carbon::now();
                                                $dayOfWeek = $now->dayOfWeek;
                                                $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;
                                                $currentTime = $now->format('H:i:s');
                                            @endphp
                                            @if(!$shop->isOpenOnDay($dayOfWeek))
                                                <span class="badge bg-secondary">Closed Today</span>
                                            @elseif($currentTime < $shop->opening_time->format('H:i:s'))
                                                <span class="badge bg-warning">Opens {{ $shop->opening_time->format('h:i A') }}</span>
                                            @else
                                                <span class="badge bg-secondary">Closed</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-store fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No shops found</h5>
                        <p class="text-muted">Create your first shop to get started.</p>
                        <a href="{{ route('admin.shops.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Shop
                        </a>
                    </div>
                @endforelse
            </div>
            @if(isset($shops) && method_exists($shops, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $shops->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
                </div>
                <div class="text-center text-muted small mt-2">
                    Showing {{ $shops->firstItem() }} to {{ $shops->lastItem() }} of {{ $shops->total() }} results
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the shop "<span id="shopName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone. All services, appointments, and slot settings associated with this shop will also be deleted.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Shop</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <img id="modalImage" src="" alt="Shop Image" 
                     style="max-width: 100%; max-height: 80vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete(shopId, shopName) {
    document.getElementById('shopName').textContent = shopName;
    document.getElementById('deleteForm').action = `/admin/shops/${shopId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush

@push('styles')
<style>
/* Card styling for better visual appeal */
.shop-card {
    transition: transform 0.2s ease-in-out;
}

.shop-card:hover {
    transform: translateY(-2px);
}

/* Tooltip styling */
.tooltip {
    font-size: 0.875rem;
}

/* Badge styling for better visibility */
.badge {
    font-weight: 500;
    letter-spacing: 0.025em;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: white !important;
}

.badge.bg-success {
    background-color: #198754 !important;
    color: white !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

.badge.bg-dark {
    background-color: #212529 !important;
    color: white !important;
}
</style>
@endpush 