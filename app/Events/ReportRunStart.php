<?php

namespace App\Events;

use App\Models\ReportAndDataViewEvent;

class ReportRunStart extends Report
{
    public function broadcastWith(): array
    {
        return (new ReportAndDataViewEvent(
            $this->report->id,
            $this->execReportInfo->instanceId,
            $this->report->name,
            0,
            '',
            [],
            [],
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
