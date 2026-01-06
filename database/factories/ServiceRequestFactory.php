<?php

namespace Database\Factories;

use App\Models\ServiceRequest;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceRequestFactory extends Factory
{
    protected $model = ServiceRequest::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 days', '+10 days');

        return [
            'uuid' => Str::uuid(),
            'client_id' => Client::inRandomOrder()->value('id'),
            'title' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement(['personal', 'event', 'business', 'vip']),
            'description' => $this->faker->paragraph(),
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+8 hours'),
            'required_staff_count' => rand(1, 5),
            'status' => $this->faker->randomElement(['pending', 'approved', 'assigned']),
            'admin_remarks' => null,
        ];
    }
}
