<?php

namespace App\Listeners;

use App\Events\EmployeeDeletedEvent;
use App\Models\ActivityLog;

class LogEmployeeDeleted
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
    public function handle(EmployeeDeletedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'delete_employee',
            subjectType: 'App\Models\Employee',
            subjectId: $event->employee->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
