@extends('layouts.admin')

@section('title', 'Create Shop')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shops.index') }}">Shops</a></li>
<li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Create New Shop</h1>
                <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Shops
                </a>
            </div>

            <form action="{{ route('admin.shops.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Shop Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('owner_name') is-invalid @enderror" 
                                                   id="owner_name" name="owner_name" value="{{ auth('admin')->user() && auth('admin')->user()->isOwner() ? auth('admin')->user()->name : old('owner_name') }}" {{ (auth('admin')->user() && auth('admin')->user()->isOwner()) ? 'readonly' : '' }} required>
                                            @error('owner_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email') }}">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Location & Map</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" name="city" value="{{ (auth('admin')->user() && auth('admin')->user()->isOwner()) ? 'Surigao City' : old('city') }}" required>
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="state" class="form-label">State/Province</label>
                                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                                   id="state" name="state" value="{{ (auth('admin')->user() && auth('admin')->user()->isOwner()) ? 'Surigao Del Norte' : old('state') }}">
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="zip_code" class="form-label">ZIP/Postal Code</label>
                                            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                                   id="zip_code" name="zip_code" value="{{ (auth('admin')->user() && auth('admin')->user()->isOwner()) ? '8400' : old('zip_code') }}">
                                            @error('zip_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                                   id="country" name="country" value="{{ old('country', 'Philippines') }}" required>
                                            @error('country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="map_embed_url" class="form-label">Google Maps Embed URL</label>
                                    <textarea class="form-control @error('map_embed_url') is-invalid @enderror" 
                                              id="map_embed_url" name="map_embed_url" rows="3" 
                                              placeholder="Paste the full iframe code or just the src URL here">{{ old('map_embed_url') }}</textarea>
                                    <small class="form-text text-muted">
                                        Paste the full iframe code from Google Maps (Share → Embed a map → Copy HTML) or just the src URL. Leave empty to use coordinates only.
                                    </small>
                                    @error('map_embed_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Operating Hours</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="opening_time" class="form-label">Opening Time <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control @error('opening_time') is-invalid @enderror" 
                                                   id="opening_time" name="opening_time" value="{{ old('opening_time', '08:00') }}" required>
                                            @error('opening_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="closing_time" class="form-label">Closing Time <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control @error('closing_time') is-invalid @enderror" 
                                                   id="closing_time" name="closing_time" value="{{ old('closing_time', '17:00') }}" required>
                                            @error('closing_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Working Days <span class="text-danger">*</span></label>
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
                                            $oldWorkingDays = old('working_days', [1, 2, 3, 4, 5]); // Default to Mon-Fri
                                        @endphp
                                        @foreach($days as $value => $day)
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input @error('working_days') is-invalid @enderror" 
                                                           type="checkbox" 
                                                           name="working_days[]" 
                                                           value="{{ $value }}" 
                                                           id="day{{ $value }}"
                                                           {{ in_array($value, $oldWorkingDays) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="day{{ $value }}">
                                                        {{ $day }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('working_days')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Shop Image</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Shop Photo</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                    <small class="form-text text-muted">
                                        Recommended size: 800x600px. Max file size: 4MB.
                                    </small>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="text-center">
                                    <img id="imagePreview" src="{{ asset('images/default-shop.png') }}" 
                                         alt="Shop Preview" 
                                         class="img-thumbnail"
                                         style="max-width: 100%; max-height: 300px;">
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror" 
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0">
                                    <small class="form-text text-muted">
                                        Lower numbers appear first in listings.
                                    </small>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                           type="checkbox" 
                                           name="is_active" 
                                           value="1" 
                                           id="is_active" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Shop
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Only active shops will be available for appointments.
                                    </small>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-save"></i> Create Shop
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imagePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Help with map embed URL
document.getElementById('map_embed_url').addEventListener('input', function() {
    const value = this.value.trim();
    if (value.includes('<iframe') && value.includes('src=')) {
        // User pasted full iframe code
        console.log('Full iframe code detected - will extract src URL automatically');
    } else if (value.includes('google.com/maps/embed')) {
        // User pasted just the src URL
        console.log('Direct src URL detected');
    }
});
</script>
@endpush 