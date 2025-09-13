<?php

namespace App\Listeners;

use App\Events\RoleMonitoringToggledEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogRoleMonitoringToggled
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
    public function handle(RoleMonitoringToggledEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'update_role_setting',
            subjectType: 'App\Models\RoleSetting',
            subjectId: $event->roleSetting->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
