<?php

namespace App\Listeners;

use App\Events\EmployeeUpdatedEvent;
use App\Models\ActivityLog;

class LogEmployeeUpdated
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
    public function handle(EmployeeUpdatedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'update_employee',
            subjectType: 'App\Models\Employee',
            subjectId: $event->employee->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
