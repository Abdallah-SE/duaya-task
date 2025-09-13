<?php

namespace App\Listeners;

use App\Events\Settings\GlobalSettingsUpdatedEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogGlobalSettingsUpdated implements ShouldQueue
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
    public function handle(GlobalSettingsUpdatedEvent $event): void
    {
        try {
            $changes = [];
            
            // Track what changed if old settings provided
            if ($event->oldSettings) {
                if ($event->oldSettings->idle_timeout != $event->settings->idle_timeout) {
                    $changes['idle_timeout'] = [
                        'from' => $event->oldSettings->idle_timeout,
                        'to' => $event->settings->idle_timeout
                    ];
                }
                if ($event->oldSettings->max_idle_warnings != $event->settings->max_idle_warnings) {
                    $changes['max_idle_warnings'] = [
                        'from' => $event->oldSettings->max_idle_warnings,
                        'to' => $event->settings->max_idle_warnings
                    ];
                }
            }

            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'update_global_idle_settings',
                'subject_type' => 'App\Models\IdleSetting',
                'subject_id' => $event->settings->id,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);

            Log::info('Global settings updated activity logged', [
                'user_id' => $event->user->id,
                'settings_id' => $event->settings->id,
                'changes' => $changes,
                'new_timeout' => $event->settings->idle_timeout,
                'new_max_warnings' => $event->settings->max_idle_warnings,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log global settings updated activity', [
                'user_id' => $event->user->id,
                'settings_id' => $event->settings->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
