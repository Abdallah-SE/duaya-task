<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Employee;
use App\Policies\UserPolicy;
use App\Policies\EmployeePolicy;

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
        // Add roleSetting relationship to Spatie's Role model
        Role::resolveRelationUsing('roleSetting', function ($roleModel) {
            return $roleModel->hasOne(\App\Models\RoleSetting::class);
        });
        
        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
