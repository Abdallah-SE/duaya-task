<?php

namespace App\Listeners;

use App\Events\UserActivityViewedEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserActivityViewed implements ShouldQueue
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
    public function handle(UserActivityViewedEvent $event): void
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

            Log::info('Model viewed activity logged', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'action' => $event->getAction(),
                'view_type' => $event->viewType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log model viewed activity', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
