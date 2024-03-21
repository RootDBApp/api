<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\ReportAndDataViewEvent;
use App\Models\ReportDataView as ReportDataViewModel;
use JetBrains\PhpStorm\Pure;

class ReportDataViewResultsReceived extends ReportDataView
{
    private array $results;

    #[Pure]
    public function __construct(ExecReportInfo $execReportInfo, ReportDataViewModel $reportDataView, array $results)
    {
        parent::__construct($execReportInfo, $reportDataView);
        $this->results = $results;
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
            $this->results,
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
