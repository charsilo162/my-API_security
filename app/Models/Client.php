<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
        use HasFactory;
          protected $fillable = [
          'user_id',
          'uuid',
          'company_name',
          'industry',
          'address',
          'contact_phone',
          'registration_number',
          ];

    
    public function user() { return $this->belongsTo(User::class); }
    public function requests() { return $this->hasMany(ServiceRequest::class); }

     protected static function booted()
    {
        static::creating(function ($client) {
            // Only generate if it's not already set manually
            if (empty($client->registration_number )) {
                $latest = static::latest('id')->first();
                
                if (!$latest) {
                    $number = 1;
                } else {
                    // Extract numeric part from EMP000901 -> 901
                    $number = (int) preg_replace('/[^0-9]/', '', $latest->registration_number ) + 1;
                }

                // Format: EMP + 6 digits (e.g., EMP000001)
                $client->registration_number  = 'REF-' . str_pad($number, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
