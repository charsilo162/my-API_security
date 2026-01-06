<?php

namespace Database\Factories;

use App\Models\ServiceRequestAssignment;
use App\Models\ServiceRequest;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceRequestAssignmentFactory extends Factory
{
    protected $model = ServiceRequestAssignment::class;

    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::inRandomOrder()->value('id'),
            'employee_id' => Employee::inRandomOrder()->value('id'),
            'assigned_at' => now(),
        ];
    }
}
