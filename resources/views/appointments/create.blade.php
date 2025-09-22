@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
<div class="booking-container">
    <!-- Page Header -->
    <div class="booking-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="booking-title">
                        <i class="fas fa-calendar-check text-primary me-3"></i>
                        Book Your Repair Appointment
                    </h1>
                    <p class="booking-subtitle">Schedule your vehicle repair with our expert technicians</p>
                </div>
                <!-- Remove the step progress bar from the header by deleting the booking-steps div and its children -->
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="booking-card">
                    <div class="booking-card-header">
                        <h2 class="card-title">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Appointment Details
                        </h2>
                        <p class="card-subtitle">Please fill in all required information to schedule your appointment</p>
                    </div>
                    
                    <div class="booking-card-body">
        @if ($errors->any())
                            <div class="alert alert-danger alert-modern">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="alert-content">
                                    <h6 class="alert-title">Please correct the following errors:</h6>
                                    <ul class="alert-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                                </div>
            </div>
        @endif

        @php
            $now = \Carbon\Carbon::now();
            $selectedDate = request('date', old('appointment_date', date('Y-m-d')));
            $isToday = $selectedDate === $now->format('Y-m-d');
            $allSlotsPast = true;
        @endphp

                        <form action="{{ route('appointments.store') }}" method="POST" class="booking-form" enctype="multipart/form-data">
                            @csrf
                            @if(isset($selectedShop) && $selectedShop)
                                <div class="selected-shop-banner d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center shop-id-left">
                                        <img src="{{ $selectedShop->image_url }}" alt="{{ $selectedShop->name }}" class="selected-shop-avatar">
                                        <div class="ms-2">
                                            <div class="selected-shop-title">{{ $selectedShop->name }}</div>
                                            <div class="selected-shop-subtitle">{{ $selectedShop->full_address }}</div>
                                        </div>
                                    </div>
                                    <div class="ms-auto">
                                        <button type="button" id="toggleChangeShop" class="change-shop-btn"><i class="fas fa-exchange-alt me-2"></i>Change shop</button>
                                    </div>
                                    <input type="hidden" name="shop_id" value="{{ $selectedShop->id }}">
                                </div>
                                <div id="changeShopWrapper" class="change-shop-wrapper mb-4" style="display:none;">
                                    <label for="change_shop_select" class="form-label fw-bold change-shop-label">
                                        <i class="fas fa-store me-2"></i>Select another shop
                                    </label>
                                    <select id="change_shop_select" class="form-select modern-select">
                                        <option value="">Choose a shop</option>
                                        @foreach($shops as $shop)
                                            <option value="{{ $shop->id }}" {{ $selectedShop->id == $shop->id ? 'selected' : '' }}>{{ $shop->name }} - {{ $shop->full_address }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="mb-4">
                                    <label for="shop_id" class="form-label fw-bold">
                                        <i class="fas fa-store me-2"></i>Select Shop
                                    </label>
                                    <select name="shop_id" id="shop_id" class="form-select modern-select" required>
                                        <option value="">Choose a shop</option>
                                        @foreach($shops as $shop)
                                            <option value="{{ $shop->id }}" {{ request('shop') == $shop->id || old('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }} - {{ $shop->full_address }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            
                            <!-- Personal Information Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="section-content">
                                        <h3 class="section-title">Personal Information</h3>
                                        <p class="section-description">Your contact details for appointment confirmation</p>
                                    </div>
                                </div>
                                
            <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="customer_name" class="form-label">
                                                <i class="fas fa-user me-2"></i>Full Name
                                            </label>
                                            <input type="text" class="form-control modern-input" id="customer_name" name="customer_name" 
                           value="{{ old('customer_name', Auth::user()->name) }}" required>
                </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="email" class="form-label">
                                                <i class="fas fa-envelope me-2"></i>Email Address
                                            </label>
                                            <input type="email" class="form-control modern-input" id="email" name="email" 
                           value="{{ old('email', Auth::user()->email) }}" required readonly>
                                        </div>
                </div>
            </div>

            <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="phone" class="form-label">
                                                <i class="fas fa-phone me-2"></i>Phone Number
                                            </label>
                                            <input type="tel" class="form-control modern-input" id="phone" name="phone" 
                           value="{{ old('phone', Auth::user()->phone) }}" required>
                </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Information Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-car"></i>
                                    </div>
                                    <div class="section-content">
                                        <h3 class="section-title">Vehicle Information</h3>
                                        <p class="section-description">Details about your vehicle for service preparation</p>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="vehicle_type" class="form-label">
                                                <i class="fas fa-car-side me-2"></i>Vehicle Type
                                            </label>
                                            <select class="form-select modern-select" id="vehicle_type" name="vehicle_type" required>
                        <option value="">Select Vehicle Type</option>
                        <option value="Car" {{ old('vehicle_type')==='Car' ? 'selected' : '' }}>Car</option>
                        <option value="Motorcycle" {{ old('vehicle_type')==='Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                        <option value="SUV" {{ old('vehicle_type')==='SUV' ? 'selected' : '' }}>SUV</option>
                        <option value="Truck" {{ old('vehicle_type')==='Truck' ? 'selected' : '' }}>Truck</option>
                    </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="vehicle_model" class="form-label">
                                                <i class="fas fa-tag me-2"></i>Vehicle Model
                                            </label>
                                            <input type="text" class="form-control modern-input" id="vehicle_model" name="vehicle_model" value="{{ old('vehicle_model') }}" required>
                                        </div>
                </div>
            </div>

            <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="vehicle_year" class="form-label">
                                                <i class="fas fa-calendar-alt me-2"></i>Vehicle Year
                                            </label>
                                            <input type="text" class="form-control modern-input" id="vehicle_year" name="vehicle_year" value="{{ old('vehicle_year') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Service & Schedule Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-tools"></i>
                </div>
                                    <div class="section-content">
                                        <h3 class="section-title">Service & Schedule</h3>
                                        <p class="section-description">Choose your service and preferred appointment time</p>
                </div>
            </div>

            <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="service_id" class="form-label">
                                                <i class="fas fa-wrench me-2"></i>Service Required
                                            </label>
                                            <select class="form-select modern-select" id="service_id" name="service_id" required>
                        <option value="">Select Service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" 
                                    data-price="{{ $service->price }}"
                                    data-description="{{ $service->description }}"
                                    data-duration="{{ $service->duration }}"
                                    {{ (string)old('service_id', request('service_id')) === (string)$service->id ? 'selected' : '' }}>
                                @php
                                    $mins = (int) $service->duration;
                                    $d = intdiv($mins, 1440); $mins -= $d * 1440;
                                    $h = intdiv($mins, 60); $mins -= $h * 60;
                                    $parts = [];
                                    if ($d > 0) $parts[] = $d . 'd';
                                    if ($h > 0) $parts[] = $h . 'h';
                                    if ($mins > 0 || empty($parts)) $parts[] = $mins . 'm';
                                    $human = implode(' ', $parts);
                                @endphp
                                {{ $service->name }} - ₱{{ number_format($service->price, 2) }} ({{ $human }})
                            </option>
                        @endforeach
                    </select>
                                            
                                            <div id="service-details" class="service-details-card" style="display: none;">
                                                <div class="service-details-header">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    Service Details
                                                </div>
                                                <div class="service-details-body">
                                                    <p class="service-description" id="service-description"></p>
                                                    <div class="service-meta">
                                                        <span class="service-duration">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <span id="service-duration"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="technician_id" class="form-label">
                                                <i class="fas fa-user-cog me-2"></i>Preferred Technician (Optional)
                                            </label>
                                            <select class="form-select modern-select" id="technician_id" name="technician_id">
                                                <option value="">Select Technician (Optional)</option>
                                                @if(isset($technicians) && $technicians->count() > 0)
                                                    @foreach($technicians as $technician)
                                                        <option value="{{ $technician->id }}" 
                                                                data-specialization="{{ $technician->specialization }}"
                                                                data-experience="{{ $technician->experience_years }}"
                                                                data-avatar="{{ $technician->profile_picture_url }}"
                                                                data-bio="{{ $technician->bio }}"
                                                                data-certifications="{{ $technician->certifications }}"
                                                                data-hourly-rate="{{ $technician->hourly_rate }}"
                                                                {{ (string)old('technician_id') === (string)$technician->id ? 'selected' : '' }}>
                                                            {{ $technician->name }}
                                                            @if($technician->specialization)
                                                                - {{ $technician->specialization }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>No technicians available for selected date</option>
                                                @endif
                                            </select>
                                            @if(isset($selectedDate) && isset($technicians) && $technicians->count() == 0)
                                                <div class="form-text text-warning">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    No technicians are available on {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}. 
                                                    Try selecting a different date.
                                                </div>
                                            @endif
                                            <div id="technician-details" class="technician-details-card" style="display: none;">
                                                <div class="technician-details-header">
                                                    <i class="fas fa-user me-2"></i>
                                                    Technician Profile
                                                </div>
                                                <div class="technician-details-body">
                                                    <div class="technician-info">
                                                        <div class="technician-avatar">
                                                            <img id="technician-avatar" src="" alt="Technician" class="rounded-circle" width="80" height="80">
                                                        </div>
                                                        <div class="technician-details">
                                                            <h6 id="technician-name" class="mb-1 fw-bold"></h6>
                                                            <p id="technician-specialization" class="mb-1 text-muted"></p>
                                                            <p id="technician-experience" class="mb-2 text-muted small"></p>
                                                            <div id="technician-bio" class="mb-2" style="display: none;">
                                                                <p class="text-muted small mb-1"><strong>Bio:</strong></p>
                                                                <p class="text-muted small" id="technician-bio-text"></p>
                                                            </div>
                                                            <div id="technician-certifications" class="mb-2" style="display: none;">
                                                                <p class="text-muted small mb-1"><strong>Certifications:</strong></p>
                                                                <p class="text-muted small" id="technician-certifications-text"></p>
                                                            </div>
                                                            <div id="technician-rate" class="mb-2" style="display: none;">
                                                                <p class="text-muted small mb-1"><strong>Hourly Rate:</strong></p>
                                                                <p class="text-success small fw-bold" id="technician-rate-text"></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="appointment_date" class="form-label">
                                                <i class="fas fa-calendar me-2"></i>Appointment Date
                                            </label>
                                            <input type="date" class="form-control modern-input" id="appointment_date" name="appointment_date" 
                           value="{{ request('date', old('appointment_date', date('Y-m-d'))) }}" required>
                                        </div>
                </div>
            </div>

            <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="appointment_time" class="form-label">
                                                <i class="fas fa-clock me-2"></i>Appointment Time
                                            </label>
                                            <select name="appointment_time" id="appointment_time" class="form-select modern-select" required>
                            <option value="">Select Time</option>
                            @php
                                $allSlotsPast = true;
                                $now = \Carbon\Carbon::now();
                            @endphp
                            @foreach($slotSettings as $slot)
                                @php
                                    $startTime = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                                    $endTime = \Carbon\Carbon::parse($slot->end_time)->format('H:i');
                                    $slots = $slotsPerTime[$startTime] ?? 0;
                                    $isPast = false;
                                    if ($isToday) {
                                        $slotEndDateTime = \Carbon\Carbon::parse($selectedDate . ' ' . $endTime);
                                        $isPast = $now->greaterThanOrEqualTo($slotEndDateTime);
                                        if(!$isPast) $allSlotsPast = false;
                                    }
                                @endphp
                                <option value="{{ $startTime }}" 
                                        {{ $slots == 0 || $isPast ? 'disabled' : '' }} 
                                        {{ (request('time') == $startTime || old('appointment_time') == $startTime) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $startTime)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $endTime)->format('h:i A') }}
                                    ({{ $slots }} slots left)
                                </option>
                            @endforeach
                        </select>
                        @if($isToday && $allSlotsPast)
                            <div class="alert alert-warning alert-modern mt-3">
                                <div class="alert-icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="alert-content">
                                    <strong>Booking Closed for Today</strong><br>
                                    Please select another date for your appointment.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Payment Section: now full-width and visually enhanced -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="form-group payment-section payment-step-card">
                        <div class="payment-step-header">
                            <span class="step-badge"><i class="fas fa-credit-card"></i></span>
                            <span class="step-title">Payment & Upload</span>
                        </div>
                        <label class="form-label mt-3">
                            <i class="fas fa-credit-card me-2"></i>Payment Method
                        </label>
                        <div id="payment-method-buttons" class="payment-methods-list">
                            @foreach($paymentMethods as $paymentMethod)
                                <button type="button"
                                    class="btn btn-outline-primary payment-method-btn payment-method-block"
                                    data-id="{{ $paymentMethod->id }}"
                                    data-account-name="{{ $paymentMethod->account_name }}"
                                    data-account-number="{{ $paymentMethod->account_number }}"
                                    data-description="{{ $paymentMethod->description }}"
                                    data-role-type="{{ $paymentMethod->role_type }}"
                                    data-image-url="{{ $paymentMethod->image_url }}"
                                    @if(old('payment_method_id') == $paymentMethod->id) aria-pressed="true" @endif
                                >
                                    <i class="fas fa-credit-card me-1"></i> {{ $paymentMethod->name }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="payment_method_id" id="payment_method_id" value="{{ old('payment_method_id') }}" required>
                        <div id="payment-details" class="payment-details-card mt-4" style="display: none;">
                            <div class="row align-items-center g-0 payment-details-flex">
                                <div class="col-md-5 text-center payment-image-col position-relative">
                                    <img id="payment-image" src="" alt="Payment Method" class="payment-method-icon-large zoomable-image" style="display:none;" onclick="showZoomedPaymentImage(this.src)">
                                    <div class="zoom-overlay" style="display:none;">Click to Zoom</div>
                                </div>
                                <div class="col-md-7 payment-info-col">
                                    <span class="payment-method-name h5 mb-2 d-block" id="payment-name"></span>
                                    <p class="payment-description mb-3" id="payment-description"></p>
                                    <div id="account-info" class="account-info mb-3" style="display: none;">
                                        <div class="account-details-grid">
                                            <div class="account-detail">
                                                <span class="account-label">Account Name:</span>
                                                <span class="account-value" id="account-name-display"></span>
                                            </div>
                                            <div class="account-detail">
                                                <span class="account-label">Account Number:</span>
                                                <span class="account-value" id="account-number-display"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Payment Proof & Reference (now truly unified) -->
                                    <div id="payment-proof-fields" class="payment-proof-fields" style="display:none;">
                                        <hr class="my-4">
                                        <div class="fw-semibold text-secondary mb-2" style="font-size:1.02rem;"><i class="fas fa-receipt me-2"></i>Upload Payment Proof & Reference Number</div>
                                        <div class="mb-3">
                                            <label for="payment_proof" class="form-label">
                                                <i class="fas fa-image me-2"></i>Payment Proof (screenshot/receipt)
                                            </label>
                                            <div class="payment-upload-area">
                                                <input type="file" class="form-control payment-upload-input" id="payment_proof" name="payment_proof" accept="image/*">
                                                <div class="mt-2">
                                                    <img id="payment-proof-preview" src="#" alt="Payment Proof Preview" class="payment-proof-preview" style="max-width:100%; display:none; border-radius:8px; border:1px solid #e9ecef; background:#f8f9fa; padding:8px;" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reference_number" class="form-label">
                                               Reference Number
                                            </label>
                                            <input type="text" class="form-control" id="reference_number" name="reference_number" maxlength="100" placeholder="Enter GCash/ PayMaya reference number">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                            <!-- Additional Notes Section -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon">
                                        <i class="fas fa-sticky-note"></i>
                                    </div>
                                    <div class="section-content">
                                        <h3 class="section-title">Additional Notes</h3>
                                        <p class="section-description">Any special instructions or concerns about your vehicle</p>
                </div>
            </div>

                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="form-group">
                                            <label for="description" class="form-label">
                                                <i class="fas fa-comment me-2"></i>Notes & Instructions
                                            </label>
                                            <textarea class="form-control modern-textarea" id="description" name="description" rows="4" 
                                                      placeholder="Describe any specific issues, concerns, or special instructions for your vehicle...">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Section -->
                            <div class="form-submit-section">
                                <div class="submit-content">
                                    <div class="submit-info">
                                        <h4 class="submit-title">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            Ready to Book?
                                        </h4>
                                        <p class="submit-description">Review your information and click the button below to confirm your appointment</p>
                                    </div>
                                    <div class="submit-action">
                                        <button type="submit" class="btn btn-primary btn-lg submit-btn">
                                            <i class="fas fa-calendar-check me-2"></i>
                                            Book Appointment
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
</div>
<!-- Modal for payment method image zoom -->
<div class="modal fade" id="paymentImageZoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 text-center">
        <img id="zoomedPaymentImage" src="" alt="Payment Method Zoomed" style="max-width:90vw;max-height:80vh;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.2);">
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Service details functionality
document.addEventListener('DOMContentLoaded', function() {
    // Toggle and handle change-shop flow when a shop is preselected
    const toggleBtn = document.getElementById('toggleChangeShop');
    const changeWrapper = document.getElementById('changeShopWrapper');
    const changeSelect = document.getElementById('change_shop_select');
    if (toggleBtn && changeWrapper) {
        toggleBtn.addEventListener('click', function() {
            changeWrapper.style.display = changeWrapper.style.display === 'none' ? '' : 'none';
        });
    }
    if (changeSelect) {
        changeSelect.addEventListener('change', function() {
            const shopId = this.value;
            if (!shopId) return;
            const dateInput = document.getElementById('appointment_date');
            const date = dateInput ? dateInput.value : '';
            const params = new URLSearchParams();
            params.set('shop', shopId);
            if (date) params.set('date', date);
            window.location.href = `{{ route('appointments.create') }}?${params.toString()}`;
        });
    }
    // When changing shop from the dropdown (when no preselected shop), reload page with ?shop=ID to scope data
    const shopSelect = document.getElementById('shop_id');
    if (shopSelect) {
        shopSelect.addEventListener('change', function() {
            const shopId = this.value;
            if (!shopId) return;
            // try to preserve selected date if present
            const dateInput = document.getElementById('appointment_date');
            const date = dateInput ? dateInput.value : '';
            const params = new URLSearchParams();
            params.set('shop', shopId);
            if (date) params.set('date', date);
            window.location.href = `{{ route('appointments.create') }}?${params.toString()}`;
        });
    }

    const serviceSelect = document.getElementById('service_id');
    const serviceDetails = document.getElementById('service-details');
    const serviceDescription = document.getElementById('service-description');
    const serviceDuration = document.getElementById('service-duration');

    if (serviceSelect) {
        serviceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.getAttribute('data-description');
            const duration = selectedOption.getAttribute('data-duration');
            
            if (description) {
                serviceDescription.textContent = description;
                serviceDetails.style.display = 'block';
            } else {
                serviceDetails.style.display = 'none';
            }
            if (duration && serviceDuration) {
                let mins = parseInt(duration, 10);
                let days = Math.floor(mins / 1440); mins -= days * 1440;
                let hours = Math.floor(mins / 60); mins -= hours * 60;
                const parts = [];
                if (days > 0) parts.push(`${days}d`);
                if (hours > 0) parts.push(`${hours}h`);
                if (mins > 0 || parts.length === 0) parts.push(`${mins}m`);
                serviceDuration.textContent = parts.join(' ');
                if (!description) serviceDetails.style.display = 'block';
            }
        });
    }

    // Technician selection functionality
    const technicianSelect = document.getElementById('technician_id');
    const technicianDetails = document.getElementById('technician-details');
    const technicianAvatar = document.getElementById('technician-avatar');
    const technicianName = document.getElementById('technician-name');
    const technicianSpecialization = document.getElementById('technician-specialization');
    const technicianExperience = document.getElementById('technician-experience');
    const technicianBio = document.getElementById('technician-bio');
    const technicianBioText = document.getElementById('technician-bio-text');
    const technicianCertifications = document.getElementById('technician-certifications');
    const technicianCertificationsText = document.getElementById('technician-certifications-text');
    const technicianRate = document.getElementById('technician-rate');
    const technicianRateText = document.getElementById('technician-rate-text');

    if (technicianSelect) {
        technicianSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const specialization = selectedOption.getAttribute('data-specialization');
            const experience = selectedOption.getAttribute('data-experience');
            const avatar = selectedOption.getAttribute('data-avatar');
            const bio = selectedOption.getAttribute('data-bio');
            const certifications = selectedOption.getAttribute('data-certifications');
            const hourlyRate = selectedOption.getAttribute('data-hourly-rate');
            
            if (this.value) {
                // Show technician details
                technicianName.textContent = selectedOption.textContent.split(' - ')[0];
                technicianSpecialization.textContent = specialization || 'No specialization specified';
                technicianExperience.textContent = experience ? `${experience} years of experience` : 'New technician';
                
                // Set avatar (use actual technician photo if available, otherwise default)
                if (avatar && avatar !== 'null') {
                    technicianAvatar.src = avatar;
                } else {
                    technicianAvatar.src = '{{ asset("images/default-profile.png") }}';
                }
                
                // Show/hide and populate bio
                if (bio && bio !== 'null') {
                    technicianBioText.textContent = bio;
                    technicianBio.style.display = 'block';
                } else {
                    technicianBio.style.display = 'none';
                }
                
                // Show/hide and populate certifications
                if (certifications && certifications !== 'null') {
                    technicianCertificationsText.textContent = certifications;
                    technicianCertifications.style.display = 'block';
                } else {
                    technicianCertifications.style.display = 'none';
                }
                
                // Show/hide and populate hourly rate
                if (hourlyRate && hourlyRate !== 'null' && hourlyRate !== '0') {
                    technicianRateText.textContent = `₱${parseFloat(hourlyRate).toFixed(2)}/hour`;
                    technicianRate.style.display = 'block';
                } else {
                    technicianRate.style.display = 'none';
                }
                
                technicianDetails.style.display = 'block';
            } else {
                technicianDetails.style.display = 'none';
            }
        });
    }

    // Date change functionality to update technicians
    const dateInput = document.getElementById('appointment_date');
    if (dateInput && technicianSelect) {
        dateInput.addEventListener('change', function() {
            const selectedDate = this.value;
            const selectedShop = '{{ $selectedShop->id ?? "" }}';
            
            if (selectedDate && selectedShop) {
                // Reload page with new date to get updated technicians
                const params = new URLSearchParams();
                params.set('shop', selectedShop);
                params.set('date', selectedDate);
                
                // Preserve other form values
                const serviceSelect = document.getElementById('service_id');
                if (serviceSelect && serviceSelect.value) {
                    params.set('service_id', serviceSelect.value);
                }
                
                window.location.href = `{{ route('appointments.create') }}?${params.toString()}`;
            }
        });
    }

    // Payment method button group functionality
    const paymentMethodButtons = document.querySelectorAll('.payment-method-btn');
    const paymentMethodInput = document.getElementById('payment_method_id');
    const paymentDetails = document.getElementById('payment-details');
    const paymentName = document.getElementById('payment-name');
    const paymentDescription = document.getElementById('payment-description');
    const accountInfo = document.getElementById('account-info');
    const accountNameDisplay = document.getElementById('account-name-display');
    const accountNumberDisplay = document.getElementById('account-number-display');

    function clearActiveButtons() {
        paymentMethodButtons.forEach(btn => btn.classList.remove('active'));
    }

    // Show/hide payment proof and reference number for GCash/PayMaya
    function updatePaymentProofSection(roleType) {
        const proofFields = document.getElementById('payment-proof-fields');
        if (roleType === 'gcash' || roleType === 'paymaya') {
            proofFields.style.display = 'block';
        } else {
            proofFields.style.display = 'none';
            document.getElementById('payment_proof').value = '';
            document.getElementById('reference_number').value = '';
            document.getElementById('payment-proof-preview').style.display = 'none';
        }
    }

    paymentMethodButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            console.log('Button clicked:', this);
            const paymentNameElem = document.getElementById('payment-name');
            const paymentDescriptionElem = document.getElementById('payment-description');
            const paymentImageElem = document.getElementById('payment-image');
            const accountInfoElem = document.getElementById('account-info');
            const accountNameDisplayElem = document.getElementById('account-name-display');
            const accountNumberDisplayElem = document.getElementById('account-number-display');
            const paymentDetailsElem = document.getElementById('payment-details');
            const roleType = this.getAttribute('data-role-type');
            const imageUrl = this.getAttribute('data-image-url');
            const accountName = this.getAttribute('data-account-name');
            const accountNumber = this.getAttribute('data-account-number');
            const description = this.getAttribute('data-description');
            clearActiveButtons();
            this.classList.add('active');
            paymentMethodInput.value = this.getAttribute('data-id');
            // Debug logs
            console.log('roleType:', roleType);
            console.log('imageUrl:', imageUrl);
            console.log('accountName:', accountName);
            console.log('accountNumber:', accountNumber);
            console.log('description:', description);
            // Update payment details
            if (paymentNameElem && paymentDescriptionElem && paymentImageElem && accountInfoElem && accountNameDisplayElem && accountNumberDisplayElem && paymentDetailsElem) {
                paymentNameElem.textContent = this.textContent.trim();
                paymentDescriptionElem.textContent = description || 'No description available';
                if (imageUrl) {
                    paymentImageElem.src = imageUrl;
                    paymentImageElem.style.display = 'inline';
                } else {
                    paymentImageElem.src = '';
                    paymentImageElem.style.display = 'none';
                }
                if (accountName && accountNumber) {
                    accountInfoElem.style.display = 'block';
                    accountNameDisplayElem.textContent = accountName;
                    accountNumberDisplayElem.textContent = accountNumber;
                } else {
                    accountInfoElem.style.display = 'none';
                }
                paymentDetailsElem.style.display = 'block';
                updatePaymentProofSection(roleType);
            } else {
                if (paymentDetailsElem) {
                    paymentDetailsElem.innerHTML = '<div class="alert alert-danger">Error: Could not update payment method details. Please contact support.</div>';
                    paymentDetailsElem.style.display = 'block';
                }
                console.error('One or more payment detail elements not found.');
            }
        });
        if (paymentMethodInput.value && btn.getAttribute('data-id') === paymentMethodInput.value) {
            btn.classList.add('active');
            btn.click();
            updatePaymentProofSection(btn.getAttribute('data-role-type'));
        }
    });

    // If no payment method selected, hide details
    if (!paymentMethodInput.value) {
        paymentDetails.style.display = 'none';
    }

    // Form validation enhancement
    const form = document.querySelector('.booking-form');
    if (form) {
        // Live remove invalid state when typing/selecting
        form.addEventListener('input', function(ev) {
            const t = ev.target;
            if (t && t.classList.contains('is-invalid')) {
                if (t.value && t.value.trim() !== '') {
                    t.classList.remove('is-invalid');
                }
            }
        });

        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            const missing = [];

            requiredFields.forEach(field => {
                const val = (field.value || '').toString().trim();
                if (!val) {
                    field.classList.add('is-invalid');
                    isValid = false;
                    // Build a human label
                    const label = form.querySelector(`label[for="${field.id}"]`);
                    const title = label ? label.textContent.replace(/\s+/g,' ').trim() : (field.name || 'This field');
                    missing.push(title);
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Show a temporary summary alert at top of form
                let alert = document.getElementById('clientValidationAlert');
                if (!alert) {
                    alert = document.createElement('div');
                    alert.id = 'clientValidationAlert';
                    alert.className = 'alert alert-danger alert-modern';
                    alert.innerHTML = '<div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="alert-content"><h6 class="alert-title">Please fill the required fields:</h6><ul class="alert-list" id="clientValidationList"></ul></div>';
                    form.prepend(alert);
                }
                const list = document.getElementById('clientValidationList');
                if (list) {
                    list.innerHTML = '';
                    missing.slice(0,6).forEach(m => {
                        const li = document.createElement('li');
                        li.textContent = m;
                        list.appendChild(li);
                    });
                    if (missing.length > 6) {
                        const li = document.createElement('li');
                        li.textContent = `+${missing.length - 6} more`;
                        list.appendChild(li);
                    }
                }
                // Scroll to first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }
            }
        });
    }

    // Image preview for payment proof
    const paymentProofInput = document.getElementById('payment_proof');
    const paymentProofPreview = document.getElementById('payment-proof-preview');
    if (paymentProofInput) {
        paymentProofInput.addEventListener('change', function(event) {
            const [file] = paymentProofInput.files;
            if (file) {
                paymentProofPreview.src = URL.createObjectURL(file);
                paymentProofPreview.style.display = 'block';
            } else {
                paymentProofPreview.src = '#';
                paymentProofPreview.style.display = 'none';
            }
        });
    }

    window.showZoomedPaymentImage = function(src) {
        const modalImg = document.getElementById('zoomedPaymentImage');
        modalImg.src = src;
        const modal = new bootstrap.Modal(document.getElementById('paymentImageZoomModal'));
        modal.show();
    }
});
</script>
@endpush

@push('styles')
<style>
/* --- User-End Booking Form Polish (Blue Theme) --- */
.booking-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #e3eefe 100%);
    min-height: 100vh;
    padding-bottom: 3rem;
}
.booking-header {
    background: #4f8cff;
    color: #fff;
    padding: 2.5rem 0 1.5rem 0;
    margin-bottom: 2.5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.07);
    text-align: center;
    border-bottom-left-radius: 32px;
    border-bottom-right-radius: 32px;
    position: relative;
}
.booking-header .header-icon-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1.2rem;
}
.booking-header .header-icon {
    background: #fff;
    color: #4f8cff !important;
    border-radius: 50%;
    padding: 1.1rem;
    font-size: 3rem !important;
    box-shadow: 0 2px 16px rgba(79,140,255,0.13);
    border: 3px solid #eaf1fb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.booking-title {
    font-size: 2.5rem;
    font-weight: 900;
    margin: 0 0 0.5rem 0;
    letter-spacing: 1px;
    color: #fff;
    text-shadow: 0 2px 8px rgba(79,140,255,0.10);
}
.booking-subtitle {
    font-size: 1.15rem;
    margin: 0.5rem 0 0 0;
    opacity: 0.97;
    color: #eaf1fb;
    font-weight: 500;
}
.booking-steps {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1.2rem;
}
.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    position: relative;
}
.step-number {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.22);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(102,126,234,0.12);
    transition: background 0.3s, border 0.3s;
}
.step.active .step-number {
    background: #4f8cff;
    border: 3px solid #4f8cff;
    color: #fff;
    box-shadow: 0 4px 15px rgba(79,140,255,0.18);
    animation: pulse 1.2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(79,140,255,0.3); }
    70% { box-shadow: 0 0 0 10px rgba(79,140,255,0); }
    100% { box-shadow: 0 0 0 0 rgba(79,140,255,0); }
}
.step-label {
    font-size: 0.95rem;
    font-weight: 600;
    opacity: 0.85;
    color: #fff;
}
.step-line {
    width: 44px;
    height: 3px;
    background: linear-gradient(90deg, #fff 0%, #4f8cff 100%);
    border-radius: 2px;
}
.booking-card {
    background: #fff;
    border-radius: 26px;
    box-shadow: 0 16px 48px rgba(102,126,234,0.10);
    overflow: hidden;
    border: 1px solid #e3e8ee;
    margin-bottom: 2.5rem;
}
.booking-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2.2rem 2rem 1.2rem 2rem;
    border-bottom: 1px solid #e3e8ee;
    text-align: center;
}
.card-title {
    font-size: 2rem;
    font-weight: 800;
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
    letter-spacing: 0.5px;
}
.card-subtitle {
    color: #6c757d;
    font-size: 1.08rem;
    margin: 0;
}
.booking-card-body {
    padding: 2.2rem 2rem 2rem 2rem;
}
.form-section {
    margin-bottom: 2.5rem;
    padding: 2.2rem 1.5rem 1.5rem 1.5rem;
    background: #f9fafb;
    border-radius: 18px;
    border: 1px solid #e3e8ee;
    box-shadow: 0 2px 8px rgba(102,126,234,0.04);
}
.section-header {
    display: flex;
    align-items: center;
    margin-bottom: 2.2rem;
    gap: 1.2rem;
}
.section-icon {
    width: 62px;
    height: 62px;
    border-radius: 16px;
    background: linear-gradient(135deg, #4f8cff 0%, #667eea 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.7rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(102,126,234,0.10);
}
.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 0.5rem 0;
}
.section-description {
    color: #6c757d;
    margin: 0;
    font-size: 1.01rem;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-label {
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.75rem;
    display: block;
    font-size: 1.01rem;
}
.modern-input,
.modern-select,
.modern-textarea {
    border: 2px solid #e3e8ee;
    border-radius: 14px;
    padding: 0.95rem 1.2rem 0.95rem 2.5rem;
    font-size: 1.12rem;
    transition: all 0.3s ease;
    background: #fff;
    position: relative;
}
.modern-input:focus,
.modern-select:focus,
.modern-textarea:focus {
    border-color: #4f8cff;
    box-shadow: 0 0 0 0.2rem rgba(79,140,255,0.13);
    outline: none;
}
.modern-input.is-invalid,
.modern-select.is-invalid,
.modern-textarea.is-invalid {
    border-color: #4f8cff;
    box-shadow: 0 0 0 0.2rem rgba(79,140,255,0.13);
}
input[type="text"], input[type="email"], input[type="tel"], input[type="date"] {
    background-repeat: no-repeat;
    background-position: 1rem center;
    background-size: 1.2rem 1.2rem;
}
#customer_name { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/person.svg'); }
#email { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/envelope.svg'); }
#phone { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/telephone.svg'); }
#vehicle_model { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/tag.svg'); }
#vehicle_year { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/calendar.svg'); }
#appointment_date { background-image: url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/icons/calendar-date.svg'); }
#reference_number { background-image: none; }
#appointment_date {
    background-color: #eaf1fb;
    border: 2px solid #4f8cff;
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.13rem;
    min-height: 52px;
    box-shadow: 0 2px 8px rgba(79,140,255,0.07);
    cursor: pointer;
}
#appointment_date:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.13);
}
.payment-methods-list {
    display: flex;
    flex-wrap: wrap;
    gap: 1.2rem;
}
.payment-method-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #fff;
    border: 2px solid #e3e8ee;
    border-radius: 16px;
    padding: 1.2rem 2.2rem;
    font-size: 1.15rem;
    font-weight: 600;
    color: #495057;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(102,126,234,0.06);
    position: relative;
    min-width: 180px;
    min-height: 56px;
}
.payment-method-btn.active,
.payment-method-btn:active {
    background: linear-gradient(135deg, #4f8cff 0%, #667eea 100%) !important;
    color: #fff !important;
    border-color: #4f8cff !important;
    box-shadow: 0 2px 8px rgba(79,140,255,0.13);
}
.payment-method-btn.active::after {
    content: '\2713';
    position: absolute;
    right: 1.2rem;
    top: 50%;
    transform: translateY(-50%);
    color: #28a745;
    font-size: 1.5rem;
    font-weight: bold;
}
.payment-method-block {
    width: 100%;
    text-align: left;
    padding: 1.2rem 2.2rem;
    border-radius: 16px;
    font-size: 1.15rem;
}
.booking-header .fa-calendar-check,
.booking-header .fa-clipboard-list {
    background: #fff;
    color: #4f8cff !important;
    border-radius: 50%;
    padding: 0.7rem;
    font-size: 2.5rem !important;
    box-shadow: 0 2px 12px rgba(79,140,255,0.10);
    border: 2px solid #eaf1fb;
    margin-right: 0.7rem;
}
/* --- Payment Section Card --- */
.payment-section {
    background: #f7faff;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(79,140,255,0.07);
    border: 1.5px solid #e3e8ee;
    padding: 2rem 1.5rem 1.5rem 1.5rem;
    margin-bottom: 2.5rem;
}
.payment-step-card {
    position: relative;
    overflow: hidden;
    border: none;
    margin-top: 1.5rem;
    animation: fadeInUp 0.7s cubic-bezier(.39,.575,.565,1) both;
}
.payment-step-header {
    background: linear-gradient(90deg, #4f8cff 0%, #667eea 100%);
    color: #fff;
    padding: 1.1rem 1.5rem;
    border-radius: 14px 14px 0 0;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(79,140,255,0.10);
}
.step-badge {
    background: #fff;
    color: #4f8cff;
    border-radius: 50%;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(79,140,255,0.13);
}
.step-title {
    font-size: 1.18rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}
@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(40px); }
    100% { opacity: 1; transform: translateY(0); }
}
.payment-details-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(102,126,234,0.08);
    border: 1px solid #e3e8ee;
    padding: 2.2rem 2rem 2rem 2rem;
    margin-top: 1.2rem;
}
.payment-details-flex {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0;
}
.payment-image-col {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    /* Remove height, background, border-radius, and box-shadow for original look */
}
.payment-method-icon-large {
    max-width: 420px;
    max-height: 420px;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(79,140,255,0.13);
    background: #f5f7fa;
    border: 2.5px solid #e3e8ee;
    object-fit: contain;
    margin: 0 auto;
    cursor: pointer;
    transition: transform 0.2s;
    display: block;
    width: auto;
    height: auto;
    min-width: unset;
    min-height: unset;
}
.payment-method-icon-large:hover {
    transform: scale(1.04) rotate(-2deg);
    box-shadow: 0 8px 32px rgba(79,140,255,0.18);
}
.payment-info-col {
    padding-left: 2.2rem;
    padding-right: 1rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.payment-details-header {
    border-bottom: 1px solid #e3e8ee;
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}
.payment-method-icon {
    background: #f5f7fa;
    border: 1.5px solid #e3e8ee;
    padding: 0.5rem;
    border-radius: 12px;
    max-width: 80px;
    max-height: 80px;
    object-fit: contain;
    box-shadow: 0 2px 8px rgba(102,126,234,0.07);
}
.payment-proof-fields {
    background: #f8fafd;
    border: 1.5px dashed #4f8cff;
    border-radius: 12px;
    padding: 1.2rem 1rem;
    margin-top: 1.5rem;
    box-shadow: 0 2px 8px rgba(79,140,255,0.04);
}
.payment-upload-area {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
}
.payment-upload-input {
    border: 2px solid #e3e8ee;
    border-radius: 10px;
    padding: 0.7rem 1rem;
    font-size: 1.05rem;
    background: #fff;
    transition: border 0.2s;
}
.payment-upload-input:focus {
    border-color: #4f8cff;
    outline: none;
}
.payment-proof-preview {
    border-radius: 8px;
    border: 1.5px solid #e3e8ee;
    background: #f8f9fa;
    padding: 8px;
    max-width: 220px;
    max-height: 180px;
    object-fit: contain;
    box-shadow: 0 2px 8px rgba(102,126,234,0.07);
}
.zoomable-image {
    cursor: zoom-in;
    position: relative;
    z-index: 1;
}
.zoom-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(44, 62, 80, 0.75);
    color: #fff;
    padding: 0.7rem 1.5rem;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    pointer-events: none;
    z-index: 2;
    opacity: 0;
    transition: opacity 0.2s;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(44,62,80,0.13);
}
.payment-image-col:hover .zoom-overlay,
.payment-image-col:focus-within .zoom-overlay {
    display: block !important;
    opacity: 1;
}
.payment-image-col:hover .zoomable-image,
.payment-image-col:focus-within .zoomable-image {
    filter: brightness(0.85) blur(0.5px);
}
@media (max-width: 768px) {
    .booking-title { font-size: 1.5rem; }
    .booking-steps { justify-content: center; margin-top: 1rem; }
    .submit-content { flex-direction: column; text-align: center; }
    .account-details-grid { grid-template-columns: 1fr; }
    .form-section { padding: 1rem; }
    .section-header { flex-direction: column; text-align: center; gap: 1rem; }
    .section-icon { width: 44px; height: 44px; font-size: 1rem; }
    .payment-methods-list { flex-direction: column; }
    .payment-section {
        padding: 1rem;
    }
    .payment-details-card {
        padding: 1rem 0.5rem;
    }
    .payment-proof-fields {
        padding: 1rem 0.5rem;
    }
    .payment-method-icon {
        max-width: 60px;
        max-height: 60px;
    }
    .payment-proof-preview {
        max-width: 100%;
        max-height: 120px;
    }
}
@media (max-width: 991.98px) {
    .payment-details-flex {
        flex-direction: column;
    }
    .payment-info-col {
        padding-left: 0;
        padding-top: 2rem;
    }
    .payment-image-col {
        min-height: 120px;
        /* Remove height and border-radius for original look */
    }
    .payment-method-icon-large {
        max-width: 180px;
        max-height: 180px;
        min-width: unset;
        min-height: unset;
    }
}

/* --- Additional mobile refinements for better fit --- */
@media (max-width: 768px) {
    .booking-header { padding: 1.25rem 0 1rem 0; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px; }
    .booking-title { font-size: 1.35rem; letter-spacing: 0.5px; }
    .booking-subtitle { font-size: 0.95rem; }

    .booking-card { border-radius: 18px; }
    .booking-card-header { padding: 1.1rem 1rem 0.8rem 1rem; }
    .card-title { font-size: 1.35rem; }
    .card-subtitle { font-size: 0.95rem; }

    .booking-card-body { padding: 1rem; }
    .form-section { margin-bottom: 1.25rem; padding: 0.9rem; border-radius: 12px; }
    .section-title { font-size: 1.05rem; }
    .section-description { font-size: 0.9rem; }

    .form-label { font-size: 0.95rem; margin-bottom: 0.5rem; }
    .modern-input, .modern-select, .modern-textarea { padding: 0.7rem 0.9rem; font-size: 1rem; border-radius: 10px; }
    /* Remove background icons for inputs on mobile */
    #customer_name,
    #email,
    #phone,
    #vehicle_model,
    #vehicle_year,
    #appointment_date,
    #reference_number {
        background-image: none !important;
        background-position: initial !important;
        background-size: 0 !important;
        padding-left: 0.9rem !important;
    }
    #appointment_date { min-height: 44px; font-size: 1rem; }

    .payment-method-btn { padding: 0.75rem 1rem; font-size: 0.95rem; min-width: 140px; min-height: 44px; border-radius: 12px; }
    .payment-method-btn.active::after { right: 0.7rem; font-size: 1.2rem; }
    .payment-details-card { padding: 1rem; }

    .submit-title { font-size: 1.1rem; }
    .submit-description { font-size: 0.95rem; }
    .submit-btn { padding: 0.6rem 1rem; font-size: 0.95rem; border-radius: 10px; }
}

/* --- Compact layout for very small devices --- */
@media (max-width: 575.98px) {
    .container { padding-left: 0.75rem; padding-right: 0.75rem; }
    .booking-container { padding-bottom: 1.5rem; }
    .booking-header { margin-bottom: 1.25rem; }

    /* Reduce visual noise */
    .booking-card, .payment-section, .form-section { box-shadow: none !important; }

    /* Stack and tighten spacing */
    .row [class^="col-"] { margin-bottom: 0.75rem; }
    .form-group { margin-bottom: 0.9rem; }

    /* Inputs */
    .modern-input, .modern-select, .modern-textarea { font-size: 0.95rem; padding: 0.65rem 0.9rem 0.65rem 1.8rem; }
    .form-label { font-size: 0.9rem; }

    /* Payment buttons: full-width, smaller gap */
    .payment-methods-list { gap: 0.5rem; }
    .payment-method-btn { width: 100%; min-width: 0; padding: 0.6rem 0.9rem; font-size: 0.9rem; border-radius: 10px; }

    /* Payment details: smaller image, less padding */
    .payment-details-card { padding: 0.75rem; }
    .payment-image-col { min-height: 100px; }
    .payment-method-icon-large { max-width: 140px; max-height: 140px; border-radius: 12px; }
    .payment-info-col { padding: 1rem 0.5rem 0 0.5rem; }
    .payment-description { font-size: 0.95rem; }

    /* Submit section: stack and full-width button */
    .submit-content { display: flex; flex-direction: column; gap: 0.75rem; }
    .submit-btn { width: 100%; padding: 0.55rem 0.9rem; font-size: 0.95rem; }
}
</style>
@endpush

@push('styles')
<style>
/* --- Unique selected shop banner + button --- */
.selected-shop-banner {
    background: linear-gradient(90deg, rgba(79,140,255,0.12) 0%, rgba(102,126,234,0.12) 100%);
    border: 1.5px solid #e3e8ee;
    border-radius: 16px;
    padding: 0.9rem 1rem;
    box-shadow: 0 2px 12px rgba(102,126,234,0.06);
}
.selected-shop-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 2px 10px rgba(79,140,255,0.15);
}
.selected-shop-title {
    font-weight: 800;
    color: #2c3e50;
}
.selected-shop-subtitle {
    font-size: 0.85rem;
    color: #6c757d;
}
.change-shop-btn {
    background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    color: #fff;
    border: none;
    padding: 0.5rem 0.85rem;
    border-radius: 9999px;
    font-weight: 600;
    box-shadow: 0 6px 18px rgba(14,165,233,0.25);
    transition: transform 0.1s ease, box-shadow 0.2s ease;
    font-size: 0.9rem;
}
.change-shop-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(14,165,233,0.32); }
.change-shop-btn:active { transform: translateY(0); box-shadow: 0 4px 12px rgba(14,165,233,0.22); }
.change-shop-wrapper {
    background: #f8fbff;
    border: 1px dashed #b6cdfc;
    border-radius: 12px;
    padding: 1rem;
}
.change-shop-label { color: #4f8cff; }

@media (max-width: 768px) {
    .selected-shop-banner { flex-direction: column; align-items: flex-start !important; gap: 0.5rem; }
    .change-shop-btn { width: 100%; text-align: center; }
}

/* --- Technician Details Card --- */
.technician-details-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 12px;
    margin-top: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.technician-details-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.technician-details-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.9rem;
}

.technician-details-body {
    padding: 1.5rem;
}

.technician-info {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
}

.technician-avatar img {
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
}

.technician-avatar img:hover {
    transform: scale(1.05);
}

.technician-details {
    flex: 1;
}

.technician-details h6 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.technician-details p {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

.technician-details .text-muted {
    color: #6c757d !important;
}

.technician-details .text-success {
    color: #28a745 !important;
}

.technician-details .fw-bold {
    font-weight: 600 !important;
}

.technician-details .small {
    font-size: 0.85rem;
}

.technician-details .mb-1 {
    margin-bottom: 0.25rem !important;
}

.technician-details .mb-2 {
    margin-bottom: 0.5rem !important;
}

@media (max-width: 768px) {
    .technician-info {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
}
</style>
@endpush