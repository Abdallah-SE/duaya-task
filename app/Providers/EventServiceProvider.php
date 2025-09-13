<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\UserActivityEvent;
use App\Events\IdleWarningEvent;
use App\Events\PenaltyAppliedEvent;
use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
use App\Events\UserActivityViewedEvent;
use App\Listeners\LogUserActivity;
use App\Listeners\HandleIdleWarning;
use App\Listeners\HandlePenaltyApplied;
use App\Listeners\LogUserActivityCreated;
use App\Listeners\LogUserActivityUpdated;
use App\Listeners\LogUserActivityDeleted;
use App\Listeners\LogUserActivityViewed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserActivityEvent::class => [
            LogUserActivity::class,
        ],
        IdleWarningEvent::class => [
            HandleIdleWarning::class,
        ],
        PenaltyAppliedEvent::class => [
            HandlePenaltyApplied::class,
        ],
        UserActivityCreatedEvent::class => [
            LogUserActivityCreated::class,
        ],
        UserActivityUpdatedEvent::class => [
            LogUserActivityUpdated::class,
        ],
        UserActivityDeletedEvent::class => [
            LogUserActivityDeleted::class,
        ],
        UserActivityViewedEvent::class => [
            LogUserActivityViewed::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
