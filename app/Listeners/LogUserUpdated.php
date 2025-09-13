<?php

namespace App\Listeners;

use App\Events\UserUpdatedEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogUserUpdated
{
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
    public function handle(UserUpdatedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'update_user',
            subjectType: 'App\Models\User',
            subjectId: $event->user->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}