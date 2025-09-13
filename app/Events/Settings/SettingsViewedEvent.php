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

class SettingsViewedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $ipAddress;
    public $device;
    public $browser;
    public $settingsType;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null,
        string $settingsType = 'general'
    ) {
        $this->user = $user;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
        $this->settingsType = $settingsType;
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

