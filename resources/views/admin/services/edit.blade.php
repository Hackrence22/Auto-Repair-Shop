@extends('layouts.admin')

@section('title', 'Edit Service')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Edit Service</h1>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Services
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('admin.services.update', $service) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Service Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $service->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" required>{{ old('description', $service->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shop_id" class="form-label">Shop <span class="text-danger">*</span></label>
                                    <select class="form-select @error('shop_id') is-invalid @enderror" 
                                            id="shop_id" name="shop_id" required>
                                        <option value="">Select a shop</option>
                                        @foreach($shops as $shop)
                                            <option value="{{ $shop->id }}" 
                                                    {{ old('shop_id', $service->shop_id) == $shop->id ? 'selected' : '' }}>
                                                {{ $shop->name }} - {{ $shop->full_address }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('shop_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Service Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" 
                                            id="type" name="type" required>
                                        <option value="">Select service type</option>
                                        <option value="repair" {{ old('type', $service->type) == 'repair' ? 'selected' : '' }}>Repair</option>
                                        <option value="maintenance" {{ old('type', $service->type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="inspection" {{ old('type', $service->type) == 'inspection' ? 'selected' : '' }}>Inspection</option>
                                        <option value="other" {{ old('type', $service->type) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price (₱)</label>
                                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $service->price) }}" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration" class="form-label">Duration</label>
                                    <div class="input-group">
                                        @php
                                            $durationValue = $service->duration;
                                            $durationUnit = 'minutes';
                                            if ($durationValue % 1440 === 0) { $durationUnit = 'days'; $durationValue = $durationValue / 1440; }
                                            elseif ($durationValue % 60 === 0) { $durationUnit = 'hours'; $durationValue = $durationValue / 60; }
                                        @endphp
                                        <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" 
                                               id="duration" name="duration" value="{{ old('duration', $durationValue) }}" required>
                                        <select class="form-select" name="duration_unit">
                                            <option value="minutes" {{ old('duration_unit', $durationUnit) === 'minutes' ? 'selected' : '' }}>Minutes</option>
                                            <option value="hours" {{ old('duration_unit', $durationUnit) === 'hours' ? 'selected' : '' }}>Hours</option>
                                            <option value="days" {{ old('duration_unit', $durationUnit) === 'days' ? 'selected' : '' }}>Days</option>
                                        </select>
                                        @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                       id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $service->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 