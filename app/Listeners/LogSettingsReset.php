<?php

namespace App\Listeners;

use App\Events\Settings\SettingsResetEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class LogSettingsReset implements ShouldQueue
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
    public function handle(SettingsResetEvent $event): void
    {
        try {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'reset_idle_settings_to_defaults',
                'subject_type' => 'App\Models\IdleSetting',
                'subject_id' => null,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log settings reset activity', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
