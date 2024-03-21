<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\ReportAndDataViewEvent;
use App\Models\ReportDataView as ReportDataViewModel;

class ReportDataViewRunEnd extends ReportDataView
{
    private float $ms_elapsed;

    public function __construct(ExecReportInfo $execReportInfo, ReportDataViewModel $reportDataView, float $ms_elapsed)
    {
        parent::__construct($execReportInfo, $reportDataView);
        $this->ms_elapsed = $ms_elapsed;
    }

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
            $this->ms_elapsed,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
