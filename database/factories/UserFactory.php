<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'email'      => $this->faker->unique()->safeEmail(),
            'phone'      => $this->faker->optional()->phoneNumber(),
            'address'    => $this->faker->optional()->address(),
            'bio'        => $this->faker->optional()->paragraph(),
            'date_of_birth' => $this->faker->optional()->date(),
            'gender'     => $this->faker->optional()->randomElement(['male', 'female', 'other']),
            'type'       => $this->faker->randomElement(['admin', 'employee', 'client']),
            'photo_path' => null,
            'email_verified_at' => now(),
            'password'   => Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn () => ['type' => 'admin']);
    }

    public function employee()
    {
        return $this->state(fn () => ['type' => 'employee']);
    }

    public function client()
    {
        return $this->state(fn () => ['type' => 'client']);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static 
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
