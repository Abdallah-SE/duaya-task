<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\RoleSetting;

class RoleMonitoringToggledEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $roleSetting;
    public $userId;
    public $ipAddress;
    public $device;
    public $browser;
    public $enabled;

    /**
     * Create a new event instance.
     */
    public function __construct(RoleSetting $roleSetting, $userId, $ipAddress, $device, $browser, $enabled)
    {
        $this->roleSetting = $roleSetting;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
        $this->enabled = $enabled;
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

