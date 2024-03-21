<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APIExceptionFatalError implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    private int $organization_id;
    private string $message;

    public function __construct(int $organization_id, string $message)
    {
        $this->organization_id = $organization_id;
        $this->message = $message;
    }


    public function broadcastOn(): Channel|array
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return [
            "message" => $this->message
        ];
    }
}
