@extends('layouts.admin')

@section('title', 'Appointment Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Appointment Details</h5>
                        <div>
                            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Appointment
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="text-muted">Name</label>
                                        <p class="mb-0">{{ $appointment->customer_name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Email</label>
                                        <p class="mb-0">{{ $appointment->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Phone</label>
                                        <p class="mb-0">{{ $appointment->phone }}</p>
                                    </div>
                                    @if($appointment->user)
                                    <div class="mb-3">
                                        <label class="text-muted">Registered User</label>
                                        <p class="mb-0">{{ $appointment->user->name }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Vehicle Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="text-muted">Vehicle Type</label>
                                        <p class="mb-0">{{ $appointment->vehicle_type }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Model</label>
                                        <p class="mb-0">{{ $appointment->vehicle_model }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Year</label>
                                        <p class="mb-0">{{ $appointment->vehicle_year }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Details -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Appointment Details</h6>
                                </div>
                                <div class="card-body">
                                    @isset($noShow)
                                        @php 
                                            $badge = $noShow['risk_level'] === 'high' ? 'danger' : ($noShow['risk_level'] === 'medium' ? 'warning' : 'success');
                                        @endphp
                                        <div class="mb-3">
                                            <span class="badge bg-{{ $badge }}">
                                                <i class="fas fa-user-times me-1"></i>
                                                No-show risk: {{ strtoupper($noShow['risk_level']) }} ({{ (int)round($noShow['probability']*100) }}%)
                                            </span>
                                        </div>
                                    @endisset
                                    <div class="mb-3">
                                        <label class="text-muted">Service Type</label>
                                        <p class="mb-0">{{ $appointment->service ? $appointment->service->name : 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Date</label>
                                        <p class="mb-0">{{ $appointment->appointment_date->format('F d, Y') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Time</label>
                                        <p class="mb-0">{{ $appointment->appointment_time->format('h:i A') }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="text-muted">Payment Method</label>
                                        <p class="mb-0">
                                            @if($appointment->paymentMethod)
                                                <span class="badge bg-info">
                                                    <i class="fas fa-credit-card me-1"></i>
                                                    {{ $appointment->paymentMethod->name }}
                                                </span>
                                                @if($appointment->paymentMethod->description)
                                                    <br><small class="text-muted">{{ $appointment->paymentMethod->description }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </p>
                                    </div>
                                    <!-- Payment Proof and Reference Number -->
                                    @if($appointment->payment_proof || $appointment->reference_number)
                                    <div class="mb-3">
                                        <label class="text-muted">Payment Proof & Reference</label>
                                        @if($appointment->payment_proof)
                                            <div class="mb-2">
                                                @php
                                                    $proofUrl = \App\Services\ImageService::getPaymentProofImageUrl($appointment->payment_proof);
                                                @endphp
                                                <a href="{{ $proofUrl }}" target="_blank">
                                                    <img src="{{ $proofUrl }}" alt="Payment Proof" style="max-width:200px; border-radius:8px; border:1px solid #e9ecef;" />
                                                </a>
                                            </div>
                                        @endif
                                        @if($appointment->reference_number)
                                            <div class="mb-2">
                                                <span class="badge bg-primary">Reference #: {{ $appointment->reference_number }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="mb-3">
                                        <label class="text-muted">Status</label>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $appointment->status === 'pending' ? 'warning' : 
                                                ($appointment->status === 'approved' ? 'info' : 
                                                ($appointment->status === 'confirmed' ? 'primary' : 
                                                ($appointment->status === 'completed' ? 'success' : 'danger'))) }}">
                                                <i class="fas fa-{{ $appointment->status === 'pending' ? 'clock' : 
                                                    ($appointment->status === 'approved' ? 'check' : 
                                                    ($appointment->status === 'confirmed' ? 'check-double' : 
                                                    ($appointment->status === 'completed' ? 'check-circle' : 'times'))) }} me-1"></i>
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                            @php
                                                $paymentStatusClass = [
                                                    'unpaid' => 'secondary',
                                                    'paid' => 'success',
                                                    'rejected' => 'danger'
                                                ][$appointment->payment_status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $paymentStatusClass }} ms-2">
                                                <i class="fas fa-money-bill-wave me-1"></i>{{ ucfirst($appointment->payment_status) }}
                                            </span>
                                            @if($appointment->cancelled_at)
                                                <small class="text-muted ms-2">
                                                    Cancelled: {{ $appointment->cancelled_at->format('M d, Y H:i') }}
                                                </small>
                                            @endif
                                        </p>
                                    </div>
                                    @php
                                        $techName = $appointment->assignedTechnician?->name ?? $appointment->technician;
                                    @endphp
                                    @if($techName)
                                    <div class="mb-3">
                                        <label class="text-muted">Assigned Technician</label>
                                        <p class="mb-0">
                                            <span class="badge bg-primary">
                                                <i class="fas fa-user-cog me-1"></i>
                                                {{ $techName }}
                                            </span>
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">Description</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $appointment->description ?: 'No description provided.' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Actions</h6>
                                </div>
                                <div class="card-body">
                                    @if(session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    @if($appointment->status === 'pending' && empty($techName))
                                        <div class="alert alert-warning mb-3">
                                            <strong>Please assign a technician before approving this appointment.</strong>
                                            <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-sm btn-primary ms-2">
                                                <i class="fas fa-user-cog me-1"></i> Edit & Assign Technician
                                            </a>
                                        </div>
                                    @endif
                                    <div class="d-flex gap-2">
                                        @if($appointment->status === 'pending')
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-info">
                                                    <i class="fas fa-check me-2"></i>Approve Appointment
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i>Cancel Appointment
                                                </button>
                                            </form>
                                        @endif

                                        @if($appointment->status === 'approved')
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-check-double me-2"></i>Confirm Appointment
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i>Cancel Appointment
                                                </button>
                                            </form>
                                        @endif

                                        @if($appointment->status === 'confirmed')
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check-circle me-2"></i>Mark as Completed
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-times me-2"></i>Cancel Appointment
                                                </button>
                                            </form>
                                        @endif

                                        @if($appointment->status === 'cancelled')
                                            <form action="{{ route('admin.appointments.updateStatus', $appointment) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-undo me-2"></i>Reopen Appointment
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 