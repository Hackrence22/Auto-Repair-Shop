<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\PaymentMethod;
use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Shop;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $isOwner = auth('admin')->user()?->isOwner();
        $ownerAdminId = auth('admin')->id();
        $ownerShopIds = collect();
        if ($isOwner) {
            $ownerShopIds = Shop::where('admin_id', $ownerAdminId)->pluck('id');
        }
        
        // Get notification data
        $unreadNotifications = $this->notificationService->getUnreadCount($ownerAdminId);
        $recentNotifications = $this->notificationService->getRecentNotifications($ownerAdminId, 5);
        
        // Basic appointment statistics
        $totalAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->count();
        $pendingAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('status', 'pending')->count();
        $confirmedAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('status', 'confirmed')->count();
        $completedAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('status', 'cancelled')->count();
        $todayAppointments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->whereDate('appointment_date', now()->toDateString())->count();
        
        // Payment statistics
        $pendingPayments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('payment_status', 'unpaid')->where('payment_proof', '!=', null)->count();
        $confirmedPayments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('payment_status', 'paid')->count();
        $rejectedPayments = Appointment::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('payment_status', 'rejected')->count();
        
        // User statistics
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $activeUsers = User::whereHas('appointments', function($query) {
            $query->where('created_at', '>=', now()->subMonths(3));
        })->count();
        
        // Service statistics
        $totalServices = Service::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->count();
        $activeServices = Service::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })->where('is_active', true)->count();
        
        // Payment method statistics
        $totalPaymentMethods = PaymentMethod::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereHas('shop', function($s) use ($ownerShopIds) {
                $s->whereIn('id', $ownerShopIds);
            }); 
        })->count();
        $activePaymentMethods = PaymentMethod::when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereHas('shop', function($s) use ($ownerShopIds) {
                $s->whereIn('id', $ownerShopIds);
            }); 
        })->where('is_active', true)->count();
        
        // Feedback statistics
        // Owners should not see feedback metrics
        $totalFeedback = $isOwner ? 0 : Feedback::count();
        $recentFeedback = $isOwner ? 0 : Feedback::where('created_at', '>=', now()->subDays(30))->count();
        
        // Revenue analytics
        $totalRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('appointments.shop_id', $ownerShopIds); 
            })
            ->sum('services.price');
            
        $monthlyRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereMonth('appointments.created_at', now()->month)
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('appointments.shop_id', $ownerShopIds); 
            })
            ->sum('services.price');
            
        $weeklyRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->where('appointments.created_at', '>=', now()->subWeek())
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('appointments.shop_id', $ownerShopIds); 
            })
            ->sum('services.price');
        
        // Service popularity analytics
        $popularServices = Service::withCount(['appointments' => function($query) use ($isOwner, $ownerShopIds) {
            $query->where('status', 'completed');
            if ($isOwner) {
                $query->whereIn('shop_id', $ownerShopIds);
            }
        }])
        ->when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereIn('shop_id', $ownerShopIds); 
        })
        ->orderBy('appointments_count', 'desc')
        ->limit(5)
        ->get();
        
        // Monthly appointment trends
        $monthlyTrends = Appointment::selectRaw('MONTH(appointment_date) as month, COUNT(*) as count')
            ->whereYear('appointment_date', now()->year)
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('shop_id', $ownerShopIds); 
            })
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
        
        // Fill missing months with 0
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($monthlyTrends[$i])) {
                $monthlyTrends[$i] = 0;
            }
        }
        ksort($monthlyTrends);
        
        // Vehicle type analytics
        $vehicleTypeStats = Appointment::selectRaw('vehicle_type, COUNT(*) as count')
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('shop_id', $ownerShopIds); 
            })
            ->groupBy('vehicle_type')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        // Payment method usage
        $paymentMethodUsage = PaymentMethod::withCount(['appointments' => function($query) use ($isOwner, $ownerShopIds) {
            $query->where('status', 'completed');
            if ($isOwner) {
                $query->whereIn('shop_id', $ownerShopIds);
            }
        }])
        ->when($isOwner, function($q) use ($ownerShopIds) { 
            $q->whereHas('shop', function($s) use ($ownerShopIds) {
                $s->whereIn('id', $ownerShopIds);
            }); 
        })
        ->orderBy('appointments_count', 'desc')
        ->get();
        
        // Recent activity
        $recentAppointments = Appointment::with('user')
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('shop_id', $ownerShopIds); 
            })
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();
            
        $recentUsers = User::when($isOwner, function($q) use ($ownerShopIds) {
            $q->whereHas('appointments', function($appt) use ($ownerShopIds) {
                $appt->whereIn('shop_id', $ownerShopIds);
            });
        })->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentFeedback = $isOwner ? collect() : Feedback::orderBy('created_at', 'desc')->limit(5)->get();
        
        // Performance metrics
        $completionRate = $totalAppointments > 0 ? round(($completedAppointments / $totalAppointments) * 100, 2) : 0;
        $cancellationRate = $totalAppointments > 0 ? round(($cancelledAppointments / $totalAppointments) * 100, 2) : 0;
        $averageAppointmentsPerUser = $totalUsers > 0 ? round($totalAppointments / $totalUsers, 2) : 0;
        
        // Today's schedule
        $todaySchedule = Appointment::with(['user', 'service'])
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('shop_id', $ownerShopIds); 
            })
            ->whereDate('appointment_date', now()->toDateString())
            ->orderBy('appointment_time')
            ->get();
        
        // Upcoming appointments
        $upcomingAppointments = Appointment::with(['user', 'service'])
            ->when($isOwner, function($q) use ($ownerShopIds) { 
                $q->whereIn('shop_id', $ownerShopIds); 
            })
            ->whereBetween('appointment_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();
        
        return view('admin.dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'confirmedAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'todayAppointments',
            'pendingPayments',
            'confirmedPayments',
            'rejectedPayments',
            'totalUsers',
            'newUsersThisMonth',
            'activeUsers',
            'totalServices',
            'activeServices',
            'totalPaymentMethods',
            'activePaymentMethods',
            'totalFeedback',
            'recentFeedback',
            'totalRevenue',
            'monthlyRevenue',
            'weeklyRevenue',
            'popularServices',
            'monthlyTrends',
            'vehicleTypeStats',
            'paymentMethodUsage',
            'recentAppointments',
            'recentUsers',
            'recentFeedback',
            'completionRate',
            'cancellationRate',
            'averageAppointmentsPerUser',
            'todaySchedule',
            'upcomingAppointments',
            'unreadNotifications',
            'recentNotifications'
        ));
    }
} 