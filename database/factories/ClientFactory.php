<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::where('type', 'client')->inRandomOrder()->value('id'),
            'uuid' => Str::uuid(),
            'company_name' => $this->faker->company(),
            'industry' => $this->faker->randomElement(['Logistics', 'Construction', 'Hospitality']),
            'address' => $this->faker->address(),
            'contact_phone' => $this->faker->phoneNumber(),
            'registration_number' => strtoupper(Str::random(10)),
        ];
    }
}
