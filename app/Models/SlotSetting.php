<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlotSetting extends Model
{
    protected $fillable = [
        'shop_id',
        'start_time',
        'end_time',
        'slots_per_hour',
        'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
} 