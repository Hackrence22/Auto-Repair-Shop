<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Service;
use App\Models\PaymentMethod;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.analytics.index');
    }

    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        // Filter by shop if owner is logged in
        $shopFilter = $this->getShopFilter();

        $revenueData = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->selectRaw('DATE(appointments.created_at) as date, SUM(services.price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $revenueData->sum('revenue');
        $averageRevenue = $revenueData->avg('revenue');

        // Owner-scoped totals: completed appointments count and distinct services used
        $totalAppointments = Appointment::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->count();

        $totalServices = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.created_at', [$startDate, $endDate])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->distinct('services.id')
            ->count('services.id');

        return view('admin.analytics.revenue', compact('revenueData', 'totalRevenue', 'averageRevenue', 'period', 'totalAppointments', 'totalServices'));
    }

    public function appointments(Request $request)
    {
        $period = $request->get('period', 'monthly');
        
        // Filter by shop if owner is logged in
        $shopFilter = $this->getShopFilter();
        
        // Appointment trends by status
        $appointmentTrends = Appointment::when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Appointment trends by date
        $dateTrends = Appointment::when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->whereBetween('appointment_date', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Service popularity
        $servicePopularity = Service::withCount(['appointments' => function($query) use ($shopFilter) {
                $query->where('status', 'completed');
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->orderBy('appointments_count', 'desc')
            ->get();

        return view('admin.analytics.appointments', compact('appointmentTrends', 'dateTrends', 'servicePopularity'));
    }

    public function customers(Request $request)
    {
        // Filter by shop if owner is logged in
        $shopFilter = $this->getShopFilter();
        
        // Customer growth (only customers who have appointments at this shop)
        $customerGrowth = User::when($shopFilter, function($query) use ($shopFilter) {
                $query->whereHas('appointments', function($q) use ($shopFilter) {
                    $q->where('shop_id', $shopFilter);
                });
            })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [now()->subMonths(6), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top customers by appointments (only for this shop)
        $topCustomers = User::withCount(['appointments' => function($query) use ($shopFilter) {
                $query->where('status', 'completed');
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->whereHas('appointments', function($q) use ($shopFilter) {
                    $q->where('shop_id', $shopFilter);
                });
            })
            ->orderBy('appointments_count', 'desc')
            ->limit(10)
            ->get();

        // Customer demographics (only for this shop)
        $customerDemographics = User::when($shopFilter, function($query) use ($shopFilter) {
                $query->whereHas('appointments', function($q) use ($shopFilter) {
                    $q->where('shop_id', $shopFilter);
                });
            })
            ->selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->whereBetween('created_at', [now()->subYear(), now()])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.analytics.customers', compact('customerGrowth', 'topCustomers', 'customerDemographics'));
    }

    public function services(Request $request)
    {
        // Filter by shop if owner is logged in
        $shopFilter = $this->getShopFilter();
        
        // Service performance
        $servicePerformance = Service::withCount(['appointments' => function($query) use ($shopFilter) {
                $query->where('status', 'completed');
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }])
            ->withSum(['appointments' => function($query) use ($shopFilter) {
                $query->where('status', 'completed');
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }], 'services.price')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->orderBy('appointments_count', 'desc')
            ->get();

        // Service revenue trends
        $serviceRevenue = Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->selectRaw('services.name, SUM(services.price) as total_revenue, COUNT(*) as appointment_count')
            ->groupBy('services.id', 'services.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return view('admin.analytics.services', compact('servicePerformance', 'serviceRevenue'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'all');
        $format = $request->get('format', 'csv');
        
        $data = [];
        
        switch ($type) {
            case 'revenue':
                $data = $this->getRevenueData();
                break;
            case 'appointments':
                $data = $this->getAppointmentData();
                break;
            case 'customers':
                $data = $this->getCustomerData();
                break;
            case 'services':
                $data = $this->getServiceData();
                break;
            default:
                $data = $this->getAllData();
        }

        if ($format === 'json') {
            return response()->json($data);
        }

        return $this->exportToCSV($data, $type);
    }

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

    private function getRevenueData()
    {
        $shopFilter = $this->getShopFilter();
        
        return Service::join('appointments', 'services.id', '=', 'appointments.service_id')
            ->where('appointments.status', 'completed')
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('appointments.shop_id', $shopFilter);
            })
            ->selectRaw('DATE(appointments.created_at) as date, services.name, services.price, appointments.customer_name')
            ->orderBy('appointments.created_at', 'desc')
            ->get();
    }

    private function getAppointmentData()
    {
        $shopFilter = $this->getShopFilter();
        
        return Appointment::with(['user', 'service'])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->select('*')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getCustomerData()
    {
        $shopFilter = $this->getShopFilter();
        
        return User::withCount(['appointments' => function($query) use ($shopFilter) {
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->whereHas('appointments', function($q) use ($shopFilter) {
                    $q->where('shop_id', $shopFilter);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getServiceData()
    {
        $shopFilter = $this->getShopFilter();
        
        return Service::withCount(['appointments' => function($query) use ($shopFilter) {
                if ($shopFilter) {
                    $query->where('shop_id', $shopFilter);
                }
            }])
            ->when($shopFilter, function($query) use ($shopFilter) {
                $query->where('shop_id', $shopFilter);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function getAllData()
    {
        return [
            'revenue' => $this->getRevenueData(),
            'appointments' => $this->getAppointmentData(),
            'customers' => $this->getCustomerData(),
            'services' => $this->getServiceData()
        ];
    }

    private function exportToCSV($data, $type)
    {
        $filename = "analytics-{$type}-" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'all') {
                foreach ($data as $section => $sectionData) {
                    fputcsv($file, ["=== {$section} ==="]);
                    if ($sectionData->count() > 0) {
                        fputcsv($file, array_keys($sectionData->first()->toArray()));
                        foreach ($sectionData as $row) {
                            fputcsv($file, $row->toArray());
                        }
                    }
                    fputcsv($file, []);
                }
            } else {
                if ($data->count() > 0) {
                    fputcsv($file, array_keys($data->first()->toArray()));
                    foreach ($data as $row) {
                        fputcsv($file, $row->toArray());
                    }
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 