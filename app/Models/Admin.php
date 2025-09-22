<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_picture',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isOwner(): bool
    {
        return ($this->role ?? 'admin') === 'owner';
    }

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            // Check if the image path already includes a directory prefix
            if (strpos($this->profile_picture, 'admin-profiles/') === 0 || 
                strpos($this->profile_picture, 'profile-pictures/') === 0) {
                return asset('uploads/' . $this->profile_picture);
            } else {
                return asset('uploads/admin-profiles/' . $this->profile_picture);
            }
        }
        return asset('images/default-profile.png');
    }
} 