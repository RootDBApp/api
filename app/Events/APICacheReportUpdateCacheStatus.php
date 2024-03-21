<?php

namespace App\Events;

use App\Models\ReportCacheStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class APICacheReportUpdateCacheStatus implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $organization_id;
    /** @var ReportCacheStatus[] $cache_status */
    private array $cache_status;

    /**
     * @param int $organization_id
     * @param ReportCacheStatus[] $cache_status
     */
    public function __construct(int $organization_id, array $cache_status)
    {
        $this->organization_id = $organization_id;
        $this->cache_status = $cache_status;
    }

    public function broadcastOn(): Channel|array
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return $this->cache_status;
    }
}
