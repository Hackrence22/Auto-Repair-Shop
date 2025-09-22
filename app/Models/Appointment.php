<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'user_id',
        'customer_name',
        'email',
        'phone',
        'vehicle_type',
        'vehicle_model',
        'vehicle_year',
        'service_id',
        'service_type',
        'appointment_date',
        'appointment_time',
        'description',
        'status',
        'cancelled_at',
        'technician',
        'technician_id',
        'payment_method_id',
        'payment_proof',
        'reference_number',
        'payment_status',
        'reminder_sent',
        'hourly_reminder_sent'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        return $this->save();
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
} 