<?php

namespace App\Listeners;

use App\Events\GlobalIdleSettingsUpdatedEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogGlobalIdleSettingsUpdated
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
    public function handle(GlobalIdleSettingsUpdatedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'update_idle_setting',
            subjectType: 'App\Models\IdleSetting',
            subjectId: $event->settings->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
