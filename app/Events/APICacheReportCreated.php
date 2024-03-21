<?php


namespace App\Events;

use App\Http\Resources\LightReport as ReportResource;

class APICacheReportCreated extends APICacheReport
{
    public function broadcastWith(): array
    {
        return (new ReportResource($this->report))->resolve();
    }
}
