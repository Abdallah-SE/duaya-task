<?php

namespace App\Listeners;

use App\Events\UserActivityUpdatedEvent;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserActivityUpdated implements ShouldQueue
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
    public function handle(UserActivityUpdatedEvent $event): void
    {
        try {
            $changedAttributes = $event->getChangedAttributes();
            
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => $event->getAction(),
                'subject_type' => $event->getModelClass(),
                'subject_id' => $event->model->id,
                'ip_address' => $event->ipAddress,
                'device' => $event->device,
                'browser' => $event->browser,
            ]);

            Log::info('Model updated activity logged', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'action' => $event->getAction(),
                'changed_attributes' => array_keys($changedAttributes),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log model updated activity', [
                'user_id' => $event->user->id,
                'model' => $event->getModelClass(),
                'model_id' => $event->model->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
