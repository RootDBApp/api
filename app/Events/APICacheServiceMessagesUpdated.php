<?php

namespace App\Events;

use App\Http\Resources\ServiceMessage as ServiceMessageResource;
use App\Models\ServiceMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APICacheServiceMessagesUpdated implements ShouldBroadcast
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
        return (ServiceMessageResource::collection(
            (new ServiceMessage())
                ->with('organizations')
                ->whereRelation('organizations', 'organization_id', '=', $this->organization_id)
                ->orderBy('created_at')
                ->get()
        ))->resolve();
    }
}
