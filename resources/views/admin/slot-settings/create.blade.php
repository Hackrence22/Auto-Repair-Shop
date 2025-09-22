@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Add New Time Slot</h1>
        <a href="{{ route('admin.slot-settings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Slot Settings
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.slot-settings.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="shop_id" class="form-label">Shop <span class="text-danger">*</span></label>
                    <select class="form-select @error('shop_id') is-invalid @enderror" 
                            id="shop_id" name="shop_id" required>
                        <option value="">Select a shop</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }} - {{ $shop->full_address }}
                            </option>
                        @endforeach
                    </select>
                    @error('shop_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                id="end_time" name="end_time" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="slots_per_hour" class="form-label">Slots per Hour</label>
                    <input type="number" class="form-control @error('slots_per_hour') is-invalid @enderror" 
                        id="slots_per_hour" name="slots_per_hour" value="{{ old('slots_per_hour') }}" 
                        min="1" required>
                    @error('slots_per_hour')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                            {{ old('is_active') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Time Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 