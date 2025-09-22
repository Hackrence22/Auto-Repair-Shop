@extends('layouts.admin')

@section('title', 'Edit Technician')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-user-edit text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Edit Technician</h1>
                        <p class="mb-0 text-secondary">Update technician profile information</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.technicians.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Technicians
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Technician Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-cog me-2 text-primary"></i>
                        Technician Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.technicians.update', $technician) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label for="shop_id" class="form-label fw-bold">Shop <span class="text-danger">*</span></label>
                                    <select name="shop_id" id="shop_id" class="form-select @error('shop_id') is-invalid @enderror" required>
                                        <option value="">Select Shop</option>
                                        @foreach($shops as $shop)
                                            <option value="{{ $shop->id }}" {{ old('shop_id', $technician->shop_id) == $shop->id ? 'selected' : '' }}>
                                                {{ $shop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shop_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $technician->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $technician->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $technician->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="specialization" class="form-label fw-bold">Specialization</label>
                                    <input type="text" name="specialization" id="specialization" class="form-control @error('specialization') is-invalid @enderror" 
                                           value="{{ old('specialization', $technician->specialization) }}" placeholder="e.g., Engine Repair, Brake Systems">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="experience_years" class="form-label fw-bold">Years of Experience</label>
                                    <input type="number" name="experience_years" id="experience_years" class="form-control @error('experience_years') is-invalid @enderror" 
                                           value="{{ old('experience_years', $technician->experience_years) }}" min="0" max="50">
                                    @error('experience_years')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Additional Information</h6>
                                
                                <div class="mb-3">
                                    <label for="bio" class="form-label fw-bold">Bio</label>
                                    <textarea name="bio" id="bio" rows="4" class="form-control @error('bio') is-invalid @enderror" 
                                              placeholder="Tell us about the technician's background and skills...">{{ old('bio', $technician->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="certifications" class="form-label fw-bold">Certifications</label>
                                    <input type="text" name="certifications" id="certifications" class="form-control @error('certifications') is-invalid @enderror" 
                                           value="{{ old('certifications', $technician->certifications) }}" placeholder="e.g., ASE Certified, Manufacturer Training">
                                    @error('certifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label fw-bold">Profile Picture</label>
                                    @if($technician->profile_picture)
                                        <div class="mb-2">
                                            <img src="{{ asset('uploads/' . $technician->profile_picture) }}" alt="Current Profile Picture" 
                                                 class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                            <div class="form-text">Current profile picture</div>
                                        </div>
                                    @endif
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control @error('profile_picture') is-invalid @enderror" 
                                           accept="image/*">
                                    <div class="form-text">Leave empty to keep current image. Recommended size: 300x300 pixels. Max file size: 2MB.</div>
                                    @error('profile_picture')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="active" {{ old('status', $technician->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $technician->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="on_leave" {{ old('status', $technician->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_available" id="is_available" class="form-check-input" 
                                               value="1" {{ old('is_available', $technician->is_available) ? 'checked' : '' }}>
                                        <label for="is_available" class="form-check-label fw-bold">Available for Appointments</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Working Hours -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">Working Hours</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="working_hours_start" class="form-label fw-bold">Start Time</label>
                                            <input type="time" name="working_hours_start" id="working_hours_start" 
                                                   class="form-control @error('working_hours_start') is-invalid @enderror" 
                                                   value="{{ old('working_hours_start', $technician->working_hours_start ? $technician->working_hours_start->format('H:i') : '') }}">
                                            @error('working_hours_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="working_hours_end" class="form-label fw-bold">End Time</label>
                                            <input type="time" name="working_hours_end" id="working_hours_end" 
                                                   class="form-control @error('working_hours_end') is-invalid @enderror" 
                                                   value="{{ old('working_hours_end', $technician->working_hours_end ? $technician->working_hours_end->format('H:i') : '') }}">
                                            @error('working_hours_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Working Days</label>
                                            <div class="row">
                                                @php
                                                    $days = [
                                                        1 => 'Monday',
                                                        2 => 'Tuesday', 
                                                        3 => 'Wednesday',
                                                        4 => 'Thursday',
                                                        5 => 'Friday',
                                                        6 => 'Saturday',
                                                        7 => 'Sunday'
                                                    ];
                                                    $currentWorkingDays = old('working_days', $technician->working_days ?? []);
                                                @endphp
                                                @foreach($days as $key => $day)
                                                    <div class="col-6">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="working_days[]" id="day_{{ $key }}" 
                                                                   class="form-check-input" value="{{ $key }}"
                                                                   {{ in_array($key, $currentWorkingDays) ? 'checked' : '' }}>
                                                            <label for="day_{{ $key }}" class="form-check-label">{{ $day }}</label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('working_days')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hourly Rate -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hourly_rate" class="form-label fw-bold">Hourly Rate (₱)</label>
                                    <input type="number" name="hourly_rate" id="hourly_rate" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           value="{{ old('hourly_rate', $technician->hourly_rate) }}" min="0" step="0.01" placeholder="0.00">
                                    @error('hourly_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.technicians.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Technician
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.form-label {
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}
</style>
@endpush
