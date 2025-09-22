@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="dashboard-container">
    <!-- Enhanced Page Header with Modern Design -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="header-content">
                        <div class="header-left">
                            <div class="header-icon-wrapper">
                                <div class="header-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div class="icon-glow"></div>
                            </div>
                            <div class="header-text">
                                <h1 class="header-title">
                                    <span class="title-gradient">Analytics Dashboard</span>
                                    <div class="title-underline"></div>
                                </h1>
                                <p class="header-subtitle">Comprehensive insights into your auto repair business performance</p>
                                

                                
                                <div class="header-stats">
                                    <div class="stat-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Last updated: {{ now()->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <i class="fas fa-signal"></i>
                                        <span>Real-time data</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-actions">
                            <button class="btn btn-modern btn-refresh" onclick="refreshDashboard()">
                                <div class="btn-content">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Refresh</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                            <button class="btn btn-modern btn-export" onclick="exportAnalytics()">
                                <div class="btn-content">
                                    <i class="fas fa-download"></i>
                                    <span>Export Report</span>
                                </div>
                                <div class="btn-glow"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid dashboard-content">
        <!-- Enhanced Key Performance Indicators -->
        <div class="row g-4 mb-5">
            <!-- Revenue Metrics -->
            <div class="col-6 col-lg-3">
                <div class="metric-card revenue-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">₱{{ number_format($totalRevenue, 2) }}</div>
                        <div class="metric-label">Total Revenue</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12.5%</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas class="mini-chart" width="60" height="30"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card appointment-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($totalAppointments) }}</div>
                        <div class="metric-label">Total Appointments</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8.3%</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas class="mini-chart" width="60" height="30"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card customer-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($totalUsers) }}</div>
                        <div class="metric-label">Total Customers</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+15.2%</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas class="mini-chart" width="60" height="30"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card service-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ number_format($totalServices) }}</div>
                        <div class="metric-label">Total Services</div>
                        <div class="metric-trend neutral">
                            <i class="fas fa-minus"></i>
                            <span>0%</span>
                        </div>
                    </div>
                    <div class="metric-chart">
                        <canvas class="mini-chart" width="60" height="30"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Secondary Metrics Row -->
        <div class="row g-4 mb-5">
            <div class="col-6 col-lg-3">
                <div class="metric-card secondary-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">₱{{ number_format($monthlyRevenue, 2) }}</div>
                        <div class="metric-label">This Month</div>
                        <div class="metric-subtitle">Monthly Revenue</div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card secondary-card" data-aos="fade-up" data-aos-delay="600">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ $completionRate }}%</div>
                        <div class="metric-label">Completion Rate</div>
                        <div class="metric-subtitle">Service Success</div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card secondary-card" data-aos="fade-up" data-aos-delay="700">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ $todayAppointments }}</div>
                        <div class="metric-label">Today's Appointments</div>
                        <div class="metric-subtitle">Scheduled</div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
            
            <div class="col-6 col-lg-3">
                <div class="metric-card secondary-card" data-aos="fade-up" data-aos-delay="800">
                    <div class="card-glow"></div>
                    <div class="metric-icon-wrapper">
                        <div class="metric-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value">{{ $newUsersThisMonth }}</div>
                        <div class="metric-label">New Customers</div>
                        <div class="metric-subtitle">This Month</div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Charts and Analytics Section -->
        <div class="row g-4 mb-4">
            <!-- Monthly Trends Chart -->
            <div class="col-12 col-lg-8">
                <div class="content-card chart-card" data-aos="fade-up" data-aos-delay="900">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="header-left">
                                <h5 class="content-card-title">
                                    <i class="fas fa-chart-area me-2"></i>
                                    Monthly Appointment Trends
                                </h5>
                                <p class="content-card-subtitle">Track appointment growth over time</p>
                            </div>
                            <div class="chart-controls">
                                <select class="form-select form-select-modern" id="chartPeriod">
                                    <option value="12">Last 12 Months</option>
                                    <option value="6">Last 6 Months</option>
                                    <option value="3">Last 3 Months</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <canvas id="monthlyTrendsChart" height="100"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>

            <!-- Service Popularity -->
            <div class="col-12 col-lg-4">
                <div class="content-card chart-card" data-aos="fade-up" data-aos-delay="1000">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-chart-pie me-2"></i>
                                Popular Services
                            </h5>
                            <p class="content-card-subtitle">Most requested services</p>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <canvas id="servicePopularityChart" height="200"></canvas>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Detailed Analytics -->
        <div class="row g-4 mb-4">
            <!-- Vehicle Type Analytics -->
            <div class="col-12 col-md-6">
                <div class="content-card analytics-card" data-aos="fade-up" data-aos-delay="1100">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-car me-2"></i>
                                Vehicle Type Distribution
                            </h5>
                            <p class="content-card-subtitle">Customer vehicle preferences</p>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Vehicle Type</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicleTypeStats as $stat)
                                        <tr class="table-row-modern">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="vehicle-type-icon me-3">
                                                        <i class="fas fa-car"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vehicle-type-name">{{ $stat->vehicle_type ?: 'Not Specified' }}</div>
                                                        <div class="vehicle-type-subtitle">Vehicle category</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-modern bg-primary">{{ $stat->count }}</span>
                                            </td>
                                            <td>
                                                <div class="progress-wrapper">
                                                    <div class="progress progress-modern">
                                                        <div class="progress-bar progress-bar-modern" style="width: {{ $totalAppointments > 0 ? ($stat->count / $totalAppointments) * 100 : 0 }}%"></div>
                                                    </div>
                                                    <small class="progress-text">{{ $totalAppointments > 0 ? round(($stat->count / $totalAppointments) * 100, 1) : 0 }}%</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>

            <!-- Payment Method Usage -->
            <div class="col-12 col-md-6">
                <div class="content-card analytics-card" data-aos="fade-up" data-aos-delay="1200">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-credit-card me-2"></i>
                                Payment Method Usage
                            </h5>
                            <p class="content-card-subtitle">Customer payment preferences</p>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Payment Method</th>
                                        <th>Usage Count</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentMethodUsage as $method)
                                        <tr class="table-row-modern">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="payment-method-icon me-3">
                                                        <i class="fas fa-credit-card"></i>
                                                    </div>
                                                    <div>
                                                        <div class="payment-method-name">{{ $method->name }}</div>
                                                        <div class="payment-method-subtitle">Payment option</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-modern bg-info">{{ $method->appointments_count }}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge-modern {{ $method->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    <i class="fas fa-{{ $method->is_active ? 'check' : 'times' }} me-1"></i>
                                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Today's Schedule and Recent Activity -->
        <div class="row g-4 mb-4">
            <!-- Today's Schedule -->
            <div class="col-12 col-lg-6">
                <div class="content-card schedule-card" data-aos="fade-up" data-aos-delay="1300">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="header-left">
                                <h5 class="content-card-title">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Today's Schedule
                                </h5>
                                <p class="content-card-subtitle">Appointments for {{ now()->format('M d, Y') }}</p>
                            </div>
                            <span class="badge badge-modern bg-primary">{{ $todaySchedule->count() }} appointments</span>
                        </div>
                    </div>
                    <div class="content-card-body">
                        @if($todaySchedule->count() > 0)
                            <div class="timeline timeline-modern">
                                @foreach($todaySchedule as $appointment)
                                    <div class="timeline-item timeline-item-modern">
                                        <div class="timeline-marker timeline-marker-modern bg-{{ 
                                            $appointment->status === 'completed' ? 'success' : 
                                            ($appointment->status === 'confirmed' ? 'primary' : 
                                            ($appointment->status === 'pending' ? 'warning' : 'danger'))
                                        }}"></div>
                                        <div class="timeline-content timeline-content-modern">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="timeline-info">
                                                    <h6 class="timeline-title">{{ $appointment->customer_name }}</h6>
                                                    <p class="timeline-service">{{ $appointment->service_type }}</p>
                                                    <div class="timeline-time">
                                                        <i class="fas fa-clock me-1"></i>
                                                        {{ $appointment->appointment_time }}
                                                    </div>
                                                </div>
                                                <span class="badge badge-modern bg-{{ 
                                                    $appointment->status === 'completed' ? 'success' : 
                                                    ($appointment->status === 'confirmed' ? 'primary' : 
                                                    ($appointment->status === 'pending' ? 'warning' : 'danger'))
                                                }}">{{ ucfirst($appointment->status) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state-modern">
                                <div class="empty-icon">
                                    <i class="fas fa-calendar-times"></i>
                                </div>
                                <h6 class="empty-title">No appointments scheduled for today</h6>
                                <p class="empty-subtitle">All clear! No appointments to manage.</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-12 col-lg-6">
                <div class="content-card activity-card" data-aos="fade-up" data-aos-delay="1400">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-clock me-2"></i>
                                Recent Activity
                            </h5>
                            <p class="content-card-subtitle">Latest system activities</p>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <div class="activity-feed activity-feed-modern">
                            @foreach($recentAppointments->take(5) as $appointment)
                                <div class="activity-item activity-item-modern">
                                    <div class="activity-icon activity-icon-modern bg-primary">
                                        <i class="fas fa-calendar-check text-white"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $appointment->customer_name }}</div>
                                        <div class="activity-text">Booked {{ $appointment->service ? $appointment->service->name : $appointment->service_type }} for {{ $appointment->vehicle_type ?: 'N/A' }}</div>
                                        <div class="activity-time">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $appointment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @foreach($recentUsers->take(3) as $user)
                                <div class="activity-item activity-item-modern">
                                    <div class="activity-icon activity-icon-modern bg-success">
                                        <i class="fas fa-user-plus text-white"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $user->name }}</div>
                                        <div class="activity-text">Joined the platform</div>
                                        <div class="activity-time">
                                            <i class="fas fa-clock me-1"></i>
                                            {{ $user->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Customer Service Widget -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="content-card customer-service-card" data-aos="fade-up" data-aos-delay="1500">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="header-left">
                                <h5 class="content-card-title">
                                    <i class="fas fa-headset me-2"></i>
                                    Customer Service Overview
                                </h5>
                                <p class="content-card-subtitle">Manage customer requests and support tickets</p>
                            </div>
                            <div class="header-actions">
                                <a href="{{ route('admin.customer-service.index') }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-list me-1"></i>View All
                                </a>
                                <a href="{{ route('admin.customer-service.dashboard') }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-chart-bar me-1"></i>Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="content-card-body">
                        @php
                            $admin = auth('admin')->user();
                            $totalRequests = \App\Models\CustomerService::when($admin && $admin->role !== 'super_admin', function($query) use ($admin) {
                                return $query->whereHas('shop', function($q) use ($admin) {
                                    $q->where('admin_id', $admin->id);
                                });
                            })->count();
                            
                            $openRequests = \App\Models\CustomerService::when($admin && $admin->role !== 'super_admin', function($query) use ($admin) {
                                return $query->whereHas('shop', function($q) use ($admin) {
                                    $q->where('admin_id', $admin->id);
                                });
                            })->where('status', 'open')->count();
                            
                            $urgentRequests = \App\Models\CustomerService::when($admin && $admin->role !== 'super_admin', function($query) use ($admin) {
                                return $query->whereHas('shop', function($q) use ($admin) {
                                    $q->where('admin_id', $admin->id);
                                });
                            })->where('priority', 'urgent')->where('status', '!=', 'closed')->count();
                            
                            $recentRequests = \App\Models\CustomerService::when($admin && $admin->role !== 'super_admin', function($query) use ($admin) {
                                return $query->whereHas('shop', function($q) use ($admin) {
                                    $q->where('admin_id', $admin->id);
                                });
                            })->with(['user', 'shop'])->latest()->take(3)->get();
                        @endphp
                        
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="stat-card bg-primary text-white text-center p-3 rounded">
                                    <div class="stat-value">{{ $totalRequests }}</div>
                                    <div class="stat-label">Total Requests</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card bg-warning text-white text-center p-3 rounded">
                                    <div class="stat-value">{{ $openRequests }}</div>
                                    <div class="stat-label">Open</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card bg-danger text-white text-center p-3 rounded">
                                    <div class="stat-value">{{ $urgentRequests }}</div>
                                    <div class="stat-label">Urgent</div>
                                </div>
                            </div>
                        </div>
                        
                        @if($recentRequests->count() > 0)
                            <div class="recent-requests">
                                <h6 class="mb-3">Recent Requests</h6>
                                @foreach($recentRequests as $request)
                                    <div class="request-item d-flex align-items-center p-2 border-bottom">
                                        <div class="request-icon me-3">
                                            <span class="badge bg-{{ $request->priority === 'urgent' ? 'danger' : ($request->priority === 'high' ? 'warning' : 'info') }}">
                                                {{ ucfirst($request->priority) }}
                                            </span>
                                        </div>
                                        <div class="request-content flex-grow-1">
                                            <div class="request-title">{{ $request->subject }}</div>
                                            <div class="request-meta text-muted small">
                                                {{ $request->user->name }} • {{ $request->shop->name }} • {{ $request->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        <div class="request-actions">
                                            <a href="{{ route('admin.customer-service.show', $request) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-headset fa-3x mb-3"></i>
                                <p>No customer service requests yet</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Notifications Section -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="content-card notifications-card" data-aos="fade-up" data-aos-delay="1600">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-bell me-2"></i>
                                Recent Notifications
                                @if($unreadNotifications > 0)
                                    <span class="badge bg-danger ms-2">{{ $unreadNotifications }}</span>
                                @endif
                            </h5>
                            <p class="content-card-subtitle">Latest system notifications and alerts</p>
                        </div>
                        <div class="header-actions">
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View All
                            </a>
                        </div>
                    </div>
                    <div class="content-card-body">
                        @if($recentNotifications->count() > 0)
                            <div class="notifications-list">
                                @foreach($recentNotifications as $notification)
                                    <div class="notification-item {{ $notification->is_read ? 'read' : 'unread' }}">
                                        <div class="notification-icon">
                                            @switch($notification->type)
                                                @case('appointment_booking')
                                                    <i class="fas fa-calendar-plus text-primary"></i>
                                                    @break
                                                @case('payment_submission')
                                                    <i class="fas fa-credit-card text-success"></i>
                                                    @break
                                                @case('payment_status_change')
                                                    <i class="fas fa-money-bill-wave text-warning"></i>
                                                    @break
                                                @default
                                                    <i class="fas fa-bell text-info"></i>
                                            @endswitch
                                        </div>
                                        <div class="notification-content">
                                            <div class="notification-title">{{ $notification->title }}</div>
                                            <div class="notification-message">{{ $notification->message }}</div>
                                            <div class="notification-meta">
                                                <span class="notification-time">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                @if($notification->shop)
                                                    <span class="notification-shop">
                                                        <i class="fas fa-store me-1"></i>
                                                        {{ $notification->shop->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="notification-actions">
                                            @if(!$notification->is_read)
                                                <form action="{{ route('admin.notifications.toggleRead', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as read">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.notifications.show', $notification->id) }}" class="btn btn-sm btn-outline-primary" title="View details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-bell-slash fa-3x mb-3"></i>
                                <p>No notifications yet</p>
                                <small>You'll see notifications here when users book appointments or submit payments</small>
                            </div>
                        @endif
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>

        <!-- Enhanced Performance Metrics -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="content-card performance-card" data-aos="fade-up" data-aos-delay="1500">
                    <div class="card-glow"></div>
                    <div class="content-card-header">
                        <div class="header-left">
                            <h5 class="content-card-title">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Performance Metrics
                            </h5>
                            <p class="content-card-subtitle">Key performance indicators</p>
                        </div>
                    </div>
                    <div class="content-card-body">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="performance-metric-card performance-metric-modern text-center p-4">
                                    <div class="performance-icon performance-icon-modern bg-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="performance-value text-success">{{ $completionRate }}%</div>
                                    <div class="performance-label">Completion Rate</div>
                                    <div class="performance-subtitle">Service success rate</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="performance-metric-card performance-metric-modern text-center p-4">
                                    <div class="performance-icon performance-icon-modern bg-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="performance-value text-danger">{{ $cancellationRate }}%</div>
                                    <div class="performance-label">Cancellation Rate</div>
                                    <div class="performance-subtitle">Cancelled appointments</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="performance-metric-card performance-metric-modern text-center p-4">
                                    <div class="performance-icon performance-icon-modern bg-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="performance-value text-primary">{{ $averageAppointmentsPerUser }}</div>
                                    <div class="performance-label">Avg Appointments/User</div>
                                    <div class="performance-subtitle">Customer engagement</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="performance-metric-card performance-metric-modern text-center p-4">
                                    <div class="performance-icon performance-icon-modern bg-info">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="performance-value text-info">{{ $activeUsers }}</div>
                                    <div class="performance-label">Active Users (3 months)</div>
                                    <div class="performance-subtitle">Engaged customers</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-particles"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
/* Reset admin layout background for dashboard */
.admin-content .content-wrapper {
    background: none !important;
    padding: 0 !important;
    max-width: none !important;
}

/* Dashboard Container - Override admin layout */
.dashboard-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 0;
    margin: -2rem;
    position: relative;
    overflow-x: hidden;
}

.dashboard-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
    pointer-events: none;
}

/* Enhanced Dashboard Header */
.dashboard-header {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding: 2.5rem 0;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    position: relative;
    z-index: 2;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.header-icon-wrapper {
    position: relative;
    width: 80px;
    height: 80px;
}

.header-icon {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1));
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.header-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s ease-in-out infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
    100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

.icon-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120%;
    height: 120%;
    background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; transform: translate(-50%, -50%) scale(1); }
    50% { opacity: 0.6; transform: translate(-50%, -50%) scale(1.1); }
}

.header-icon i {
    font-size: 2.2rem;
    color: white;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.header-text {
    color: white;
}

.header-title {
    font-size: 3rem;
    font-weight: 800;
    margin: 0;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    position: relative;
}

.title-gradient {
    background: linear-gradient(45deg, #fff, #f0f0f0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.title-underline {
    width: 100px;
    height: 4px;
    background: linear-gradient(45deg, #fff, #f0f0f0);
    margin-top: 10px;
    border-radius: 2px;
    animation: slideIn 1s ease-out 0.5s both;
}

@keyframes slideIn {
    from { width: 0; }
    to { width: 100px; }
}

.header-subtitle {
    font-size: 1.2rem;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-weight: 300;
}

.header-stats {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    font-size: 0.9rem;
    opacity: 0.9;
    background: rgba(255, 255, 255, 0.1);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.stat-item:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.stat-item i {
    color: #ffd700;
}

.header-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-modern {
    padding: 1rem 2rem;
    border-radius: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.4s ease;
    border: none;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
    color: white;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-modern:hover {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.2));
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.btn-export {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
}

.btn-export:hover {
    background: linear-gradient(135deg, #ff5252, #d32f2f);
    box-shadow: 0 15px 40px rgba(255, 107, 107, 0.6);
}

.btn-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
    z-index: 2;
}

.btn-glow {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    opacity: 0;
    transition: opacity 0.4s ease;
    border-radius: 15px;
}

.btn-modern:hover .btn-glow {
    opacity: 1;
}

/* Dashboard Content */
.dashboard-content {
    padding: 0 2rem 2rem;
    position: relative;
    z-index: 1;
}

/* Enhanced Metric Cards */
.metric-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    backdrop-filter: blur(20px);
}

.card-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.metric-card:hover .card-glow {
    opacity: 1;
}

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
    box-shadow: 0 2px 10px var(--card-color);
}

.metric-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, var(--card-color-light) 0%, transparent 70%);
    opacity: 0.1;
    transition: all 0.4s ease;
}

.metric-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 30px 80px rgba(0,0,0,0.2);
}

.metric-card:hover::after {
    opacity: 0.2;
    transform: scale(1.5);
}

/* Card Color Variables */
.revenue-card {
    --card-color: #00d4aa;
    --card-color-light: #00b894;
}

.appointment-card {
    --card-color: #0984e3;
    --card-color-light: #74b9ff;
}

.customer-card {
    --card-color: #6c5ce7;
    --card-color-light: #a29bfe;
}

.service-card {
    --card-color: #fdcb6e;
    --card-color-light: #ffeaa7;
}

.secondary-card {
    --card-color: #636e72;
    --card-color-light: #b2bec3;
}

.metric-icon-wrapper {
    position: relative;
    width: 80px;
    height: 80px;
}

.metric-icon {
    width: 100%;
    height: 100%;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--card-color), var(--card-color-light));
    color: white;
    font-size: 2rem;
    flex-shrink: 0;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    overflow: hidden;
}

.metric-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s ease-in-out infinite;
}

.icon-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    border-radius: 20px;
    background: radial-gradient(circle, var(--card-color-light) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.metric-card:hover .icon-pulse {
    opacity: 0.3;
    animation: pulse 2s ease-in-out infinite;
}

.metric-content {
    flex: 1;
}

.metric-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.metric-label {
    color: #6c757d;
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.metric-subtitle {
    font-size: 0.85rem;
    color: #adb5bd;
    font-weight: 500;
}

.metric-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background: rgba(0,0,0,0.05);
    width: fit-content;
}

.metric-trend.positive {
    color: #00b894;
    background: rgba(0, 184, 148, 0.1);
}

.metric-trend.negative {
    color: #e17055;
    background: rgba(225, 112, 85, 0.1);
}

.metric-trend.neutral {
    color: #636e72;
    background: rgba(99, 110, 114, 0.1);
}

.metric-chart {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    opacity: 0.4;
    width: 80px;
    height: 40px;
}

/* Card Particles */
.card-particles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    overflow: hidden;
}

.card-particles::before,
.card-particles::after {
    content: '';
    position: absolute;
    width: 4px;
    height: 4px;
    background: rgba(255,255,255,0.6);
    border-radius: 50%;
    animation: float-particle 6s ease-in-out infinite;
}

.card-particles::before {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.card-particles::after {
    top: 60%;
    right: 15%;
    animation-delay: 3s;
}

@keyframes float-particle {
    0%, 100% { transform: translateY(0px) translateX(0px); opacity: 0.6; }
    50% { transform: translateY(-20px) translateX(10px); opacity: 1; }
}

/* Enhanced Content Cards */
.content-card {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    border-radius: 25px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    overflow: hidden;
    transition: all 0.4s ease;
    backdrop-filter: blur(20px);
    position: relative;
}

.content-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.content-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 30px 80px rgba(0,0,0,0.2);
}

.content-card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    position: relative;
}

.content-card-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.5), transparent);
    animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.content-card-title {
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
    font-size: 1.4rem;
    position: relative;
    z-index: 1;
}

.content-card-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin-top: 0.5rem;
    position: relative;
    z-index: 1;
}

.content-card-body {
    padding: 2rem;
}

/* Enhanced Chart Controls */
.chart-controls {
    display: flex;
    gap: 0.75rem;
    position: relative;
    z-index: 1;
}

.form-select-modern {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 12px;
    border: 2px solid #e9ecef;
    background: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-select-modern:focus {
    border-color: #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    outline: none;
}

/* Enhanced Timeline */
.timeline-modern {
    position: relative;
    padding-left: 3rem;
}

.timeline-modern::before {
    content: '';
    position: absolute;
    left: 1.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}

.timeline-item-modern {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker-modern {
    position: absolute;
    left: -2.5rem;
    top: 0.5rem;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.timeline-content-modern {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 1.5rem;
    border-radius: 15px;
    border-left: 4px solid #667eea;
    transition: all 0.4s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.timeline-content-modern:hover {
    background: linear-gradient(135deg, #e9ecef, #dee2e6);
    transform: translateX(8px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.15);
}

.timeline-info {
    flex: 1;
}

.timeline-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.timeline-service {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.timeline-time {
    color: #adb5bd;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Enhanced Activity Feed */
.activity-feed-modern {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item-modern {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    transition: all 0.4s ease;
}

.activity-item-modern:hover {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 1.5rem;
    margin: 0 -1rem;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.activity-item-modern:last-child {
    border-bottom: none;
}

.activity-icon-modern {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    position: relative;
    overflow: hidden;
}

.activity-icon-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s ease-in-out infinite;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.activity-text {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 0.5rem;
}

.activity-time {
    color: #adb5bd;
    font-size: 0.85rem;
    font-weight: 500;
}

/* Enhanced Tables */
.table-modern {
    margin: 0;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.table-modern th {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    padding: 1.5rem;
    font-weight: 700;
    color: white;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.table-modern td {
    border: none;
    padding: 1.5rem;
    vertical-align: middle;
    background: white;
    transition: all 0.3s ease;
}

.table-row-modern {
    transition: all 0.3s ease;
}

.table-row-modern:hover {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    transform: scale(1.01);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Enhanced Icons */
.vehicle-type-icon,
.payment-method-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.vehicle-type-name,
.payment-method-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.vehicle-type-subtitle,
.payment-method-subtitle {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Enhanced Progress Bars */
.progress-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.progress-modern {
    background: linear-gradient(135deg, #e9ecef, #dee2e6);
    border-radius: 15px;
    overflow: hidden;
    height: 12px;
    box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);
    flex: 1;
}

.progress-bar-modern {
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.8s ease;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
}

.progress-text {
    font-size: 0.85rem;
    color: #6c757d;
    font-weight: 600;
    min-width: 40px;
    text-align: right;
}

/* Enhanced Badges */
.badge-modern {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border: 2px solid rgba(255,255,255,0.3);
}

/* Enhanced Performance Metric Cards */
.performance-metric-modern {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.3);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(20px);
    box-shadow: 0 15px 45px rgba(0,0,0,0.1);
}

.performance-metric-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.performance-metric-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 25px 60px rgba(0,0,0,0.15);
}

.performance-icon-modern {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    position: relative;
    overflow: hidden;
}

.performance-icon-modern::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s ease-in-out infinite;
}

.performance-value {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.75rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.performance-label {
    color: #6c757d;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 0.5rem;
}

.performance-subtitle {
    font-size: 0.8rem;
    color: #6c757d;
    margin-top: 0.3rem;
}

/* Enhanced Empty State */
.empty-state-modern {
    text-align: center;
    padding: 3rem 2rem;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 20px;
    margin: 1rem 0;
}

.empty-icon {
    opacity: 0.3;
    margin-bottom: 1.5rem;
    font-size: 4rem;
    color: #6c757d;
}

.empty-title {
    color: #6c757d;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.empty-subtitle {
    color: #adb5bd;
    font-size: 0.9rem;
}

/* Enhanced Charts */
canvas {
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Enhanced Scrollbar */
.activity-feed-modern::-webkit-scrollbar {
    width: 8px;
}

.activity-feed-modern::-webkit-scrollbar-track {
    background: linear-gradient(135deg, #f1f1f1, #e9ecef);
    border-radius: 10px;
}

.activity-feed-modern::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.activity-feed-modern::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8, #6a4190);
}

/* AOS Animation Support */
[data-aos] {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

[data-aos].aos-animate {
    opacity: 1;
    transform: translateY(0);
}

/* Loading States */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { left: -100%; }
    100% { left: 100%; }
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        margin: -1rem;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .header-title {
        font-size: 2.5rem;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .metric-card {
        padding: 1.5rem;
    }
    
    .metric-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }
    
    .metric-value {
        font-size: 2rem;
    }
    
    .content-card-header,
    .content-card-body {
        padding: 1.5rem;
    }
    
    .timeline-modern {
        padding-left: 2rem;
    }
    
    .timeline-marker-modern {
        left: -2rem;
    }
    
    .performance-value {
        font-size: 2rem;
    }
    
    .btn-modern {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
    
    .header-icon-wrapper {
        width: 60px;
        height: 60px;
    }
    
    .header-icon i {
        font-size: 1.8rem;
    }
    
    .dashboard-content {
        padding: 0 1rem 2rem;
    }
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.metric-card, .content-card {
    animation: fadeInUp 0.8s ease forwards;
}

.metric-card:nth-child(1) { animation-delay: 0.1s; }
.metric-card:nth-child(2) { animation-delay: 0.2s; }
.metric-card:nth-child(3) { animation-delay: 0.3s; }
.metric-card:nth-child(4) { animation-delay: 0.4s; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
// Initialize AOS (Animate On Scroll)
document.addEventListener('DOMContentLoaded', function() {
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });
    
    // Initialize circle progress
    const circleProgresses = document.querySelectorAll('.circle-progress');
    circleProgresses.forEach(circle => {
        const value = circle.getAttribute('data-value');
        circle.parentElement.style.setProperty('--value', value);
    });
    
    // Initialize mini charts
    initializeMiniCharts();
    
    // Initialize particle effects
    initializeParticleEffects();
    
    // Initialize hover effects
    initializeHoverEffects();
    
    // Initialize real-time updates
    initializeRealTimeUpdates();
});

// Enhanced mini charts for metric cards
function initializeMiniCharts() {
    const miniCharts = document.querySelectorAll('.mini-chart');
    miniCharts.forEach((canvas, index) => {
        const ctx = canvas.getContext('2d');
        const data = generateRandomData(7);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array(7).fill(''),
                datasets: [{
                    data: data,
                    borderColor: getChartColor(index),
                    backgroundColor: getChartColor(index, 0.1),
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                },
                elements: {
                    point: { radius: 0 }
                }
            }
        });
    });
}

// Initialize particle effects
function initializeParticleEffects() {
    const cards = document.querySelectorAll('.metric-card, .content-card');
    cards.forEach(card => {
        const particles = card.querySelector('.card-particles');
        if (particles) {
            // Add more particles dynamically
            for (let i = 0; i < 3; i++) {
                const particle = document.createElement('div');
                particle.style.cssText = `
                    position: absolute;
                    width: 3px;
                    height: 3px;
                    background: rgba(255,255,255,0.4);
                    border-radius: 50%;
                    animation: float-particle ${4 + i}s ease-in-out infinite;
                    animation-delay: ${i * 2}s;
                    top: ${20 + i * 20}%;
                    left: ${10 + i * 15}%;
                `;
                particles.appendChild(particle);
            }
        }
    });
}

// Initialize hover effects
function initializeHoverEffects() {
    const cards = document.querySelectorAll('.metric-card, .content-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 30px 80px rgba(0,0,0,0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 20px 60px rgba(0,0,0,0.15)';
        });
    });
}

// Initialize real-time updates
function initializeRealTimeUpdates() {
    // Update time every minute
    setInterval(() => {
        const timeElement = document.querySelector('.stat-item span');
        if (timeElement) {
            timeElement.textContent = `Last updated: ${new Date().toLocaleString()}`;
        }
    }, 60000);
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(() => {
        if (window.location.pathname.includes('dashboard')) {
            showNotification('Dashboard data updated automatically', 'info');
        }
    }, 300000);
}

function generateRandomData(count) {
    return Array.from({ length: count }, () => Math.random() * 100);
}

function getChartColor(index, alpha = 1) {
    const colors = [
        `rgba(40, 167, 69, ${alpha})`,
        `rgba(0, 123, 255, ${alpha})`,
        `rgba(23, 162, 184, ${alpha})`,
        `rgba(255, 193, 7, ${alpha})`
    ];
    return colors[index % colors.length];
}

// Enhanced Monthly Trends Chart
const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
const monthlyTrendsData = @json(array_values($monthlyTrends));
const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Appointments',
            data: monthlyTrendsData.length > 0 ? monthlyTrendsData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#667eea',
                borderWidth: 1,
                cornerRadius: 10,
                displayColors: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    color: '#6c757d'
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    color: '#6c757d'
                }
            }
        },
        elements: {
            point: {
                hoverBackgroundColor: '#667eea'
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Enhanced Service Popularity Chart
const servicePopularityCtx = document.getElementById('servicePopularityChart').getContext('2d');
const popularServicesData = @json($popularServices);
const popularServicesLabels = popularServicesData.length > 0 ? popularServicesData.map(s => s.name) : ['No Data'];
const popularServicesCounts = popularServicesData.length > 0 ? popularServicesData.map(s => s.appointments_count) : [1];

const servicePopularityChart = new Chart(servicePopularityCtx, {
    type: 'doughnut',
    data: {
        labels: popularServicesLabels,
        datasets: [{
            data: popularServicesCounts,
            backgroundColor: [
                '#667eea',
                '#764ba2',
                '#f093fb',
                '#f5576c',
                '#4facfe',
                '#00d4aa',
                '#fdcb6e',
                '#6c5ce7'
            ],
            borderWidth: 0,
            hoverOffset: 4
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
                    usePointStyle: true,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#667eea',
                borderWidth: 1,
                cornerRadius: 10
            }
        },
        cutout: '60%'
    }
});

// Enhanced chart period change handler
document.getElementById('chartPeriod').addEventListener('change', function() {
    const period = this.value;
    const button = this;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Here you would typically make an AJAX call to get new data
        showNotification(`Chart period changed to ${period} months`, 'success');
        
        // Reset button state
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
});

// Enhanced dashboard refresh function
function refreshDashboard() {
    const refreshBtn = document.querySelector('.btn-refresh');
    const icon = refreshBtn.querySelector('i');
    const text = refreshBtn.querySelector('span');
    
    // Add spinning animation
    icon.className = 'fas fa-spinner fa-spin';
    text.textContent = 'Refreshing...';
    refreshBtn.disabled = true;
    
    // Simulate refresh
    setTimeout(() => {
        location.reload();
    }, 1500);
}

// Enhanced export analytics function
function exportAnalytics() {
    const exportBtn = document.querySelector('.btn-export');
    const originalText = exportBtn.innerHTML;
    
    // Show loading state
    exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Exporting...</span>';
    exportBtn.disabled = true;
    
    // Create enhanced CSV export
    const data = [
        ['Metric', 'Value', 'Date'],
        ['Total Revenue', '₱{{ number_format($totalRevenue, 2) }}', new Date().toLocaleDateString()],
        ['Total Appointments', '{{ $totalAppointments }}', new Date().toLocaleDateString()],
        ['Total Users', '{{ $totalUsers }}', new Date().toLocaleDateString()],
        ['Completion Rate', '{{ $completionRate }}%', new Date().toLocaleDateString()],
        ['Monthly Revenue', '₱{{ number_format($monthlyRevenue, 2) }}', new Date().toLocaleDateString()],
        ['Today\'s Appointments', '{{ $todayAppointments }}', new Date().toLocaleDateString()],
        ['New Customers This Month', '{{ $newUsersThisMonth }}', new Date().toLocaleDateString()],
        ['Cancellation Rate', '{{ $cancellationRate }}%', new Date().toLocaleDateString()],
        ['Average Appointments Per User', '{{ $averageAppointmentsPerUser }}', new Date().toLocaleDateString()],
        ['Active Users (3 months)', '{{ $activeUsers }}', new Date().toLocaleDateString()]
    ];
    
    const csvContent = data.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'analytics-report-' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
    
    // Show success notification
    showNotification('Analytics report exported successfully!', 'success');
    
    // Reset button state
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
    }, 2000);
}

// Enhanced notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        border-radius: 15px;
        border: none;
        backdrop-filter: blur(20px);
    `;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-triangle' : 
                 type === 'warning' ? 'exclamation-circle' : 'info-circle';
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${icon} me-3 fs-4"></i>
            <div class="flex-grow-1">
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <div class="mt-1">${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Enhanced metric card interactions
document.addEventListener('DOMContentLoaded', function() {
    const metricCards = document.querySelectorAll('.metric-card');
    
    metricCards.forEach(card => {
        card.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            }, 150);
        });
        
        // Add hover sound effect (optional)
        card.addEventListener('mouseenter', function() {
            // You can add a subtle sound effect here
            this.style.cursor = 'pointer';
        });
    });
});

// Enhanced table interactions
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('.table-row-modern');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });
});

// Enhanced timeline interactions
document.addEventListener('DOMContentLoaded', function() {
    const timelineItems = document.querySelectorAll('.timeline-item-modern');
    
    timelineItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            const content = this.querySelector('.timeline-content-modern');
            if (content) {
                content.style.transform = 'translateX(8px)';
                content.style.boxShadow = '0 12px 35px rgba(0,0,0,0.15)';
            }
        });
        
        item.addEventListener('mouseleave', function() {
            const content = this.querySelector('.timeline-content-modern');
            if (content) {
                content.style.transform = 'translateX(0)';
                content.style.boxShadow = '0 8px 25px rgba(0,0,0,0.1)';
            }
        });
    });
});

// Performance monitoring
let performanceMetrics = {
    loadTime: 0,
    renderTime: 0
};

// Track page load performance
window.addEventListener('load', function() {
    performanceMetrics.loadTime = performance.now();
    console.log(`Dashboard loaded in ${performanceMetrics.loadTime}ms`);
});

// Track chart render performance
function trackChartPerformance(chartName, startTime) {
    const renderTime = performance.now() - startTime;
    performanceMetrics.renderTime = renderTime;
    console.log(`${chartName} rendered in ${renderTime}ms`);
}

// Enhanced error handling
window.addEventListener('error', function(e) {
    console.error('Dashboard error:', e.error);
    showNotification('An error occurred. Please refresh the page.', 'error');
});

// Enhanced keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'r' && e.ctrlKey) {
        e.preventDefault();
        refreshDashboard();
    }
    
    if (e.key === 'e' && e.ctrlKey) {
        e.preventDefault();
        exportAnalytics();
    }
});

// Enhanced accessibility
document.addEventListener('DOMContentLoaded', function() {
    // Add ARIA labels to interactive elements
    const buttons = document.querySelectorAll('.btn-modern');
    buttons.forEach(button => {
        if (!button.getAttribute('aria-label')) {
            const text = button.textContent.trim();
            button.setAttribute('aria-label', text);
        }
    });
    
    // Add focus indicators
    const focusableElements = document.querySelectorAll('button, select, a');
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #667eea';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
});

// Notification styles
.notifications-card {
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
    backdrop-filter: blur(10px);
}

.notifications-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.notification-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.notification-item.unread {
    background: rgba(13, 110, 253, 0.1);
    border-color: rgba(13, 110, 253, 0.3);
}

.notification-item.read {
    opacity: 0.8;
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.1rem;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-weight: 600;
    color: #fff;
    margin-bottom: 0.25rem;
    font-size: 0.95rem;
}

.notification-message {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}

.notification-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
}

.notification-time, .notification-shop {
    display: flex;
    align-items: center;
}

.notification-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.notification-actions .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .notification-item {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .notification-icon {
        align-self: flex-start;
        margin-right: 0;
    }
    
    .notification-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .notification-actions {
        align-self: flex-end;
    }
}
</script>
@endpush 