<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Service;
use App\Models\SlotSetting;
use App\Models\PaymentMethod;
use App\Models\Notification;
use App\Models\Admin;
use App\Services\AppointmentSchedulingService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use App\Services\EmailService;

class AppointmentController extends Controller
{
    protected $schedulingService;
    protected $notificationService;
    
    public function __construct(AppointmentSchedulingService $schedulingService, NotificationService $notificationService)
    {
        $this->schedulingService = $schedulingService;
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $q = request('q');
        $appointments = Appointment::with(['service', 'shop'])
            ->where('user_id', auth()->id())
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('customer_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('vehicle_model', 'like', "%$q%")
                        ->orWhere('status', 'like', "%$q%")
                        ->orWhereDate('appointment_date', $q)
                        ->orWhereHas('service', function($s) use ($q) { $s->where('name', 'like', "%$q%"); })
                        ->orWhereHas('shop', function($s) use ($q) { $s->where('name', 'like', "%$q%"); });
                });
            })
            ->orderBy('appointment_date', 'asc')
            ->paginate(10)
            ->withQueryString();
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $selectedShop = null;
        $shopId = request('shop') ?? request('shop_id');
        if ($shopId) {
            $selectedShop = \App\Models\Shop::find($shopId);
        }
        $shops = \App\Models\Shop::active()->ordered()->get();
        $services = $selectedShop ? $selectedShop->services()->where('is_active', 1)->get() : \App\Models\Service::where('is_active', 1)->get();
        $slotSettings = $selectedShop ? $selectedShop->slotSettings()->where('is_active', 1)->get() : \App\Models\SlotSetting::where('is_active', 1)->get();
        $paymentMethods = $selectedShop ? PaymentMethod::active()->ordered()->where('shop_id', $selectedShop->id)->get() : PaymentMethod::active()->ordered()->get();
        
        // Get the selected date or default to today
        $selectedDate = request('date', date('Y-m-d'));
        $selectedServiceId = request('service_id');
        $preferredTime = request('time');
        
        // Use enhanced scheduling algorithm if shop and service are selected
        $optimalSlots = [];
        if ($selectedShop && $selectedServiceId) {
            $optimalSlots = $this->schedulingService->calculateOptimalSlots(
                $selectedShop->id, 
                $selectedServiceId, 
                $selectedDate, 
                $preferredTime
            );
        }
        
        // Fallback to old method for backward compatibility
        $slotsPerTime = [];
        if (empty($optimalSlots)) {
            $existingAppointments = Appointment::whereDate('appointment_date', $selectedDate)
                ->where('status', '!=', 'cancelled')
                ->get();
            
            foreach ($slotSettings as $slot) {
                $startTime = \Carbon\Carbon::parse($slot->start_time)->format('H:i');
                $appointmentsInSlot = $existingAppointments->filter(function ($appointment) use ($startTime) {
                    return \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') === $startTime;
                })->count();
                $slotsPerTime[$startTime] = $slot->slots_per_hour - $appointmentsInSlot;
            }
        }

        // Get technicians for the selected shop
        $technicians = collect();
        if ($selectedShop) {
            $technicians = \App\Models\Technician::where('shop_id', $selectedShop->id)
                ->where('status', 'active')
                ->where('is_available', true)
                ->orderBy('name')
                ->get();
            
            // Filter technicians based on working days if a date is selected
            if ($selectedDate) {
                $dayOfWeek = \Carbon\Carbon::parse($selectedDate)->dayOfWeek;
                // Convert Carbon dayOfWeek (0=Sunday, 1=Monday, etc.) to our format (1=Monday, 7=Sunday)
                $dayNumber = $dayOfWeek == 0 ? 7 : $dayOfWeek;
                
                $technicians = $technicians->filter(function($technician) use ($dayNumber) {
                    // If technician has no working days set, assume they work all days
                    if (empty($technician->working_days)) {
                        return true;
                    }
                    // Check if the selected day is in technician's working days
                    return in_array($dayNumber, $technician->working_days);
                });
            }
        }

        return view('appointments.create', compact(
            'services', 
            'slotsPerTime', 
            'slotSettings',
            'paymentMethods',
            'selectedShop',
            'shops',
            'optimalSlots',
            'selectedDate',
            'selectedServiceId',
            'preferredTime',
            'technicians'
        ));
    }

    private function calculateAvailableSlots($settings, $appointments, $date = null)
    {
        $slots = [];
        $selectedDate = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::today();
        $now = \Carbon\Carbon::now();
        $isToday = $selectedDate->isSameDay($now);
        $startTime = \Carbon\Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $settings['opening_time']);
        $endTime = \Carbon\Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $settings['closing_time']);
        $duration = $settings['default_duration'];
        $maxAppointments = $settings['max_appointments'];
        while ($startTime < $endTime) {
            $timeSlot = $startTime->format('H:i');
            // Skip lunch break slot (12:00 - 1:00 PM)
            if ($timeSlot === '12:00') {
                $startTime->addMinutes($duration);
                continue;
            }
            $slotTime = \Carbon\Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $timeSlot);
            $endTimeSlot = $startTime->copy()->addMinutes($duration)->format('H:i');
            
            // Calculate if the slot is in the past
            $isPast = false;
            if ($isToday) {
                $currentTime = \Carbon\Carbon::now();
                $slotDateTime = \Carbon\Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $timeSlot);
                $isPast = $slotDateTime->lessThan($currentTime);
            } else {
                $isPast = false;
            }
            
            $appointmentsInSlot = $appointments->filter(function ($appointment) use ($timeSlot) {
                return \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') === $timeSlot;
            })->count();
            
            $slots[] = [
                'time' => $timeSlot,
                'time_range' => \Carbon\Carbon::createFromFormat('H:i', $timeSlot)->format('g:i A') . ' - ' . \Carbon\Carbon::createFromFormat('H:i', $endTimeSlot)->format('g:i A'),
                'total' => $maxAppointments,
                'available' => $maxAppointments - $appointmentsInSlot,
                'is_available' => $appointmentsInSlot < $maxAppointments && !$isPast,
                'is_past' => $isPast
            ];
            $startTime->addMinutes($duration);
        }
        return $slots;
    }

    public function store(Request $request)
    {
        // Get the selected payment method to check its type
        $paymentMethod = \App\Models\PaymentMethod::find($request->payment_method_id);
        $roleType = $paymentMethod ? $paymentMethod->role_type : null;

        $rules = [
            'shop_id' => 'required|exists:shops,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string',
        ];
        if (in_array($roleType, ['gcash', 'paymaya'])) {
            $rules['payment_proof'] = 'required|image|mimes:jpeg,png,jpg,gif|max:4096';
            $rules['reference_number'] = 'required|string|max:100';
        } else {
            $rules['payment_proof'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096';
            $rules['reference_number'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        // Get the service to determine service_type
        $service = Service::findOrFail($validated['service_id']);

        $appointment = new Appointment([
            'user_id' => auth()->id(),
            'shop_id' => $validated['shop_id'],
            'technician_id' => $validated['technician_id'] ?? null,
            'customer_name' => $validated['customer_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_model' => $validated['vehicle_model'],
            'vehicle_year' => $validated['vehicle_year'],
            'service_id' => $validated['service_id'],
            'service_type' => $service->name, // Set service_type to the service name for consistency
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'payment_method_id' => $validated['payment_method_id'],
            'description' => $validated['description'],
            'status' => 'pending',
            'reference_number' => $validated['reference_number'] ?? null,
            'payment_status' => 'unpaid',
        ]);

        // Handle payment proof upload
        if ($request->hasFile('payment_proof')) {
            $path = $request->file('payment_proof')->store('payment-proofs', 'public');
            $appointment->payment_proof = $path;
        }

        $appointment->save();
        
        // Notify both admin and owner using the notification service
        $this->notificationService->notifyAppointmentBooking($appointment);
        
        // If payment proof was uploaded, also notify about payment submission
            if ($appointment->payment_proof) {
            $this->notificationService->notifyPaymentSubmission($appointment);
        }
        
        // Email: appointment booked
        try {
            $appointment->loadMissing(['shop', 'service']);
            $emailService = app(EmailService::class);
            $emailService->sendAppointmentBooked([
                'email' => $appointment->email,
                'user_name' => $appointment->customer_name,
                'date' => $appointment->appointment_date->format('M d, Y'),
                'time' => $appointment->appointment_time->format('h:i A'),
                'service' => optional($appointment->service)->name ?? $appointment->service_type,
                'shop' => $appointment->shop,
                'appointment_id' => $appointment->id,
            ]);
        } catch (\Throwable $e) {}

        // SMS: appointment booked confirmation
        try {
            $sms = app(SmsService::class);
            $to = $sms->toE164($appointment->phone);
            if ($to) {
                $sms->send($to, 'Hi ' . $appointment->customer_name . '! Your auto repair appointment has been booked for ' . $appointment->appointment_date->format('M d, Y') . ' at ' . $appointment->appointment_time->format('h:i A') . '. We\'ll review and confirm shortly. Thank you for choosing us! 🚗');
            }
        } catch (\Throwable $e) {}
        
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['service', 'shop']);
        $noShow = app(\App\Services\NoShowPredictionService::class)->predict($appointment);
        // Quick top recommendations (optional UI usage)
        try {
            $recs = app(\App\Services\RecommendationService::class)->getPersonalizedRecommendations(auth()->id(), $appointment->shop_id, 3);
        } catch (\Throwable $e) {
            $recs = [];
        }
        if (empty($recs)) {
            // Fallback: show popular active services for this shop or globally
            $fallbackQuery = \App\Models\Service::where('is_active', true);
            if (!empty($appointment->shop_id)) {
                $fallbackQuery->where('shop_id', $appointment->shop_id);
            }
            $popular = $fallbackQuery->orderBy('price', 'asc')->take(3)->get();
            if ($popular->isNotEmpty()) {
                $recs = $popular->map(function ($service) {
                    return [
                        'type' => 'popular',
                        'service' => $service,
                        'reason' => 'Popular service',
                        'priority' => 'medium',
                        'score' => 20,
                    ];
                })->all();
            }
        }
        return view('appointments.show', compact('appointment', 'noShow', 'recs'));
    }

    public function edit(Appointment $appointment)
    {
        $services = \App\Models\Service::where('is_active', 1)->get();
        return view('appointments.edit', compact('appointment', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_model' => 'required|string|max:100',
            'vehicle_year' => 'required|string|max:4',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        unset($data['service_type']);
        $appointment->update($data);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }

    public function cancel(Appointment $appointment)
    {
        // Check if the appointment is already cancelled
        if ($appointment->status === 'cancelled') {
            return redirect()->route('appointments.index')
                ->with('error', 'This appointment is already cancelled.');
        }

        // Check if the appointment is in the past
        if ($appointment->appointment_date < now()) {
            return redirect()->route('appointments.index')
                ->with('error', 'Cannot cancel past appointments.');
        }

        // Update the appointment status and cancelled_at timestamp
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment has been cancelled successfully.');
    }

    public function getSlots(Request $request)
    {
        $date = $request->input('date');
        $time = $request->input('time');
        $shopId = $request->input('shop_id');
        $serviceId = $request->input('service_id');
        
        if (!$date) {
            return response()->json(['slots' => []]);
        }

        // Use enhanced scheduling algorithm if shop and service are provided
        if ($shopId && $serviceId) {
            $optimalSlots = $this->schedulingService->calculateOptimalSlots(
                $shopId, 
                $serviceId, 
                $date, 
                $time
            );
            
            return response()->json([
                'slots' => $optimalSlots,
                'algorithm' => 'enhanced'
            ]);
        }

        // If a specific time is requested, return only the available slots for that time
        if ($time) {
            $slotSettings = \App\Models\SlotSetting::where('is_active', true)->get();
            $slotTimes = [];
            foreach ($slotSettings as $setting) {
                $startTime = \Carbon\Carbon::parse($setting->start_time);
                $slotTimes[] = $startTime->format('H:i');
            }
            $slotTimes = array_unique($slotTimes);

            if (!in_array($time, $slotTimes)) {
                return response()->json(['available_slots' => 0]);
            }

            $slotsPerHour = 1;
            foreach ($slotSettings as $setting) {
                if (\Carbon\Carbon::parse($setting->start_time)->format('H:i') === $time) {
                    $slotsPerHour = $setting->slots_per_hour;
                    break;
                }
            }

            $bookedSlots = \App\Models\Appointment::whereDate('appointment_date', $date)
                ->whereTime('appointment_time', $time)
                ->where('status', '!=', 'cancelled')
                ->count();

            $availableSlots = $slotsPerHour - $bookedSlots;

            return response()->json(['available_slots' => max(0, $availableSlots)]);
        }

        // Default: return all slots for the date (legacy, not used by your JS)
        $appointments = \App\Models\Appointment::whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();
        // $slots = $this->calculateAvailableSlots($settings, $appointments, $date); // No longer used
        return response()->json(['slots' => []]);
    }
    
    /**
     * Get optimal slots using enhanced scheduling algorithm
     */
    public function getOptimalSlots(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|date_format:H:i'
        ]);
        
        $optimalSlots = $this->schedulingService->calculateOptimalSlots(
            $request->shop_id,
            $request->service_id,
            $request->date,
            $request->preferred_time
        );
        
        return response()->json([
            'success' => true,
            'slots' => $optimalSlots,
            'total_slots' => count($optimalSlots),
            'recommended_slots' => array_slice($optimalSlots, 0, 3) // Top 3 recommended slots
        ]);
    }
    
    /**
     * Get alternative suggestions if preferred time is unavailable
     */
    public function getAlternativeSuggestions(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'required|date_format:H:i'
        ]);
        
        $alternatives = $this->schedulingService->suggestAlternatives(
            $request->shop_id,
            $request->service_id,
            $request->date,
            $request->preferred_time
        );
        
        return response()->json([
            'success' => true,
            'alternatives' => $alternatives,
            'has_alternatives' => !empty($alternatives)
        ]);
    }

    public function history()
    {
        $q = request('q');
        $payments = \App\Models\Appointment::with(['paymentMethod'])
            ->where('user_id', auth()->id())
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('id', $q)
                        ->orWhere('reference_number', 'like', "%$q%")
                        ->orWhere('status', 'like', "%$q%")
                        ->orWhere('payment_status', 'like', "%$q%")
                        ->orWhereDate('appointment_date', $q)
                        ->orWhereHas('paymentMethod', function($pm) use ($q) { $pm->where('name', 'like', "%$q%"); });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
        return view('appointments.history', compact('payments'));
    }

    public function historyCsv()
    {
        $payments = Appointment::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
        $filename = 'payment_history_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        $columns = ['ID', 'Date', 'Time', 'Payment Method', 'Status', 'Payment Status', 'Reference', 'Proof'];
        $callback = function() use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->appointment_date,
                    $p->appointment_time,
                    $p->paymentMethod ? $p->paymentMethod->name : '-',
                    $p->status,
                    $p->payment_status,
                    $p->reference_number,
                    $p->payment_proof ? asset('uploads/' . $p->payment_proof) : '-',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function historyPdf()
    {
        $payments = Appointment::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();
        $pdf = \PDF::loadView('appointments.history_pdf', compact('payments'));
        $filename = 'payment_history_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
} 