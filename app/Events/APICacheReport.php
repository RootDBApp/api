<?php

namespace App\Events;

use App\Models\Report;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class APICacheReport implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function broadcastOn(): Channel|array
    {
        return new Channel('organization.' . $this->report->organization_id);
    }
}
