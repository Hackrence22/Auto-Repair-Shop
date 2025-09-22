@extends('layouts.admin')

@section('title', 'Appointments')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4" style="gap:0.75rem;">
        <h1 class="h3 mb-0">Appointments</h1>
        <div class="d-flex align-items-center" style="gap:0.5rem;">
            <form method="GET" action="{{ route('admin.appointments.index') }}" class="d-flex" role="search">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, email, shop, service, status...">
                </div>
            </form>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Create New Appointment
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @foreach($appointmentsByShop as $shopName => $appointmentsGroup)
        <h4 class="mt-4 mb-3 text-primary"><i class="fas fa-store me-2"></i>{{ $shopName }}</h4>
        <div class="table-responsive mb-5">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Service</th>
                        <th>Vehicle</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointmentsGroup as $appointment)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $appointment->appointment_date->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ date('h:i A', strtotime($appointment->appointment_time)) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($appointment->user && $appointment->user->profile_picture)
                                        <img src="{{ Storage::disk('public')->url($appointment->user->profile_picture) }}" alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                    <div class="d-flex flex-column">
                                        <span>{{ $appointment->customer_name }}</span>
                                        <small class="text-muted">{{ $appointment->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($appointment->service)
                                    {{ $appointment->service->name }}
                                @elseif($appointment->service_type)
                                    {{ $appointment->service_type }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $appointment->vehicle_type }}</span>
                                    <small class="text-muted">{{ $appointment->vehicle_model }} ({{ $appointment->vehicle_year }})</small>
                                </div>
                            </td>
                            <td>
                                @if($appointment->paymentMethod)
                                    <span class="badge bg-info">
                                        <i class="fas fa-credit-card me-1"></i>
                                        {{ $appointment->paymentMethod->name }}
                                    </span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
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
                                        'cancelled' => 'times',
                                        default => 'question'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">
                                    <i class="fas fa-{{ $statusIcon }} me-1"></i>
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.appointments.edit', $appointment) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No appointments found for this shop.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    @if(isset($appointments) && method_exists($appointments, 'links'))
        <div class="d-flex justify-content-center mt-2">
            {{ $appointments->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
        </div>
        <div class="text-center text-muted small mt-2">
            Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} results
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush 