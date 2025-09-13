<?php

namespace App\Listeners;

use App\Events\UserActivityEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
class LogUserActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserActivityEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->user->id,
            action: $event->action,
            subjectType: $event->subjectType,
            subjectId: $event->subjectId,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
