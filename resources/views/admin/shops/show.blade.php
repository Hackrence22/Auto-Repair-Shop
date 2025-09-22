@extends('layouts.admin')

@section('title', 'Shop Details - ' . $shop->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.shops.index') }}">Shops</a></li>
<li class="breadcrumb-item active">{{ $shop->name }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">{{ $shop->name }}</h1>
                <div class="btn-group">
                    <a href="{{ route('admin.shops.edit', $shop) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Shop
                    </a>
                    <a href="{{ route('admin.shops.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Shops
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Shop Information Card -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Shop Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <img src="{{ $shop->image_url }}" 
                                     alt="{{ $shop->name }}" 
                                     class="img-thumbnail"
                                     style="max-width: 200px; max-height: 200px; object-fit: cover; cursor: pointer;"
                                     onclick="showImageModal('{{ $shop->image_url }}')">
                            </div>

                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Owner:</td>
                                    <td>{{ $shop->owner_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Phone:</td>
                                    <td>
                                        <a href="tel:{{ $shop->phone }}" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i>{{ $shop->phone }}
                                        </a>
                                    </td>
                                </tr>
                                @if($shop->email)
                                <tr>
                                    <td class="fw-bold text-muted">Email:</td>
                                    <td>
                                        <a href="mailto:{{ $shop->email }}" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i>{{ $shop->email }}
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="fw-bold text-muted">Address:</td>
                                    <td>{{ $shop->full_address }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Hours:</td>
                                    <td>{{ $shop->operating_hours }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Working Days:</td>
                                    <td>{{ $shop->working_days_text }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Status:</td>
                                    <td>
                                        <span class="badge {{ $shop->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $shop->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($shop->isCurrentlyOpen())
                                            <br><small class="text-success">Currently Open</small>
                                        @else
                                            <br><small class="text-muted">Currently Closed</small>
                                        @endif
                                    </td>
                                </tr>
                                @if($shop->description)
                                <tr>
                                    <td class="fw-bold text-muted">Description:</td>
                                    <td>{{ $shop->description }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar me-2"></i>Quick Stats
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="fw-bold h4 text-primary">{{ $shop->services->count() }}</div>
                                    <small class="text-muted">Services</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold h4 text-success">{{ $shop->appointments->count() }}</div>
                                    <small class="text-muted">Appointments</small>
                                </div>
                                <div class="col-4">
                                    <div class="fw-bold h4 text-info">{{ $shop->slotSettings->count() }}</div>
                                    <small class="text-muted">Slot Settings</small>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col-12">
                                    <div class="fw-bold h5">
                                        <i class="fas fa-star me-1" style="color:#ffc107;"></i>
                                        {{ $shop->average_rating ? number_format($shop->average_rating, 1) : 'No ratings' }}
                                        <small class="text-muted">({{ $shop->ratings_count ?? $shop->ratings->count() }} reviews)</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map and Content -->
                <div class="col-lg-8">
                    <!-- Map Card -->
                    @if($shop->hasMapEmbedUrl())
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-map-marker-alt me-2"></i>Location Map
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @if($shop->map_embed_url && $shop->map_embed_url !== 'null')
                                <iframe src="{{ $shop->map_embed_url }}" 
                                        width="100%" 
                                        height="300" 
                                        style="border:0;" 
                                        allowfullscreen="" 
                                        loading="lazy" 
                                        referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            @else
                                <div class="d-flex align-items-center justify-content-center" style="height: 300px; background-color: #f8f9fa;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                                        <p class="mb-1">Map not available</p>
                                        <small>No map embed URL provided</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Services Tab -->
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="shopTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab">
                                        <i class="fas fa-tools me-1"></i>Services ({{ $shop->services->count() }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button" role="tab">
                                        <i class="fas fa-calendar-check me-1"></i>Recent Appointments ({{ $shop->appointments->count() }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="slots-tab" data-bs-toggle="tab" data-bs-target="#slots" type="button" role="tab">
                                        <i class="fas fa-clock me-1"></i>Slot Settings ({{ $shop->slotSettings->count() }})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="ratings-tab" data-bs-toggle="tab" data-bs-target="#ratings" type="button" role="tab">
                                        <i class="fas fa-star me-1"></i>Ratings ({{ $shop->ratings_count ?? $shop->ratings->count() }})
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="shopTabsContent">
                                <!-- Services Tab -->
                                <div class="tab-pane fade show active" id="services" role="tabpanel">
                                    @if($shop->services->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Service Name</th>
                                                        <th>Type</th>
                                                        <th>Price</th>
                                                        <th>Duration</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shop->services as $service)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold">{{ $service->name }}</div>
                                                            @if($service->description)
                                                                <small class="text-muted">{{ Str::limit($service->description, 50) }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ ucfirst($service->type) }}</td>
                                                        <td>PHP {{ number_format($service->price, 2) }}</td>
                                                        <td>{{ $service->duration }} min</td>
                                                        <td>
                                                            <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-tools fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No services available for this shop.</p>
                                            <a href="{{ route('admin.services.create', ['shop_id' => $shop->id]) }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Service
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Appointments Tab -->
                                <div class="tab-pane fade" id="appointments" role="tabpanel">
                                    @if($shop->appointments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Customer</th>
                                                        <th>Service</th>
                                                        <th>Date & Time</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shop->appointments->take(10) as $appointment)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-bold">{{ $appointment->customer_name }}</div>
                                                            <small class="text-muted">{{ $appointment->vehicle_type }} {{ $appointment->vehicle_model }}</small>
                                                        </td>
                                                        <td>{{ $appointment->service_type }}</td>
                                                        <td>
                                                            <div>{{ $appointment->appointment_date->format('M d, Y') }}</div>
                                                            <small class="text-muted">{{ $appointment->appointment_time->format('h:i A') }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ 
                                                                $appointment->status === 'completed' ? 'success' : 
                                                                ($appointment->status === 'confirmed' ? 'primary' : 
                                                                ($appointment->status === 'cancelled' ? 'danger' : 'warning'))
                                                            }}">
                                                                {{ ucfirst($appointment->status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($shop->appointments->count() > 10)
                                        <div class="text-center">
                                            <a href="{{ route('admin.appointments.index', ['shop_id' => $shop->id]) }}" class="btn btn-outline-primary">
                                                View All Appointments
                                            </a>
                                        </div>
                                        @endif
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No appointments found for this shop.</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Slot Settings Tab -->
                                <div class="tab-pane fade" id="slots" role="tabpanel">
                                    @if($shop->slotSettings->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Time Range</th>
                                                        <th>Slots per Hour</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shop->slotSettings as $slot)
                                                    <tr>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }} - 
                                                            {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                                        </td>
                                                        <td>{{ $slot->slots_per_hour }}</td>
                                                        <td>
                                                            <span class="badge {{ $slot->is_active ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $slot->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('admin.slot-settings.edit', $slot) }}" class="btn btn-sm btn-outline-warning">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No slot settings configured for this shop.</p>
                                            <a href="{{ route('admin.slot-settings.create', ['shop_id' => $shop->id]) }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Slot Setting
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Ratings Tab -->
                                <div class="tab-pane fade" id="ratings" role="tabpanel">
                                    @if($shop->ratings->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>User</th>
                                                        <th>Rating</th>
                                                        <th>Comment</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($shop->ratings as $rating)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <img src="{{ $rating->user->profile_picture_url }}" onerror="this.onerror=null;this.src='{{ $rating->user->avatar ?? asset('images/default-profile.png') }}';" class="rounded-circle me-2" style="width:32px;height:32px;object-fit:cover;">
                                                                <span>{{ $rating->user->name }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @for($i=1; $i<=5; $i++)
                                                                <i class="fas fa-star" style="color: {{ $i <= $rating->rating ? '#ffc107' : '#e4e5e9' }};"></i>
                                                            @endfor
                                                        </td>
                                                        <td>{{ $rating->comment }}</td>
                                                        <td>{{ $rating->created_at->format('M d, Y h:i A') }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="fas fa-star fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No ratings yet for this shop.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 text-center">
                <img id="modalImage" src="" alt="Shop Image" 
                     style="max-width: 100%; max-height: 80vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endpush 