<?php

namespace App\Events;

use App\Http\Resources\PrimeReactTree as PrimeReactTreeResource;
use App\Models\Directory;
use App\Tools\PrimeReactTreeDirectoryTools;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APICachePrimeReactTreeDirectoriesUpdatedEvent implements ShouldBroadcast
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
        $primeReactTree = (new PrimeReactTreeDirectoryTools((new Directory)->where('organization_id', '=', $this->organization_id)->get()))->getPrimeReactTree();
        return (PrimeReactTreeResource::collection($primeReactTree->all()))->resolve();
    }
}
