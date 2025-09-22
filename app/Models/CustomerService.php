<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_id', 'subject', 'message', 'category', 'priority', 'status',
        'admin_reply', 'assigned_admin_id', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function assignedAdmin()
    {
        return $this->belongsTo(Admin::class, 'assigned_admin_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_admin_id');
    }

    // Accessors
    public function getPriorityColorAttribute()
    {
        // Bootstrap badge background classes
        return [
            'low' => 'bg-success',
            'medium' => 'bg-info',
            'high' => 'bg-warning text-dark',
            'urgent' => 'bg-danger',
        ][$this->priority] ?? 'bg-secondary';
    }

    public function getStatusColorAttribute()
    {
        return [
            'open' => 'bg-primary',
            'in_progress' => 'bg-warning text-dark',
            'resolved' => 'bg-success',
            'closed' => 'bg-secondary',
        ][$this->status] ?? 'bg-secondary';
    }

    public function getCategoryColorAttribute()
    {
        return [
            'booking' => 'bg-secondary',
            'shop' => 'bg-dark',
            'payment' => 'bg-success',
            'appointment' => 'bg-primary',
            'other' => 'bg-secondary',
        ][$this->category] ?? 'bg-secondary';
    }

    // Methods
    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function assignToAdmin($adminId)
    {
        $this->update(['assigned_admin_id' => $adminId]);
    }
}
