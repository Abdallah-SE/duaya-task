<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Employee;

class EmployeeCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $employee;
    public $userId;
    public $ipAddress;
    public $device;
    public $browser;

    /**
     * Create a new event instance.
     */
    public function __construct(Employee $employee, $userId, $ipAddress, $device, $browser)
    {
        $this->employee = $employee;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->device = $device;
        $this->browser = $browser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
