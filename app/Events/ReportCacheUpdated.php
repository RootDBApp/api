<?php

namespace App\Events;

use App\Models\ReportCacheStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCacheUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $report_id;
    private string $websocket_id;
    private ReportCacheStatus $cache_status;


    public function __construct(int $report_id, string $websocket_id, ReportCacheStatus $cache_status)
    {
        $this->report_id = $report_id;
        $this->websocket_id = $websocket_id;
        $this->cache_status = $cache_status;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new PrivateChannel('user.' . $this->websocket_id);

    }

    public function broadcastWith(): array
    {
        return ($this->cache_status)->toArray();
    }
}
