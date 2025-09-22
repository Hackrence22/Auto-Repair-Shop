@extends('layouts.admin')

@section('title', 'Revenue Analytics')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4" style="min-height: 110px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-dollar-sign text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Revenue Analytics</h1>
                        <p class="mb-0 text-secondary">Track your financial performance and revenue trends</p>
                        @php
                            $admin = auth('admin')->user();
                            $shop = null;
                            if ($admin && $admin->isOwner()) {
                                $shop = \App\Models\Shop::where('admin_id', $admin->id)->first();
                            }
                        @endphp
                        @if($shop)
                            <div class="mt-2">
                                <span class="badge bg-primary fs-6">
                                    <i class="fas fa-store me-1"></i>{{ $shop->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exportRevenueData()">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                    <a href="{{ route('admin.analytics.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-dollar-sign text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-success">₱{{ number_format($totalRevenue, 2) }}</div>
                    <div class="text-secondary small">Total Revenue</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-secondary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-calendar-check text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-secondary">{{ $totalAppointments ?? 0 }}</div>
                    <div class="text-secondary small">Completed Appointments</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-primary">₱{{ number_format($averageRevenue, 2) }}</div>
                    <div class="text-secondary small">Average Revenue</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-dark bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-briefcase text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-dark">{{ $totalServices ?? 0 }}</div>
                    <div class="text-secondary small">Unique Services</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-calendar text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-info">{{ $revenueData->count() }}</div>
                    <div class="text-secondary small">Revenue Days</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="shadow-sm rounded-4 p-3 d-flex align-items-center gap-3 bg-white">
                <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="fas fa-trending-up text-white"></i>
                </div>
                <div>
                    <div class="fw-bold fs-4 text-warning">+15%</div>
                    <div class="text-secondary small">Growth Rate</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="content-card-title">
                            <i class="fas fa-chart-area me-2"></i>
                            Revenue Trends
                        </h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateChart('daily')">Daily</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateChart('weekly')">Weekly</button>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="updateChart('monthly')">Monthly</button>
                        </div>
                    </div>
                </div>
                <div class="content-card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Details -->
    <div class="row g-4 mb-4">
        <!-- Revenue by Service -->
        <div class="col-12 col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Revenue by Service
                    </h5>
                </div>
                <div class="content-card-body">
                    <canvas id="serviceRevenueChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Table -->
        <div class="col-12 col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">
                        <i class="fas fa-table me-2"></i>
                        Revenue Details
                    </h5>
                </div>
                <div class="content-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Revenue</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueData->take(10) as $data)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($data->date)->format('M d, Y') }}</td>
                                        <td class="fw-bold text-success">₱{{ number_format($data->revenue, 2) }}</td>
                                        <td>
                                            @if($data->revenue > $averageRevenue)
                                                <span class="text-success"><i class="fas fa-arrow-up"></i></span>
                                            @else
                                                <span class="text-danger"><i class="fas fa-arrow-down"></i></span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Insights -->
    <div class="row g-4">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">
                        <i class="fas fa-lightbulb me-2"></i>
                        Financial Insights
                    </h5>
                </div>
                <div class="content-card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon bg-success">
                                    <i class="fas fa-trending-up text-white"></i>
                                </div>
                                <div class="insight-content">
                                    <h6>Peak Revenue Day</h6>
                                    <p class="text-success">Friday</p>
                                    <small class="text-muted">Highest average daily revenue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon bg-primary">
                                    <i class="fas fa-calendar text-white"></i>
                                </div>
                                <div class="insight-content">
                                    <h6>Best Month</h6>
                                    <p class="text-primary">December</p>
                                    <small class="text-muted">Highest monthly revenue</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon bg-warning">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <div class="insight-content">
                                    <h6>Growth Trend</h6>
                                    <p class="text-warning">+15% monthly</p>
                                    <small class="text-muted">Consistent growth pattern</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Content Card Styles */
.content-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.content-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.content-card-title {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.content-card-body {
    padding: 1.5rem;
}

/* Insight Card Styles */
.insight-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.insight-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.insight-content h6 {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
}

.insight-content p {
    margin: 0.25rem 0;
    font-weight: 600;
}

.insight-content small {
    color: #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .content-card-header,
    .content-card-body {
        padding: 1rem;
    }
    
    .insight-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($revenueData->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('M d'); })),
        datasets: [{
            label: 'Revenue',
            data: @json($revenueData->pluck('revenue')),
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                },
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Service Revenue Chart
const serviceRevenueCtx = document.getElementById('serviceRevenueChart').getContext('2d');
const serviceRevenueChart = new Chart(serviceRevenueCtx, {
    type: 'doughnut',
    data: {
        labels: ['Oil Change', 'Brake Service', 'Tire Rotation', 'Engine Tune-up', 'Other'],
        datasets: [{
            data: [30, 25, 20, 15, 10],
            backgroundColor: [
                '#28a745',
                '#007bff',
                '#ffc107',
                '#dc3545',
                '#6c757d'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        }
    }
});

function updateChart(period) {
    // Update chart based on selected period
    console.log('Updating chart for period:', period);
    // Implementation for different time periods
}

function exportRevenueData() {
    // Export revenue data
    window.location.href = '{{ route("admin.analytics.export") }}?type=revenue&format=csv';
}
</script>
@endpush 