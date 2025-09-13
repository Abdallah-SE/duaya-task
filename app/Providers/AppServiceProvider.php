<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use App\Models\User;
use App\Models\Employee;
use App\Policies\UserPolicy;
use App\Policies\EmployeePolicy;
use App\Events\IdleWarningEvent;
use App\Events\PenaltyAppliedEvent;
use App\Events\UserCreatedEvent;
use App\Events\UserUpdatedEvent;
use App\Events\UserDeletedEvent;
use App\Events\EmployeeCreatedEvent;
use App\Events\EmployeeUpdatedEvent;
use App\Events\EmployeeDeletedEvent;
use App\Listeners\HandleIdleWarning;
use App\Listeners\HandlePenaltyApplied;
use App\Listeners\LogUserCreated;
use App\Listeners\LogUserUpdated;
use App\Listeners\LogUserDeleted;
use App\Listeners\LogEmployeeCreated;
use App\Listeners\LogEmployeeUpdated;
use App\Listeners\LogEmployeeDeleted;

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

        // Events and listeners are automatically discovered via #[AsEventListener] attributes
        // No manual registration needed when event discovery is enabled
    }
}
