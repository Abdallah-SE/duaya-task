<?php

namespace App\Traits;

use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
use App\Events\UserActivityViewedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait TracksUserActivity
{
    /**
     * Boot the trait and set up model event listeners.
     */
    protected static function bootTracksUserActivity()
    {
        static::created(function (Model $model) {
            if (Auth::check()) {
                event(new UserActivityCreatedEvent($model, Auth::user()));
            }
        });

        static::updated(function (Model $model) {
            if (Auth::check()) {
                $originalData = $model->getOriginal();
                $updatedData = $model->getChanges();
                
                event(new UserActivityUpdatedEvent(
                    $model, 
                    Auth::user(), 
                    $originalData, 
                    $updatedData
                ));
            }
        });

        static::deleted(function (Model $model) {
            if (Auth::check()) {
                event(new UserActivityDeletedEvent($model, Auth::user(), $model->getOriginal()));
            }
        });
    }

    /**
     * Log a view event for this model.
     */
    public function logView(User $user, string $viewType = 'view', array $metadata = []): void
    {
        event(new UserActivityViewedEvent($this, $user, $viewType, $metadata));
    }

    /**
     * Log a custom activity for this model.
     */
    public function logActivity(User $user, string $action, array $metadata = []): void
    {
        event(new \App\Events\UserActivityEvent(
            $user,
            $action,
            get_class($this),
            $this->id,
            request()->ip(),
            $this->getDeviceInfo(),
            $this->getBrowserInfo(),
            $metadata
        ));
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
