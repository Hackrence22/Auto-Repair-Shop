<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RecommendationService;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected $recommendationService;
    
    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
        $this->middleware('auth');
    }
    
    /**
     * Display the recommendations page
     */
    public function index()
    {
        return view('recommendations.index');
    }
    
    /**
     * Get personalized recommendations for the authenticated user
     */
    public function getRecommendations(Request $request)
    {
        $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'limit' => 'nullable|integer|min:1|max:10'
        ]);
        
        $userId = Auth::id();
        $shopId = $request->input('shop_id');
        $limit = $request->input('limit', 5);
        
        $recommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId, 
            $shopId, 
            $limit
        );
        
        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'total_count' => count($recommendations)
        ]);
    }
    
    /**
     * Get maintenance due dates for specific services
     */
    public function getMaintenanceDue(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string',
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $userId = Auth::id();
        $serviceName = $request->input('service_name');
        $shopId = $request->input('shop_id');
        
        $maintenanceInfo = $this->recommendationService->getNextMaintenanceDue(
            $userId, 
            $serviceName, 
            $shopId
        );
        
        if (!$maintenanceInfo) {
            return response()->json([
                'success' => false,
                'message' => 'No previous service found for this type'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'maintenance_info' => $maintenanceInfo
        ]);
    }
    
    /**
     * Get recommendations for a specific shop
     */
    public function getShopRecommendations(Request $request, $shopId)
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:10'
        ]);
        
        $userId = Auth::id();
        $limit = $request->input('limit', 5);
        
        $recommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId, 
            $shopId, 
            $limit
        );
        
        return response()->json([
            'success' => true,
            'shop_id' => $shopId,
            'recommendations' => $recommendations,
            'total_count' => count($recommendations)
        ]);
    }
    
    /**
     * Get urgent maintenance recommendations
     */
    public function getUrgentRecommendations(Request $request)
    {
        $userId = Auth::id();
        $shopId = $request->input('shop_id');
        
        $allRecommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId, 
            $shopId, 
            20 // Get more recommendations to filter
        );
        
        // Filter for urgent recommendations
        $urgentRecommendations = array_filter($allRecommendations, function ($recommendation) {
            return isset($recommendation['urgency']) && $recommendation['urgency'] === 'urgent';
        });
        
        return response()->json([
            'success' => true,
            'urgent_recommendations' => array_values($urgentRecommendations),
            'total_urgent' => count($urgentRecommendations)
        ]);
    }
    
    /**
     * Get seasonal recommendations
     */
    public function getSeasonalRecommendations(Request $request)
    {
        $request->validate([
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $userId = Auth::id();
        $shopId = $request->input('shop_id');
        
        $allRecommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId, 
            $shopId, 
            20
        );
        
        // Filter for seasonal recommendations
        $seasonalRecommendations = array_filter($allRecommendations, function ($recommendation) {
            return $recommendation['type'] === 'seasonal';
        });
        
        return response()->json([
            'success' => true,
            'seasonal_recommendations' => array_values($seasonalRecommendations),
            'total_seasonal' => count($seasonalRecommendations)
        ]);
    }
    
    /**
     * Get cross-selling recommendations
     */
    public function getCrossSellingRecommendations(Request $request)
    {
        $request->validate([
            'shop_id' => 'nullable|exists:shops,id'
        ]);
        
        $userId = Auth::id();
        $shopId = $request->input('shop_id');
        
        $allRecommendations = $this->recommendationService->getPersonalizedRecommendations(
            $userId, 
            $shopId, 
            20
        );
        
        // Filter for cross-selling recommendations
        $crossSellingRecommendations = array_filter($allRecommendations, function ($recommendation) {
            return $recommendation['type'] === 'cross_selling';
        });
        
        return response()->json([
            'success' => true,
            'cross_selling_recommendations' => array_values($crossSellingRecommendations),
            'total_cross_selling' => count($crossSellingRecommendations)
        ]);
    }
}
