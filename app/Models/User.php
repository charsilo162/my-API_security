<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory,HasApiTokens, Notifiable;
protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'phone',
    'address',
    'bio',
    'date_of_birth',
    'gender',
    'type',
    'photo_path',
    'email_verified_at',
    'password',
];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
/**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }
    public function employee()
        {
            return $this->hasOne(Employee::class);
        }
public function client()
{
    return $this->hasOne(Client::class);
}

/**
 * Accessor for Photo URL
 * Usage: $user->photo_url
 */
public function getPhotoUrlAttribute()
{
    if ($this->photo && Storage::disk('public')->exists($this->photo)) {
        return asset('storage/' . $this->photo);
    }

    // Return a default avatar if no photo exists
    return asset('storage/profile_photos/img.jpg'); 
}

    public function getDisplayNameAttribute()
    {
        if ($this->type === 'client' && $this->client) {
            return $this->client->company_name;
        }

        return "{$this->first_name} {$this->last_name}";
    }

}