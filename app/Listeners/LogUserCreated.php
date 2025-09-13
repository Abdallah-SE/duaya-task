<?php

namespace App\Listeners;

use App\Events\UserCreatedEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogUserCreated
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
    public function handle(UserCreatedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'create_user',
            subjectType: 'App\Models\User',
            subjectId: $event->user->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}