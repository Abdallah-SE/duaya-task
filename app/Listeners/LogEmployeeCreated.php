<?php

namespace App\Listeners;

use App\Events\EmployeeCreatedEvent;
use App\Models\ActivityLog;

class LogEmployeeCreated
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
    public function handle(EmployeeCreatedEvent $event): void
    {
        ActivityLog::logActivity(
            userId: $event->userId,
            action: 'create_employee',
            subjectType: 'App\Models\Employee',
            subjectId: $event->employee->id,
            ipAddress: $event->ipAddress,
            device: $event->device,
            browser: $event->browser
        );
    }
}
