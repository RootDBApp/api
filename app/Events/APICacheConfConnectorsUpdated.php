<?php

namespace App\Events;

use App\Http\Resources\ConfConnector as ConfConnectorResource;
use App\Models\ConfConnector;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APICacheConfConnectorsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    private int $organization_id;

    public function __construct(int $organization_id)
    {
        $this->organization_id = $organization_id;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return (ConfConnectorResource::collection(
            (new ConfConnector())
                ->where('organization_id', '=', $this->organization_id)
                ->get()
        ))->resolve();
    }
}
