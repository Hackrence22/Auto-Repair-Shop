<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsApiController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get dashboard analytics
     */
    public function dashboard(): JsonResponse
    {
        $analytics = $this->analyticsService->getDashboardAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get appointment analytics
     */
    public function appointments(): JsonResponse
    {
        $analytics = $this->analyticsService->getAppointmentAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request): JsonResponse
    {
        $period = $request->get('period', 'monthly');
        $analytics = $this->analyticsService->getRevenueAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
            'period' => $period
        ]);
    }

    /**
     * Get customer analytics
     */
    public function customers(): JsonResponse
    {
        $analytics = $this->analyticsService->getCustomerAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get service analytics
     */
    public function services(): JsonResponse
    {
        $analytics = $this->analyticsService->getServiceAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get performance metrics
     */
    public function performance(): JsonResponse
    {
        $metrics = $this->analyticsService->getPerformanceMetrics();
        
        return response()->json([
            'success' => true,
            'data' => $metrics
        ]);
    }

    /**
     * Get vehicle type analytics
     */
    public function vehicleTypes(): JsonResponse
    {
        $analytics = $this->analyticsService->getVehicleTypeAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get payment method analytics
     */
    public function paymentMethods(): JsonResponse
    {
        $analytics = $this->analyticsService->getPaymentMethodAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get time-based analytics
     */
    public function timeBased(): JsonResponse
    {
        $analytics = $this->analyticsService->getTimeBasedAnalytics();
        
        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * Get comparison data
     */
    public function comparison(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $comparison = $this->analyticsService->getComparisonData($period);
        
        return response()->json([
            'success' => true,
            'data' => $comparison,
            'period' => $period
        ]);
    }

    /**
     * Get real-time updates
     */
    public function realTime(): JsonResponse
    {
        // Get current day's data
        $todayAppointments = \App\Models\Appointment::whereDate('appointment_date', now()->toDateString())->count();
        $todayRevenue = \App\Models\Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereDate('appointments.created_at', now()->toDateString())
            ->sum('services.price');
        $todayCustomers = \App\Models\User::whereDate('created_at', now()->toDateString())->count();

        return response()->json([
            'success' => true,
            'data' => [
                'today_appointments' => $todayAppointments,
                'today_revenue' => $todayRevenue,
                'today_customers' => $todayCustomers,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get custom date range analytics
     */
    public function customRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $appointments = \App\Models\Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count();
        $revenue = \App\Models\Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->sum('services.price');
        $customers = \App\Models\User::whereBetween('created_at', [$startDate, $endDate])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'appointments' => $appointments,
                'revenue' => $revenue,
                'customers' => $customers,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }
} 