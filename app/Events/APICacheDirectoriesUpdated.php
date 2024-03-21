<?php

namespace App\Events;

use App\Http\Resources\Directory as DirectoryResource;
use App\Models\Directory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APICacheDirectoriesUpdated implements ShouldBroadcast
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
        return (DirectoryResource::collection(
            (new Directory())
                ->where('organization_id', '=', $this->organization_id)
                ->get()
        ))->resolve();
    }
}
