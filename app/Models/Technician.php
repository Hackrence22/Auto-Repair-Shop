<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Technician extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'email',
        'phone',
        'bio',
        'specialization',
        'experience_years',
        'profile_picture',
        'certifications',
        'status',
        'working_hours_start',
        'working_hours_end',
        'working_days',
        'hourly_rate',
        'is_available',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_available' => 'boolean',
        'working_hours_start' => 'datetime',
        'working_hours_end' => 'datetime',
        'hourly_rate' => 'decimal:2',
    ];

    // Relationships
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    // Accessors
    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            $path = strpos($this->profile_picture, 'technicians/') === 0
                ? $this->profile_picture
                : ('technicians/' . $this->profile_picture);
            return Storage::disk('public')->url($path);
        }
        return asset('images/default-technician.png');
    }

    public function getWorkingDaysTextAttribute()
    {
        if (!$this->working_days) {
            return 'No working days set';
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $workingDayNames = array_map(function($day) use ($days) {
            return $days[$day - 1] ?? '';
        }, $this->working_days);

        return implode(', ', array_filter($workingDayNames));
    }

    public function getStatusBadgeAttribute()
    {
        $statusClasses = [
            'active' => 'bg-success',
            'inactive' => 'bg-secondary',
            'on_leave' => 'bg-warning'
        ];

        $statusText = ucfirst(str_replace('_', ' ', $this->status));
        $statusClass = $statusClasses[$this->status] ?? 'bg-secondary';

        return "<span class='badge {$statusClass}'>{$statusText}</span>";
    }

    public function getExperienceTextAttribute()
    {
        if ($this->experience_years == 0) {
            return 'New';
        } elseif ($this->experience_years == 1) {
            return '1 year';
        } else {
            return "{$this->experience_years} years";
        }
    }
}
