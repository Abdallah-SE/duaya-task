<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\IdleSetting;

class GlobalIdleSettingsUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $settings;
    public $userId;
    public $ipAddress;
    public $device;
    public $browser;
    public $oldSettings;

    /**
     * Create a new event instance.
     */
    public function __construct(IdleSetting $settings, $userId, $ipAddress, $device, $browser, $oldSettings = null)
    {
        $this->settings = $settings;
        $this->userId = $userId;
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

