<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class NoShowPredictionService
{
    /**
     * Predict probability a booking will be a no-show.
     * Returns: [probability => float 0..1, risk_level => low|medium|high, factors => array]
     */
    public function predict(Appointment $appointment): array
    {
        $score = 0.0;
        $factors = [];

        // Lead time: very short lead time can increase risk slightly
        $leadDays = 0;
        try {
            $leadDays = Carbon::parse($appointment->appointment_date)->diffInDays($appointment->created_at);
        } catch (\Throwable $e) {
            $leadDays = 0;
        }
        if ($leadDays <= 1) { $score += 0.4; $factors[] = 'short_lead_time'; }
        elseif ($leadDays <= 3) { $score += 0.2; $factors[] = 'medium_lead_time'; }
        else { $score -= 0.1; $factors[] = 'long_lead_time'; }

        // Payment: proof/reference lowers risk; cash (walk-in-like) slightly higher risk
        if (!empty($appointment->payment_proof) || !empty($appointment->reference_number)) {
            $score -= 0.4; $factors[] = 'has_payment_commitment';
        } else {
            $factors[] = 'no_payment_commitment';
        }
        $paymentRole = $appointment->paymentMethod->role_type ?? null;
        if (in_array($paymentRole, ['gcash', 'paymaya'])) { $score -= 0.1; $factors[] = 'e_wallet_payment'; }
        elseif ($paymentRole === 'cash') { $score += 0.1; $factors[] = 'cash_payment'; }

        // User history: count cancelled appointments vs completed
        $userRiskAdj = 0.0;
        if ($appointment->user_id) {
            $completed = \App\Models\Appointment::where('user_id', $appointment->user_id)->where('status', 'completed')->count();
            $cancelled = \App\Models\Appointment::where('user_id', $appointment->user_id)->where('status', 'cancelled')->count();
            if ($completed + $cancelled > 0) {
                $ratio = $cancelled / max(1, ($completed + $cancelled));
                $userRiskAdj = ($ratio - 0.2) * 0.8; // center at 20% cancels
                $score += $userRiskAdj;
                $factors[] = 'user_history';
            } else {
                $score += 0.1; $factors[] = 'new_user';
            }
        } else {
            $score += 0.1; $factors[] = 'guest_user';
        }

        // Time-of-day and day-of-week effects (late slots and Mondays slightly riskier)
        try {
            $time = Carbon::parse($appointment->appointment_time);
            $hour = (int) $time->format('H');
            if ($hour >= 18 || $hour <= 8) { $score += 0.1; $factors[] = 'late_or_early_slot'; }
        } catch (\Throwable $e) {}
        try {
            $day = Carbon::parse($appointment->appointment_date)->dayOfWeek; // 0=Sun
            if ($day === 1) { $score += 0.05; $factors[] = 'monday_effect'; }
            if ($day === 5) { $score += 0.05; $factors[] = 'friday_effect'; }
        } catch (\Throwable $e) {}

        // Service type fallback: long-duration services may have slightly lower no-show
        $duration = $appointment->service->duration ?? 60;
        if ($duration >= 120) { $score -= 0.05; $factors[] = 'long_service'; }

        // Sigmoid to map score to probability
        $prob = 1.0 / (1.0 + exp(-($score)));

        $risk = 'low';
        if ($prob >= 0.65) { $risk = 'high'; }
        elseif ($prob >= 0.4) { $risk = 'medium'; }

        return [
            'probability' => round($prob, 3),
            'risk_level' => $risk,
            'factors' => $factors,
        ];
    }
}


