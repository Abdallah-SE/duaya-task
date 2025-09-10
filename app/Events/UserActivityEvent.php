<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class UserActivityEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $action;
    public $subjectType;
    public $subjectId;
    public $ipAddress;
    public $device;
    public $browser;
    public $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null,
        array $metadata = []
    ) {
        $this->user = $user;
        $this->action = $action;
        $this->subjectType = $subjectType;
        $this->subjectId = $subjectId;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
        $this->metadata = $metadata;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user-activity.' . $this->user->id),
        ];
    }
}
