@extends('layouts.admin')

@section('title', 'Edit Appointment')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Appointment</h1>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Appointments
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                id="customer_name" name="customer_name" value="{{ old('customer_name', $appointment->customer_name) }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', $appointment->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                id="phone" name="phone" value="{{ old('phone', $appointment->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="technician_id" class="form-label">Assign Technician</label>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select @error('technician_id') is-invalid @enderror" id="technician_id" name="technician_id">
                                    <option value="">Select technician</option>
                                </select>
                                <div class="form-check form-switch ms-2" title="Toggle to assign/unassign">
                                    <input class="form-check-input" type="checkbox" id="assign_toggle" checked>
                                    <label class="form-check-label" for="assign_toggle">Assign</label>
                                </div>
                            </div>
                            @error('technician_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">Filtered by this appointment's shop and date/time.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <input type="text" class="form-control @error('vehicle_type') is-invalid @enderror" 
                                id="vehicle_type" name="vehicle_type" value="{{ old('vehicle_type', $appointment->vehicle_type) }}" required>
                            @error('vehicle_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="vehicle_model" class="form-label">Vehicle Model</label>
                            <input type="text" class="form-control @error('vehicle_model') is-invalid @enderror" 
                                id="vehicle_model" name="vehicle_model" value="{{ old('vehicle_model', $appointment->vehicle_model) }}" required>
                            @error('vehicle_model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="vehicle_year" class="form-label">Vehicle Year</label>
                            <input type="text" class="form-control @error('vehicle_year') is-invalid @enderror" 
                                id="vehicle_year" name="vehicle_year" value="{{ old('vehicle_year', $appointment->vehicle_year) }}" required>
                            @error('vehicle_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service</label>
                            <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id', $appointment->service_id) == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Appointment Date</label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                id="appointment_date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                            @error('appointment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="appointment_time" class="form-label">Appointment Time</label>
                            <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                id="appointment_time" name="appointment_time" value="{{ old('appointment_time', $appointment->appointment_time->format('H:i')) }}" required>
                            @error('appointment_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                id="description" name="description" rows="5">{{ old('description', $appointment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $appointment->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const technicianSelect = document.getElementById('technician_id');
    const assignToggle = document.getElementById('assign_toggle');
    const currentTechnicianId = '{{ old('technician_id', $appointment->technician_id) }}';
    const shopId = '{{ $appointment->shop_id }}';
    const date = '{{ $appointment->appointment_date->format('Y-m-d') }}';

    // Populate technicians filtered by shop and date
    try {
        const url = '{{ route('admin.technicians.by-shop-date') }}' + `?shop_id=${shopId}&date=${date}`;
        const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (resp.ok) {
            const data = await resp.json();
            technicianSelect.innerHTML = '<option value="">Select technician</option>';
            (data || []).forEach(function(t) {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.name} ${t.is_available ? '' : '(Unavailable)'}`;
                if (String(t.id) === String(currentTechnicianId)) opt.selected = true;
                technicianSelect.appendChild(opt);
            });
        }
    } catch (_) {}

    // Toggle enable/disable assignment
    if (assignToggle) {
        const updateState = () => {
            technicianSelect.disabled = !assignToggle.checked;
            if (!assignToggle.checked) technicianSelect.value = '';
        };
        assignToggle.addEventListener('change', updateState);
        updateState();
    }
});
</script>
@endpush