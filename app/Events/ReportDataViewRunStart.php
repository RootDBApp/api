<?php

namespace App\Events;

use App\Models\ReportAndDataViewEvent;

class ReportDataViewRunStart extends ReportDataView
{
    public function broadcastWith(): array
    {
        return (new ReportAndDataViewEvent(
            $this->reportDataView->report_id,
            $this->execReportInfo->instanceId,
            $this->reportDataView->report->name,
            $this->reportDataView->id,
            $this->reportDataView->name,
            [],
            [],
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
