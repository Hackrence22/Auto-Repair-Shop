@extends('layouts.admin')

@section('title', 'Slot Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
        <h1 class="h3 mb-0">Slot Settings</h1>
        <div class="d-flex align-items-center" style="gap:0.5rem;">
            <form method="GET" action="{{ route('admin.slot-settings.index') }}" class="d-flex" role="search">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search shop or time/slots...">
                </div>
            </form>
            <a href="{{ route('admin.slot-settings.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Time Slot
            </a>
        </div>
    </div>

    @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

            @php
                $slotSettingsByShop = $slotSettings->groupBy('shop_id');
            @endphp

            <div class="row g-4">
                @forelse($slotSettingsByShop as $shopId => $shopSlotSettings)
                    @php
                        $shop = $shopSlotSettings->first()->shop;
                    @endphp
                    
                    <!-- Shop Header Card -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-2 border-white" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <h5 class="mb-0 fw-bold">{{ $shop->name }}</h5>
                                        <small class="text-light">{{ $shop->full_address }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-light text-dark fs-6">{{ $shopSlotSettings->count() }} Time Slots</div>
                                    <br>
                                    <small class="text-light">{{ $shop->operating_hours }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slot Cards -->
                    @foreach($shopSlotSettings as $setting)
                        @php
                            $hours = \Carbon\Carbon::parse($setting->start_time)->diffInHours(\Carbon\Carbon::parse($setting->end_time));
                            $totalSlots = $hours * $setting->slots_per_hour;
                        @endphp
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm border-0 slot-card">
                                <div class="card-header bg-info text-white d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-clock fa-lg"></i>
                                        <div>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($setting->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($setting->end_time)->format('h:i A') }}</div>
                                            <small class="text-light">{{ $hours }} hours</small>
                                        </div>
                                    </div>
                                    <span class="badge {{ $setting->is_active ? 'bg-success' : 'bg-danger' }} text-white">{{ $setting->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Slots per Hour:</span> 
                                        <span class="fw-bold text-info fs-5">{{ $setting->slots_per_hour }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Total Slots:</span> 
                                        <span class="badge bg-dark text-white fs-6">{{ $totalSlots }} slots</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="fw-bold text-muted">Duration:</span> 
                                        <span class="text-muted">{{ $hours }} hours</span>
                                    </div>
                                    <div class="mb-3">
                                        <span class="fw-bold text-muted">Time Range:</span>
                                        <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($setting->start_time)->format('h:i A') }} to {{ \Carbon\Carbon::parse($setting->end_time)->format('h:i A') }}</p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-primary text-white"><i class="fas fa-store me-1"></i> {{ $shop->name }}</span>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ $setting->slots_per_hour }}/hr</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pt-2 pb-3">
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.slot-settings.show', $setting) }}" class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.slot-settings.edit', $setting) }}" class="btn btn-outline-warning btn-sm" data-bs-toggle="tooltip" title="Edit Slot">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.slot-settings.destroy', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this slot setting?')" data-bs-toggle="tooltip" title="Delete Slot">
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
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No slot settings found</h5>
                        <p class="text-muted">Start by creating time slots for your shops.</p>
                        <a href="{{ route('admin.slot-settings.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Time Slot
                        </a>
                    </div>
                @endforelse
            </div>
            @if(isset($slotSettings) && method_exists($slotSettings, 'links'))
                <div class="d-flex justify-content-center mt-3">
                    {{ $slotSettings->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
                </div>
                <div class="text-center text-muted small mt-2">
                    Showing {{ $slotSettings->firstItem() }} to {{ $slotSettings->lastItem() }} of {{ $slotSettings->total() }} results
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Slot Details Modal -->
<div class="modal fade" id="slotDetailsModal" tabindex="-1" aria-labelledby="slotDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="slotDetailsModalLabel">
                    <i class="fas fa-clock me-2"></i>Time Slot Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Slot Information -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-info mb-3">
                            <i class="fas fa-clock me-2"></i>Time Slot Information
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Time Range:</td>
                                <td id="modal-slot-time"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Slots per Hour:</td>
                                <td id="modal-slots-per-hour"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Total Hours:</td>
                                <td id="modal-total-hours"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Total Slots:</td>
                                <td id="modal-total-slots"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Status:</td>
                                <td id="modal-slot-status"></td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Shop Information -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-store me-2"></i>Shop Information
                        </h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="fw-bold text-muted">Shop Name:</td>
                                <td id="modal-slot-shop-name"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Owner:</td>
                                <td id="modal-slot-shop-owner"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Phone:</td>
                                <td id="modal-slot-shop-phone"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Address:</td>
                                <td id="modal-slot-shop-address"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Hours:</td>
                                <td id="modal-slot-shop-hours"></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Working Days:</td>
                                <td id="modal-slot-shop-days"></td>
                            </tr>
                </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection 

@push('scripts')
<script>
function showSlotDetails(startTime, endTime, slotsPerHour, status, shopName, shopOwner, shopPhone, shopAddress, shopHours, shopDays) {
    // Calculate total hours and slots
    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);
    const totalHours = (end - start) / (1000 * 60 * 60);
    const totalSlots = totalHours * slotsPerHour;
    
    // Populate modal with slot details
    document.getElementById('modal-slot-time').textContent = `${startTime} - ${endTime}`;
    document.getElementById('modal-slots-per-hour').textContent = slotsPerHour;
    document.getElementById('modal-total-hours').textContent = `${totalHours} hours`;
    document.getElementById('modal-total-slots').innerHTML = `<span class="badge bg-secondary">${totalSlots} slots</span>`;
    document.getElementById('modal-slot-status').innerHTML = `<span class="badge bg-${status === 'Active' ? 'success' : 'danger'}">${status}</span>`;
    
    // Populate shop details
    document.getElementById('modal-slot-shop-name').textContent = shopName;
    document.getElementById('modal-slot-shop-owner').textContent = shopOwner;
    document.getElementById('modal-slot-shop-phone').innerHTML = `<a href="tel:${shopPhone}" class="text-decoration-none"><i class="fas fa-phone me-1"></i>${shopPhone}</a>`;
    document.getElementById('modal-slot-shop-address').textContent = shopAddress;
    document.getElementById('modal-slot-shop-hours').textContent = shopHours;
    document.getElementById('modal-slot-shop-days').textContent = shopDays;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('slotDetailsModal')).show();
}
</script>
@endpush

@push('styles')
<style>
/* Card styling for better visual appeal */
.slot-card {
    transition: transform 0.2s ease-in-out;
}

.slot-card:hover {
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