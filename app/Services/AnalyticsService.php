<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\PaymentMethod;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics()
    {
        return [
            'appointments' => $this->getAppointmentAnalytics(),
            'revenue' => $this->getRevenueAnalytics(),
            'customers' => $this->getCustomerAnalytics(),
            'services' => $this->getServiceAnalytics(),
            'performance' => $this->getPerformanceMetrics(),
        ];
    }

    /**
     * Get appointment analytics
     */
    public function getAppointmentAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        $total = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->count();
        
        $today = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->whereDate('appointment_date', now()->toDateString())->count();
        
        $thisWeek = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])->count();
        
        $thisMonth = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->whereMonth('appointment_date', now()->month)->count();

        $statusBreakdown = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $monthlyTrends = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->selectRaw('MONTH(appointment_date) as month, COUNT(*) as count')
            ->whereYear('appointment_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($monthlyTrends[$i])) {
                $monthlyTrends[$i] = 0;
            }
        }
        ksort($monthlyTrends);

        return [
            'total' => $total,
            'today' => $today,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'status_breakdown' => $statusBreakdown,
            'monthly_trends' => array_values($monthlyTrends),
        ];
    }

    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        $totalRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->sum('services.price');

        $monthlyRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereMonth('appointments.created_at', now()->month)
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->sum('services.price');

        $weeklyRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->where('appointments.created_at', '>=', now()->subWeek())
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->sum('services.price');

        $dailyRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereDate('appointments.created_at', now()->toDateString())
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->sum('services.price');

        // Revenue trends for last 30 days
        $revenueTrends = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->where('appointments.created_at', '>=', now()->subDays(30))
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->selectRaw('DATE(appointments.created_at) as date, SUM(services.price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total' => $totalRevenue,
            'monthly' => $monthlyRevenue,
            'weekly' => $weeklyRevenue,
            'daily' => $dailyRevenue,
            'trends' => $revenueTrends,
        ];
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        $total = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->count();
        
        $newThisMonth = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->whereMonth('created_at', now()->month)->count();
        
        $newThisWeek = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->where('created_at', '>=', now()->subWeek())->count();
        
        $activeUsers = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->whereHas('appointments', function($query) use ($shopFilter) {
            $query->where('created_at', '>=', now()->subMonths(3));
            if ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            }
        })->count();

        // Customer growth trends (only customers who have appointments at this shop)
        $growthTrends = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top customers
        $topCustomers = User::withCount(['appointments' => function($query) {
            $query->where('status', 'completed');
        }])
        ->when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })
        ->orderBy('appointments_count', 'desc')
        ->limit(10)
        ->get();

        return [
            'total' => $total,
            'new_this_month' => $newThisMonth,
            'new_this_week' => $newThisWeek,
            'active' => $activeUsers,
            'growth_trends' => $growthTrends,
            'top_customers' => $topCustomers,
        ];
    }

    /**
     * Get service analytics
     */
    public function getServiceAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        $total = Service::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->count();
        
        $active = Service::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->where('is_active', true)->count();

        // Popular services
        $popularServices = Service::withCount(['appointments' => function($query) {
            $query->where('status', 'completed');
        }])
        ->when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })
        ->orderBy('appointments_count', 'desc')
        ->limit(5)
        ->get();

        // Service revenue
        $serviceRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->selectRaw('services.name, SUM(services.price) as revenue, COUNT(*) as count')
            ->groupBy('services.id', 'services.name')
            ->orderBy('revenue', 'desc')
            ->get();

        return [
            'total' => $total,
            'active' => $active,
            'popular' => $popularServices,
            'revenue' => $serviceRevenue,
        ];
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics()
    {
        $shopFilter = $this->getShopFilter();
        
        $totalAppointments = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->count();
        
        $completedAppointments = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->where('status', 'completed')->count();
        
        $cancelledAppointments = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->where('status', 'cancelled')->count();
        
        $totalUsers = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->count();

        $completionRate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0;
        $cancellationRate = $totalAppointments > 0 ? round(($cancelledAppointments / $totalAppointments) * 100, 2) : 0;
        $averageAppointmentsPerUser = $totalUsers > 0 ? round($totalAppointments / $totalUsers, 2) : 0;

        // Average appointment duration (if available)
        $averageDuration = Service::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->avg('duration') ?? 60; // Default 60 minutes

        return [
            'completion_rate' => $completionRate,
            'cancellation_rate' => $cancellationRate,
            'avg_appointments_per_user' => $averageAppointmentsPerUser,
            'avg_duration' => $averageDuration,
        ];
    }

    /**
     * Get vehicle type analytics
     */
    public function getVehicleTypeAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        return Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->selectRaw('vehicle_type, COUNT(*) as count')
            ->groupBy('vehicle_type')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Get payment method analytics
     */
    public function getPaymentMethodAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        return PaymentMethod::withCount(['appointments' => function($query) use ($shopFilter) {
            $query->where('status', 'completed');
            if ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            }
        }])
        ->when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('shop', function($q) use ($shopFilter) {
                $q->where('id', $shopFilter);
            });
        })
        ->orderBy('appointments_count', 'desc')
        ->get();
    }

    /**
     * Get time-based analytics
     */
    public function getTimeBasedAnalytics()
    {
        $shopFilter = $this->getShopFilter();
        
        // Peak hours
        $peakHours = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->selectRaw('HOUR(appointment_time) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Peak days
        $peakDays = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->selectRaw('DAYOFWEEK(appointment_date) as day, COUNT(*) as count')
            ->groupBy('day')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'peak_hours' => $peakHours,
            'peak_days' => $peakDays,
        ];
    }

    /**
     * Generate comparison data
     */
    public function getComparisonData($period = 'month')
    {
        $currentPeriod = $this->getPeriodData($period);
        $previousPeriod = $this->getPeriodData($period, true);

        return [
            'current' => $currentPeriod,
            'previous' => $previousPeriod,
            'growth' => $this->calculateGrowth($currentPeriod, $previousPeriod),
        ];
    }

    /**
     * Get data for specific period
     */
    private function getPeriodData($period, $previous = false)
    {
        $shopFilter = $this->getShopFilter();
        
        $date = $previous ? now()->subMonth() : now();
        
        switch ($period) {
            case 'week':
                $startDate = $previous ? $date->subWeek() : $date->startOfWeek();
                $endDate = $previous ? $date->endOfWeek() : $date->endOfWeek();
                break;
            case 'month':
            default:
                $startDate = $previous ? $date->startOfMonth() : $date->startOfMonth();
                $endDate = $previous ? $date->endOfMonth() : $date->endOfMonth();
                break;
        }

        $appointments = Appointment::when($shopFilter, function($query) use ($shopFilter) {
            $query->where('shop_id', $shopFilter);
        })->whereBetween('appointment_date', [$startDate, $endDate])->count();
        
        $revenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->sum('services.price');
        
        $customers = User::when($shopFilter, function($query) use ($shopFilter) {
            $query->whereHas('appointments', function($q) use ($shopFilter) {
                $q->where('shop_id', $shopFilter);
            });
        })->whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'appointments' => $appointments,
            'revenue' => $revenue,
            'customers' => $customers,
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($current, $previous)
    {
        $growth = [];
        
        foreach ($current as $key => $value) {
            $previousValue = $previous[$key] ?? 0;
            if ($previousValue > 0) {
                $growth[$key] = round((($value - $previousValue) / $previousValue) * 100, 2);
            } else {
                $growth[$key] = $value > 0 ? 100 : 0;
            }
        }

        return $growth;
    }

    /**
     * Get shop filter for current admin
     */
    private function getShopFilter()
    {
        $admin = auth('admin')->user();
        if ($admin && $admin->isOwner()) {
            // Get the shop ID for this owner
            $shop = \App\Models\Shop::where('admin_id', $admin->id)->first();
            return $shop ? $shop->id : null;
        }
        return null;
    }
} 