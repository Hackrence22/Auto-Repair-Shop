<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Appointment;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PricingOptimizationService
{
    /**
     * Calculate optimal pricing for a service based on various factors
     */
    public function calculateOptimalPricing($serviceId, $shopId = null, $date = null)
    {
        $service = Service::findOrFail($serviceId);
        $selectedDate = $date ? Carbon::parse($date) : Carbon::now();
        
        $pricingFactors = [
            'base_price' => $service->price,
            'demand_factor' => $this->calculateDemandFactor($serviceId, $shopId, $selectedDate),
            'seasonal_factor' => $this->calculateSeasonalFactor($service, $selectedDate),
            'competition_factor' => $this->calculateCompetitionFactor($service, $shopId),
            'time_factor' => $this->calculateTimeFactor($selectedDate),
            'capacity_factor' => $this->calculateCapacityFactor($shopId, $selectedDate)
        ];
        
        $optimalPrice = $this->computeOptimalPrice($pricingFactors);
        $priceRange = $this->calculatePriceRange($optimalPrice, $pricingFactors);
        
        return [
            'current_price' => $service->price,
            'optimal_price' => $optimalPrice,
            'price_range' => $priceRange,
            'pricing_factors' => $pricingFactors,
            'recommendations' => $this->generatePricingRecommendations($pricingFactors, $optimalPrice, $service->price),
            'market_analysis' => $this->generateMarketAnalysis($pricingFactors)
        ];
    }
    
    /**
     * Calculate demand factor based on appointment history
     */
    private function calculateDemandFactor($serviceId, $shopId, $date)
    {
        $startDate = $date->copy()->subDays(30);
        $endDate = $date->copy()->addDays(30);
        
        $query = Appointment::where('service_id', $serviceId)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');
            
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        
        $appointments = $query->get();
        
        // Calculate demand intensity
        $totalAppointments = $appointments->count();
        $peakDays = $appointments->groupBy('appointment_date')
            ->map(function ($dayAppointments) {
                return $dayAppointments->count();
            })
            ->max();
        
        // Normalize demand factor (0.8 to 1.2)
        $demandFactor = 1.0;
        
        if ($totalAppointments > 0) {
            $avgDailyDemand = $totalAppointments / 60; // 60 days period
            $demandFactor = min(1.2, max(0.8, 1 + ($avgDailyDemand - 2) * 0.1));
        }
        
        return [
            'factor' => $demandFactor,
            'total_appointments' => $totalAppointments,
            'peak_daily_demand' => $peakDays ?? 0,
            'avg_daily_demand' => $totalAppointments / 60
        ];
    }
    
    /**
     * Calculate seasonal pricing factor
     */
    private function calculateSeasonalFactor($service, $date)
    {
        $month = $date->month;
        $serviceName = strtolower($service->name);
        
        $seasonalFactors = [
            // Winter services (Dec-Feb)
            'battery' => [12, 1, 2, 1.15],
            'heater' => [12, 1, 2, 1.15],
            'winter_tire' => [12, 1, 2, 1.1],
            
            // Summer services (Jun-Aug)
            'ac' => [6, 7, 8, 1.2],
            'coolant' => [6, 7, 8, 1.1],
            'summer_tire' => [6, 7, 8, 1.05],
            
            // Rainy season services (Jun-Oct)
            'brake' => [6, 7, 8, 9, 10, 1.1],
            'wiper' => [6, 7, 8, 9, 10, 1.05],
            
            // Year-round services with slight variations
            'oil' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1.0],
            'tire' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1.0]
        ];
        
        $factor = 1.0;
        $season = 'year-round';
        
        foreach ($seasonalFactors as $serviceType => $months) {
            if (str_contains($serviceName, $serviceType)) {
                $applicableMonths = array_slice($months, 0, -1);
                $seasonalFactor = end($months);
                
                if (in_array($month, $applicableMonths)) {
                    $factor = $seasonalFactor;
                    $season = $this->getSeasonName($month);
                    break;
                }
            }
        }
        
        return [
            'factor' => $factor,
            'season' => $season,
            'month' => $month
        ];
    }
    
    /**
     * Calculate competition factor based on nearby shops
     */
    private function calculateCompetitionFactor($service, $shopId)
    {
        // Get similar services from other shops
        $similarServices = Service::where('name', 'like', '%' . substr($service->name, 0, 5) . '%')
            ->where('id', '!=', $service->id)
            ->where('is_active', true)
            ->get();
        
        if ($similarServices->isEmpty()) {
            return [
                'factor' => 1.0,
                'competitor_count' => 0,
                'avg_competitor_price' => $service->price,
                'price_position' => 'no_competition'
            ];
        }
        
        $avgCompetitorPrice = $similarServices->avg('price');
        $minCompetitorPrice = $similarServices->min('price');
        $maxCompetitorPrice = $similarServices->max('price');
        
        // Calculate competitive position
        $pricePosition = 'competitive';
        $factor = 1.0;
        
        if ($service->price < $avgCompetitorPrice * 0.9) {
            $pricePosition = 'below_market';
            $factor = 1.05; // Can increase price slightly
        } elseif ($service->price > $avgCompetitorPrice * 1.1) {
            $pricePosition = 'above_market';
            $factor = 0.95; // Should decrease price
        }
        
        return [
            'factor' => $factor,
            'competitor_count' => $similarServices->count(),
            'avg_competitor_price' => $avgCompetitorPrice,
            'min_competitor_price' => $minCompetitorPrice,
            'max_competitor_price' => $maxCompetitorPrice,
            'price_position' => $pricePosition
        ];
    }
    
    /**
     * Calculate time-based pricing factor
     */
    private function calculateTimeFactor($date)
    {
        $dayOfWeek = $date->dayOfWeek;
        $hour = $date->hour;
        
        // Weekend and evening premium
        $timeFactor = 1.0;
        $timeType = 'regular';
        
        if ($dayOfWeek == 0 || $dayOfWeek == 6) { // Weekend
            $timeFactor = 1.1;
            $timeType = 'weekend';
        } elseif ($hour >= 17 || $hour <= 9) { // Evening/Morning
            $timeFactor = 1.05;
            $timeType = 'off_peak';
        }
        
        // Holiday premium (simplified)
        $holidays = [12, 25, 1, 1]; // Dec 25, Jan 1
        if (in_array($date->month . $date->day, $holidays)) {
            $timeFactor = 1.15;
            $timeType = 'holiday';
        }
        
        return [
            'factor' => $timeFactor,
            'time_type' => $timeType,
            'day_of_week' => $dayOfWeek,
            'hour' => $hour
        ];
    }
    
    /**
     * Calculate capacity factor based on shop availability
     */
    private function calculateCapacityFactor($shopId, $date)
    {
        if (!$shopId) {
            return ['factor' => 1.0, 'capacity_utilization' => 0.5];
        }
        
        // Get shop's appointment capacity for the date
        $appointments = Appointment::where('shop_id', $shopId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->count();
        
        // Assume shop can handle 20 appointments per day (configurable)
        $maxCapacity = 20;
        $utilization = $appointments / $maxCapacity;
        
        // Higher utilization = higher prices
        $capacityFactor = 1.0;
        if ($utilization > 0.8) {
            $capacityFactor = 1.1; // High demand, increase price
        } elseif ($utilization < 0.3) {
            $capacityFactor = 0.95; // Low demand, decrease price
        }
        
        return [
            'factor' => $capacityFactor,
            'capacity_utilization' => $utilization,
            'appointments_booked' => $appointments,
            'max_capacity' => $maxCapacity
        ];
    }
    
    /**
     * Compute optimal price based on all factors
     */
    private function computeOptimalPrice($factors)
    {
        $basePrice = $factors['base_price'];
        
        // Weighted combination of factors
        $weightedFactor = (
            $factors['demand_factor']['factor'] * 0.25 +
            $factors['seasonal_factor']['factor'] * 0.20 +
            $factors['competition_factor']['factor'] * 0.25 +
            $factors['time_factor']['factor'] * 0.15 +
            $factors['capacity_factor']['factor'] * 0.15
        );
        
        $optimalPrice = $basePrice * $weightedFactor;
        
        // Apply reasonable bounds (10% to 30% variation)
        $minPrice = $basePrice * 0.9;
        $maxPrice = $basePrice * 1.3;
        
        return max($minPrice, min($maxPrice, $optimalPrice));
    }
    
    /**
     * Calculate price range for the service
     */
    private function calculatePriceRange($optimalPrice, $factors)
    {
        $basePrice = $factors['base_price'];
        
        return [
            'min_price' => $basePrice * 0.85,
            'recommended_min' => $optimalPrice * 0.95,
            'optimal_price' => $optimalPrice,
            'recommended_max' => $optimalPrice * 1.05,
            'max_price' => $basePrice * 1.35
        ];
    }
    
    /**
     * Generate pricing recommendations
     */
    private function generatePricingRecommendations($factors, $optimalPrice, $currentPrice)
    {
        $recommendations = [];
        
        // Demand-based recommendations
        if ($factors['demand_factor']['factor'] > 1.1) {
            $recommendations[] = [
                'type' => 'demand',
                'priority' => 'high',
                'message' => 'High demand detected - consider increasing price by 5-10%',
                'action' => 'increase_price'
            ];
        } elseif ($factors['demand_factor']['factor'] < 0.9) {
            $recommendations[] = [
                'type' => 'demand',
                'priority' => 'medium',
                'message' => 'Low demand - consider promotional pricing',
                'action' => 'decrease_price'
            ];
        }
        
        // Competition-based recommendations
        if ($factors['competition_factor']['price_position'] === 'above_market') {
            $recommendations[] = [
                'type' => 'competition',
                'priority' => 'high',
                'message' => 'Price is above market average - consider price adjustment',
                'action' => 'decrease_price'
            ];
        } elseif ($factors['competition_factor']['price_position'] === 'below_market') {
            $recommendations[] = [
                'type' => 'competition',
                'priority' => 'medium',
                'message' => 'Price is below market - room for increase',
                'action' => 'increase_price'
            ];
        }
        
        // Seasonal recommendations
        if ($factors['seasonal_factor']['factor'] > 1.1) {
            $recommendations[] = [
                'type' => 'seasonal',
                'priority' => 'medium',
                'message' => "Peak season for this service - seasonal pricing recommended",
                'action' => 'seasonal_increase'
            ];
        }
        
        // Capacity-based recommendations
        if ($factors['capacity_factor']['capacity_utilization'] > 0.8) {
            $recommendations[] = [
                'type' => 'capacity',
                'priority' => 'high',
                'message' => 'High capacity utilization - premium pricing justified',
                'action' => 'premium_pricing'
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Generate market analysis summary
     */
    private function generateMarketAnalysis($factors)
    {
        return [
            'market_position' => $this->determineMarketPosition($factors),
            'pricing_strategy' => $this->recommendPricingStrategy($factors),
            'risk_assessment' => $this->assessPricingRisk($factors),
            'opportunities' => $this->identifyOpportunities($factors)
        ];
    }
    
    /**
     * Determine market position
     */
    private function determineMarketPosition($factors)
    {
        $competitionFactor = $factors['competition_factor']['factor'];
        $demandFactor = $factors['demand_factor']['factor'];
        
        if ($competitionFactor > 1.05 && $demandFactor > 1.05) {
            return 'premium_position';
        } elseif ($competitionFactor < 0.95 && $demandFactor < 0.95) {
            return 'value_position';
        } else {
            return 'competitive_position';
        }
    }
    
    /**
     * Recommend pricing strategy
     */
    private function recommendPricingStrategy($factors)
    {
        $demandFactor = $factors['demand_factor']['factor'];
        $competitionFactor = $factors['competition_factor']['factor'];
        $seasonalFactor = $factors['seasonal_factor']['factor'];
        
        if ($demandFactor > 1.1 && $seasonalFactor > 1.1) {
            return 'peak_pricing';
        } elseif ($demandFactor < 0.9) {
            return 'promotional_pricing';
        } elseif ($competitionFactor < 0.95) {
            return 'competitive_pricing';
        } else {
            return 'standard_pricing';
        }
    }
    
    /**
     * Assess pricing risk
     */
    private function assessPricingRisk($factors)
    {
        $risks = [];
        
        if ($factors['competition_factor']['factor'] > 1.1) {
            $risks[] = 'price_competition_risk';
        }
        
        if ($factors['demand_factor']['factor'] < 0.8) {
            $risks[] = 'demand_decline_risk';
        }
        
        if ($factors['capacity_factor']['capacity_utilization'] > 0.9) {
            $risks[] = 'capacity_constraint_risk';
        }
        
        return [
            'risk_level' => count($risks) > 1 ? 'high' : (count($risks) > 0 ? 'medium' : 'low'),
            'risks' => $risks
        ];
    }
    
    /**
     * Identify pricing opportunities
     */
    private function identifyOpportunities($factors)
    {
        $opportunities = [];
        
        if ($factors['demand_factor']['factor'] > 1.1) {
            $opportunities[] = 'demand_increase_opportunity';
        }
        
        if ($factors['competition_factor']['factor'] < 0.95) {
            $opportunities[] = 'market_share_opportunity';
        }
        
        if ($factors['seasonal_factor']['factor'] > 1.1) {
            $opportunities[] = 'seasonal_premium_opportunity';
        }
        
        return $opportunities;
    }
    
    /**
     * Get season name
     */
    private function getSeasonName($month)
    {
        if (in_array($month, [12, 1, 2])) {
            return 'winter';
        } elseif (in_array($month, [3, 4, 5])) {
            return 'spring';
        } elseif (in_array($month, [6, 7, 8])) {
            return 'summer';
        } else {
            return 'fall';
        }
    }
    
    /**
     * Get bulk pricing recommendations
     */
    public function getBulkPricingRecommendations($serviceIds, $shopId = null)
    {
        $recommendations = [];
        
        foreach ($serviceIds as $serviceId) {
            $pricing = $this->calculateOptimalPricing($serviceId, $shopId);
            $recommendations[] = [
                'service_id' => $serviceId,
                'current_price' => $pricing['current_price'],
                'optimal_price' => $pricing['optimal_price'],
                'recommendations' => $pricing['recommendations']
            ];
        }
        
        // Calculate bundle discount
        $totalCurrentPrice = collect($recommendations)->sum('current_price');
        $totalOptimalPrice = collect($recommendations)->sum('optimal_price');
        
        $bundleDiscount = max(0.05, min(0.15, ($totalCurrentPrice - $totalOptimalPrice) / $totalCurrentPrice));
        
        return [
            'individual_pricing' => $recommendations,
            'bundle_pricing' => [
                'total_current_price' => $totalCurrentPrice,
                'total_optimal_price' => $totalOptimalPrice,
                'recommended_bundle_discount' => $bundleDiscount,
                'bundle_price' => $totalOptimalPrice * (1 - $bundleDiscount)
            ]
        ];
    }
}
