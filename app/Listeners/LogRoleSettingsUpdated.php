<?php

namespace App\Listeners;

use App\Events\Settings\RoleSettingsUpdatedEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogRoleSettingsUpdated implements ShouldQueue
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
    public function handle(RoleSettingsUpdatedEvent $event): void
    {
        try {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'update_role_idle_settings',
                'subject_type' => 'App\Models\RoleSetting',
                'subject_id' => null,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log role settings updated activity', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
