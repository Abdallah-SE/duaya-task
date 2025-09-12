<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserActivityDeletedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Model $model;
    public User $user;
    public array $originalData;
    public array $metadata;
    public string $ipAddress;
    public string $device;
    public string $browser;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Model $model,
        User $user,
        array $originalData = [],
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null
    ) {
        $this->model = $model;
        $this->user = $user;
        $this->originalData = $originalData;
        $this->metadata = $metadata;
        $this->ipAddress = $ipAddress ?? request()->ip();
        $this->device = $device ?? $this->getDeviceInfo();
        $this->browser = $browser ?? $this->getBrowserInfo();
    }

    /**
     * Get the model class name.
     */
    public function getModelClass(): string
    {
        return get_class($this->model);
    }

    /**
     * Get the model name for display.
     */
    public function getModelName(): string
    {
        return class_basename($this->model);
    }

    /**
     * Get the action name.
     */
    public function getAction(): string
    {
        return 'deleted_' . strtolower($this->getModelName());
    }

    /**
     * Get device information.
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
     * Get browser information.
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
