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
use App\Models\RoleSetting;

class RoleSettingsUpdatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $roleSettings;
    public $ipAddress;
    public $device;
    public $browser;
    public $updatedRoles;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        array $roleSettings,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null,
        array $updatedRoles = []
    ) {
        $this->user = $user;
        $this->roleSettings = $roleSettings;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
        $this->updatedRoles = $updatedRoles;
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

