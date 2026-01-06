<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

use App\Models\User;
use App\Models\Category;


class DatabaseSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

 public function run(): void
{
    // Admins
    // \App\Models\User::factory()->admin()->count(2)->create();

    // // Employees
    // \App\Models\User::factory()->employee()->count(10)->create();
    // \App\Models\Employee::factory()->count(10)->create();

    // Clients
    //\App\Models\User::factory()->client()->count(5)->create();
    \App\Models\Client::factory()->count(5)->create();

    // Service Requests
    \App\Models\ServiceRequest::factory()->count(10)->create();

    // Assign employees to requests
    \App\Models\ServiceRequestAssignment::factory()->count(15)->create();
}

}
