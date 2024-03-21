<?php

namespace App\Events;

use App\Http\Resources\ReportParameterInput as ReportParameterInputResource;
use App\Models\ReportParameterInput;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class APICacheReportParameterInputsUpdated implements ShouldBroadcast
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
        request()->request->add(['parameter-default-value' => 1]);

        return (ReportParameterInputResource::collection(
            (new ReportParameterInput)
                ->with('confConnector')
                ->with('parameterInputType')
                ->with('parameterInputDataType')
                ->whereRelation('confConnector', 'organization_id', '=', $this->organization_id)
                ->orderBy('name')
                ->get()
        ))->resolve();
    }
}
