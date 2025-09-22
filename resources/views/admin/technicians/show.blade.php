@extends('layouts.admin')

@section('title', 'Technician Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-user text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Technician Details</h1>
                        <p class="mb-0 text-secondary">View technician profile information</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.technicians.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Technicians
                    </a>
                    <a href="{{ route('admin.technicians.edit', $technician) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Technician
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Information -->
    <div class="row">
        <!-- Basic Information -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle me-2 text-primary"></i>
                        Profile Information
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($technician->profile_picture)
                        <img src="{{ Storage::disk('public')->url($technician->profile_picture) }}" alt="Profile Picture" 
                             class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    @else
                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 150px; height: 150px;">
                            <i class="fas fa-user text-white" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                    
                    <h4 class="fw-bold text-dark mb-2">{{ $technician->name }}</h4>
                    <p class="text-muted mb-3">{{ $technician->specialization ?: 'General Technician' }}</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge {{ $technician->status_badge }} fs-6">{{ ucfirst($technician->status) }}</span>
                        <span class="badge {{ $technician->is_available ? 'bg-success' : 'bg-danger' }} fs-6">
                            {{ $technician->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </div>
                    
                    <div class="text-start">
                        <div class="mb-2">
                            <strong>Shop:</strong> 
                            <span class="text-primary">{{ $technician->shop->name }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Experience:</strong> 
                            <span class="text-muted">{{ $technician->experience_text }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Hourly Rate:</strong> 
                            <span class="text-success">₱{{ number_format($technician->hourly_rate, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Information -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Contact & Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Email</label>
                                <p class="mb-0">{{ $technician->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Phone</label>
                                <p class="mb-0">{{ $technician->phone }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($technician->bio)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Bio</label>
                            <p class="mb-0">{{ $technician->bio }}</p>
                        </div>
                    @endif
                    
                    @if($technician->certifications)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted">Certifications</label>
                            <p class="mb-0">{{ $technician->certifications }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Working Schedule -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Working Schedule
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Working Hours</label>
                                <p class="mb-0">
                                    @if($technician->working_hours_start && $technician->working_hours_end)
                                        {{ $technician->working_hours_start->format('g:i A') }} - {{ $technician->working_hours_end->format('g:i A') }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted">Working Days</label>
                                <p class="mb-0">
                                    @if($technician->working_days && count($technician->working_days) > 0)
                                        {{ $technician->working_days_text }}
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>
                        Recent Appointments
                    </h5>
                </div>
                <div class="card-body">
                    @if($technician->appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($technician->appointments->take(5) as $appointment)
                                        <tr>
                                            <td>{{ $appointment->appointment_date->format('M d, Y') }}</td>
                                            <td>{{ $appointment->user->name }}</td>
                                            <td>{{ $appointment->service->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $appointment->status == 'completed' ? 'success' : ($appointment->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($appointment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($technician->appointments->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('admin.appointments.index', ['technician_id' => $technician->id]) }}" class="btn btn-outline-primary btn-sm">
                                    View All Appointments
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center mb-0">No appointments found for this technician.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 0.75rem;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endpush
