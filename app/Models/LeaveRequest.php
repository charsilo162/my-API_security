<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id', 'leave_type_id', 'start_date', 'end_date', 
        'total_days', 'reason', 'status', 'manager_remarks', 'approved_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => $model->uuid = (string) Str::uuid());
    }

    public function user() { return $this->belongsTo(User::class); }
    
    public function leaveType() { return $this->belongsTo(LeaveType::class); }
    
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function getRouteKeyName() { return 'uuid'; }
}