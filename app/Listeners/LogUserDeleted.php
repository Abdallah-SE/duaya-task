<?php

namespace App\Listeners;

use App\Events\UserDeletedEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogUserDeleted
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
    public function handle(UserDeletedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'delete_user',
            subjectType: 'App\Models\User',
            subjectId: $event->user->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}