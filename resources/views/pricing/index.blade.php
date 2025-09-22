@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-chart-line text-success me-2"></i>
                    Pricing Optimization Dashboard
                </h2>
                <div>
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshPricingData()">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <button class="btn btn-success btn-sm" onclick="showBulkPricingModal()">
                        <i class="fas fa-layer-group me-1"></i>Bulk Pricing
                    </button>
                </div>
            </div>
            
            <!-- Service Selection -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="service-select" class="form-label">Select Service</label>
                            <select id="service-select" class="form-select" onchange="loadPricingAnalysis()">
                                <option value="">Choose a service...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date-select" class="form-label">Date</label>
                            <input type="date" id="date-select" class="form-control" value="{{ date('Y-m-d') }}" onchange="loadPricingAnalysis()">
                        </div>
                        <div class="col-md-4">
                            <label for="shop-select" class="form-label">Shop (Optional)</label>
                            <select id="shop-select" class="form-select" onchange="loadPricingAnalysis()">
                                <option value="">All shops</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading Spinner -->
            <div id="loading-spinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Analyzing pricing factors...</p>
            </div>
            
            <!-- Pricing Analysis Container -->
            <div id="pricing-container" style="display: none;">
                <!-- Price Summary -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Current Price</h6>
                                <h4 class="text-primary" id="current-price">₱0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Optimal Price</h6>
                                <h4 class="text-success" id="optimal-price">₱0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Price Change</h6>
                                <h4 id="price-change">₱0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6 class="card-title text-muted">Risk Level</h6>
                                <h4 id="risk-level">Low</h4>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Factors -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2"></i>
                                    Pricing Factors
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="pricing-factors"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    Recommendations
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="pricing-recommendations"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Market Analysis -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>
                                    Market Analysis
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="market-analysis"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-trending-up me-2"></i>
                                    Price Range
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="price-range"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Trends Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line me-2"></i>
                            Pricing Trends
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="pricing-trends-chart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Pricing Modal -->
<div class="modal fade" id="bulkPricingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2"></i>
                    Bulk Pricing Analysis
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Services</label>
                    <div id="bulk-service-selection"></div>
                </div>
                <div id="bulk-pricing-results" style="display: none;">
                    <h6>Bulk Pricing Recommendations</h6>
                    <div id="bulk-pricing-content"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="analyzeBulkPricing()">Analyze</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let currentPricingData = null;
let pricingChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadServices();
    loadShops();
});

function loadServices() {
    fetch('/api/services')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('service-select');
            data.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading services:', error));
}

function loadShops() {
    fetch('/api/shops')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('shop-select');
            data.forEach(shop => {
                const option = document.createElement('option');
                option.value = shop.id;
                option.textContent = shop.name;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading shops:', error));
}

function loadPricingAnalysis() {
    const serviceId = document.getElementById('service-select').value;
    const date = document.getElementById('date-select').value;
    const shopId = document.getElementById('shop-select').value;
    
    if (!serviceId) {
        document.getElementById('pricing-container').style.display = 'none';
        return;
    }
    
    showLoading();
    
    const params = new URLSearchParams({
        service_id: serviceId,
        date: date
    });
    
    if (shopId) {
        params.append('shop_id', shopId);
    }
    
    fetch(`/pricing/optimal?${params}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                currentPricingData = data.pricing_analysis;
                displayPricingAnalysis(data.pricing_analysis);
                loadPricingTrends(serviceId, shopId);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error loading pricing analysis:', error);
        });
}

function displayPricingAnalysis(pricing) {
    // Update summary cards
    document.getElementById('current-price').textContent = `₱${pricing.current_price}`;
    document.getElementById('optimal-price').textContent = `₱${pricing.optimal_price.toFixed(2)}`;
    
    const priceChange = pricing.optimal_price - pricing.current_price;
    const priceChangeElement = document.getElementById('price-change');
    priceChangeElement.textContent = `₱${priceChange.toFixed(2)}`;
    priceChangeElement.className = priceChange >= 0 ? 'text-success' : 'text-danger';
    
    document.getElementById('risk-level').textContent = pricing.market_analysis.risk_assessment.risk_level;
    
    // Display pricing factors
    displayPricingFactors(pricing.pricing_factors);
    
    // Display recommendations
    displayRecommendations(pricing.recommendations);
    
    // Display market analysis
    displayMarketAnalysis(pricing.market_analysis);
    
    // Display price range
    displayPriceRange(pricing.price_range);
    
    document.getElementById('pricing-container').style.display = 'block';
}

function displayPricingFactors(factors) {
    const container = document.getElementById('pricing-factors');
    
    const factorData = [
        { name: 'Demand Factor', value: factors.demand_factor.factor, color: 'primary' },
        { name: 'Seasonal Factor', value: factors.seasonal_factor.factor, color: 'success' },
        { name: 'Competition Factor', value: factors.competition_factor.factor, color: 'warning' },
        { name: 'Time Factor', value: factors.time_factor.factor, color: 'info' },
        { name: 'Capacity Factor', value: factors.capacity_factor.factor, color: 'secondary' }
    ];
    
    container.innerHTML = factorData.map(factor => `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span>${factor.name}</span>
            <span class="badge bg-${factor.color}">${factor.value.toFixed(2)}</span>
        </div>
    `).join('');
}

function displayRecommendations(recommendations) {
    const container = document.getElementById('pricing-recommendations');
    
    if (recommendations.length === 0) {
        container.innerHTML = '<p class="text-muted">No specific recommendations at this time.</p>';
        return;
    }
    
    container.innerHTML = recommendations.map(rec => `
        <div class="alert alert-${getPriorityColor(rec.priority)} mb-2">
            <div class="d-flex justify-content-between">
                <strong>${rec.type.replace('_', ' ').toUpperCase()}</strong>
                <span class="badge bg-${getPriorityColor(rec.priority)}">${rec.priority}</span>
            </div>
            <p class="mb-0 mt-1">${rec.message}</p>
        </div>
    `).join('');
}

function displayMarketAnalysis(analysis) {
    const container = document.getElementById('market-analysis');
    
    container.innerHTML = `
        <div class="mb-3">
            <strong>Market Position:</strong>
            <span class="badge bg-primary ms-2">${analysis.market_position.replace('_', ' ')}</span>
        </div>
        <div class="mb-3">
            <strong>Pricing Strategy:</strong>
            <span class="badge bg-success ms-2">${analysis.pricing_strategy.replace('_', ' ')}</span>
        </div>
        <div class="mb-3">
            <strong>Risk Level:</strong>
            <span class="badge bg-${getRiskColor(analysis.risk_assessment.risk_level)} ms-2">${analysis.risk_assessment.risk_level}</span>
        </div>
        <div>
            <strong>Opportunities:</strong>
            <ul class="mt-1">
                ${analysis.opportunities.map(opp => `<li>${opp.replace('_', ' ')}</li>`).join('')}
            </ul>
        </div>
    `;
}

function displayPriceRange(range) {
    const container = document.getElementById('price-range');
    
    container.innerHTML = `
        <div class="mb-2">
            <strong>Min Price:</strong> ₱${range.min_price.toFixed(2)}
        </div>
        <div class="mb-2">
            <strong>Recommended Min:</strong> ₱${range.recommended_min.toFixed(2)}
        </div>
        <div class="mb-2">
            <strong>Optimal Price:</strong> ₱${range.optimal_price.toFixed(2)}
        </div>
        <div class="mb-2">
            <strong>Recommended Max:</strong> ₱${range.recommended_max.toFixed(2)}
        </div>
        <div>
            <strong>Max Price:</strong> ₱${range.max_price.toFixed(2)}
        </div>
    `;
}

function loadPricingTrends(serviceId, shopId) {
    const params = new URLSearchParams({
        service_id: serviceId,
        period: 'month'
    });
    
    if (shopId) {
        params.append('shop_id', shopId);
    }
    
    fetch(`/pricing/trends?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPricingTrends(data.pricing_trends);
            }
        })
        .catch(error => console.error('Error loading pricing trends:', error));
}

function displayPricingTrends(trends) {
    const ctx = document.getElementById('pricing-trends-chart').getContext('2d');
    
    if (pricingChart) {
        pricingChart.destroy();
    }
    
    pricingChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trends.map(t => t.date),
            datasets: [{
                label: 'Optimal Price',
                data: trends.map(t => t.optimal_price),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Current Price',
                data: trends.map(t => t.current_price),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

function showBulkPricingModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkPricingModal'));
    modal.show();
    
    // Load services for bulk selection
    fetch('/api/services')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('bulk-service-selection');
            container.innerHTML = data.map(service => `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${service.id}" id="service-${service.id}">
                    <label class="form-check-label" for="service-${service.id}">
                        ${service.name} - ₱${service.price}
                    </label>
                </div>
            `).join('');
        });
}

function analyzeBulkPricing() {
    const selectedServices = Array.from(document.querySelectorAll('#bulk-service-selection input:checked'))
        .map(input => input.value);
    
    if (selectedServices.length === 0) {
        alert('Please select at least one service.');
        return;
    }
    
    const shopId = document.getElementById('shop-select').value;
    const params = new URLSearchParams({
        service_ids: selectedServices
    });
    
    if (shopId) {
        params.append('shop_id', shopId);
    }
    
    fetch(`/pricing/bulk?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBulkPricingResults(data.bulk_pricing);
            }
        })
        .catch(error => console.error('Error analyzing bulk pricing:', error));
}

function displayBulkPricingResults(bulkPricing) {
    const container = document.getElementById('bulk-pricing-content');
    
    let html = '<div class="row mb-3">';
    html += '<div class="col-md-6"><h6>Individual Pricing</h6>';
    html += bulkPricing.individual_pricing.map(pricing => `
        <div class="card mb-2">
            <div class="card-body">
                <h6>Service ID: ${pricing.service_id}</h6>
                <p>Current: ₱${pricing.current_price} | Optimal: ₱${pricing.optimal_price.toFixed(2)}</p>
            </div>
        </div>
    `).join('');
    html += '</div>';
    
    html += '<div class="col-md-6"><h6>Bundle Pricing</h6>';
    html += `
        <div class="card">
            <div class="card-body">
                <p><strong>Total Current Price:</strong> ₱${bulkPricing.bundle_pricing.total_current_price.toFixed(2)}</p>
                <p><strong>Total Optimal Price:</strong> ₱${bulkPricing.bundle_pricing.total_optimal_price.toFixed(2)}</p>
                <p><strong>Recommended Discount:</strong> ${(bulkPricing.bundle_pricing.recommended_bundle_discount * 100).toFixed(1)}%</p>
                <p><strong>Bundle Price:</strong> ₱${bulkPricing.bundle_pricing.bundle_price.toFixed(2)}</p>
            </div>
        </div>
    `;
    html += '</div></div>';
    
    container.innerHTML = html;
    document.getElementById('bulk-pricing-results').style.display = 'block';
}

function getPriorityColor(priority) {
    const colors = {
        'high': 'danger',
        'medium': 'warning',
        'low': 'success'
    };
    return colors[priority] || 'secondary';
}

function getRiskColor(risk) {
    const colors = {
        'high': 'danger',
        'medium': 'warning',
        'low': 'success'
    };
    return colors[risk] || 'secondary';
}

function refreshPricingData() {
    if (document.getElementById('service-select').value) {
        loadPricingAnalysis();
    }
}

function showLoading() {
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('pricing-container').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loading-spinner').style.display = 'none';
}
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.alert {
    border-radius: 0.375rem;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
}
</style>
@endpush
