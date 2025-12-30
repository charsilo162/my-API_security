<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class Employee extends Model
{

    


    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'designation',
        'department',
        'joining_date',
        'account_holder_name',
        'account_number',
        'bank_name',
        'branch_name',
        'routing_number',
        'swift_code',
        'photo_path',
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    protected static function boot()
{
    parent::boot();
    static::creating(function ($model) {
        $model->uuid = (string) Str::uuid();
    });
}

// Ensure the API uses the UUID for route model binding
public function getRouteKeyName()
{
    return 'uuid';
}
    /**
     * Automatic Employee Code Generation
     */
    protected static function booted()
    {
        static::creating(function ($employee) {
            // Only generate if it's not already set manually
            if (empty($employee->employee_code)) {
                $latest = static::latest('id')->first();
                
                if (!$latest) {
                    $number = 1;
                } else {
                    // Extract numeric part from EMP000901 -> 901
                    $number = (int) preg_replace('/[^0-9]/', '', $latest->employee_code) + 1;
                }

                // Format: EMP + 6 digits (e.g., EMP000001)
                $employee->employee_code = 'EMP' . str_pad($number, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
