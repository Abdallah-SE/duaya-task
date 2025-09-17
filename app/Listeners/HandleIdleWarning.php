<?php

namespace App\Listeners;

use App\Events\IdleWarningEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;
use Illuminate\Support\Facades\Log;

#[AsEventListener]
class HandleIdleWarning
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
    public function handle(IdleWarningEvent $event): void
    {
        try {
            // Log the idle warning activity
            ActivityLog::logActivity(
                userId: $event->user->id,
                action: 'idle_warning',
                subjectType: 'App\Models\IdleSession',
                subjectId: $event->sessionId
            );

            Log::info('Idle warning event processed', [
                'user_id' => $event->user->id,
                'warning_count' => $event->warningCount,
                'session_id' => $event->sessionId,
                'max_warnings' => $event->maxWarnings
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process idle warning event', [
                'user_id' => $event->user->id,
                'warning_count' => $event->warningCount,
                'session_id' => $event->sessionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get warning type based on warning count.
     */
    private function getWarningType(int $warningCount): string
    {
        return match ($warningCount) {
            1 => 'alert',
            2 => 'warning',
            3 => 'final_warning',
            default => 'unknown'
        };
    }

}
