<?php

namespace App\Events;

use App\Models\CacheJob;
use App\Tools\Tools;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheJobUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $organization_id;
    private CacheJob $cacheJob;

    public function __construct(int $organization_id, CacheJob $cacheJob)
    {
        $this->organization_id = $organization_id;
        $this->cacheJob = $cacheJob;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'                      => $this->cacheJob->id,
            'running'                 => $this->cacheJob->running,
            'last_run'                => $this->cacheJob->last_run,
            'last_run_duration'       => $this->cacheJob->last_run_duration,
            'last_num_parameter_sets' => $this->cacheJob->last_num_parameter_sets,
            'last_cache_size'         => Tools::formatBytes($this->cacheJob->last_cache_size_b),
        ];
    }
}
