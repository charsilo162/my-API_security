<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
      use HasFactory;
      protected $fillable = [
            'uuid',
            'client_id',
            'title',
            'category',
            'description',
            'start_date',
            'end_date',
            'required_staff_count',
            'status',
            'admin_remarks',
        ];

  protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function client() { return $this->belongsTo(Client::class); }

    // Relationship to the security guards assigned
    // public function assignedStaff() 
    // {
    //     return $this->belongsToMany(Employee::class, 'service_request_assignments');
    // }

    public function assignedStaff() 
{
    return $this->belongsToMany(Employee::class, 'service_request_assignments')
                ->withPivot('confirmed_at') // <--- THIS IS THE MISSING PIECE
                ->withTimestamps();
}

    public function getProgressAttribute()
        {
            return match($this->status) {
                'pending' => 20,
                'approved' => 40,
                'assigned' => 60,
                'active' => 80,
                'completed' => 100,
                default => 0
            };
        }
}
