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

class UserLogoutEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $ipAddress;
    public string $device;
    public string $browser;
    public array $metadata;
    public string $logoutType;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user,
        string $logoutType,
        ?string $ipAddress = null,
        ?string $device = null,
        ?string $browser = null,
        array $metadata = []
    ) {
        $this->user = $user;
        $this->logoutType = $logoutType;
        $this->ipAddress = $ipAddress ?? request()->ip();
        $this->device = $device ?? $this->getDeviceInfo();
        $this->browser = $browser ?? $this->getBrowserInfo();
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
