<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

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
            $path = strpos($this->profile_picture, 'admin-profiles/') === 0 ||
                    strpos($this->profile_picture, 'profile-pictures/') === 0
                ? $this->profile_picture
                : ('admin-profiles/' . $this->profile_picture);
            return Storage::disk('public')->url($path);
        }
        return asset('images/default-profile.png');
    }
} 