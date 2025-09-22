@extends('layouts.app')

@section('title', 'Our Shops')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Our Auto Repair Shops</h1>
                <p class="lead text-muted">Find the perfect auto repair shop near you in Surigao City</p>
            </div>

            <div class="row g-4">
                <div class="col-12 mb-3">
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                        <input type="text" id="shopSearch" class="form-control" placeholder="Search shops by name, city, owner, address...">
                    </div>
                </div>
                @forelse($shops as $shop)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm border-0 shop-card">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between" style="border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $shop->image_url }}" alt="{{ $shop->name }}" class="rounded-circle border border-2 border-white" style="width: 48px; height: 48px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                    <div>
                                        <div class="fw-bold fs-5">{{ $shop->name }}</div>
                                        <div class="small text-light">{{ $shop->city }}</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($shop->isCurrentlyOpen())
                                        <span class="badge bg-success text-white">Open Now</span>
                                    @else
                                        @php
                                            $now = \Carbon\Carbon::now();
                                            $dayOfWeek = $now->dayOfWeek;
                                            $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;
                                            $currentTime = $now->format('H:i:s');
                                        @endphp
                                        @if(!$shop->isOpenOnDay($dayOfWeek))
                                            <span class="badge bg-secondary text-white">Closed Today</span>
                                        @elseif($currentTime < $shop->opening_time->format('H:i:s'))
                                            <span class="badge bg-warning text-dark">Opens {{ $shop->opening_time->format('h:i A') }}</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Closed</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="card-body pb-2">
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="{{ optional($shop->admin)->profile_picture ? asset('uploads/' . $shop->admin->profile_picture) : asset('images/default-profile.png') }}" class="rounded-circle me-2" style="width:28px;height:28px;object-fit:cover;">
                                        <div>
                                            <span class="fw-bold text-muted">Owner:</span> {{ $shop->owner_name }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-muted">Phone:</span> <a href="tel:{{ $shop->phone }}" class="text-decoration-none">{{ $shop->phone }}</a>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-muted">Email:</span> <a href="mailto:{{ $shop->email }}" class="text-decoration-none">{{ $shop->email }}</a>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="fw-bold text-muted">Address:</span> <span class="text-muted">{{ $shop->full_address }}</span>
                                </div>
                                <div class="mb-3">
                                    <span class="fw-bold text-muted">Hours:</span> {{ $shop->operating_hours }}<br>
                                    <span class="fw-bold text-muted">Days:</span> {{ $shop->working_days_text }}
                                </div>
                                @if($shop->description)
                                    <div class="mb-3">
                                        <span class="fw-bold text-muted">About:</span> <span class="text-muted">{{ Str::limit($shop->description, 80) }}</span>
                                    </div>
                                @endif
                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <span class="badge bg-primary text-white"><i class="fas fa-tools me-1"></i> {{ $shop->services->count() }} Services</span>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> {{ $shop->slotSettings->count() }} Time Slots</span>
                                    @if($shop->average_rating)
                                        <span class="badge bg-secondary text-white d-inline-flex align-items-center" style="gap:6px;">
                                            <i class="fas fa-star" style="color:#ffc107;"></i>
                                            {{ number_format($shop->average_rating, 1) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">No ratings</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center pt-2 pb-3">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('shops.show', $shop) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                                @if($shop->hasMapEmbedUrl())
                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#mapModal"
                                            data-shop-name="{{ $shop->name }}"
                                            data-map-url="{{ $shop->map_embed_url }}">
                                        <i class="fas fa-map-marker-alt me-1"></i> View Map
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-store fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No shops available</h5>
                        <p class="text-muted">Please check back later for available auto repair shops.</p>
                    </div>
                @endforelse
            </div>
            @if(method_exists($shops, 'links'))
                <div class="d-flex justify-content-center mt-4">
                    {{ $shops->withQueryString()->onEachSide(1)->links('vendor.pagination.shops') }}
                </div>
                <div class="text-center text-muted small mt-2 pagination-info">
                    Showing {{ $shops->firstItem() }} to {{ $shops->lastItem() }} of {{ $shops->total() }} results
                </div>
            @endif
        </div>
    </div>
</div>

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
.shop-card {
    transition: transform 0.2s ease-in-out;
}

.shop-card:hover {
    transform: translateY(-2px);
}

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

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
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

/* Mobile-responsive layout and spacing */
@media (max-width: 768px) {
    .container.py-5 {
        padding-top: 0.5rem !important;
        padding-bottom: 1rem !important;
    }
    
    .text-center.mb-5 {
        margin-bottom: 1rem !important;
    }
    
    .display-4 {
        font-size: 1.75rem !important;
        line-height: 1.2 !important;
        margin-bottom: 0.5rem !important;
    }
    
    .lead {
        font-size: 0.95rem !important;
        line-height: 1.4 !important;
        margin-bottom: 1rem !important;
    }
    
    .row.g-4 {
        gap: 0.75rem !important;
    }
    
    .col-12.col-md-6.col-lg-4 {
        margin-bottom: 0.75rem;
    }
}

/* Mobile-responsive shop cards */
@media (max-width: 768px) {
    .shop-card {
        margin-bottom: 0.75rem;
        border-radius: 12px !important;
    }
    
    .shop-card .card-header {
        padding: 0.75rem !important;
        border-radius: 12px 12px 0 0 !important;
    }
    
    .shop-card .card-header .d-flex.align-items-center.gap-3 {
        gap: 0.5rem !important;
    }
    
    .shop-card .card-header img {
        width: 40px !important;
        height: 40px !important;
    }
    
    .shop-card .card-header .fw-bold.fs-5 {
        font-size: 1rem !important;
        line-height: 1.2 !important;
    }
    
    .shop-card .card-header .small {
        font-size: 0.8rem !important;
    }
    
    .shop-card .card-body {
        padding: 0.75rem !important;
    }
    
    .shop-card .card-body .mb-3 {
        margin-bottom: 0.75rem !important;
    }
    
    .shop-card .card-body .fw-bold.text-muted {
        color: #495057 !important;
        font-size: 0.85rem !important;
    }
    
    .shop-card .card-body .text-muted {
        color: #6c757d !important;
        font-size: 0.85rem !important;
    }
    
    .shop-card .card-body a {
        color: #0d6efd !important;
        font-size: 0.85rem !important;
    }
    
    .shop-card .card-footer {
        padding: 0.75rem !important;
        border-radius: 0 0 12px 12px !important;
    }
    
    .shop-card .card-footer .d-flex.gap-2 {
        gap: 0.5rem !important;
        flex-direction: row !important;
        width: 100% !important;
        flex-wrap: wrap !important;
        justify-content: flex-start !important;
    }
    
    .shop-card .card-footer .btn {
        width: auto !important;
        padding: 0.5rem 1rem !important;
        font-size: 0.8rem !important;
        border-radius: 6px !important;
        margin-bottom: 0 !important;
        height: auto !important;
        line-height: 1.4 !important;
        flex: 0 1 auto !important;
        min-width: 80px !important;
        white-space: normal !important;
        text-align: center !important;
        word-wrap: break-word !important;
    }
    
    .shop-card .card-footer .btn:last-child {
        margin-bottom: 0 !important;
    }
    
    .shop-card .card-footer .btn-sm {
        padding: 0.5rem 1rem !important;
        font-size: 0.8rem !important;
        height: auto !important;
    }
    
    .shop-card .card-footer .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        border: none !important;
        color: white !important;
        font-weight: 500 !important;
    }
    
    .shop-card .card-footer .btn-outline-info {
        background: white !important;
        border: 1px solid #0dcaf0 !important;
        color: #0dcaf0 !important;
        font-weight: 500 !important;
    }
    
    .shop-card .card-footer .btn-outline-info:hover {
        background: #0dcaf0 !important;
        color: white !important;
    }
}

/* Mobile-responsive badges */
@media (max-width: 768px) {
    .badge {
        font-size: 0.75rem !important;
        padding: 0.4rem 0.6rem !important;
        border-radius: 6px !important;
    }
    
    .d-flex.gap-2.mb-3.flex-wrap {
        gap: 0.5rem !important;
        margin-bottom: 0.75rem !important;
    }
    
    .d-flex.gap-2.mb-3.flex-wrap .badge {
        font-size: 0.7rem !important;
        padding: 0.35rem 0.5rem !important;
    }
}

/* Mobile-responsive text colors and visibility */
@media (max-width: 768px) {
    .text-primary {
        color: #0d6efd !important;
    }
    
    .text-muted {
        color: #6c757d !important;
    }
    
    .text-light {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .bg-primary {
        background-color: #0d6efd !important;
    }
    
    .bg-success {
        background-color: #198754 !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    
    .bg-secondary {
        background-color: #6c757d !important;
    }
    
    .bg-info {
        background-color: #0dcaf0 !important;
    }
}

/* Mobile-responsive empty state */
@media (max-width: 768px) {
    .col-12.text-center.py-5 {
        padding-top: 2rem !important;
        padding-bottom: 2rem !important;
    }
    
    .fas.fa-store.fa-3x {
        font-size: 2.5rem !important;
    }
    
    .text-center h5 {
        font-size: 1.1rem !important;
    }
    
    .text-center p {
        font-size: 0.9rem !important;
    }
}

/* Pagination alignment fixes */
.pagination { margin-bottom: 0; }
.pagination .page-link { border-radius: 8px; }
.pagination .page-item.active .page-link { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); border: none; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle map modal
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

    // Client-side search filtering for shops list
    const searchInput = document.getElementById('shopSearch');
    if (searchInput) {
        const cardCols = Array.from(document.querySelectorAll('.row.g-4 > .col-12.col-md-6.col-lg-4'));
        function normalize(text) { return (text || '').toLowerCase(); }
        function filterCards() {
            const q = normalize(searchInput.value);
            cardCols.forEach(function(col) {
                const text = normalize(col.innerText);
                col.style.display = text.includes(q) ? '' : 'none';
            });
        }
        searchInput.addEventListener('input', filterCards);
    }
});
</script>
@endpush 