<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\SmsService;
use App\Services\EmailService;
use PDF;

class PaymentManagementController extends Controller
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $q = request('q');
        $payments = Appointment::with(['user', 'service', 'shop', 'paymentMethod'])
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $adminId = auth('admin')->id();
                $adminName = auth('admin')->user()->name;
                $query->whereHas('shop', function($s) use ($adminId, $adminName) {
                    $s->where('admin_id', $adminId)
                      ->orWhere(function($ss) use ($adminName) { $ss->whereNull('admin_id')->where('owner_name', $adminName); });
                });
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('id', $q)
                        ->orWhere('customer_name', 'like', "%$q%")
                        ->orWhere('reference_number', 'like', "%$q%")
                        ->orWhere('status', 'like', "%$q%")
                        ->orWhere('payment_status', 'like', "%$q%")
                        ->orWhereDate('appointment_date', $q)
                        ->orWhereHas('paymentMethod', function($pm) use ($q) { $pm->where('name', 'like', "%$q%"); })
                        ->orWhereHas('shop', function($s) use ($q) { $s->where('name', 'like', "%$q%"); })
                        ->orWhereHas('user', function($u) use ($q) { $u->where('email', 'like', "%$q%"); });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
        $paymentsByShop = $payments->getCollection()->groupBy(function($payment) {
            return $payment->shop ? $payment->shop->name : 'No Shop';
        });
        return view('admin.payments.index', compact('paymentsByShop', 'payments'));
    }

    public function confirm($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->payment_status = 'paid';
        $appointment->status = 'completed'; // Mark as completed for analytics
        $appointment->save();
        
        // Generate PDF receipt
        $pdf = PDF::loadView('appointments.receipt_pdf', ['appointment' => $appointment]);
        $pdfPath = 'receipts/receipt_' . $appointment->id . '_' . now()->format('Ymd_His') . '.pdf';
        \Storage::disk('public')->put($pdfPath, $pdf->output());
        // Generate a public URL for the receipt (works for local or GCS)
        $receiptUrl = \Storage::disk('public')->url($pdfPath);
        
        // Notify user with receipt link
        $this->notificationService->sendPaymentConfirmationNotification($appointment, $receiptUrl);
        // Email: payment approved
        try {
            $emailService = app(EmailService::class);
            $emailService->sendPaymentStatus([
                'email' => $appointment->email,
                'user_name' => $appointment->customer_name,
                'appointment_id' => $appointment->id,
                'amount' => optional($appointment->service)->price ?? 0,
                'reference' => $appointment->reference_number,
                'status' => 'approved',
                'note' => null,
            ]);
        } catch (\Throwable $e) {}
        // SMS: payment confirmed
        try {
            $sms = app(SmsService::class);
            $to = $sms->toE164($appointment->phone);
            if ($to) {
                $sms->send($to, 'Hi ' . $appointment->customer_name . '! 🎉 Your payment has been confirmed! Your appointment for ' . $appointment->appointment_date->format('M d, Y') . ' at ' . $appointment->appointment_time->format('h:i A') . ' is all set. Thank you for your business!');
            }
        } catch (\Throwable $e) {}
        
        // Notify shop owner about payment confirmation
        $this->notificationService->notifyPaymentStatusChange($appointment, 'confirmed', auth('admin')->id());
        return redirect()->back()->with('success', 'Payment confirmed and marked as paid.');
    }

    public function reject($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->payment_status = 'rejected';
        $appointment->save();
        
        // Notify user
        $this->notificationService->sendPaymentRejectionNotification($appointment);
        // Email: payment rejected
        try {
            $emailService = app(EmailService::class);
            $emailService->sendPaymentStatus([
                'email' => $appointment->email,
                'user_name' => $appointment->customer_name,
                'appointment_id' => $appointment->id,
                'amount' => optional($appointment->service)->price ?? 0,
                'reference' => $appointment->reference_number,
                'status' => 'rejected',
                'note' => 'Payment was rejected. Please re-upload correct proof or contact support.',
            ]);
        } catch (\Throwable $e) {}
        // SMS: payment rejected
        try {
            $sms = app(SmsService::class);
            $to = $sms->toE164($appointment->phone);
            if ($to) {
                $sms->send($to, 'Hi ' . $appointment->customer_name . ', we need to verify your payment for your appointment on ' . $appointment->appointment_date->format('M d, Y') . ' at ' . $appointment->appointment_time->format('h:i A') . '. Please contact us with correct payment details. Thank you! 📞');
            }
        } catch (\Throwable $e) {}
        
        // Notify shop owner about payment rejection
        $this->notificationService->notifyPaymentStatusChange($appointment, 'rejected', auth('admin')->id());
        return redirect()->back()->with('success', 'Payment rejected.');
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        // Only allow delete if not paid
        if ($appointment->payment_status !== 'paid') {
            $appointment->delete();
            return redirect()->back()->with('success', 'Payment deleted.');
        }
        return redirect()->back()->with('error', 'Cannot delete a confirmed (paid) payment.');
    }

    public function history()
    {
        $q = request('q');
        $payments = \App\Models\Appointment::with(['user', 'service', 'shop', 'paymentMethod'])
            ->when(auth('admin')->user()?->isOwner(), function($query) {
                $adminId = auth('admin')->id();
                $adminName = auth('admin')->user()->name;
                $query->whereHas('shop', function($s) use ($adminId, $adminName) {
                    $s->where('admin_id', $adminId)
                      ->orWhere(function($ss) use ($adminName) { $ss->whereNull('admin_id')->where('owner_name', $adminName); });
                });
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('id', $q)
                        ->orWhere('customer_name', 'like', "%$q%")
                        ->orWhere('reference_number', 'like', "%$q%")
                        ->orWhere('status', 'like', "%$q%")
                        ->orWhere('payment_status', 'like', "%$q%")
                        ->orWhereDate('appointment_date', $q)
                        ->orWhereHas('paymentMethod', function($pm) use ($q) { $pm->where('name', 'like', "%$q%"); })
                        ->orWhereHas('shop', function($s) use ($q) { $s->where('name', 'like', "%$q%"); })
                        ->orWhereHas('user', function($u) use ($q) { $u->where('email', 'like', "%$q%"); });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
        $paymentsByShop = $payments->getCollection()->groupBy(function($payment) {
            return $payment->shop ? $payment->shop->name : 'No Shop';
        });
        return view('admin.payments.history', compact('paymentsByShop', 'payments'));
    }

    public function historyCsv()
    {
        $payments = \App\Models\Appointment::orderByDesc('created_at')->get();
        $filename = 'admin_payment_history_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        $columns = ['ID', 'Customer', 'Date', 'Time', 'Payment Method', 'Status', 'Payment Status', 'Reference', 'Proof'];
        $callback = function() use ($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($payments as $p) {
                fputcsv($file, [
                    $p->id,
                    $p->customer_name,
                    $p->appointment_date,
                    $p->appointment_time,
                    $p->paymentMethod ? $p->paymentMethod->name : '-',
                    $p->status,
                    $p->payment_status,
                    $p->reference_number,
                    $p->payment_proof ? \Storage::disk('public')->url($p->payment_proof) : '-',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function historyPdf()
    {
        $payments = \App\Models\Appointment::orderByDesc('created_at')->get();
        $pdf = \PDF::loadView('admin.payments.history_pdf', compact('payments'));
        $filename = 'admin_payment_history_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
} 