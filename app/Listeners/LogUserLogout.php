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
            // Determine the action based on logout type and context
            $isAutoLogout = request()->is('idle-monitoring/handle-warning');
            
            if ($isAutoLogout) {
                $action = 'auto_logout_employee_user';
            } else {
                // Use the logout type from the event (logout_admin_user, logout_employee_user, auto_logout_employee_user, etc.)
                $action = $event->logoutType;
            }
            
            ActivityLog::logActivity(
                userId: $event->user->id,
                action: $action,
                subjectType: 'App\Models\User',
                subjectId: $event->user->id,
                ipAddress: $event->ipAddress,
                device: $event->device,
                browser: $event->browser
            );

            Log::info('User logout activity logged', [
                'user_id' => $event->user->id,
                'action' => $action,
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
