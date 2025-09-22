<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\SlotSetting;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentSchedulingService
{
    /**
     * Calculate optimal appointment slots based on service type, duration, and availability
     */
    public function calculateOptimalSlots($shopId, $serviceId, $date, $preferredTime = null)
    {
        $shop = Shop::findOrFail($shopId);
        $service = Service::findOrFail($serviceId);
        $selectedDate = Carbon::parse($date);
        
        // Get slot settings for the shop
        $slotSettings = $shop->slotSettings()->where('is_active', true)->get();
        
        // Get existing appointments for the date
        $existingAppointments = Appointment::where('shop_id', $shopId)
            ->whereDate('appointment_date', $selectedDate)
            ->where('status', '!=', 'cancelled')
            ->get();
        
        // Calculate service duration (default 60 minutes if not specified)
        $serviceDuration = $service->duration ?? 60;
        
        // Get optimal slots based on service type and duration
        $optimalSlots = $this->getOptimalSlotsForService($slotSettings, $existingAppointments, $selectedDate, $serviceDuration, $preferredTime);
        
        // Add conflict detection and resolution
        $conflictFreeSlots = $this->resolveConflicts($optimalSlots, $existingAppointments, $serviceDuration);
        
        // Add priority scoring
        $scoredSlots = $this->scoreSlots($conflictFreeSlots, $service, $selectedDate);
        
        return $scoredSlots;
    }
    
    /**
     * Get optimal slots based on service type and duration
     */
    private function getOptimalSlotsForService($slotSettings, $existingAppointments, $selectedDate, $serviceDuration, $preferredTime = null)
    {
        $optimalSlots = [];
        $now = Carbon::now();
        $isToday = $selectedDate->isSameDay($now);
        
        foreach ($slotSettings as $setting) {
            $startTime = Carbon::parse($setting->start_time);
            $endTime = Carbon::parse($setting->end_time);
            $slotsPerHour = $setting->slots_per_hour;
            
            // Calculate available time slots
            $currentTime = $startTime->copy();
            while ($currentTime->copy()->addMinutes($serviceDuration) <= $endTime) {
                $slotTime = $currentTime->format('H:i');
                
                // Skip if slot is in the past for today
                if ($isToday) {
                    $slotDateTime = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $slotTime);
                    if ($slotDateTime->lessThan($now)) {
                        $currentTime->addMinutes(30); // Move to next 30-minute slot
                        continue;
                    }
                }
                
                // Calculate booked appointments in this slot
                $bookedAppointments = $existingAppointments->filter(function ($appointment) use ($slotTime) {
                    return Carbon::parse($appointment->appointment_time)->format('H:i') === $slotTime;
                });
                
                $availableSlots = $slotsPerHour - $bookedAppointments->count();
                
                if ($availableSlots > 0) {
                    $optimalSlots[] = [
                        'time' => $slotTime,
                        'time_range' => $this->formatTimeRange($slotTime, $serviceDuration),
                        'available_slots' => $availableSlots,
                        'total_slots' => $slotsPerHour,
                        'booked_slots' => $bookedAppointments->count(),
                        'service_duration' => $serviceDuration,
                        'is_preferred' => $preferredTime && $slotTime === $preferredTime,
                        'slot_setting_id' => $setting->id
                    ];
                }
                
                $currentTime->addMinutes(30); // Move to next 30-minute slot
            }
        }
        
        return $optimalSlots;
    }
    
    /**
     * Resolve scheduling conflicts
     */
    private function resolveConflicts($slots, $existingAppointments, $serviceDuration)
    {
        $conflictFreeSlots = [];
        
        foreach ($slots as $slot) {
            $slotStart = Carbon::parse($slot['time']);
            $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);
            
            $hasConflict = false;
            
            // Check for overlapping appointments
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->service->duration ?? 60);
                
                // Check if appointments overlap
                if ($this->appointmentsOverlap($slotStart, $slotEnd, $appointmentStart, $appointmentEnd)) {
                    $hasConflict = true;
                    break;
                }
            }
            
            if (!$hasConflict) {
                $conflictFreeSlots[] = $slot;
            }
        }
        
        return $conflictFreeSlots;
    }
    
    /**
     * Check if two appointment times overlap
     */
    private function appointmentsOverlap($start1, $end1, $start2, $end2)
    {
        return $start1 < $end2 && $start2 < $end1;
    }
    
    /**
     * Score slots based on various factors
     */
    private function scoreSlots($slots, $service, $selectedDate)
    {
        $scoredSlots = [];
        
        foreach ($slots as $slot) {
            $score = 0;
            
            // Base score for availability
            $score += $slot['available_slots'] * 10;
            
            // Bonus for preferred time
            if ($slot['is_preferred']) {
                $score += 50;
            }
            
            // Time-based scoring (morning slots preferred for most services)
            $hour = (int) Carbon::parse($slot['time'])->format('H');
            if ($hour >= 8 && $hour <= 11) {
                $score += 20; // Morning bonus
            } elseif ($hour >= 14 && $hour <= 16) {
                $score += 15; // Afternoon bonus
            }
            
            // Service-specific scoring
            $score += $this->getServiceSpecificScore($service, $slot['time']);
            
            // Day-specific scoring (avoid Mondays and Fridays if possible)
            $dayOfWeek = $selectedDate->dayOfWeek;
            if ($dayOfWeek === 1) { // Monday
                $score -= 10;
            } elseif ($dayOfWeek === 5) { // Friday
                $score -= 5;
            }
            
            $slot['score'] = $score;
            $scoredSlots[] = $slot;
        }
        
        // Sort by score (highest first)
        usort($scoredSlots, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $scoredSlots;
    }
    
    /**
     * Get service-specific scoring
     */
    private function getServiceSpecificScore($service, $time)
    {
        $hour = (int) Carbon::parse($time)->format('H');
        $serviceType = strtolower($service->name);
        
        $score = 0;
        
        // Oil changes and quick services prefer morning slots
        if (str_contains($serviceType, 'oil') || str_contains($serviceType, 'quick')) {
            if ($hour >= 8 && $hour <= 11) {
                $score += 15;
            }
        }
        
        // Major repairs prefer afternoon slots
        if (str_contains($serviceType, 'repair') || str_contains($serviceType, 'major')) {
            if ($hour >= 13 && $hour <= 16) {
                $score += 15;
            }
        }
        
        // Emergency services get high priority
        if (str_contains($serviceType, 'emergency') || str_contains($serviceType, 'urgent')) {
            $score += 30;
        }
        
        return $score;
    }
    
    /**
     * Format time range for display
     */
    private function formatTimeRange($startTime, $duration)
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = $start->copy()->addMinutes($duration);
        
        return $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }
    
    /**
     * Suggest alternative times if preferred time is unavailable
     */
    public function suggestAlternatives($shopId, $serviceId, $date, $preferredTime)
    {
        $optimalSlots = $this->calculateOptimalSlots($shopId, $serviceId, $date, $preferredTime);
        
        // Find the preferred time slot
        $preferredSlot = collect($optimalSlots)->firstWhere('is_preferred', true);
        
        if (!$preferredSlot || $preferredSlot['available_slots'] <= 0) {
            // Return top 3 alternatives
            return collect($optimalSlots)->take(3)->map(function ($slot) {
                return [
                    'time' => $slot['time'],
                    'time_range' => $slot['time_range'],
                    'available_slots' => $slot['available_slots'],
                    'score' => $slot['score'],
                    'reason' => $this->getSuggestionReason($slot)
                ];
            });
        }
        
        return [];
    }
    
    /**
     * Get reason for suggestion
     */
    private function getSuggestionReason($slot)
    {
        $hour = (int) Carbon::parse($slot['time'])->format('H');
        
        if ($hour >= 8 && $hour <= 11) {
            return 'Optimal morning slot with high availability';
        } elseif ($hour >= 13 && $hour <= 16) {
            return 'Good afternoon slot with moderate traffic';
        } else {
            return 'Available slot with good service window';
        }
    }
    
    /**
     * Check if appointment can be scheduled without conflicts
     */
    public function canScheduleAppointment($shopId, $serviceId, $date, $time)
    {
        $service = Service::findOrFail($serviceId);
        $serviceDuration = $service->duration ?? 60;
        
        $appointmentStart = Carbon::parse($date . ' ' . $time);
        $appointmentEnd = $appointmentStart->copy()->addMinutes($serviceDuration);
        
        // Get existing appointments for the time period
        $conflictingAppointments = Appointment::where('shop_id', $shopId)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(function ($appointment) use ($appointmentStart, $appointmentEnd) {
                $existingStart = Carbon::parse($appointment->appointment_time);
                $existingEnd = $existingStart->copy()->addMinutes($appointment->service->duration ?? 60);
                
                return $this->appointmentsOverlap($appointmentStart, $appointmentEnd, $existingStart, $existingEnd);
            });
        
        return $conflictingAppointments->count() === 0;
    }
}
