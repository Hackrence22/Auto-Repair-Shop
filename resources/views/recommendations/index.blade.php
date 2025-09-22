@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-lightbulb text-warning me-2"></i>
                    Personalized Recommendations
                </h2>
                <button class="btn btn-outline-primary btn-sm" onclick="refreshRecommendations()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
            
            <!-- Loading Spinner -->
            <div id="loading-spinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Analyzing your service history...</p>
            </div>
            
            <!-- Recommendations Container -->
            <div id="recommendations-container" style="display: none;">
                <!-- Urgent Recommendations -->
                <div id="urgent-recommendations" class="mb-4" style="display: none;">
                    <div class="alert alert-warning border-warning">
                        <h5 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Urgent Maintenance Due
                        </h5>
                        <p class="mb-0">These services are overdue and should be scheduled soon.</p>
                    </div>
                    <div id="urgent-cards" class="row g-3"></div>
                </div>
                
                <!-- Regular Recommendations -->
                <div id="regular-recommendations">
                    <h4 class="mb-3">
                        <i class="fas fa-star text-primary me-2"></i>
                        Recommended Services
                    </h4>
                    <div id="recommendation-cards" class="row g-3"></div>
                </div>
                
                <!-- No Recommendations -->
                <div id="no-recommendations" class="text-center py-5" style="display: none;">
                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                    <h5 class="text-muted">No recommendations available</h5>
                    <p class="text-muted">Complete your first service to get personalized recommendations.</p>
                    <a href="{{ route('shops.index') }}" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Find Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommendation Modal -->
<div class="modal fade" id="recommendationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tools me-2"></i>
                    <span id="modal-service-name"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-primary mb-2">Why this service?</h6>
                        <p id="modal-reason" class="mb-3"></p>
                        
                        <h6 class="text-primary mb-2">Service Details</h6>
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Price:</strong> <span id="modal-price"></span>
                            </div>
                            <div class="col-6">
                                <strong>Duration:</strong> <span id="modal-duration"></span>
                            </div>
                        </div>
                        
                        <div id="modal-maintenance-info" style="display: none;">
                            <h6 class="text-primary mb-2">Maintenance Schedule</h6>
                            <div class="alert alert-info">
                                <div id="modal-maintenance-details"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title">Ready to book?</h6>
                                <p class="card-text small text-muted">Schedule this service now</p>
                                <button class="btn btn-primary w-100" onclick="bookRecommendedService()">
                                    <i class="fas fa-calendar-plus me-1"></i>Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentRecommendations = [];
let selectedService = null;

document.addEventListener('DOMContentLoaded', function() {
    loadRecommendations();
});

function loadRecommendations() {
    showLoading();
    
    fetch('/recommendations/data?limit=10')
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success && data.recommendations.length > 0) {
                currentRecommendations = data.recommendations;
                displayRecommendations(data.recommendations);
            } else {
                showNoRecommendations();
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading recommendations:', error);
            showNoRecommendations();
        });
}

function displayRecommendations(recommendations) {
    const urgentRecommendations = recommendations.filter(rec => 
        rec.urgency === 'urgent' || rec.priority === 'high'
    );
    
    const regularRecommendations = recommendations.filter(rec => 
        rec.urgency !== 'urgent' && rec.priority !== 'high'
    );
    
    // Display urgent recommendations
    if (urgentRecommendations.length > 0) {
        displayRecommendationCards('urgent-cards', urgentRecommendations);
        document.getElementById('urgent-recommendations').style.display = 'block';
    }
    
    // Display regular recommendations
    if (regularRecommendations.length > 0) {
        displayRecommendationCards('recommendation-cards', regularRecommendations);
    }
    
    document.getElementById('recommendations-container').style.display = 'block';
}

function displayRecommendationCards(containerId, recommendations) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    recommendations.forEach((recommendation, index) => {
        const card = createRecommendationCard(recommendation, index);
        container.appendChild(card);
    });
}

function createRecommendationCard(recommendation, index) {
    const col = document.createElement('div');
    col.className = 'col-12 col-md-6 col-lg-4';
    
    const priorityClass = recommendation.priority === 'high' ? 'border-warning' : 
                         recommendation.priority === 'medium' ? 'border-info' : 'border-secondary';
    
    const urgencyBadge = recommendation.urgency === 'urgent' ? 
        '<span class="badge bg-danger ms-2">Urgent</span>' : '';
    
    col.innerHTML = `
        <div class="card h-100 recommendation-card ${priorityClass}" 
             onclick="showRecommendationDetails(${index})" 
             style="cursor: pointer; transition: transform 0.2s;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">${recommendation.service.name}</h6>
                    ${urgencyBadge}
                </div>
                
                <p class="card-text small text-muted mb-2">
                    <i class="fas fa-info-circle me-1"></i>
                    ${recommendation.reason}
                </p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-primary fw-bold">₱${recommendation.service.price}</span>
                    <span class="badge bg-${getTypeBadgeColor(recommendation.type)}">
                        ${getTypeDisplayName(recommendation.type)}
                    </span>
                </div>
                
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        ${recommendation.service.duration || 60} minutes
                    </small>
                </div>
            </div>
        </div>
    `;
    
    return col;
}

function getTypeBadgeColor(type) {
    const colors = {
        'preventive_maintenance': 'success',
        'service_based': 'primary',
        'cross_selling': 'info',
        'seasonal': 'warning',
        'popular': 'secondary'
    };
    return colors[type] || 'secondary';
}

function getTypeDisplayName(type) {
    const names = {
        'preventive_maintenance': 'Maintenance',
        'service_based': 'Related',
        'cross_selling': 'Bundle',
        'seasonal': 'Seasonal',
        'popular': 'Popular'
    };
    return names[type] || type;
}

function showRecommendationDetails(index) {
    const recommendation = currentRecommendations[index];
    selectedService = recommendation.service;
    
    document.getElementById('modal-service-name').textContent = recommendation.service.name;
    document.getElementById('modal-reason').textContent = recommendation.reason;
    document.getElementById('modal-price').textContent = `₱${recommendation.service.price}`;
    document.getElementById('modal-duration').textContent = `${recommendation.service.duration || 60} minutes`;
    
    // Show maintenance info if available
    if (recommendation.type === 'preventive_maintenance') {
        loadMaintenanceInfo(recommendation.service.name);
    } else {
        document.getElementById('modal-maintenance-info').style.display = 'none';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('recommendationModal'));
    modal.show();
}

function loadMaintenanceInfo(serviceName) {
    fetch(`/recommendations/maintenance-due?service_name=${encodeURIComponent(serviceName)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const info = data.maintenance_info;
                const daysText = info.is_overdue ? 
                    `${Math.abs(info.days_until_due)} days overdue` : 
                    `Due in ${info.days_until_due} days`;
                
                document.getElementById('modal-maintenance-details').innerHTML = `
                    <div class="row">
                        <div class="col-6">
                            <strong>Last Service:</strong><br>
                            ${new Date(info.last_service_date).toLocaleDateString()}
                        </div>
                        <div class="col-6">
                            <strong>Next Due:</strong><br>
                            ${new Date(info.next_due_date).toLocaleDateString()}
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-${info.is_overdue ? 'danger' : 'warning'}">
                            ${daysText}
                        </span>
                    </div>
                `;
                document.getElementById('modal-maintenance-info').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading maintenance info:', error);
            document.getElementById('modal-maintenance-info').style.display = 'none';
        });
}

function bookRecommendedService() {
    if (selectedService) {
        // Redirect to appointment creation with pre-selected service
        window.location.href = `/appointments/create?service_id=${selectedService.id}`;
    }
}

function refreshRecommendations() {
    loadRecommendations();
}

function showLoading() {
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('recommendations-container').style.display = 'none';
    document.getElementById('no-recommendations').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loading-spinner').style.display = 'none';
}

function showNoRecommendations() {
    document.getElementById('recommendations-container').style.display = 'none';
    document.getElementById('no-recommendations').style.display = 'block';
}
</script>

<style>
.recommendation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.recommendation-card {
    border-left: 4px solid;
}

@media (max-width: 768px) {
    .recommendation-card {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
