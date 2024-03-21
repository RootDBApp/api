<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\Report as ReportModel;
use App\Models\ReportAndDataViewEvent;

class ReportRunEnd extends Report
{
    private float $ms_elapsed;

    public function __construct(ExecReportInfo $execReportInfo, ReportModel $report, float $ms_elapsed)
    {
        parent::__construct($execReportInfo, $report);
        $this->ms_elapsed = $ms_elapsed;
    }

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
            $this->ms_elapsed,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
