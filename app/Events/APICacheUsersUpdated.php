<?php

namespace App\Events;

use App\Tools\UserTools;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class APICacheUsersUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $organization_id;

    public function __construct(int $organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function broadcastOn(): Channel|array
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return UserTools::getUserCollection()->resolve();
    }
}
