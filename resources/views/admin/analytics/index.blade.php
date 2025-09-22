@extends('layouts.admin')

@section('title', 'Analytics Center')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap justify-content-between align-items-center p-4 mb-3 shadow-sm bg-white rounded-4" style="min-height: 110px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                        <i class="fas fa-chart-bar text-white fs-2"></i>
                    </div>
                    <div>
                        <h1 class="fw-bold mb-1" style="font-size:2rem; color:#2c3e50;">Analytics Center</h1>
                        <p class="mb-0 text-secondary">Deep dive into your business performance and insights</p>
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
                    <button class="btn btn-outline-primary" onclick="exportAllAnalytics()">
                        <i class="fas fa-download me-2"></i>Export All
                    </button>
                    <button class="btn btn-primary" onclick="generateReport()">
                        <i class="fas fa-file-pdf me-2"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Navigation Cards -->
    <div class="row g-4 mb-4">
        <!-- Revenue Analytics -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="analytics-card" onclick="window.location.href='{{ route('admin.analytics.revenue') }}'">
                <div class="analytics-card-header bg-success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="analytics-card-body">
                    <h5>Revenue Analytics</h5>
                    <p>Track income, trends, and financial performance</p>
                    <div class="analytics-card-stats">
                        <span class="stat-item">
                            <i class="fas fa-chart-line text-success"></i>
                            Revenue Trends
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-percentage text-info"></i>
                            Growth Rate
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointment Analytics -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="analytics-card" onclick="window.location.href='{{ route('admin.analytics.appointments') }}'">
                <div class="analytics-card-header bg-primary">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="analytics-card-body">
                    <h5>Appointment Analytics</h5>
                    <p>Monitor booking patterns and service utilization</p>
                    <div class="analytics-card-stats">
                        <span class="stat-item">
                            <i class="fas fa-clock text-warning"></i>
                            Booking Trends
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-check-circle text-success"></i>
                            Completion Rates
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Analytics -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="analytics-card" onclick="window.location.href='{{ route('admin.analytics.customers') }}'">
                <div class="analytics-card-header bg-info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="analytics-card-body">
                    <h5>Customer Analytics</h5>
                    <p>Understand customer behavior and demographics</p>
                    <div class="analytics-card-stats">
                        <span class="stat-item">
                            <i class="fas fa-user-plus text-success"></i>
                            Growth
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-star text-warning"></i>
                            Loyalty
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Analytics -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="analytics-card" onclick="window.location.href='{{ route('admin.analytics.services') }}'">
                <div class="analytics-card-header bg-warning">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="analytics-card-body">
                    <h5>Service Analytics</h5>
                    <p>Analyze service performance and popularity</p>
                    <div class="analytics-card-stats">
                        <span class="stat-item">
                            <i class="fas fa-fire text-danger"></i>
                            Popularity
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-chart-pie text-primary"></i>
                            Performance
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">
                        <i class="fas fa-lightbulb me-2"></i>
                        Quick Insights
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
                                    <h6>Revenue Growth</h6>
                                    <p class="text-success">+15% this month</p>
                                    <small class="text-muted">Compared to last month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon bg-primary">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <div class="insight-content">
                                    <h6>Customer Acquisition</h6>
                                    <p class="text-primary">+25 new customers</p>
                                    <small class="text-muted">This week</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="insight-card">
                                <div class="insight-icon bg-warning">
                                    <i class="fas fa-star text-white"></i>
                                </div>
                                <div class="insight-content">
                                    <h6>Service Rating</h6>
                                    <p class="text-warning">4.8/5.0 average</p>
                                    <small class="text-muted">Based on feedback</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="row g-4">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <h5 class="content-card-title">
                        <i class="fas fa-file-alt me-2"></i>
                        Recent Reports
                    </h5>
                </div>
                <div class="content-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Type</th>
                                    <th>Generated</th>
                                    <th>Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Monthly Revenue Report</td>
                                    <td>{{ now()->subDays(5)->format('M d, Y') }}</td>
                                    <td>November 2024</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Download</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Customer Analytics</td>
                                    <td>{{ now()->subDays(10)->format('M d, Y') }}</td>
                                    <td>Last 30 days</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Download</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Service Performance</td>
                                    <td>{{ now()->subDays(15)->format('M d, Y') }}</td>
                                    <td>Q4 2024</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Download</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Analytics Card Styles */
.analytics-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
}

.analytics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.analytics-card-header {
    padding: 2rem;
    text-align: center;
}

.analytics-card-header i {
    font-size: 2.5rem;
}

.analytics-card-body {
    padding: 1.5rem;
}

.analytics-card-body h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.analytics-card-body p {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.analytics-card-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: #495057;
}

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
    .analytics-card-header {
        padding: 1.5rem;
    }
    
    .analytics-card-header i {
        font-size: 2rem;
    }
    
    .analytics-card-body {
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
<script>
function exportAllAnalytics() {
    // Export all analytics data
    window.location.href = '{{ route("admin.analytics.export") }}?type=all&format=csv';
}

function generateReport() {
    // Generate comprehensive PDF report
    alert('PDF report generation will be implemented here');
}

// Add click effects to analytics cards
document.addEventListener('DOMContentLoaded', function() {
    const analyticsCards = document.querySelectorAll('.analytics-card');
    
    analyticsCards.forEach(card => {
        card.addEventListener('click', function() {
            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255,255,255,0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = '50%';
            ripple.style.top = '50%';
            ripple.style.width = '100px';
            ripple.style.height = '100px';
            ripple.style.marginLeft = '-50px';
            ripple.style.marginTop = '-50px';
            
            this.style.position = 'relative';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Add CSS animation for ripple effect
const style = document.createElement('style');
style.textContent = `
@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
`;
document.head.appendChild(style);
</script>
@endpush 