<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'owner_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'map_embed_url',
        'description',
        'image',
        'opening_time',
        'closing_time',
        'working_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_active' => 'boolean',
        'opening_time' => 'datetime',
        'closing_time' => 'datetime',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function slotSettings()
    {
        return $this->hasMany(SlotSetting::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function technicians()
    {
        return $this->hasMany(Technician::class);
    }

    public function ratings()
    {
        return $this->hasMany(ShopRating::class);
    }

    public function getAverageRatingAttribute()
    {
        if (!array_key_exists('ratings_avg_rating', $this->attributes)) {
            return round((float) $this->ratings()->avg('rating'), 2) ?: null;
        }
        return $this->attributes['ratings_avg_rating'] !== null
            ? round((float) $this->attributes['ratings_avg_rating'], 2)
            : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('uploads/' . $this->image) : asset('images/default-shop.png');
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip_code,
            $this->country
        ]);
        return implode(', ', $parts);
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

    public function getOperatingHoursAttribute()
    {
        if (!$this->opening_time || !$this->closing_time) {
            return 'Hours not set';
        }

        return $this->opening_time->format('g:i A') . ' - ' . $this->closing_time->format('g:i A');
    }

    public function getMapEmbedUrlAttribute($value)
    {
        // Return the original value, but ensure it's not an empty string
        return $value && trim($value) !== '' ? $value : null;
    }

    public function hasMapEmbedUrl()
    {
        return !empty($this->attributes['map_embed_url']) && trim($this->attributes['map_embed_url']) !== '';
    }

    // Methods
    public function isOpenOnDay($dayOfWeek)
    {
        return $this->working_days && in_array($dayOfWeek, $this->working_days);
    }

    public function isCurrentlyOpen()
    {
        $now = \Carbon\Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
        $currentTime = $now->format('H:i:s');

        // Convert Sunday (0) to 7 for our system (1-7 = Mon-Sun)
        $dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek;

        // Convert opening and closing times to time format for comparison
        $openingTime = $this->opening_time->format('H:i:s');
        $closingTime = $this->closing_time->format('H:i:s');

        return $this->is_active && 
               $this->isOpenOnDay($dayOfWeek) &&
               $currentTime >= $openingTime &&
               $currentTime <= $closingTime;
    }
}
