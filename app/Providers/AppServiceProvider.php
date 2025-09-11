<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
    }
}
