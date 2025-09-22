<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get personalized service recommendations for a user
     */
    public function getPersonalizedRecommendations($userId, $shopId = null, $limit = 5)
    {
        $user = User::findOrFail($userId);
        $recommendations = [];
        
        // Get user's appointment history
        $appointmentHistory = $this->getUserAppointmentHistory($userId, $shopId);
        
        // 1. Service-based recommendations (based on previous services)
        $serviceRecommendations = $this->getServiceBasedRecommendations($appointmentHistory, $shopId);
        $recommendations = array_merge($recommendations, $serviceRecommendations);
        
        // 2. Preventive maintenance recommendations
        $maintenanceRecommendations = $this->getPreventiveMaintenanceRecommendations($appointmentHistory, $user);
        $recommendations = array_merge($recommendations, $maintenanceRecommendations);
        
        // 3. Cross-selling recommendations
        $crossSellingRecommendations = $this->getCrossSellingRecommendations($appointmentHistory, $shopId);
        $recommendations = array_merge($recommendations, $crossSellingRecommendations);
        
        // 4. Seasonal recommendations
        $seasonalRecommendations = $this->getSeasonalRecommendations($shopId);
        $recommendations = array_merge($recommendations, $seasonalRecommendations);
        
        // Score and sort recommendations
        $scoredRecommendations = $this->scoreRecommendations($recommendations, $appointmentHistory);
        
        // Remove duplicates and limit results
        $uniqueRecommendations = $this->removeDuplicateRecommendations($scoredRecommendations);
        
        return array_slice($uniqueRecommendations, 0, $limit);
    }
    
    /**
     * Get user's appointment history
     */
    private function getUserAppointmentHistory($userId, $shopId = null)
    {
        $query = Appointment::where('user_id', $userId)
            ->where('status', '!=', 'cancelled')
            ->with(['service', 'shop'])
            ->orderBy('appointment_date', 'desc');
            
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        
        return $query->get();
    }
    
    /**
     * Get service-based recommendations based on user history
     */
    private function getServiceBasedRecommendations($appointmentHistory, $shopId = null)
    {
        $recommendations = [];
        
        if ($appointmentHistory->isEmpty()) {
            // For new customers, recommend popular services
            return $this->getPopularServicesRecommendations($shopId);
        }
        
        // Analyze service patterns
        $serviceFrequency = $appointmentHistory->groupBy('service_id')
            ->map(function ($appointments) {
                return [
                    'count' => $appointments->count(),
                    'last_visit' => $appointments->first()->appointment_date,
                    'service' => $appointments->first()->service
                ];
            });
        
        // Recommend services that complement previous services
        foreach ($serviceFrequency as $serviceId => $data) {
            $service = $data['service'];
            $complementaryServices = $this->getComplementaryServices($service, $shopId);
            
            foreach ($complementaryServices as $complementaryService) {
                $recommendations[] = [
                    'type' => 'service_based',
                    'service' => $complementaryService,
                    'reason' => "Based on your previous {$service->name} service",
                    'priority' => 'high',
                    'score' => $data['count'] * 10
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Get preventive maintenance recommendations
     */
    private function getPreventiveMaintenanceRecommendations($appointmentHistory, $user)
    {
        $recommendations = [];
        
        if ($appointmentHistory->isEmpty()) {
            return $recommendations;
        }
        
        $lastAppointment = $appointmentHistory->first();
        $daysSinceLastVisit = Carbon::now()->diffInDays($lastAppointment->appointment_date);
        
        // Oil change recommendations (every 3-6 months or 3000-5000 miles)
        $lastOilChange = $appointmentHistory->where('service.name', 'like', '%oil%')->first();
        if (!$lastOilChange || $daysSinceLastVisit > 90) {
            $oilChangeService = $this->findServiceByName('oil change', $lastAppointment->shop_id);
            if ($oilChangeService) {
                $recommendations[] = [
                    'type' => 'preventive_maintenance',
                    'service' => $oilChangeService,
                    'reason' => 'Time for routine oil change',
                    'priority' => 'high',
                    'score' => 80,
                    'urgency' => $daysSinceLastVisit > 180 ? 'urgent' : 'recommended'
                ];
            }
        }
        
        // Brake inspection recommendations (every 6-12 months)
        $lastBrakeService = $appointmentHistory->where('service.name', 'like', '%brake%')->first();
        if (!$lastBrakeService || $daysSinceLastVisit > 180) {
            $brakeService = $this->findServiceByName('brake inspection', $lastAppointment->shop_id);
            if ($brakeService) {
                $recommendations[] = [
                    'type' => 'preventive_maintenance',
                    'service' => $brakeService,
                    'reason' => 'Brake system inspection due',
                    'priority' => 'medium',
                    'score' => 60,
                    'urgency' => $daysSinceLastVisit > 365 ? 'urgent' : 'recommended'
                ];
            }
        }
        
        // Tire rotation recommendations (every 6 months)
        $lastTireService = $appointmentHistory->where('service.name', 'like', '%tire%')->first();
        if (!$lastTireService || $daysSinceLastVisit > 180) {
            $tireService = $this->findServiceByName('tire rotation', $lastAppointment->shop_id);
            if ($tireService) {
                $recommendations[] = [
                    'type' => 'preventive_maintenance',
                    'service' => $tireService,
                    'reason' => 'Tire rotation recommended',
                    'priority' => 'medium',
                    'score' => 50,
                    'urgency' => 'recommended'
                ];
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Get cross-selling recommendations
     */
    private function getCrossSellingRecommendations($appointmentHistory, $shopId = null)
    {
        $recommendations = [];
        
        if ($appointmentHistory->isEmpty()) {
            return $recommendations;
        }
        
        // Define service bundles and cross-selling rules
        $serviceBundles = [
            'oil_change' => ['tire_rotation', 'brake_inspection', 'air_filter'],
            'brake_service' => ['tire_rotation', 'wheel_alignment', 'brake_fluid'],
            'tire_service' => ['wheel_alignment', 'brake_inspection', 'suspension_check'],
            'engine_repair' => ['oil_change', 'air_filter', 'fuel_filter', 'spark_plugs']
        ];
        
        $lastService = $appointmentHistory->first()->service;
        $serviceName = strtolower($lastService->name);
        
        // Find applicable bundles
        foreach ($serviceBundles as $bundleKey => $bundleServices) {
            if (str_contains($serviceName, $bundleKey) || str_contains($bundleKey, $serviceName)) {
                foreach ($bundleServices as $bundleService) {
                    $service = $this->findServiceByName($bundleService, $shopId);
                    if ($service && !$this->hasRecentService($appointmentHistory, $service->id)) {
                        $recommendations[] = [
                            'type' => 'cross_selling',
                            'service' => $service,
                            'reason' => "Often needed with {$lastService->name}",
                            'priority' => 'medium',
                            'score' => 40
                        ];
                    }
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Get seasonal recommendations
     */
    private function getSeasonalRecommendations($shopId = null)
    {
        $recommendations = [];
        $currentMonth = Carbon::now()->month;
        
        // Winter preparations (October - December)
        if ($currentMonth >= 10 && $currentMonth <= 12) {
            $winterServices = ['battery_check', 'heater_inspection', 'winter_tire_installation'];
            foreach ($winterServices as $serviceName) {
                $service = $this->findServiceByName($serviceName, $shopId);
                if ($service) {
                    $recommendations[] = [
                        'type' => 'seasonal',
                        'service' => $service,
                        'reason' => 'Winter preparation service',
                        'priority' => 'medium',
                        'score' => 30
                    ];
                }
            }
        }
        
        // Summer preparations (April - June)
        if ($currentMonth >= 4 && $currentMonth <= 6) {
            $summerServices = ['ac_inspection', 'coolant_check', 'summer_tire_installation'];
            foreach ($summerServices as $serviceName) {
                $service = $this->findServiceByName($serviceName, $shopId);
                if ($service) {
                    $recommendations[] = [
                        'type' => 'seasonal',
                        'service' => $service,
                        'reason' => 'Summer preparation service',
                        'priority' => 'medium',
                        'score' => 30
                    ];
                }
            }
        }
        
        return $recommendations;
    }
    
    /**
     * Get popular services for new customers
     */
    private function getPopularServicesRecommendations($shopId = null)
    {
        $query = Service::where('is_active', true);
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        
        $popularServices = $query->orderBy('price', 'asc')->take(3)->get();
        
        $recommendations = [];
        foreach ($popularServices as $service) {
            $recommendations[] = [
                'type' => 'popular',
                'service' => $service,
                'reason' => 'Popular service for new customers',
                'priority' => 'medium',
                'score' => 25
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Get complementary services
     */
    private function getComplementaryServices($service, $shopId = null)
    {
        $complementaryMap = [
            'oil_change' => ['tire_rotation', 'air_filter_replacement', 'brake_inspection'],
            'brake_service' => ['tire_rotation', 'wheel_alignment', 'brake_fluid_change'],
            'tire_service' => ['wheel_alignment', 'brake_inspection', 'suspension_check'],
            'engine_repair' => ['oil_change', 'air_filter', 'fuel_filter', 'spark_plugs'],
            'ac_service' => ['air_filter', 'coolant_check', 'heater_inspection']
        ];
        
        $serviceName = strtolower($service->name);
        $complementaryServices = [];
        
        foreach ($complementaryMap as $key => $services) {
            if (str_contains($serviceName, $key) || str_contains($key, $serviceName)) {
                foreach ($services as $complementaryService) {
                    $foundService = $this->findServiceByName($complementaryService, $shopId);
                    if ($foundService) {
                        $complementaryServices[] = $foundService;
                    }
                }
            }
        }
        
        return $complementaryServices;
    }
    
    /**
     * Find service by name
     */
    private function findServiceByName($serviceName, $shopId = null)
    {
        $query = Service::where('is_active', true)
            ->where('name', 'like', "%{$serviceName}%");
            
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }
        
        return $query->first();
    }
    
    /**
     * Check if user has recent service
     */
    private function hasRecentService($appointmentHistory, $serviceId, $days = 90)
    {
        return $appointmentHistory->where('service_id', $serviceId)
            ->where('appointment_date', '>=', Carbon::now()->subDays($days))
            ->isNotEmpty();
    }
    
    /**
     * Score recommendations based on various factors
     */
    private function scoreRecommendations($recommendations, $appointmentHistory)
    {
        foreach ($recommendations as &$recommendation) {
            $score = $recommendation['score'] ?? 0;
            
            // Boost score for urgent preventive maintenance
            if (isset($recommendation['urgency']) && $recommendation['urgency'] === 'urgent') {
                $score += 20;
            }
            
            // Boost score for services not recently performed
            if (!$this->hasRecentService($appointmentHistory, $recommendation['service']->id, 30)) {
                $score += 10;
            }
            
            // Reduce score for expensive services
            if ($recommendation['service']->price > 200) {
                $score -= 10;
            }
            
            $recommendation['final_score'] = max(0, $score);
        }
        
        // Sort by final score (highest first)
        usort($recommendations, function ($a, $b) {
            return ($b['final_score'] ?? 0) <=> ($a['final_score'] ?? 0);
        });
        
        return $recommendations;
    }
    
    /**
     * Remove duplicate recommendations
     */
    private function removeDuplicateRecommendations($recommendations)
    {
        $seen = [];
        $unique = [];
        
        foreach ($recommendations as $recommendation) {
            $serviceId = $recommendation['service']->id;
            if (!in_array($serviceId, $seen)) {
                $seen[] = $serviceId;
                $unique[] = $recommendation;
            }
        }
        
        return $unique;
    }
    
    /**
     * Get next maintenance due date for a specific service
     */
    public function getNextMaintenanceDue($userId, $serviceName, $shopId = null)
    {
        $appointmentHistory = $this->getUserAppointmentHistory($userId, $shopId);
        
        $lastService = $appointmentHistory->where('service.name', 'like', "%{$serviceName}%")->first();
        
        if (!$lastService) {
            return null;
        }
        
        // Define maintenance intervals (in days)
        $maintenanceIntervals = [
            'oil' => 90,
            'brake' => 180,
            'tire' => 180,
            'ac' => 365,
            'battery' => 730
        ];
        
        $serviceName = strtolower($serviceName);
        $interval = 90; // Default interval
        
        foreach ($maintenanceIntervals as $key => $days) {
            if (str_contains($serviceName, $key)) {
                $interval = $days;
                break;
            }
        }
        
        $nextDue = Carbon::parse($lastService->appointment_date)->addDays($interval);
        
        return [
            'last_service_date' => $lastService->appointment_date,
            'next_due_date' => $nextDue,
            'days_until_due' => Carbon::now()->diffInDays($nextDue, false),
            'is_overdue' => $nextDue->isPast()
        ];
    }
}
