<?php

namespace App\Events\Settings;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\IdleSetting;

class GlobalSettingsUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $settings;
    public $ipAddress;
    public $device;
    public $browser;
    public $oldSettings;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        IdleSetting $settings,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null,
        ?IdleSetting $oldSettings = null
    ) {
        $this->user = $user;
        $this->settings = $settings;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
        $this->oldSettings = $oldSettings;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('settings-updates'),
        ];
    }
}

