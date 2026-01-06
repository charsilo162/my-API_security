<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestAssignment extends Model
{
     use HasFactory;
     protected $fillable = [
            'service_request_id',
            'employee_id',
            'assigned_at',
        ];

}
