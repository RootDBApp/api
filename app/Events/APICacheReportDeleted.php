<?php

namespace App\Events;

use App\Http\Resources\LightReport as ReportResource;

class APICacheReportDeleted extends APICacheReport
{
    public function broadcastWith(): array
    {
        return (new ReportResource($this->report))->resolve();
    }
}
