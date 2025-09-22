@extends('layouts.app')

@section('title', $shop->name)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Shop Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-3 border-primary" style="width: 120px; height: 120px; object-fit: cover;">
                            <div class="mt-2">
                                <span class="badge bg-secondary text-white">
                                    <i class="fas fa-star me-1" style="color:#ffc107;"></i>
                                    {{ $shop->average_rating ? number_format($shop->average_rating, 1) : 'No ratings yet' }}
                                </span>
                                <span class="text-muted small ms-2">({{ $shop->ratings->count() }} reviews)</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h1 class="display-5 fw-bold text-primary mb-2">{{ $shop->name }}</h1>
                            <p class="lead text-white bg-primary px-3 py-2 rounded-3 d-inline-block mb-3">{{ $shop->full_address }}</p>
                            <div class="d-flex gap-3 mb-3">
                                <a href="mailto:{{ $shop->email }}" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>{{ $shop->email }}
                                </a>
                                @if($shop->hasMapEmbedUrl())
                                    <button type="button" 
                                            class="btn btn-info text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#mapModal"
                                            data-shop-name="{{ $shop->name }}"
                                            data-map-url="{{ $shop->map_embed_url }}">
                                        <i class="fas fa-map-marker-alt me-2"></i>View Map
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            @if($shop->isCurrentlyOpen())
                                <span class="badge bg-success fs-6 p-3">Open Now</span>
                            @else
                                @php
                                    $now = \Carbon\Carbon::now();
                                    $dayOfWeek = $now->dayOfWeek;
                                    $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;
                                    $currentTime = $now->format('H:i:s');
                                @endphp
                                @if(!$shop->isOpenOnDay($dayOfWeek))
                                    <span class="badge bg-secondary fs-6 p-3">Closed Today</span>
                                @elseif($currentTime < $shop->opening_time->format('H:i:s'))
                                    <span class="badge bg-warning fs-6 p-3">Opens {{ $shop->opening_time->format('h:i A') }}</span>
                                @else
                                    <span class="badge bg-secondary fs-6 p-3">Closed</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Shop Information -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Shop Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 d-flex align-items-center">
                                <img src="{{ optional($shop->admin)->profile_picture ? asset('uploads/' . $shop->admin->profile_picture) : asset('images/default-profile.png') }}" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
                                <div>
                                    <span class="fw-bold text-muted">Owner:</span> {{ $shop->owner_name }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="fw-bold text-muted">Operating Hours:</span><br>
                                <span class="text-muted">{{ $shop->operating_hours }}</span>
                            </div>
                            <div class="mb-3">
                                <span class="fw-bold text-muted">Working Days:</span><br>
                                <span class="text-muted">{{ $shop->working_days_text }}</span>
                            </div>
                            @if($shop->description)
                                <div class="mb-3">
                                    <span class="fw-bold text-muted">About:</span><br>
                                    <span class="text-muted">{{ $shop->description }}</span>
                                </div>
                            @endif
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary text-white"><i class="fas fa-tools me-1"></i> {{ $shop->services->count() }} Services</span>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ $shop->slotSettings->count() }} Time Slots</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Available Services</h5>
                        </div>
                        <div class="card-body">
                            @if($shop->services->count() > 0)
                                <div class="row g-3">
                                    @foreach($shop->services as $service)
                                        <div class="col-md-6">
                                            <div class="card border h-100">
                                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">{{ $service->name }}</h6>
                                                    <span class="badge bg-{{ $service->type === 'repair' ? 'danger' : ($service->type === 'maintenance' ? 'warning' : ($service->type === 'inspection' ? 'info' : 'secondary')) }} text-white">{{ ucfirst($service->type) }}</span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-2">{{ Str::limit($service->description, 100) }}</p>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="fw-bold text-success fs-5">₱{{ number_format($service->price, 2) }}</span>
                                                        <span class="text-muted">{{ $service->duration }} minutes</span>
                                                    </div>
                                                    <button class="btn btn-outline-info btn-sm w-100 mt-2" data-bs-toggle="modal" data-bs-target="#serviceDetailsModal" onclick="showServiceDetails({{ $service->id }})">
                                                        <i class="fas fa-eye me-1"></i>View Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">No services available</h6>
                                    <p class="text-muted">Please contact the shop for available services.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Time Slots -->
            @if($shop->slotSettings->count() > 0)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Available Time Slots</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $now = \Carbon\Carbon::now();
                            $selectedDate = request('date', now()->format('Y-m-d'));
                            $isToday = $selectedDate === $now->format('Y-m-d');
                            $allSlotsPast = true;
                        @endphp
                        <div class="row g-3 slot-list-scroll">
                            @foreach($shop->slotSettings as $setting)
                                @if($setting->is_active)
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
                                    
                                    <div class="col-md-4">
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
                                @endif
                            @endforeach
                        </div>
                        @if($allSlotsPast)
                            <div class="text-center mt-3">
                                <p class="text-muted">All slots for today are past. Please select another date.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Map Section -->
            @if($shop->hasMapEmbedUrl())
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-16x9">
                            @php
                                $mapSrc = $shop->map_embed_url;
                                if ($shop->map_embed_url && str_contains($shop->map_embed_url, '<iframe')) {
                                    preg_match('/src="([^"]+)"/', $shop->map_embed_url, $matches);
                                    if (isset($matches[1])) {
                                        $mapSrc = $matches[1];
                                    }
                                }
                            @endphp
                            @if($mapSrc && $mapSrc !== 'null')
                                <iframe src="{{ $mapSrc }}" 
                                        style="border:0;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                        <p class="mb-1">Map not available</p>
                                        <small>Please contact the shop for directions</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Ratings Section -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Ratings</h5>
                    <span class="small">Average: {{ $shop->average_rating ? number_format($shop->average_rating, 1) : 'N/A' }} ({{ $shop->ratings->count() }} reviews)</span>
                </div>
                <div class="card-body">
                    @auth
                        <form method="POST" action="{{ route('shops.ratings.store', $shop) }}" class="mb-4" id="ratingForm">
                            @csrf
                            <div class="row g-3 align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label mb-1">Your Rating</label>
                                    <div id="starRating" class="d-flex align-items-center" data-selected="0" style="gap:6px; font-size: 1.25rem; cursor: pointer;">
                                        <i class="fas fa-star text-muted" data-value="1"></i>
                                        <i class="fas fa-star text-muted" data-value="2"></i>
                                        <i class="fas fa-star text-muted" data-value="3"></i>
                                        <i class="fas fa-star text-muted" data-value="4"></i>
                                        <i class="fas fa-star text-muted" data-value="5"></i>
                                    </div>
                                    <input type="hidden" name="rating" id="rating" value="" required>
                                    @error('rating')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-7">
                                    <label for="comment" class="form-label mb-0">Comment (optional)</label>
                                    <input id="comment" name="comment" type="text" class="form-control @error('comment') is-invalid @enderror" placeholder="Share your experience..." maxlength="2000">
                                    @error('comment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="submit" class="btn btn-secondary mt-4"><i class="fas fa-paper-plane me-1"></i>Submit</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-light border">
                            <i class="fas fa-info-circle me-2"></i>
                            Please <a href="{{ route('login') }}" class="text-decoration-underline">login</a> to rate this shop.
                        </div>
                    @endauth

                    @if($shop->ratings->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($shop->ratings as $rating)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <img src="{{ $rating->user->profile_picture_url }}" onerror="this.onerror=null;this.src='{{ $rating->user->avatar ?? asset('images/default-profile.png') }}';" class="rounded-circle me-2" style="width:28px;height:28px;object-fit:cover;">
                                            <strong>{{ $rating->user->name }}</strong>
                                            <span class="ms-2">
                                                @for($i=1; $i<=5; $i++)
                                                    <i class="fas fa-star" style="color: {{ $i <= $rating->rating ? '#ffc107' : '#e4e5e9' }};"></i>
                                                @endfor
                                            </span>
                                            @if($rating->comment)
                                                <div class="text-muted small mt-1">{{ $rating->comment }}</div>
                                            @endif
                                        </div>
                                        <div class="text-muted small">{{ $rating->created_at->diffForHumans() }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center text-muted py-3">No ratings yet.</div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <a href="{{ route('shops.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to All Shops
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="serviceDetailsModalLabel">Service Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="serviceDetailsContent">
        <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-weight: 500;
    letter-spacing: 0.025em;
}

.badge.bg-primary {
    background-color: #0d6efd !important;
    color: white !important;
}

.badge.bg-success {
    background-color: #198754 !important;
    color: white !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-info {
    background-color: #0dcaf0 !important;
    color: #000 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Slot card styling */
.slot-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

/* Limit visible slot cards to 6 with scroll on shop page */
.slot-list-scroll { max-height: 780px; overflow-y: auto; padding-right: 6px; }
@media (min-width: 992px) { /* lg: 3 cols → 2 rows */
  .slot-list-scroll { max-height: calc(2 * 260px); }
}
@media (min-width: 768px) and (max-width: 991.98px) { /* md: 2 cols → 3 rows */
  .slot-list-scroll { max-height: calc(3 * 260px); }
}
@media (max-width: 767.98px) { /* sm: 1 col → 6 rows */
  .slot-list-scroll { max-height: calc(6 * 260px); }
}
.slot-list-scroll::-webkit-scrollbar { width: 6px; }
.slot-list-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 6px; }
</style>
@endpush 

@push('scripts')
<script>
// Star rating widget
document.addEventListener('DOMContentLoaded', function() {
    const starContainer = document.getElementById('starRating');
    const ratingInput = document.getElementById('rating');
    if (starContainer && ratingInput) {
        const stars = Array.from(starContainer.querySelectorAll('i.fas.fa-star'));
        function paint(n) {
            stars.forEach(function(star, idx) {
                star.style.color = (idx < n) ? '#ffc107' : '#e4e5e9';
                star.classList.toggle('text-muted', !(idx < n));
            });
        }
        stars.forEach(function(star) {
            star.addEventListener('mouseover', function() { paint(parseInt(star.dataset.value, 10)); });
            star.addEventListener('mouseout', function() { paint(parseInt(starContainer.dataset.selected || '0', 10)); });
            star.addEventListener('click', function() {
                const val = parseInt(star.dataset.value, 10);
                starContainer.dataset.selected = String(val);
                ratingInput.value = String(val);
                paint(val);
            });
        });
        paint(0);
    }
});

function showServiceDetails(serviceId) {
    const content = document.getElementById('serviceDetailsContent');
    content.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    fetch('/services/' + serviceId)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = `
                <h4 class="mb-3 text-primary">${data.name}</h4>
                <p><strong>Type:</strong> ${data.type}</p>
                <p><strong>Description:</strong> ${data.description}</p>
                <p><strong>Price:</strong> ₱${parseFloat(data.price).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
                <p><strong>Duration:</strong> ${data.duration} minutes</p>
                ${data.shop ? `<p><strong>Shop:</strong> ${data.shop.name} <br><span class='text-muted small'>${data.shop.address}</span></p>` : ''}
            `;
        })
        .catch(() => {
            content.innerHTML = '<div class="text-danger">Failed to load service details.</div>';
        });
}

// Handle map modal
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>

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

@endpush 