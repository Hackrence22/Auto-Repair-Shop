<!-- Analytics Widget -->
<div class="analytics-widget">
    <div class="widget-header">
        <h6 class="widget-title">
            <i class="fas fa-chart-line me-2"></i>
            Quick Analytics
        </h6>
        <div class="widget-actions">
            <button class="btn btn-sm btn-outline-primary" onclick="refreshWidget()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
    
    <div class="widget-content">
        <div class="row g-2">
            <div class="col-6">
                <div class="metric-item">
                    <div class="metric-value text-success">{{ $totalRevenue ?? '₱0.00' }}</div>
                    <div class="metric-label">Total Revenue</div>
                </div>
            </div>
            <div class="col-6">
                <div class="metric-item">
                    <div class="metric-value text-primary">{{ $totalAppointments ?? 0 }}</div>
                    <div class="metric-label">Appointments</div>
                </div>
            </div>
            <div class="col-6">
                <div class="metric-item">
                    <div class="metric-value text-info">{{ $totalUsers ?? 0 }}</div>
                    <div class="metric-label">Customers</div>
                </div>
            </div>
            <div class="col-6">
                <div class="metric-item">
                    <div class="metric-value text-warning">{{ $completionRate ?? 0 }}%</div>
                    <div class="metric-label">Completion Rate</div>
                </div>
            </div>
        </div>
        
        <!-- Mini Chart -->
        <div class="widget-chart mt-3">
            <canvas id="miniChart" height="60"></canvas>
        </div>
        
        <!-- Quick Actions -->
        <div class="widget-actions mt-3">
            <a href="{{ route('admin.analytics.index') }}" class="btn btn-sm btn-primary w-100">
                <i class="fas fa-chart-bar me-2"></i>View Full Analytics
            </a>
        </div>
    </div>
</div>

<style>
.analytics-widget {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.widget-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: between;
    align-items: center;
}

.widget-title {
    margin: 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.9rem;
}

.widget-content {
    padding: 1rem;
}

.metric-item {
    text-align: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.metric-value {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.metric-label {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
}

.widget-chart {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 0.5rem;
}

.widget-actions {
    display: flex;
    gap: 0.5rem;
}
</style>

<script>
// Mini Chart
const miniCtx = document.getElementById('miniChart').getContext('2d');
const miniChart = new Chart(miniCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Appointments',
            data: [12, 19, 15, 25, 22, 30, 28],
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 2,
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
                display: false
            },
            x: {
                display: false
            }
        },
        elements: {
            point: {
                radius: 0
            }
        }
    }
});

function refreshWidget() {
    // Refresh widget data
    location.reload();
}
</script> 