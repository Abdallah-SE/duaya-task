<?php

namespace App\Listeners;

use App\Events\IdleWarningEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Events\Attributes\AsEventListener;

#[AsEventListener]
class HandleIdleWarning implements ShouldQueue
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
    public function handle(IdleWarningEvent $event): void
    {
        // Log the idle warning with descriptive action name
        $actionName = match($event->warningCount) {
            1 => 'create_idle_alert',
            2 => 'create_idle_warning', 
            3 => 'create_idle_logout',
            default => 'create_idle_warning_' . $event->warningCount
        };
        
        ActivityLog::logActivity(
            userId: $event->user->id,
            action: $actionName,
            subjectType: 'App\Models\IdleSession',
            subjectId: $event->sessionId,
            ipAddress: request()->ip(),
            device: $this->getDeviceInfo(),
            browser: $this->getBrowserInfo()
        );
    }

    /**
     * Get device information from request.
     */
    private function getDeviceInfo(): string
    {
        $userAgent = request()->userAgent();
        
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get browser information from request.
     */
    private function getBrowserInfo(): string
    {
        $userAgent = request()->userAgent();
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }
}
