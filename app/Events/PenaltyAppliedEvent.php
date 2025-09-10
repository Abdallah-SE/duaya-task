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
use App\Models\Penalty;

class PenaltyAppliedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $penalty;
    public $reason;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        Penalty $penalty,
        string $reason
    ) {
        $this->user = $user;
        $this->penalty = $penalty;
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('penalties.' . $this->user->id),
            new PrivateChannel('admin-notifications'),
        ];
    }
}
