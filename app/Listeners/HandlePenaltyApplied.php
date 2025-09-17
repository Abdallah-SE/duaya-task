<?php

namespace App\Listeners;

use App\Events\PenaltyAppliedEvent;
use App\Models\ActivityLog;
use Illuminate\Events\Attributes\AsEventListener;
use Illuminate\Support\Facades\Log;

#[AsEventListener]
class HandlePenaltyApplied
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
    public function handle(PenaltyAppliedEvent $event): void
    {
        try {
            // Log the penalty applied activity
            ActivityLog::logActivity(
                userId: $event->user->id,
                action: 'penalty_applied',
                subjectType: 'App\Models\Penalty',
                subjectId: $event->penalty->id
            );

            Log::info('Penalty applied event processed', [
                'user_id' => $event->user->id,
                'penalty_id' => $event->penalty->id,
                'reason' => $event->reason
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process penalty applied event', [
                'user_id' => $event->user->id,
                'penalty_id' => $event->penalty->id,
                'reason' => $event->reason,
                'error' => $e->getMessage()
            ]);
        }
    }

}
