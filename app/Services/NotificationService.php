<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Appointment;
use App\Models\Shop;
use App\Models\Admin;

class NotificationService
{
    /**
     * Create notifications for new appointment booking
     */
    public function notifyAppointmentBooking(Appointment $appointment)
    {
        $shop = $appointment->shop;
        
        // Notify the shop owner (admin)
        if ($shop && $shop->admin_id) {
            $admin = Admin::find($shop->admin_id);
            if ($admin) {
                Notification::create([
                    'admin_id' => $admin->id,
                    'shop_id' => $shop->id,
                    'type' => 'appointment_booking',
                    'title' => 'New Appointment Booked',
                    'message' => "New appointment #{$appointment->id} booked by {$appointment->customer_name} for {$appointment->appointment_date->format('M d, Y')} at {$appointment->appointment_time->format('h:i A')}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                        'appointment_time' => $appointment->appointment_time->format('H:i'),
                        'service_name' => $appointment->service->name ?? 'Unknown Service',
                        'vehicle_info' => "{$appointment->vehicle_year} {$appointment->vehicle_type} {$appointment->vehicle_model}"
                    ],
                ]);
            }
        }
        
        // Also notify the main admin if different from shop owner
        $mainAdmin = Admin::where('role', 'admin')->first();
        if ($mainAdmin && (!$shop->admin_id || $mainAdmin->id !== $shop->admin_id)) {
            Notification::create([
                'admin_id' => $mainAdmin->id,
                'shop_id' => $shop->id,
                'type' => 'appointment_booking',
                'title' => 'New Appointment Booked',
                'message' => "New appointment #{$appointment->id} booked at {$shop->name} by {$appointment->customer_name}",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'shop_name' => $shop->name,
                    'customer_name' => $appointment->customer_name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_time->format('H:i'),
                    'service_name' => $appointment->service->name ?? 'Unknown Service'
                ],
            ]);
        }
    }

    /**
     * Create notifications for payment submission
     */
    public function notifyPaymentSubmission(Appointment $appointment)
    {
        $shop = $appointment->shop;
        
        // Notify the shop owner (admin)
        if ($shop && $shop->admin_id) {
            $admin = Admin::find($shop->admin_id);
            if ($admin) {
                Notification::create([
                    'admin_id' => $admin->id,
                    'shop_id' => $shop->id,
                    'type' => 'payment_submission',
                    'title' => 'Payment Proof Submitted',
                    'message' => "Payment proof submitted for appointment #{$appointment->id} by {$appointment->customer_name}. Reference: {$appointment->reference_number}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'reference_number' => $appointment->reference_number,
                        'payment_method' => $appointment->paymentMethod->name ?? 'Unknown',
                        'payment_proof' => $appointment->payment_proof,
                        'amount' => $appointment->service->price ?? 0,
                        'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                        'appointment_time' => $appointment->appointment_time->format('H:i')
                    ],
                ]);
            }
        }
        
        // Also notify the main admin if different from shop owner
        $mainAdmin = Admin::where('role', 'admin')->first();
        if ($mainAdmin && (!$shop->admin_id || $mainAdmin->id !== $shop->admin_id)) {
            Notification::create([
                'admin_id' => $mainAdmin->id,
                'shop_id' => $shop->id,
                'type' => 'payment_submission',
                'title' => 'Payment Proof Submitted',
                'message' => "Payment proof submitted for appointment #{$appointment->id} at {$shop->name} by {$appointment->customer_name}",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'shop_name' => $shop->name,
                    'customer_name' => $appointment->customer_name,
                    'reference_number' => $appointment->reference_number,
                    'payment_method' => $appointment->paymentMethod->name ?? 'Unknown',
                    'amount' => $appointment->service->price ?? 0
                ],
            ]);
        }
    }

    /**
     * Create notifications for payment confirmation/rejection
     */
    public function notifyPaymentStatusChange(Appointment $appointment, $status, $adminId)
    {
        $shop = $appointment->shop;
        $admin = Admin::find($adminId);
        
        // Notify the shop owner if different from the admin who made the change
        if ($shop && $shop->admin_id && $shop->admin_id !== $adminId) {
            $shopOwner = Admin::find($shop->admin_id);
            if ($shopOwner) {
                $statusText = ucfirst($status);
                Notification::create([
                    'admin_id' => $shopOwner->id,
                    'shop_id' => $shop->id,
                    'type' => 'payment_status_change',
                    'title' => "Payment {$statusText}",
                    'message' => "Payment for appointment #{$appointment->id} has been {$status} by {$admin->name}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'payment_status' => $status,
                        'changed_by' => $admin->name,
                        'amount' => $appointment->service->price ?? 0,
                        'reference_number' => $appointment->reference_number
                    ],
                ]);
            }
        }
    }

    /**
     * Get unread notification count for admin
     */
    public function getUnreadCount($adminId)
    {
        return Notification::where('admin_id', $adminId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Get recent notifications for admin
     */
    public function getRecentNotifications($adminId, $limit = 5)
    {
        return Notification::where('admin_id', $adminId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Send payment confirmation notification to user
     */
    public function sendPaymentConfirmationNotification(Appointment $appointment, $receiptPdfPath)
    {
        Notification::create([
            'user_id' => $appointment->user_id,
            'admin_id' => auth('admin')->id(),
            'shop_id' => $appointment->shop_id,
            'type' => 'payment_confirmed',
            'title' => 'Payment Confirmed',
            'message' => 'Your payment for appointment #' . $appointment->id . ' has been confirmed.',
            'data' => [
                'appointment_id' => $appointment->id, 
                'receipt_pdf' => $receiptPdfPath
            ],
        ]);
    }

    /**
     * Send payment rejection notification to user
     */
    public function sendPaymentRejectionNotification(Appointment $appointment)
    {
        Notification::create([
            'user_id' => $appointment->user_id,
            'admin_id' => auth('admin')->id(),
            'shop_id' => $appointment->shop_id,
            'type' => 'payment_rejected',
            'title' => 'Payment Rejected',
            'message' => 'Your payment for appointment #' . $appointment->id . ' was rejected. Please check your details or contact support.',
            'data' => [
                'appointment_id' => $appointment->id
            ],
        ]);
    }
}
