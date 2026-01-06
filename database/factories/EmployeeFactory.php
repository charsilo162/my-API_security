<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::where('type', 'employee')->inRandomOrder()->value('id'),
            'uuid' => Str::uuid(),
            'employee_code' => 'EMP-' . strtoupper(Str::random(6)),
            'designation' => $this->faker->randomElement(['Security Guard', 'Bouncer', 'Supervisor']),
            'department' => $this->faker->randomElement(['Operations', 'Field', 'Admin']),
            'joining_date' => $this->faker->date(),
            'account_holder_name' => $this->faker->name(),
            'account_number' => $this->faker->bankAccountNumber(),
            'bank_name' => $this->faker->company(),
            'branch_name' => $this->faker->city(),
            'routing_number' => $this->faker->numerify('########'),
            'swift_code' => strtoupper(Str::random(8)),
        ];
    }
}
