/**
 * Analytics Utility Functions
 * Handles analytics data fetching, chart updates, and real-time updates
 */

class AnalyticsManager {
    constructor() {
        this.charts = {};
        this.updateInterval = null;
        this.isInitialized = false;
    }

    /**
     * Initialize analytics
     */
    init() {
        if (this.isInitialized) return;
        
        this.setupEventListeners();
        this.startRealTimeUpdates();
        this.isInitialized = true;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Period selector changes
        document.querySelectorAll('[data-analytics-period]').forEach(element => {
            element.addEventListener('change', (e) => {
                this.updateCharts(e.target.value);
            });
        });

        // Refresh buttons
        document.querySelectorAll('[data-analytics-refresh]').forEach(element => {
            element.addEventListener('click', () => {
                this.refreshData();
            });
        });

        // Export buttons
        document.querySelectorAll('[data-analytics-export]').forEach(element => {
            element.addEventListener('click', (e) => {
                const type = e.target.dataset.analyticsType || 'all';
                this.exportData(type);
            });
        });
    }

    /**
     * Fetch analytics data
     */
    async fetchData(endpoint, params = {}) {
        try {
            const url = new URL(`/admin/api/analytics/${endpoint}`, window.location.origin);
            Object.keys(params).forEach(key => {
                url.searchParams.append(key, params[key]);
            });

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Error fetching analytics data:', error);
            return null;
        }
    }

    /**
     * Update charts with new data
     */
    async updateCharts(period = 'monthly') {
        const data = await this.fetchData('dashboard', { period });
        
        if (!data || !data.success) {
            console.error('Failed to fetch analytics data');
            return;
        }

        this.updateRevenueChart(data.data.revenue);
        this.updateAppointmentChart(data.data.appointments);
        this.updateServiceChart(data.data.services);
        this.updateMetrics(data.data);
    }

    /**
     * Update revenue chart
     */
    updateRevenueChart(revenueData) {
        const chartId = 'revenueChart';
        if (!this.charts[chartId]) return;

        const chart = this.charts[chartId];
        
        if (revenueData.trends && revenueData.trends.length > 0) {
            chart.data.labels = revenueData.trends.map(item => 
                new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            );
            chart.data.datasets[0].data = revenueData.trends.map(item => item.revenue);
        }
        
        chart.update();
    }

    /**
     * Update appointment chart
     */
    updateAppointmentChart(appointmentData) {
        const chartId = 'appointmentChart';
        if (!this.charts[chartId]) return;

        const chart = this.charts[chartId];
        
        if (appointmentData.monthly_trends) {
            chart.data.datasets[0].data = appointmentData.monthly_trends;
        }
        
        chart.update();
    }

    /**
     * Update service chart
     */
    updateServiceChart(serviceData) {
        const chartId = 'serviceChart';
        if (!this.charts[chartId]) return;

        const chart = this.charts[chartId];
        
        if (serviceData.popular) {
            chart.data.labels = serviceData.popular.map(service => service.name);
            chart.data.datasets[0].data = serviceData.popular.map(service => service.appointments_count);
        }
        
        chart.update();
    }

    /**
     * Update metrics display
     */
    updateMetrics(data) {
        // Update metric cards
        this.updateMetricCard('total-revenue', data.revenue.total);
        this.updateMetricCard('total-appointments', data.appointments.total);
        this.updateMetricCard('total-customers', data.customers.total);
        this.updateMetricCard('completion-rate', data.performance.completion_rate + '%');
    }

    /**
     * Update individual metric card
     */
    updateMetricCard(metricId, value) {
        const element = document.querySelector(`[data-metric="${metricId}"]`);
        if (element) {
            element.textContent = this.formatValue(metricId, value);
        }
    }

    /**
     * Format value based on metric type
     */
    formatValue(metricId, value) {
        if (metricId.includes('revenue')) {
            return new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            }).format(value);
        }
        
        if (metricId.includes('rate')) {
            return value + '%';
        }
        
        return new Intl.NumberFormat().format(value);
    }

    /**
     * Register a chart
     */
    registerChart(chartId, chart) {
        this.charts[chartId] = chart;
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        this.updateInterval = setInterval(async () => {
            const data = await this.fetchData('real-time');
            if (data && data.success) {
                this.updateRealTimeMetrics(data.data);
            }
        }, 30000); // Update every 30 seconds
    }

    /**
     * Update real-time metrics
     */
    updateRealTimeMetrics(data) {
        this.updateMetricCard('today-appointments', data.today_appointments);
        this.updateMetricCard('today-revenue', data.today_revenue);
        this.updateMetricCard('today-customers', data.today_customers);
    }

    /**
     * Refresh all data
     */
    async refreshData() {
        await this.updateCharts();
        this.showNotification('Data refreshed successfully', 'success');
    }

    /**
     * Export analytics data
     */
    exportData(type = 'all') {
        const url = `/admin/analytics/export?type=${type}&format=csv`;
        window.location.href = url;
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Destroy analytics manager
     */
    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        
        this.charts = {};
        this.isInitialized = false;
    }
}

// Global analytics instance
window.analyticsManager = new AnalyticsManager();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.analyticsManager.init();
});

// Export utility functions
window.AnalyticsUtils = {
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(amount);
    },
    
    formatNumber: (number) => {
        return new Intl.NumberFormat().format(number);
    },
    
    formatPercentage: (value) => {
        return value + '%';
    },
    
    formatDate: (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    
    getChartColors: () => {
        return [
            '#667eea',
            '#764ba2',
            '#f093fb',
            '#f5576c',
            '#4facfe',
            '#00f2fe',
            '#43e97b',
            '#38f9d7'
        ];
    }
}; 