<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\Employee;
use App\Policies\BlogPostPolicy;
use App\Policies\EmployeePolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      Gate::policy(BlogPost::class, BlogPostPolicy::class);
      Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
