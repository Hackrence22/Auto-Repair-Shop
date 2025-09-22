@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="fas fa-cogs fa-lg me-2"></i>
                    <h3 class="card-title mb-0">Settings</h3>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Business Hours -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light d-flex align-items-center">
                            <i class="fas fa-clock fa-lg me-2 text-primary"></i>
                            <h5 class="mb-0">Business Hours</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Set your shop's business hours for different days.</p>
                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="group" value="business_hours">
                                
                                <div class="mb-3">
                                    <label class="form-label">Weekday Hours</label>
                                    <input type="text" name="weekday_hours" class="form-control" 
                                        value="{{ \App\Models\Setting::where('key', 'weekday_hours')->first()?->value ?? 'Monday - Friday: 8:00 AM - 6:00 PM' }}" 
                                        placeholder="e.g., Monday - Friday: 8:00 AM - 6:00 PM" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Weekend Hours</label>
                                    <input type="text" name="weekend_hours" class="form-control" 
                                        value="{{ \App\Models\Setting::where('key', 'weekend_hours')->first()?->value ?? 'Saturday: 9:00 AM - 4:00 PM' }}" 
                                        placeholder="e.g., Saturday: 9:00 AM - 4:00 PM" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Sunday Hours</label>
                                    <input type="text" name="sunday_hours" class="form-control" 
                                        value="{{ \App\Models\Setting::where('key', 'sunday_hours')->first()?->value ?? 'Sunday - Closed' }}" 
                                        placeholder="e.g., Sunday - Closed" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Business Hours
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 