<?php

namespace App\Listeners;

use App\Events\UserActivityDeletedEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserActivityDeleted implements ShouldQueue
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
    public function handle(UserActivityDeletedEvent $event): void
    {
        try {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => $event->getAction(),
                'subject_type' => $event->getModelClass(),
                'subject_id' => $event->model->id,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);

            Log::info('Model deleted activity logged', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'action' => $event->getAction(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log model deleted activity', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
