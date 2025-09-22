<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PricingOptimizationService;
use Illuminate\Support\Facades\Auth;

class PricingController extends Controller
{
    protected $pricingService;
    
    public function __construct(PricingOptimizationService $pricingService)
    {
        $this->pricingService = $pricingService;
        $this->middleware('auth');
    }
    
    /**
     * Get optimal pricing for a specific service
     */
    public function getOptimalPricing(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id',
            'date' => 'nullable|date'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing(
            $request->service_id,
            $request->shop_id,
            $request->date
        );
        
        return response()->json([
            'success' => true,
            'pricing_analysis' => $pricing
        ]);
    }
    
    /**
     * Get bulk pricing recommendations for multiple services
     */
    public function getBulkPricing(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $bulkPricing = $this->pricingService->getBulkPricingRecommendations(
            $request->service_ids,
            $request->shop_id
        );
        
        return response()->json([
            'success' => true,
            'bulk_pricing' => $bulkPricing
        ]);
    }
    
    /**
     * Get pricing trends for a service over time
     */
    public function getPricingTrends(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id',
            'period' => 'nullable|in:week,month,quarter,year'
        ]);
        
        $period = $request->input('period', 'month');
        $trends = $this->getPricingTrendsData($request->service_id, $request->shop_id, $period);
        
        return response()->json([
            'success' => true,
            'pricing_trends' => $trends
        ]);
    }
    
    /**
     * Get competitive pricing analysis
     */
    public function getCompetitiveAnalysis(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing($request->service_id);
        $competitiveAnalysis = $pricing['pricing_factors']['competition_factor'];
        
        return response()->json([
            'success' => true,
            'competitive_analysis' => $competitiveAnalysis
        ]);
    }
    
    /**
     * Get seasonal pricing recommendations
     */
    public function getSeasonalPricing(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing(
            $request->service_id,
            $request->shop_id
        );
        
        $seasonalAnalysis = $pricing['pricing_factors']['seasonal_factor'];
        
        return response()->json([
            'success' => true,
            'seasonal_pricing' => $seasonalAnalysis
        ]);
    }
    
    /**
     * Get demand-based pricing recommendations
     */
    public function getDemandPricing(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id',
            'date' => 'nullable|date'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing(
            $request->service_id,
            $request->shop_id,
            $request->date
        );
        
        $demandAnalysis = $pricing['pricing_factors']['demand_factor'];
        
        return response()->json([
            'success' => true,
            'demand_pricing' => $demandAnalysis
        ]);
    }
    
    /**
     * Get pricing risk assessment
     */
    public function getPricingRisk(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing(
            $request->service_id,
            $request->shop_id
        );
        
        $riskAssessment = $pricing['market_analysis']['risk_assessment'];
        
        return response()->json([
            'success' => true,
            'risk_assessment' => $riskAssessment
        ]);
    }
    
    /**
     * Get pricing opportunities
     */
    public function getPricingOpportunities(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $pricing = $this->pricingService->calculateOptimalPricing(
            $request->service_id,
            $request->shop_id
        );
        
        $opportunities = $pricing['market_analysis']['opportunities'];
        
        return response()->json([
            'success' => true,
            'opportunities' => $opportunities
        ]);
    }
    
    /**
     * Generate pricing trends data
     */
    private function getPricingTrendsData($serviceId, $shopId = null, $period = 'month')
    {
        $trends = [];
        $currentDate = now();
        
        switch ($period) {
            case 'week':
                $days = 7;
                $interval = 'day';
                break;
            case 'month':
                $days = 30;
                $interval = 'day';
                break;
            case 'quarter':
                $days = 90;
                $interval = 'week';
                break;
            case 'year':
                $days = 365;
                $interval = 'month';
                break;
            default:
                $days = 30;
                $interval = 'day';
        }
        
        for ($i = 0; $i < $days; $i++) {
            $date = $currentDate->copy()->subDays($i);
            
            $pricing = $this->pricingService->calculateOptimalPricing(
                $serviceId,
                $shopId,
                $date
            );
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'optimal_price' => $pricing['optimal_price'],
                'current_price' => $pricing['current_price'],
                'demand_factor' => $pricing['pricing_factors']['demand_factor']['factor'],
                'seasonal_factor' => $pricing['pricing_factors']['seasonal_factor']['factor']
            ];
        }
        
        return array_reverse($trends);
    }
    
    /**
     * Display the pricing optimization dashboard
     */
    public function index()
    {
        return view('pricing.index');
    }
    
    /**
     * Get pricing summary for dashboard
     */
    public function getPricingSummary(Request $request)
    {
        $request->validate([
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        // Get all services for the shop
        $services = \App\Models\Service::where('is_active', true);
        if ($request->shop_id) {
            $services->whereHas('shops', function ($q) use ($request) {
                $q->where('shop_id', $request->shop_id);
            });
        }
        $services = $services->get();
        
        $summary = [];
        foreach ($services as $service) {
            $pricing = $this->pricingService->calculateOptimalPricing(
                $service->id,
                $request->shop_id
            );
            
            $summary[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'current_price' => $service->price,
                'optimal_price' => $pricing['optimal_price'],
                'price_change' => $pricing['optimal_price'] - $service->price,
                'price_change_percentage' => (($pricing['optimal_price'] - $service->price) / $service->price) * 100,
                'recommendations' => count($pricing['recommendations']),
                'risk_level' => $pricing['market_analysis']['risk_assessment']['risk_level']
            ];
        }
        
        return response()->json([
            'success' => true,
            'pricing_summary' => $summary
        ]);
    }
}
