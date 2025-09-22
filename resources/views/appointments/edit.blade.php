@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
<div class="card">
    <div class="card-header">
        <h2>Edit Appointment</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('appointments.update', $appointment) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                           id="customer_name" name="customer_name" 
                           value="{{ old('customer_name', $appointment->customer_name) }}" required>
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           id="email" name="email" 
                           value="{{ old('email', $appointment->email) }}" required readonly>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                           id="phone" name="phone" 
                           value="{{ old('phone', $appointment->phone) }}" required>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="vehicle_type" class="form-label">Vehicle Type</label>
                    <select class="form-select @error('vehicle_type') is-invalid @enderror" 
                            id="vehicle_type" name="vehicle_type" required>
                        <option value="">Select Vehicle Type</option>
                        <option value="Car" {{ old('vehicle_type', $appointment->vehicle_type) === 'Car' ? 'selected' : '' }}>Car</option>
                        <option value="Motorcycle" {{ old('vehicle_type', $appointment->vehicle_type) === 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                        <option value="SUV" {{ old('vehicle_type', $appointment->vehicle_type) === 'SUV' ? 'selected' : '' }}>SUV</option>
                        <option value="Truck" {{ old('vehicle_type', $appointment->vehicle_type) === 'Truck' ? 'selected' : '' }}>Truck</option>
                    </select>
                    @error('vehicle_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="vehicle_model" class="form-label">Vehicle Model</label>
                    <input type="text" class="form-control @error('vehicle_model') is-invalid @enderror" 
                           id="vehicle_model" name="vehicle_model" 
                           value="{{ old('vehicle_model', $appointment->vehicle_model) }}" required>
                    @error('vehicle_model')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="vehicle_year" class="form-label">Vehicle Year</label>
                    <input type="text" class="form-control @error('vehicle_year') is-invalid @enderror" 
                           id="vehicle_year" name="vehicle_year" 
                           value="{{ old('vehicle_year', $appointment->vehicle_year) }}" required>
                    @error('vehicle_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
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
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <div class="form-control bg-light">
                        <span class="badge bg-{{ $appointment->status === 'pending' ? 'warning' : 
                            ($appointment->status === 'confirmed' ? 'info' : 
                            ($appointment->status === 'completed' ? 'success' : 'danger')) }}">
                            {{ ucfirst($appointment->status) }}
                        </span>
                    </div>
                    <input type="hidden" name="status" value="{{ $appointment->status }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="appointment_date" class="form-label">Preferred Date</label>
                    <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                           id="appointment_date" name="appointment_date" 
                           value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                    @error('appointment_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="appointment_time" class="form-label">Preferred Time</label>
                    <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                           id="appointment_time" name="appointment_time" 
                           value="{{ old('appointment_time', $appointment->appointment_time->format('H:i')) }}" required>
                    @error('appointment_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Additional Notes</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                          id="description" name="description" rows="3">{{ old('description', $appointment->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="text-end">
                <a href="{{ route('appointments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Appointment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set minimum date to tomorrow for new dates
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('appointment_date').min = tomorrow.toISOString().split('T')[0];

    // Handle vehicle type change to show relevant services
    document.getElementById('vehicle_type').addEventListener('change', function() {
        const vehicleType = this.value;
        const serviceType = document.getElementById('service_type');
        const carServices = document.querySelectorAll('.car-services');
        const motorcycleServices = document.querySelectorAll('.motorcycle-services');

        // Hide all service groups first
        carServices.forEach(group => group.style.display = 'none');
        motorcycleServices.forEach(group => group.style.display = 'none');

        // Show relevant services based on vehicle type
        if (vehicleType === 'Car' || vehicleType === 'SUV' || vehicleType === 'Truck') {
            carServices.forEach(group => group.style.display = 'block');
        } else if (vehicleType === 'Motorcycle') {
            motorcycleServices.forEach(group => group.style.display = 'block');
        }
    });

    // Trigger vehicle type change event on page load to show correct services
    document.getElementById('vehicle_type').dispatchEvent(new Event('change'));
</script>
@endpush 