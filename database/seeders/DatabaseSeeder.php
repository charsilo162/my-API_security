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
        /*
        |--------------------------------------------------------------------------
        | 1. CORE TABLE DATA (ONLY 4 EACH)
        |--------------------------------------------------------------------------
        */

    User::factory(4)->create();
        // Category::factory(4)->create();

    }
}
