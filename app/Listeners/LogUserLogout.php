<?php

namespace App\Listeners;

use App\Events\UserLogoutEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\Attributes\AsEventListener;
use Illuminate\Support\Facades\Log;

#[AsEventListener]
class LogUserLogout
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
    public function handle(UserLogoutEvent $event): void
    {
        try {
            ActivityLog::logActivity(
                userId: $event->user->id,
                action: 'logout_user',
                subjectType: 'App\Models\User',
                subjectId: $event->user->id,
                ipAddress: $event->ipAddress,
                device: $event->device,
                browser: $event->browser
            );

            Log::info('User logout activity logged', [
                'user_id' => $event->user->id,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log user logout activity', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
