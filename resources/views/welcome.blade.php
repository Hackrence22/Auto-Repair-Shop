@extends('layouts.app')

@section('title', "Welcome to Auto Repair Shop")

@section('content')
<div class="welcome-section text-center py-5 fade-in">
    <div class="container">
        <h1 class="display-4 mb-4">Welcome to Auto Repair Shop</h1>
        <p class="lead mb-5">Your trusted partner for all automotive and motorcycle services</p>
        
        @guest
            <div class="row justify-content-center mb-5">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Get Started</h3>
                            <p class="card-text mb-4">Create an account to book appointments and manage your vehicle services</p>
                            <div class="d-grid gap-3">
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Register Now
                                </a>
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endguest
        
        <!-- Business Hours -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8">
                <div class="card shadow-lg border-0 mb-4 business-hours-card">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white"><i class="fas fa-clock me-2"></i>Business Hours</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6">
                                <div class="hours-box mb-3">
                                    <h4 class="text-white mb-3">Weekdays</h4>
                                    <p class="lead text-white-75">{{ \App\Models\Setting::where('key', 'weekday_hours')->first()?->value ?? 'Monday - Friday: 8:00 AM - 6:00 PM' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="hours-box">
                                    <h4 class="text-white mb-3">Weekend</h4>
                                    <p class="lead text-white-75">{{ \App\Models\Setting::where('key', 'weekend_hours')->first()?->value ?? 'Saturday: 9:00 AM - 4:00 PM' }}</p>
                                    <p class="lead text-white-75">{{ \App\Models\Setting::where('key', 'sunday_hours')->first()?->value ?? 'Sunday - Closed' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Available Time Slots -->
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Available Time Slots by Shop</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $shopsWithSlots = \App\Models\Shop::with(['slotSettings' => function($query) {
                                $query->where('is_active', true);
                            }])->withAvg('ratings', 'rating')->withCount('ratings')->active()->ordered()->get();
                            $shopsWithSlots = $shopsWithSlots->filter(function($shop) {
                                return $shop->slotSettings->count() > 0;
                            });
                        @endphp
                        <div class="row mb-3">
                            <div class="col-12 col-md-6 col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" id="welcomeShopSearch" class="form-control" placeholder="Search shops...">
                                </div>
                            </div>
                        </div>
                        @if($shopsWithSlots->count() > 0)
                            @php
                                $now = \Carbon\Carbon::now();
                                $selectedDate = request('date', now()->format('Y-m-d'));
                                $isToday = $selectedDate === $now->format('Y-m-d');
                                $allSlotsPast = true;
                            @endphp
                            <div class="welcome-shops-scroll" id="welcomeShopsScroll">
                            @foreach($shopsWithSlots as $shop)
                                <div class="mb-4 welcome-shop-item">
                                    <div class="d-flex align-items-center mb-3">
                                        <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-2 border-primary me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-start">{{ $shop->name }}</h6>
                                            <small class="text-muted">{{ $shop->full_address }}</small>
                                            <div class="mt-1 d-flex align-items-center" style="gap:6px;">
                                                <span>
                                                    @php $avg = (float) ($shop->average_rating ?? 0); @endphp
                                                    @for($i=1;$i<=5;$i++)
                                                        <i class="fas fa-star" style="color: {{ $i <= round($avg) ? '#ffc107' : '#e4e5e9' }};"></i>
                                                    @endfor
                                                </span>
                                                <small class="text-muted">{{ $shop->average_rating ? number_format($shop->average_rating, 1) : 'No ratings' }} ({{ $shop->ratings_count ?? 0 }})</small>
                                            </div>
                                        </div>
                                        <div class="ms-auto">
                                            @if($shop->isCurrentlyOpen())
                                                <span class="badge bg-success">Open Now</span>
                                            @else
                                                <span class="badge bg-secondary">Closed</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row g-3 slot-list-scroll">
                                        @foreach($shop->slotSettings as $setting)
                                        @php
                                            $slotStart = \Carbon\Carbon::parse($setting->start_time);
                                            $slotEnd = \Carbon\Carbon::parse($setting->end_time);
                                            $slotTime = $slotStart->format('H:i');
                                            $isSlotPast = $isToday && $now->greaterThanOrEqualTo($slotEnd);
                                            if(!$isSlotPast) $allSlotsPast = false;
                                            $slotsPerHour = $setting->slots_per_hour;
                                            $totalSlots = $slotsPerHour;
                                            $bookedSlots = \App\Models\Appointment::whereDate('appointment_date', $selectedDate)
                                                ->whereTime('appointment_time', $slotTime)
                                                ->where('status', '!=', 'cancelled')
                                                ->count();
                                            $availableSlots = $totalSlots - $bookedSlots;
                                        @endphp
                                        
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="card h-100 slot-card {{ ($availableSlots <= 0 || $isSlotPast) ? 'unavailable' : 'available' }}">
                                                <div class="card-body text-center">
                                                    <div class="slot-time mb-2">
                                                        <i class="fas fa-clock me-2"></i>
                                                        {{ $slotStart->format('h:i A') }} - {{ $slotEnd->format('h:i A') }}
                                                    </div>
                                                    <div class="slot-info mb-3">
                                                            <span class="badge bg-{{ $availableSlots > 0 && !$isSlotPast ? 'success' : 'secondary' }}">
                                                                {{ $availableSlots }} slots available
                                                            </span>
                                                        </div>
                                                        @if($availableSlots > 0 && !$isSlotPast)
                                                            <a href="{{ route('appointments.create', ['shop' => $shop->id]) }}" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-calendar-plus me-1"></i>Book Now
                                                            </a>
                                                        @else
                                                            <span class="text-muted">Unavailable</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <a href="{{ route('shops.show', $shop) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Shop Details
                                        </a>
                                        @if($shop->hasMapEmbedUrl())
                                            <button type="button" 
                                                    class="btn btn-outline-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#mapModal"
                                                    data-shop-name="{{ $shop->name }}"
                                                    data-map-url="{{ $shop->map_embed_url }}">
                                                <i class="fas fa-map-marker-alt me-1"></i>View Map
                                            </button>
                                        @endif
                                    </div>
                                    @if(!$loop->last)
                                        <hr class="my-4">
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @if($allSlotsPast)
                                <div class="text-center mt-3">
                                    <p class="text-muted">All slots for today are past. Please select another date.</p>
                            </div>
                            @endif
                        @else
                            <div class="text-center">
                                <p class="text-muted">No time slots available at the moment.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Modal -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Book Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bookingForm" action="{{ route('appointments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="appointment_time" id="selectedTime">
                            <input type="hidden" name="appointment_date" id="selectedDate">
                            
                            <div class="mb-3">
                                <label class="form-label">Selected Time</label>
                                <input type="text" class="form-control" id="displayTime" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Service Type</label>
                                <select name="service_type" class="form-select" required>
                                    <option value="">Select a service</option>
                                    <option value="car_maintenance">Car Maintenance</option>
                                    <option value="motorcycle_maintenance">Motorcycle Maintenance</option>
                                    <option value="diagnostic">Diagnostic Service</option>
                                    <option value="repair">Repair Service</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" form="bookingForm" class="btn btn-primary">Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="services-section py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Our Services</h2>
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <div class="service-icon mb-3">
                            <i class="fas fa-tools fa-3x"></i>
                        </div>
                        <h3 class="card-title">Car Maintenance</h3>
                        <p class="card-text">Regular maintenance services for your car</p>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Oil Changes</li>
                            <li><i class="fas fa-check text-success me-2"></i>Brake Service</li>
                            <li><i class="fas fa-check text-success me-2"></i>Tire Rotation</li>
                            <li><i class="fas fa-check text-success me-2"></i>Filter Replacement</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <div class="service-icon mb-3">
                            <i class="fas fa-motorcycle fa-3x"></i>
                        </div>
                        <h3 class="card-title">Motorcycle Maintenance</h3>
                        <p class="card-text">Specialized maintenance for motorcycles</p>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Chain Service</li>
                            <li><i class="fas fa-check text-success me-2"></i>Valve Adjustment</li>
                            <li><i class="fas fa-check text-success me-2"></i>Carburetor Tuning</li>
                            <li><i class="fas fa-check text-success me-2"></i>Battery Service</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <div class="service-icon mb-3">
                            <i class="fas fa-car-battery fa-3x"></i>
                        </div>
                        <h3 class="card-title">Diagnostic Services</h3>
                        <p class="card-text">Advanced diagnostic tools for all vehicles</p>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Engine Diagnostics</li>
                            <li><i class="fas fa-check text-success me-2"></i>Computer Analysis</li>
                            <li><i class="fas fa-check text-success me-2"></i>Performance Testing</li>
                            <li><i class="fas fa-check text-success me-2"></i>Emission Testing</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 service-card">
                    <div class="card-body text-center">
                        <div class="service-icon mb-3">
                            <i class="fas fa-wrench fa-3x"></i>
                        </div>
                        <h3 class="card-title">Repair Services</h3>
                        <p class="card-text">Expert repairs for cars and motorcycles</p>
                        <ul class="list-unstyled text-start">
                            <li><i class="fas fa-check text-success me-2"></i>Engine Repair</li>
                            <li><i class="fas fa-check text-success me-2"></i>Transmission Service</li>
                            <li><i class="fas fa-check text-success me-2"></i>Electrical Systems</li>
                            <li><i class="fas fa-check text-success me-2"></i>Body Work</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="how-it-works py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 step-card">
                    <div class="card-body text-center">
                        <div class="step-number mb-3">1</div>
                        <h4>Book Appointment</h4>
                        <p>Schedule your service online at your convenience</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 step-card">
                    <div class="card-body text-center">
                        <div class="step-number mb-3">2</div>
                        <h4>Drop Off</h4>
                        <p>Bring your vehicle to our shop at your scheduled time</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 step-card">
                    <div class="card-body text-center">
                        <div class="step-number mb-3">3</div>
                        <h4>Service</h4>
                        <p>Our experts will perform the necessary services</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 step-card">
                    <div class="card-body text-center">
                        <div class="step-number mb-3">4</div>
                        <h4>Pick Up</h4>
                        <p>Collect your vehicle when the service is complete</p>
                    </div>
                </div>
            </div>
        </div>
        <h2 class="text-center my-5">Customer Service</h2>
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 step-card" style="border-radius: 20px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-3" style="color: var(--primary-color);">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-color);">Email</h5>
                        <p class="mb-0">
                            <a href="mailto:clarencelisondra45@gmail.com" class="text-decoration-underline">clarencelisondra45@gmail.com</a><br>
                            <a href="mailto:mendozaivann157@gmail.com" class="text-decoration-underline">mendozaivann157@gmail.com</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 step-card" style="border-radius: 20px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-3" style="color: var(--primary-color);">
                            <i class="fas fa-phone fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-color);">Phone</h5>
                        <p class="mb-0">
                            <a href="tel:09460721827" class="text-decoration-underline">09460721827</a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 step-card" style="border-radius: 20px;">
                    <div class="card-body text-center py-4">
                        <div class="mb-3" style="color: var(--primary-color);">
                            <i class="fab fa-facebook fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color: var(--primary-color);">Facebook</h5>
                        <p class="mb-0">
                            <a href="https://facebook.com/clarence.angelo.927" target="_blank" class="text-decoration-underline">clarence angelo d. lisondra</a><br>
                            <a href="https://facebook.com/mhendz.vanniee" target="_blank" class="text-decoration-underline">mhendz vanniee</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Feedback Section -->
<div class="row justify-content-center my-5">
    <div class="col-12 col-lg-8">
        <div class="card h-100 step-card" style="border-radius: 20px;">
            <div class="card-body text-center py-4">
                <h2 class="mb-3 fw-bold" style="color: var(--primary-color);">We Value Your Feedback</h2>
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('feedback.store') }}" class="mt-4">
                    @csrf
                    <div class="row mb-3">
                        @auth
                            <input type="hidden" name="name" value="{{ Auth::user()->name }}">
                            <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                            <div class="col-12 mb-2 text-start d-flex align-items-center" style="gap: 0.75rem;">
                                @php
                                    $imgSrc = Auth::user()->profile_picture && Storage::disk('public')->exists(Auth::user()->profile_picture)
                                        ? Storage::disk('public')->url(Auth::user()->profile_picture)
                                        : asset('images/default-profile.png');
                                @endphp
                                <img src="{{ $imgSrc }}" alt="Avatar" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ Auth::user()->name }}</span>
                                    <span class="text-muted small">{{ Auth::user()->email }}</span>
                                </div>
                            </div>
                        @else
                            <div class="col-12 col-md-6 mb-3 mb-md-0">
                                <input type="text" name="name" class="form-control" placeholder="Your Name" required value="{{ old('name') }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="email" name="email" class="form-control" placeholder="Your Email (optional)" value="{{ old('email') }}">
                            </div>
                        @endauth
                    </div>
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="4" placeholder="Your Feedback" required>{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary px-5">Submit Feedback</button>
                </form>

                @if(isset($feedbacks) && $feedbacks->count())
                    <div class="mt-5 text-start">
                        <h4 class="mb-4">Recent Feedback</h4>
                        <div class="feedback-scroll-container">
                            <div class="feedback-scroll-content">
                        @foreach($feedbacks as $feedback)
                            @php
                                $user = \App\Models\User::where('email', $feedback->email)->first();
                                        $imgSrc = $user && $user->profile_picture && Storage::disk('public')->exists($user->profile_picture)
                                            ? Storage::disk('public')->url($user->profile_picture)
                                            : asset('images/default-profile.png');
                            @endphp
                                    <div class="feedback-item">
                            <div class="d-flex align-items-start mb-3" style="gap: 1rem;">
                                <img src="{{ $imgSrc }}" alt="Avatar" style="width:48px;height:48px;object-fit:cover;border-radius:50%;">
                                <div>
                                    <span class="fw-bold">{{ $feedback->name }}</span>
                                    <div class="text-muted small">{{ $feedback->created_at->format('M d, Y H:i') }}</div>
                                    <div>{{ $feedback->message }}</div>
                                    @if($feedback->reply)
                                        <div class="mt-2 ms-4 p-3 bg-light border rounded" style="max-width: 600px;">
                                            <div class="fw-semibold text-primary mb-1"><i class="fas fa-reply me-1"></i>Admin Reply</div>
                                            <div>{{ $feedback->reply }}</div>
                                        </div>
                                    @endif
                                            </div>
                                </div>
                            </div>
                        @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Customer Service Widget for Authenticated Users -->
@auth
<div class="row justify-content-center my-5">
    <div class="col-12 col-lg-8">
        <div class="card h-100 step-card" style="border-radius: 20px;">
            <div class="card-body text-center py-4">
                <h2 class="mb-3 fw-bold" style="color: var(--primary-color);">
                    <i class="fas fa-headset me-2"></i>Need Help?
                </h2>
                <p class="text-muted mb-4">Have a question, concern, or need assistance? Our customer service team is here to help!</p>
                
                @php
                    $user = Auth::user();
                    $recentRequests = \App\Models\CustomerService::where('user_id', $user->id)
                        ->with(['shop', 'assignedAdmin'])
                        ->latest()
                        ->take(3)
                        ->get();
                    $openRequests = \App\Models\CustomerService::where('user_id', $user->id)
                        ->where('status', 'open')
                        ->count();
                @endphp
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="bg-primary text-white p-3 rounded">
                            <div class="h4 mb-1">{{ $openRequests }}</div>
                            <div class="small">Open Requests</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-success text-white p-3 rounded">
                            <div class="h4 mb-1">{{ $recentRequests->count() }}</div>
                            <div class="small">Total Requests</div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="{{ route('customer-service.create') }}" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>New Request
                    </a>
                    <a href="{{ route('customer-service.index') }}" class="btn btn-outline-primary px-4">
                        <i class="fas fa-list me-2"></i>View All
                    </a>
                </div>
                
                @if($recentRequests->count() > 0)
                    <div class="mt-4 text-start">
                        <h6 class="mb-3">Recent Requests</h6>
                        @foreach($recentRequests as $request)
                            <div class="border rounded p-3 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-semibold">{{ $request->subject }}</div>
                                        <div class="text-muted small">{{ $request->shop->name }} • {{ $request->created_at->format('M d, Y') }}</div>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $request->priority === 'urgent' ? 'danger' : ($request->priority === 'high' ? 'warning' : 'info') }}">
                                            {{ ucfirst($request->priority) }}
                                        </span>
                                        <span class="badge bg-{{ $request->status === 'open' ? 'primary' : ($request->status === 'resolved' ? 'success' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                        </span>
                                    </div>
                                </div>
                                @if($request->admin_reply)
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <div class="fw-semibold text-success small">
                                            <i class="fas fa-reply me-1"></i>Admin Response
                                        </div>
                                        <div class="small">{{ Str::limit($request->admin_reply, 100) }}</div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endauth

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="mapModalLabel">
                    <i class="fas fa-map-marker-alt me-2"></i>Shop Location
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="mapContainer" style="width: 100%; height: 500px;">
                    <!-- Map will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.welcome-section {
    background: var(--gradient-primary);
    color: white;
    padding: 2rem 0;
    margin-top: -2rem;
}

/* Force the welcome page container to sit correctly under the navbar */
.welcome-section .container {
    margin-top: 10px !important;
}

@media (max-width: 768px) {
    .welcome-section {
        padding: 1rem 0;
        margin-top: 0 !important; /* Prevent heading from overflowing container on mobile */
        overflow-x: hidden;
    }
    .welcome-section .container { padding-left: 1rem; padding-right: 1rem; margin-top: 10px !important; }
    .welcome-section h1 {
        font-size: 2rem;
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.2;
        margin: 0 0 0.75rem 0;
    }
    .welcome-section .lead {
        font-size: 1rem;
        word-wrap: break-word;
        white-space: normal;
    }
}

.welcome-section h1 {
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.service-card {
    transition: transform 0.3s ease;
}

.service-card:hover {
    transform: translateY(-10px);
}

.service-icon {
    color: var(--secondary-color);
    margin-bottom: 1.5rem;
}

.step-card {
    border: none;
    background: white;
    transition: transform 0.3s ease;
}

.step-card:hover {
    transform: translateY(-5px);
}

.step-number {
    width: 40px;
    height: 40px;
    background: var(--gradient-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-weight: bold;
    font-size: 1.2rem;
}

.list-unstyled li {
    margin-bottom: 0.5rem;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.btn-outline-primary {
    border: 2px solid var(--secondary-color);
    color: var(--secondary-color);
}

.btn-outline-primary:hover {
    background: var(--gradient-primary);
    border-color: transparent;
    color: white;
}

/* Mobile-responsive slot cards */
.slot-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

@media (max-width: 768px) {
    .slot-card {
        margin-bottom: 1rem;
    }
    
    .slot-card .card-body {
        padding: 1rem;
    }
    
    .slot-time {
        font-size: 1rem !important;
    }
    
    .slot-card .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
}

.slot-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.slot-card.available {
    border-top: 4px solid #28a745;
}

.slot-card.unavailable {
    opacity: 0.6;
    background-color: #f8f9fa;
}

.slot-time {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.slot-info {
    color: #6c757d;
}

.slot-card .btn {
    width: 100%;
    margin-top: 0.5rem;
}

/* Welcome shops scrolling container: show up to 4 shops then scroll */
.welcome-shops-scroll {
    max-height: 1200px; /* fallback */
    overflow-y: auto;
    padding-right: 4px;
}
@media (min-width: 992px) {
    .welcome-shops-scroll { max-height: calc(4 * 300px); }
}
@media (max-width: 991.98px) {
    .welcome-shops-scroll { max-height: calc(4 * 360px); }
}
.welcome-shops-scroll::-webkit-scrollbar { width: 6px; }
.welcome-shops-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 6px; }
.welcome-shop-item { scroll-margin-top: 12px; }

/* Limit visible slot cards to 6 with scroll */
.slot-list-scroll { max-height: 780px; overflow-y: auto; padding-right: 6px; }
@media (min-width: 992px) { /* lg: 3 cols → 2 rows = 6 */
  .slot-list-scroll { max-height: calc(2 * 260px); }
}
@media (min-width: 768px) and (max-width: 991.98px) { /* md: 2 cols → 3 rows = 6 */
  .slot-list-scroll { max-height: calc(3 * 260px); }
}
@media (max-width: 767.98px) { /* sm: 1 col → 6 rows = 6 */
  .slot-list-scroll { max-height: calc(6 * 260px); }
}
.slot-list-scroll::-webkit-scrollbar { width: 6px; }
.slot-list-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 6px; }

/* Mobile-responsive shop section */
.shop-section {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

@media (max-width: 768px) {
    .shop-section {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .shop-section .d-flex.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
    
    .shop-section .ms-auto {
        margin-left: 0 !important;
        margin-top: 0.5rem;
    }
    
    .shop-section img {
        width: 35px !important;
        height: 35px !important;
    }
    
    .shop-section h6 {
        font-size: 1rem;
    }
    
    .shop-section .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .shop-section .btn {
        width: 100%;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}

.shop-section:last-child {
    margin-bottom: 0;
}

.shop-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding-bottom: 15px;
    margin-bottom: 20px;
}

#slot-date-picker {
    background-color: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
}

#slot-date-picker::-webkit-calendar-picker-indicator {
    filter: invert(1);
}

.business-hours-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .business-hours-card .row {
        flex-direction: column;
    }
    
    .business-hours-card .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .hours-box {
        padding: 1rem;
    }
    
    .hours-box h4 {
        font-size: 1.25rem;
    }
    
    .hours-box .lead {
        font-size: 1rem;
    }
}

.hours-box {
    padding: 1.5rem;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    transition: transform 0.3s ease;
}

.hours-box:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
}

.hours-box h4 {
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.hours-box .lead {
    font-size: 1.2rem;
    font-weight: 400;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.75);
}

/* Mobile-responsive services section */
@media (max-width: 768px) {
    .services-section .row {
        flex-direction: column;
    }
    
    .services-section .col-md-3 {
        margin-bottom: 1.5rem;
    }
    
    .service-card .card-body {
        padding: 1.5rem 1rem;
    }
    
    .service-card h3 {
        font-size: 1.25rem;
    }
    
    .service-icon i {
        font-size: 2.5rem !important;
    }
}

/* Mobile-responsive how-it-works section */
@media (max-width: 768px) {
    .how-it-works .row {
        flex-direction: column;
    }
    
    .how-it-works .col-md-3 {
        margin-bottom: 1.5rem;
    }
    
    .step-card .card-body {
        padding: 1.5rem 1rem;
    }
    
    .step-card h4 {
        font-size: 1.25rem;
    }
    
    .step-number {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
}

/* Mobile-responsive customer service section */
@media (max-width: 768px) {
    .how-it-works .row:last-child {
        flex-direction: column;
    }
    
    .how-it-works .row:last-child .col-md-4 {
        margin-bottom: 1.5rem;
    }
    
    .step-card[style*="border-radius: 20px"] .card-body {
        padding: 1.5rem 1rem;
    }
    
    .step-card[style*="border-radius: 20px"] h5 {
        font-size: 1.1rem;
    }
    
    .step-card[style*="border-radius: 20px"] i {
        font-size: 1.5rem !important;
    }
}

/* Mobile-responsive feedback section */
@media (max-width: 768px) {
    .feedback-scroll-container {
        max-height: 300px;
    }
    
    .feedback-item {
        padding: 10px;
    }
    
    .feedback-item img {
        width: 40px !important;
        height: 40px !important;
    }
    
    .feedback-item .d-flex {
        gap: 0.5rem !important;
    }
}

/* Feedback scrolling styles */
.feedback-scroll-container {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.feedback-scroll-content {
    padding: 20px;
}

.feedback-item {
    padding: 15px;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.3s ease;
}

.feedback-item:last-child {
    border-bottom: none;
}

.feedback-item:hover {
    background-color: #f8f9fa;
}

/* Custom scrollbar */
.feedback-scroll-container::-webkit-scrollbar {
    width: 8px;
}

.feedback-scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.feedback-scroll-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

.feedback-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth scrolling animation */
.feedback-scroll-content {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mobile-responsive modal */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-xl {
        max-width: calc(100% - 1rem);
    }
    
    #mapContainer {
        height: 300px !important;
    }
    
    .modal-body {
        padding: 0.5rem;
    }
    
    .modal-footer {
        padding: 0.75rem;
    }
    
    .modal-footer .btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
}

/* Mobile-responsive buttons */
@media (max-width: 768px) {
    .btn {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .btn-lg {
        padding: 1rem 1.5rem;
        font-size: 1rem;
    }
}

/* Mobile-responsive cards */
@media (max-width: 768px) {
    .card {
        margin-bottom: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .card-header {
        padding: 0.75rem 1rem;
    }
    
    .card-title {
        font-size: 1.25rem;
    }
}

/* Mobile-responsive text */
@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .lead {
        font-size: 1rem;
    }
    
    h2 {
        font-size: 1.75rem;
    }
    
    h3 {
        font-size: 1.5rem;
    }
    
    h4 {
        font-size: 1.25rem;
    }
    
    h5 {
        font-size: 1.1rem;
    }
    
    h6 {
        font-size: 1rem;
    }
}

/* Mobile-responsive spacing */
@media (max-width: 768px) {
    .py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .mb-5 {
        margin-bottom: 2rem !important;
    }
    
    .mt-5 {
        margin-top: 2rem !important;
    }
    
    .g-4 {
        gap: 1rem !important;
    }
    
    .row.g-4 > * {
        margin-bottom: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent browser from restoring scroll position and force top on refresh
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
    window.addEventListener('pageshow', function() { window.scrollTo(0, 0); });

    const datePicker = document.getElementById('slot-date-picker');
    
    // Only set date picker if it exists
    if (datePicker) {
        // Set date picker to today by default
        const today = new Date().toISOString().split('T')[0];
        datePicker.value = today;
        datePicker.setAttribute('min', today);
        
        // Update slots when date changes
        datePicker.addEventListener('change', function() {
            window.location.href = `{{ route('welcome') }}?date=${this.value}`;
        });
    }

    // Feedback scrolling functionality
    const feedbackContainer = document.querySelector('.feedback-scroll-container');
    if (feedbackContainer) {
        // Ensure container starts at top without scrolling the page
        feedbackContainer.scrollTop = 0;

        // Add smooth scrolling on mouse wheel
        feedbackContainer.addEventListener('wheel', function(e) {
            e.preventDefault();
            this.scrollTop += e.deltaY;
        });

        // Add keyboard navigation
        feedbackContainer.addEventListener('keydown', function(e) {
            const scrollAmount = 50;
            switch(e.key) {
                case 'ArrowUp':
                    e.preventDefault();
                    this.scrollTop -= scrollAmount;
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.scrollTop += scrollAmount;
                    break;
                case 'PageUp':
                    e.preventDefault();
                    this.scrollTop -= this.clientHeight;
                    break;
                case 'PageDown':
                    e.preventDefault();
                    this.scrollTop += this.clientHeight;
                    break;
            }
        });

        // Make container focusable for keyboard navigation
        feedbackContainer.setAttribute('tabindex', '0');
    }

    // Map modal functionality
    const mapModal = document.getElementById('mapModal');
    if (mapModal) {
        mapModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const shopName = button.getAttribute('data-shop-name');
            const mapUrl = button.getAttribute('data-map-url');
            
            console.log('Map URL:', mapUrl); // Debug log
            
            // Update modal title with shop name
            document.getElementById('mapModalLabel').innerHTML = 
                '<i class="fas fa-map-marker-alt me-2"></i>' + shopName + ' - Location';
            
            // Extract src URL from embedded iframe if it's a full iframe
            let mapSrc = mapUrl;
            if (mapUrl && mapUrl.trim() !== '' && mapUrl.includes('<iframe')) {
                const srcMatch = mapUrl.match(/src="([^"]+)"/);
                if (srcMatch) {
                    mapSrc = srcMatch[1];
                    console.log('Extracted src:', mapSrc); // Debug log
                }
            }
            
            // Load the map
            const mapContainer = document.getElementById('mapContainer');
            if (mapSrc && mapSrc.trim() !== '' && mapSrc !== 'null') {
                mapContainer.innerHTML = `
                    <iframe src="${mapSrc}" 
                            width="100%" 
                            height="500" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                `;
            } else {
                mapContainer.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-map-marker-alt fa-3x mb-3"></i><p>Map not available for this location</p><small class="text-muted">Please contact the shop for directions</small></div>';
            }
        });
        
        // Clear map when modal is hidden
        mapModal.addEventListener('hidden.bs.modal', function() {
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.innerHTML = '<!-- Map will be loaded here -->';
        });
    }

    // Client-side shop search on welcome page
    const welcomeSearch = document.getElementById('welcomeShopSearch');
    if (welcomeSearch) {
        const items = Array.from(document.querySelectorAll('.welcome-shop-item'));
        function normalize(text) { return (text || '').toLowerCase(); }
        function filter() {
            const q = normalize(welcomeSearch.value);
            items.forEach(function(item) {
                const text = normalize(item.innerText);
                item.style.display = text.includes(q) ? '' : 'none';
            });
        }
        welcomeSearch.addEventListener('input', filter);
    }
});
</script>
@endpush
