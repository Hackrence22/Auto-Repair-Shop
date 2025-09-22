@extends('layouts.app')

@section('title', 'Appointment Details')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 details-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('appointments.index') }}" class="btn btn-outline-primary me-3 back-btn">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <h5 class="mb-0 page-title">
                                <i class="fas fa-wrench me-2"></i>Appointment Details
                            </h5>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row g-4">
                        <!-- Appointment Status -->
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1">Status</h6>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ][$appointment->status] ?? 'secondary';
                                                $paymentStatusClass = [
                                                    'unpaid' => 'secondary',
                                                    'paid' => 'success',
                                                    'rejected' => 'danger'
                                                ][$appointment->payment_status] ?? 'secondary';
                                            @endphp
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                            <span class="badge bg-{{ $statusClass }} px-3 py-2 fs-6">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                                <span class="badge bg-{{ $paymentStatusClass }} px-3 py-2 fs-6">
                                                <i class="fas fa-money-bill-wave me-1"></i>{{ ucfirst($appointment->payment_status) }}
                                            </span>
                                                @isset($noShow)
                                                    @php $riskBadge = $noShow['risk_level']==='high'?'danger':($noShow['risk_level']==='medium'?'warning':'success'); @endphp
                                                    <span class="badge bg-{{ $riskBadge }} px-3 py-2 fs-6"><i class="fas fa-user-times me-1"></i> No-show risk: {{ strtoupper($noShow['risk_level']) }} ({{ (int)round($noShow['probability']*100) }}%)</span>
                                                @endisset
                                            </div>
                                            @php $techName = $appointment->assignedTechnician?->name ?? $appointment->technician; @endphp
                                            @if($techName)
                                                <div class="mt-2">
                                                    <span class="badge bg-primary"><i class="fas fa-user-cog me-1"></i> Technician: {{ $techName }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        @if($appointment->cancelled_at)
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Cancelled: {{ $appointment->cancelled_at->format('M d, Y H:i') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date and Time -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-clock me-2"></i>Schedule
                                    </h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-calendar text-primary me-3 fa-lg"></i>
                                        <div>
                                            <div class="fw-bold">{{ $appointment->appointment_date->format('l, F d, Y') }}</div>
                                            <small class="text-muted">{{ $appointment->appointment_time }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-tools me-2"></i>Service
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-wrench text-primary me-3 fa-lg"></i>
                                        <div>
                                            <div class="fw-bold">{{ $appointment->service ? $appointment->service->name : 'N/A' }}</div>
                                            @if($appointment->service)
                                                <div class="text-muted">Price: ${{ number_format($appointment->service->price, 2) }}</div>
                                                <div class="text-muted small">{{ $appointment->service->description }}</div>
                                            @endif
                                            <small class="text-muted">Service Type</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shop Info -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-store me-2"></i>Shop
                                    </h6>
                                    @if($appointment->shop)
                                        <div class="fw-bold">{{ $appointment->shop->name }}</div>
                                        <div class="text-muted small">{{ $appointment->shop->full_address }}</div>
                                    @else
                                        <div class="text-muted">N/A</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-car me-2"></i>Vehicle Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-car text-primary me-3 fa-lg"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->vehicle_type }}</div>
                                                    <small class="text-muted">Vehicle Type</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-tag text-primary me-3 fa-lg"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->vehicle_model }}</div>
                                                    <small class="text-muted">Model</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-calendar-alt text-primary me-3 fa-lg"></i>
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->vehicle_year }}</div>
                                                    <small class="text-muted">Year</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technician & Payment Details -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3"><i class="fas fa-user-cog me-2"></i>Technician</h6>
                                    @php $techName = $appointment->assignedTechnician?->name ?? $appointment->technician; @endphp
                                    @if($techName)
                                        <div class="fw-bold">{{ $techName }}</div>
                                        <small class="text-muted">Assigned Technician</small>
                                    @else
                                        <div class="text-muted">Not assigned</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3"><i class="fas fa-money-bill-wave me-2"></i>Payment</h6>
                                    <div>
                                        <div class="fw-bold">Method: {{ $appointment->paymentMethod?->name ?? 'N/A' }}</div>
                                        @if($appointment->reference_number)
                                            <div class="mt-2"><span class="badge bg-primary">Ref: {{ $appointment->reference_number }}</span></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Proof and Reference Number -->
                        @if($appointment->payment_proof || $appointment->reference_number)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-receipt me-2"></i>Payment Proof & Reference
                                    </h6>
                                    <div class="mb-2">
                                        <label class="form-label">Payment Proof:</label><br>
                                        @php
                                            $proofUrl = \App\Services\ImageService::getPaymentProofImageUrl($appointment->payment_proof);
                                        @endphp
                                        <a href="{{ $proofUrl }}" target="_blank">
                                            <img src="{{ $proofUrl }}" alt="Payment Proof" style="max-width:200px; border-radius:8px; border:1px solid #e9ecef;" />
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>
                                        </div>
                                    @endif

                        <!-- Recommendations -->
                        @if(!empty($recs))
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-lightbulb me-2"></i>Recommended For You
                                    </h6>
                                    <div class="list-group list-group-flush">
                                        @foreach($recs as $rec)
                                            @php
                                                $service = $rec['service'] ?? null;
                                                $reason = $rec['reason'] ?? '';
                                                $type = ucfirst(str_replace('_', ' ', $rec['type'] ?? ''));
                                                $priority = $rec['priority'] ?? 'medium';
                                                $badgeClass = $priority === 'high' ? 'danger' : ($priority === 'low' ? 'success' : 'warning');
                                            @endphp
                                            <div class="list-group-item d-flex align-items-start justify-content-between">
                                                <div>
                                                    <div class="fw-bold">{{ $service?->name ?? 'Service' }}</div>
                                                    @if($reason)
                                                        <small class="text-muted">{{ $reason }}</small>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $badgeClass }}">{{ strtoupper($priority) }}</span>
                                                    @if($service)
                                                        <a href="{{ route('services.details', $service) }}" class="btn btn-sm btn-outline-primary ms-2">
                                                            <i class="fas fa-info-circle me-1"></i>View
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Actions -->
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                @if($appointment->status !== 'cancelled' && $appointment->appointment_date >= now())
                                    <a href="{{ route('appointments.edit', $appointment) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Edit Appointment
                                    </a>
                                    
                                    <form action="{{ route('appointments.cancel', $appointment) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="fas fa-times me-2"></i>Cancel Appointment
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('appointments.destroy', $appointment) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this appointment? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Desktop default header styling */
.details-header {
    background: linear-gradient(135deg, #1f3b59 0%, #1f84d9 100%) !important;
    border: none !important;
    border-radius: 16px 16px 0 0 !important;
}
.details-header .page-title,
.details-header .page-title i {
    color: #ffffff !important;
}
.details-header .back-btn {
    width: 50% !important;
    border-color: rgba(255,255,255,0.4) !important;
    color: #ffffff !important;
}
.details-header .back-btn:hover {
    width: 50% !important;
    background: rgba(255,255,255,0.1) !important;
}

/* Mobile adjustments */
@media (max-width: 767.98px) {
    .details-header { padding: 0.6rem 0.8rem !important; }
    .details-header .page-title { font-size: 1rem !important; }
    .details-header .page-title i { font-size: 1rem !important; }
    .details-header .back-btn { padding: 0.4rem 0.6rem !important; border-radius: 10px !important; }
}
</style>
@endpush

@push('scripts')
<script>
    // Auto-hide alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(function() {
                const alert = bootstrap.Alert.getOrCreateInstance(successAlert);
                alert.close();
            }, 3000);
        }
    });
</script>
@endpush 