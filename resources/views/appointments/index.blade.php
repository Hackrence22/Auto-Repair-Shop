@extends('layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3" style="gap:0.75rem;">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-calendar-alt me-2"></i> Appointments
                    </h5>
                    <div class="d-flex align-items-center" style="gap:0.5rem;">
                        <form method="GET" action="{{ route('appointments.index') }}" class="d-flex" role="search">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-0" name="q" value="{{ request('q') }}" placeholder="Search date (YYYY-MM-DD), service, shop, status...">
                            </div>
                        </form>
                        <a href="{{ route('appointments.create') }}" class="btn btn-light text-primary">
                            <i class="fas fa-plus-circle me-2"></i>New
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($appointments->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No appointments found.</p>
                        </div>
                    @else
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Service</th>
                                        <th>Vehicle</th>
                                        <th>Shop</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($appointments as $appointment)
                                        <tr>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">{{ $appointment->appointment_date->format('M d, Y') }}</span>
                                                    <small class="text-muted">{{ date('h:i A', strtotime($appointment->appointment_time)) }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $appointment->service ? $appointment->service->name : 'N/A' }}</td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ $appointment->vehicle_type }}</span>
                                                    <small class="text-muted">{{ $appointment->vehicle_model }} ({{ $appointment->vehicle_year }})</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span>{{ $appointment->shop ? $appointment->shop->name : 'N/A' }}</span>
                                                    <small class="text-muted">{{ $appointment->shop ? $appointment->shop->address : 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($appointment->status) {
                                                        'pending' => 'warning',
                                                        'approved' => 'info',
                                                        'confirmed' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    
                                                    $statusIcon = match($appointment->status) {
                                                        'pending' => 'clock',
                                                        'approved' => 'check',
                                                        'confirmed' => 'check-double',
                                                        'completed' => 'check-circle',
                                                        'cancelled' => 'trash',
                                                        default => 'question-circle'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $statusClass }} d-flex align-items-center" style="width: fit-content;">
                                                    <i class="fas fa-{{ $statusIcon }} me-1"></i>
                                                    {{ ucfirst($appointment->status) }}
                                                    @if($appointment->status === 'cancelled' && $appointment->cancelled_at)
                                                        <small class="ms-1">({{ $appointment->cancelled_at->diffForHumans() }})</small>
                                                    @endif
                                                    @if($appointment->status === 'approved')
                                                        <small class="ms-1">({{ $appointment->updated_at->diffForHumans() }})</small>
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       data-bs-toggle="tooltip"
                                                       data-bs-placement="top"
                                                       title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($appointment->status === 'pending' || $appointment->status === 'approved')
                                                        <a href="{{ route('appointments.edit', $appointment) }}" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Edit Appointment">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <form action="{{ route('appointments.cancel', $appointment) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Cancel Appointment">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($appointment->status === 'cancelled')
                                                        <form action="{{ route('appointments.destroy', $appointment) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    data-bs-toggle="tooltip"
                                                                    data-bs-placement="top"
                                                                    title="Delete Appointment">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="d-md-none">
                            @foreach($appointments as $appointment)
                                @php
                                    $statusClass = match($appointment->status) {
                                        'pending' => 'warning',
                                        'approved' => 'info',
                                        'confirmed' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                    
                                    $statusIcon = match($appointment->status) {
                                        'pending' => 'clock',
                                        'approved' => 'check',
                                        'confirmed' => 'check-double',
                                        'completed' => 'check-circle',
                                        'cancelled' => 'trash',
                                        default => 'question-circle'
                                    };
                                @endphp
                                <div class="card appointment-card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $appointment->appointment_date->format('M d, Y') }}</h6>
                                                <small class="text-muted">{{ date('h:i A', strtotime($appointment->appointment_time)) }}</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $statusClass }} d-flex align-items-center">
                                            <i class="fas fa-{{ $statusIcon }} me-1"></i>
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="info-item">
                                                    <label class="text-muted small">Service</label>
                                                    <div class="fw-semibold">{{ $appointment->service ? $appointment->service->name : 'N/A' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="info-item">
                                                    <label class="text-muted small">Vehicle</label>
                                                    <div class="fw-semibold">{{ $appointment->vehicle_type }}</div>
                                                    <small class="text-muted">{{ $appointment->vehicle_model }} ({{ $appointment->vehicle_year }})</small>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="info-item">
                                                    <label class="text-muted small">Shop</label>
                                                    <div class="fw-semibold">{{ $appointment->shop ? $appointment->shop->name : 'N/A' }}</div>
                                                    <small class="text-muted">{{ $appointment->shop ? $appointment->shop->address : 'N/A' }}</small>
                                                </div>
                                            </div>
                                            @if($appointment->status === 'cancelled' && $appointment->cancelled_at)
                                                <div class="col-12">
                                                    <small class="text-muted">Cancelled {{ $appointment->cancelled_at->diffForHumans() }}</small>
                                                </div>
                                            @endif
                                            @if($appointment->status === 'approved')
                                                <div class="col-12">
                                                    <small class="text-muted">Approved {{ $appointment->updated_at->diffForHumans() }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-2 mt-3 flex-wrap">
                                            <a href="{{ route('appointments.show', $appointment) }}" 
                                               class="btn btn-sm btn-outline-info flex-fill">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                            
                                            @if($appointment->status === 'pending' || $appointment->status === 'approved')
                                                <a href="{{ route('appointments.edit', $appointment) }}" 
                                                   class="btn btn-sm btn-outline-warning flex-fill">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </a>
                                                
                                                <form action="{{ route('appointments.cancel', $appointment) }}" 
                                                      method="POST" 
                                                      class="flex-fill"
                                                      onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                </form>
                                            @endif

                                            @if($appointment->status === 'cancelled')
                                                <form action="{{ route('appointments.destroy', $appointment) }}" 
                                                      method="POST" 
                                                      class="flex-fill"
                                                      onsubmit="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="fas fa-trash me-1"></i>Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-center mt-2 mb-1">
                            {{ $appointments->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
                        </div>
                        <div class="text-center text-muted small">
                            Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} results
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Auto-hide alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            setTimeout(function() {
                const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                alert.close();
            }, 3000);
        }

        if (errorAlert) {
            setTimeout(function() {
                const alert = bootstrap.Alert.getOrCreateInstance(errorAlert);
                alert.close();
            }, 3000);
        }
    });
</script>
@endpush

@push('styles')
<style>
/* Mobile-responsive appointment cards */
@media (max-width: 767.98px) {
    .appointment-card {
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        border: 1px solid #e9ecef !important;
        transition: transform 0.2s ease-in-out !important;
        margin-bottom: 1rem !important;
        background: white !important;
    }
    
    .appointment-card:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
    
    .appointment-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-bottom: 1px solid #dee2e6 !important;
        padding: 1rem !important;
        border-radius: 12px 12px 0 0 !important;
        margin: 0 !important;
    }
    
    .appointment-card .card-body {
        padding: 1rem !important;
        margin: 0 !important;
    }
    
    .info-item {
        margin-bottom: 0.5rem !important;
    }
    
    .info-item label {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        margin-bottom: 0.35rem !important;
        color: #495057 !important;
        display: block !important;
    }
    
    .info-item .fw-semibold {
        font-size: 0.9rem !important;
        color: #212529 !important;
        margin-bottom: 0.2rem !important;
        font-weight: 600 !important;
    }
    
    .info-item small {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        font-weight: 500 !important;
    }
    
    .appointment-card .btn {
        font-size: 0.8rem !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        min-height: 36px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
        width: 100% !important;
    }
    
    .appointment-card .btn i {
        font-size: 0.8rem !important;
        margin-right: 0.45rem !important;
    }
    
    .appointment-card .badge {
        font-size: 0.75rem !important;
        padding: 0.4rem 0.6rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
    }
    
    .appointment-card .badge i {
        font-size: 0.7rem !important;
    }
    
    /* Card header improvements */
    .appointment-card .card-header h6 {
        font-size: 1rem !important;
        color: #212529 !important;
        font-weight: 700 !important;
        margin: 0 !important;
    }
    
    .appointment-card .card-header small {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        font-weight: 500 !important;
    }
    
    .appointment-card .card-header i {
        font-size: 1.05rem !important;
        color: #0d6efd !important;
    }
    
    /* Button group improvements */
    .appointment-card .d-flex.gap-2 {
        gap: 0.5rem !important;
        flex-wrap: wrap !important;
    }
    
    .appointment-card .flex-fill {
        flex: 1 1 auto !important;
        min-width: 0 !important;
    }
    
    /* Status badge improvements */
    .appointment-card .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
        font-weight: 700 !important;
    }
    
    .appointment-card .badge.bg-info {
        background-color: #0dcaf0 !important;
        color: #000 !important;
        font-weight: 700 !important;
    }
    
    .appointment-card .badge.bg-primary {
        background-color: #0d6efd !important;
        color: #fff !important;
        font-weight: 700 !important;
    }
    
    .appointment-card .badge.bg-success {
        background-color: #198754 !important;
        color: #fff !important;
        font-weight: 700 !important;
    }
    
    .appointment-card .badge.bg-danger {
        background-color: #dc3545 !important;
        color: #fff !important;
        font-weight: 700 !important;
    }
    
    .appointment-card .badge.bg-secondary {
        background-color: #6c757d !important;
        color: #fff !important;
        font-weight: 700 !important;
    }
    
    /* Button color improvements */
    .appointment-card .btn-outline-info {
        color: #0dcaf0 !important;
        border-color: #0dcaf0 !important;
        background-color: transparent !important;
    }
    
    .appointment-card .btn-outline-info:hover {
        background-color: #0dcaf0 !important;
        color: #000 !important;
    }
    
    .appointment-card .btn-outline-warning {
        color: #ffc107 !important;
        border-color: #ffc107 !important;
        background-color: transparent !important;
    }
    
    .appointment-card .btn-outline-warning:hover {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .appointment-card .btn-outline-danger {
        color: #dc3545 !important;
        border-color: #dc3545 !important;
        background-color: transparent !important;
    }
    
    .appointment-card .btn-outline-danger:hover {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
    
    /* Timestamp improvements */
    .appointment-card small.text-muted {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        font-weight: 500 !important;
        background-color: #f8f9fa !important;
        padding: 0.2rem 0.4rem !important;
        border-radius: 4px !important;
        display: inline-block !important;
    }

    .btn-primary {
        width: 50% !important;
    }
    
    /* Row and column improvements */
    .appointment-card .row.g-3 {
        margin: 0 !important;
    }
    
    .appointment-card .row.g-3 > * {
        padding: 0.5rem !important;
    }
    
    .appointment-card .col-6,
    .appointment-card .col-12 {
        padding: 0.5rem !important;
    }
    
    /* Container improvements */
    .container {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
    
    /* Card improvements */
    .card {
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    
    .card-header {
        padding: 0.6rem 0.9rem !important;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .card-body {
        padding: 0.9rem !important;
    }
    
    /* Button improvements */
    .btn {
        font-size: 0.8rem !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 8px !important;
    }
    
    .btn-sm {
        font-size: 0.75rem !important;
        padding: 0.35rem 0.6rem !important;
        border-radius: 6px !important;
    }
    
    /* Hide desktop table on mobile */
    .d-none.d-md-block {
        display: none !important;
    }
    
    /* Show mobile cards on mobile */
    .d-md-none {
        display: block !important;
    }

    /* Status badge adjustments for mobile */
    .appointment-card .badge {
        background-clip: padding-box !important;
    }
    .appointment-card .badge i {
        color: #ffffff !important;
    }
    .appointment-card .badge.bg-info,
    .appointment-card .badge.bg-primary,
    .appointment-card .badge.bg-success,
    .appointment-card .badge.bg-warning,
    .appointment-card .badge.bg-danger,
    .appointment-card .badge.bg-secondary {
        color: #ffffff !important;
    }
    .appointment-card .badge.bg-warning { color: #000 !important; }
}

/* Desktop table improvements */
@media (min-width: 768px) {
    .table th {
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
}

/* General mobile improvements */
@media (max-width: 767.98px) {
    .container {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    /* Top header styling on mobile */
    .card > .card-header.bg-white {
        background: linear-gradient(135deg, #1f3b59 0%, #1f84d9 100%) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 16px 16px 0 0 !important;
        padding: 0.6rem 0.8rem !important;
    }
    .card > .card-header.bg-white h5,
    .card > .card-header.bg-white h5 i {
        color: #fff !important;
        font-size: 1rem !important;
        margin: 0 !important;
    }
    .card > .card-header.bg-white .btn {
        padding: 0.45rem 0.8rem !important;
        font-size: 0.85rem !important;
        border-radius: 10px !important;
        background: linear-gradient(135deg, #2b6cb0 0%, #2f8de4 100%) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 3px 10px rgba(0,0,0,0.12) !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
    }
    
    .card-body {
        padding: 0.9rem;
    }
    
    .btn {
        font-size: 0.8rem !important;
        padding: 0.5rem 0.8rem !important;
        border-radius: 10px !important;
    }
    
    .btn-sm {
        font-size: 0.75rem !important;
        padding: 0.35rem 0.6rem !important;
        border-radius: 8px !important;
    }
}
</style>
@endpush 