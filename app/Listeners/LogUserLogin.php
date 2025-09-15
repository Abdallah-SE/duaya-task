<?php

namespace App\Listeners;

use App\Events\UserLoginEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\Attributes\AsEventListener;
use Illuminate\Support\Facades\Log;

#[AsEventListener]
class LogUserLogin
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
    public function handle(UserLoginEvent $event): void
    {
        try {
            ActivityLog::logActivity(
                userId: $event->user->id,
                action: $event->loginType,
                subjectType: 'App\Models\User',
                subjectId: $event->user->id,
                ipAddress: $event->ipAddress,
                device: $event->device,
                browser: $event->browser
            );

            Log::info('User login activity logged', [
                'user_id' => $event->user->id,
                'login_type' => $event->loginType,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log user login activity', [
                'user_id' => $event->user->id,
                'login_type' => $event->loginType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
