<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Feedback;
use App\Models\SlotSetting;

class HomeController extends Controller
{
    /**
     * Show the application welcome page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Fetch recent feedbacks with replies for display on welcome page
        $feedbacks = Feedback::latest()->limit(10)->get();
            
        return view('welcome', compact('feedbacks'));
    }

    private function calculateAvailableSlots($settings, $appointments, $date = null)
    {
        $slots = [];
        $today = $date ? Carbon::parse($date) : Carbon::today();
        $now = Carbon::now();
        $isToday = $today->isSameDay($now);
        $startTime = Carbon::parse($settings['opening_time']);
        $endTime = Carbon::parse($settings['closing_time']);
        $duration = $settings['default_duration'];
        $maxAppointments = $settings['max_appointments'];
        while ($startTime->copy()->addMinutes($duration) <= $endTime) {
            $timeSlot = $startTime->format('H:i');
            // Skip lunch break slot (12:00 - 1:00 PM)
            if ($timeSlot === '12:00') {
                $startTime->addMinutes($duration);
                continue;
            }
            $slotTime = Carbon::parse($today->format('Y-m-d') . ' ' . $timeSlot);
            $endTimeSlot = $startTime->copy()->addMinutes($duration)->format('H:i');
            
            // Calculate if the slot is in the past
            $isPast = false;
            if ($isToday) {
                $currentTime = Carbon::now();
                $slotDateTime = Carbon::parse($today->format('Y-m-d') . ' ' . $timeSlot);
                $isPast = $slotDateTime->lessThan($currentTime);
            }
            
            $appointmentsInSlot = $appointments->filter(function ($appointment) use ($timeSlot) {
                return Carbon::parse($appointment->appointment_time)->format('H:i') === $timeSlot;
            })->count();
            
            $slots[] = [
                'time' => $timeSlot,
                'time_range' => Carbon::createFromFormat('H:i', $timeSlot)->format('g:i A') . ' - ' . Carbon::createFromFormat('H:i', $endTimeSlot)->format('g:i A'),
                'total' => $maxAppointments,
                'available' => $maxAppointments - $appointmentsInSlot,
                'is_available' => $appointmentsInSlot < $maxAppointments && !$isPast,
                'is_past' => $isPast
            ];
            $startTime->addMinutes($duration);
        }
        return $slots;
    }

    public function getAppointmentCount(Request $request)
    {
        $date = $request->input('date');
        $count = \App\Models\Appointment::whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
        $max = Setting::getGroup('appointments')['max_appointments'] ?? 20;
        return response()->json([
            'count' => $count,
            'max' => $max
        ]);
    }

    public function getAvailableSlots(Request $request)
    {
        $date = $request->input('date');
        $time = $request->input('time');
        
        if (!$date || !$time) {
            return response()->json(['error' => 'Date and time are required'], 400);
        }

        $slotSetting = SlotSetting::where('is_active', true)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>', $time)
            ->first();

        if (!$slotSetting) {
            return response()->json(['error' => 'No active slot setting found for this time'], 404);
        }

        $startTime = Carbon::parse($slotSetting->start_time);
        $endTime = Carbon::parse($slotSetting->end_time);
        $slotsPerHour = $slotSetting->slots_per_hour;
        $totalHours = $endTime->diffInHours($startTime);
        $totalSlots = $slotsPerHour * $totalHours;

        // Calculate booked slots for each hour in the range
        $bookedSlots = 0;
        for ($hour = $startTime->copy(); $hour->lt($endTime); $hour->addHour()) {
            $bookedSlots += Appointment::whereDate('appointment_date', $date)
                ->whereTime('appointment_time', $hour->format('H:i'))
                ->where('status', '!=', 'cancelled')
                ->count();
        }

        $availableSlots = $totalSlots - $bookedSlots;

        return response()->json([
            'available_slots' => max(0, $availableSlots),
            'total_slots' => $totalSlots,
            'booked_slots' => $bookedSlots
        ]);
    }
}
