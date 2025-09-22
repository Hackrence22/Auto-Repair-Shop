@extends('layouts.app')

@section('content')
<div class="container py-4 customer-service-create">
    <div class="mx-auto" style="max-width: 760px;">
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('customer-service.index') }}" class="btn btn-link p-0 me-2"><i class="fas fa-arrow-left"></i></a>
            <h1 class="h3 mb-0">Submit Customer Service Request</h1>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
            <form action="{{ route('customer-service.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="shop_id" class="form-label">Select Shop</label>
                    <select name="shop_id" id="shop_id" class="form-select @error('shop_id') is-invalid @enderror" required>
                        <option value="">Choose a shop</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->name }} - {{ $shop->address }}
                            </option>
                        @endforeach
                    </select>
                    @error('shop_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Issue Category</label>
                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="">Select category</option>
                        <option value="booking" {{ old('category') == 'booking' ? 'selected' : '' }}>Booking System Issue</option>
                        <option value="shop" {{ old('category') == 'shop' ? 'selected' : '' }}>Shop Related Problem</option>
                        <option value="payment" {{ old('category') == 'payment' ? 'selected' : '' }}>Payment Problem</option>
                        <option value="appointment" {{ old('category') == 'appointment' ? 'selected' : '' }}>Appointment Issue</option>
                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('category')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" 
                           class="form-control @error('subject') is-invalid @enderror" 
                           placeholder="Brief description of your issue" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="priority" class="form-label">Priority Level</label>
                    <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="">Select priority</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low - General inquiry or non-urgent matter</option>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium - Standard request or minor issue</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High - Important issue affecting service</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent - Critical issue requiring immediate attention</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Detailed Message</label>
                    <textarea name="message" id="message" rows="6" class="form-control @error('message') is-invalid @enderror" placeholder="Please provide detailed information about your issue, concern, or request. Include any relevant details that will help us assist you better." required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Maximum 1000 characters</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('customer-service.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <div class="fw-semibold mb-1">Important Information</div>
            <ul class="mb-0 small">
                <li>Once submitted, your request cannot be edited or deleted.</li>
                <li>An admin will review and respond to your request.</li>
                <li>You will receive notifications about any updates.</li>
                <li>Choose the appropriate category and priority level.</li>
                <li>Provide as much detail as possible for better assistance.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
