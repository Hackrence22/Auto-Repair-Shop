<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'profile_picture',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function getProfilePictureUrlAttribute()
    {
        if ($this->profile_picture) {
            // If already a full URL (e.g., Cloudinary secure URL), return as-is
            if (str_starts_with($this->profile_picture, 'http://') || str_starts_with($this->profile_picture, 'https://')) {
                return $this->profile_picture;
            }
            $path = strpos($this->profile_picture, 'profile-pictures/') === 0 ||
                    strpos($this->profile_picture, 'user-profiles/') === 0
                ? $this->profile_picture
                : ('profile-pictures/' . $this->profile_picture);
            return Storage::disk('public')->url($path);
        }
        if ($this->avatar) {
            return $this->avatar;
        }
        return asset('images/default-profile.png');
    }
}
