@extends('layouts.admin')

@section('title', 'Services')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
                <h1 class="h3 mb-0">Services</h1>
                <div class="d-flex align-items-center" style="gap:0.5rem;">
                    <form method="GET" action="{{ route('admin.services.index') }}" class="d-flex" role="search">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, type, shop, description...">
                        </div>
                    </form>
                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Service
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $servicesByShop = $services->groupBy('shop_id');
            @endphp

            <div class="row g-4">
                @forelse($servicesByShop as $shopId => $shopServices)
                    @php
                        $shop = $shopServices->first()->shop;
                    @endphp
                    
                    <!-- Shop Header Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-2 border-white" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-0 fw-bold">{{ $shop->name }}</h5>
                                        <small class="text-light">{{ $shop->full_address }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-light text-dark fs-6">{{ $shopServices->count() }} Services</div>
                                    <br>
                                    <small class="text-light">{{ $shop->operating_hours }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Service Cards -->
                    @foreach($shopServices as $service)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 service-card">
                                <div class="card-header bg-{{ $service->type === 'repair' ? 'danger' : ($service->type === 'maintenance' ? 'warning' : ($service->type === 'inspection' ? 'info' : 'secondary')) }} text-white d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-{{ $service->type === 'repair' ? 'wrench' : ($service->type === 'maintenance' ? 'cog' : ($service->type === 'inspection' ? 'search' : 'tools')) }} fa-lg"></i>
                                        <div>
                                            <div class="fw-bold">{{ $service->name }}</div>
                                            <small class="text-light">ID: {{ $service->id }}</small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-danger' }} text-white">{{ $service->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Type:</span> 
                                        <span class="badge bg-{{ $service->type === 'repair' ? 'danger' : ($service->type === 'maintenance' ? 'warning' : ($service->type === 'inspection' ? 'info' : 'secondary')) }} text-white">{{ ucfirst($service->type) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Price:</span> 
                                        <span class="fw-bold text-success fs-5">₱{{ number_format($service->price, 2) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Duration:</span> 
                                        @php
                                            $mins = (int) $service->duration;
                                            $days = intdiv($mins, 1440);
                                            $mins -= $days * 1440;
                                            $hours = intdiv($mins, 60);
                                            $mins -= $hours * 60;
                                            $parts = [];
                                            if ($days > 0) $parts[] = $days . 'd';
                                            if ($hours > 0) $parts[] = $hours . 'h';
                                            if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';
                                            $humanDuration = implode(' ', $parts);
                                        @endphp
                                        <span class="text-muted">{{ $humanDuration }}</span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="fw-bold text-muted">Description:</span>
                                        <p class="text-muted mb-0">{{ Str::limit($service->description, 80) }}</p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-primary text-white"><i class="fas fa-store me-1"></i> {{ $shop->name }}</span>
                                        <span class="badge bg-dark text-white"><i class="fas fa-clock me-1"></i> {{ $humanDuration }}</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pt-2 pb-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.services.show', $service) }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Edit Service">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service?')" data-bs-toggle="tooltip" title="Delete Service">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Shop: {{ $shop->name }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No services found</h5>
                        <p class="text-muted">Start by creating services for your shops.</p>
                        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Service
                        </a>
                    </div>
                @endforelse
            </div>
            @if(isset($services) && method_exists($services, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $services->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
                </div>
                <div class="text-center text-muted small mt-2">
                    Showing {{ $services->firstItem() }} to {{ $services->lastItem() }} of {{ $services->total() }} results
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="serviceDetailsModalLabel">
                    <i class="fas fa-tools me-2"></i>Service Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Service Information -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-tools me-2"></i>Service Information
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Service Name:</td>
                                <td id="modal-service-name"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Type:</td>
                                <td id="modal-service-type"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Price:</td>
                                <td class="fw-bold text-success" id="modal-service-price"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Duration:</td>
                                <td id="modal-service-duration"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Status:</td>
                                <td id="modal-service-status"></td>
                            </tr>
                        </table>
                        <div class="mb-3">
                            <label class="fw-bold text-muted">Description:</label>
                            <p class="mt-2" id="modal-service-description"></p>
                        </div>
                    </div>
                    
                    <!-- Shop Information -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-info mb-3">
                            <i class="fas fa-store me-2"></i>Shop Information
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Shop Name:</td>
                                <td id="modal-shop-name"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Owner:</td>
                                <td id="modal-shop-owner"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Phone:</td>
                                <td id="modal-shop-phone"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Address:</td>
                                <td id="modal-shop-address"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Hours:</td>
                                <td id="modal-shop-hours"></td>
                                    </tr>
                                    <tr>
                                <td class="fw-bold text-muted">Working Days:</td>
                                <td id="modal-shop-days"></td>
                                    </tr>
                        </table>
                    </div>
                </div>
                    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="modal-edit-link">
                    <i class="fas fa-edit"></i> Edit Service
                </a>
            </div>
        </div>
    </div>
</div>

@endsection 

@push('scripts')
<script>
function showServiceDetails(serviceId, serviceName, description, type, price, duration, status, shopName, shopOwner, shopPhone, shopAddress, shopHours, shopDays) {
    // Populate modal with service details
    document.getElementById('modal-service-name').textContent = serviceName;
    document.getElementById('modal-service-description').textContent = description;
    document.getElementById('modal-service-type').innerHTML = `<span class="badge bg-info">${type.charAt(0).toUpperCase() + type.slice(1)}</span>`;
    document.getElementById('modal-service-price').textContent = `₱${price}`;
    // Format duration to d h m
    try {
        let mins = parseInt(duration, 10);
        let days = Math.floor(mins / 1440); mins -= days * 1440;
        let hours = Math.floor(mins / 60); mins -= hours * 60;
        const parts = [];
        if (days > 0) parts.push(`${days}d`);
        if (hours > 0) parts.push(`${hours}h`);
        if (mins > 0 || parts.length === 0) parts.push(`${mins}m`);
        document.getElementById('modal-service-duration').textContent = parts.join(' ');
    } catch (_) {
        document.getElementById('modal-service-duration').textContent = `${duration} minutes`;
    }
    document.getElementById('modal-service-status').innerHTML = `<span class="badge bg-${status === 'Active' ? 'success' : 'danger'}">${status}</span>`;
    
    // Populate shop details
    document.getElementById('modal-shop-name').textContent = shopName;
    document.getElementById('modal-shop-owner').textContent = shopOwner;
    document.getElementById('modal-shop-phone').innerHTML = `<a href="tel:${shopPhone}" class="text-decoration-none"><i class="fas fa-phone me-1"></i>${shopPhone}</a>`;
    document.getElementById('modal-shop-address').textContent = shopAddress;
    document.getElementById('modal-shop-hours').textContent = shopHours;
    document.getElementById('modal-shop-days').textContent = shopDays;
    
    // Set edit link
    document.getElementById('modal-edit-link').href = `/admin/services/${serviceId}/edit`;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('serviceDetailsModal')).show();
}
</script>
@endpush

@push('styles')
<style>
/* Card styling for better visual appeal */
.service-card {
    transition: transform 0.2s ease-in-out;
}

.service-card:hover {
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