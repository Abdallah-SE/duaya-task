<?php

namespace App\Traits;

use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
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
}
