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
use App\Models\IdleSession;

class IdleWarningEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $warningCount;
    public $maxWarnings;
    public $sessionId;
    public $timeoutSeconds;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        int $warningCount,
        int $maxWarnings,
        ?int $sessionId = null,
        int $timeoutSeconds = 5
    ) {
        $this->user = $user;
        $this->warningCount = $warningCount;
        $this->maxWarnings = $maxWarnings;
        $this->sessionId = $sessionId;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('idle-warnings.' . $this->user->id),
        ];
    }
}
