<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\ReportAndDataViewEvent;
use App\Models\ReportDataView as ReportDataViewModel;
use Illuminate\Support\Facades\Log;

class ReportDataViewError extends ReportDataView
{
    protected string $error;

    public function __construct(ExecReportInfo $execReportInfo, ReportDataViewModel $reportDataView, string $exception)
    {
        parent::__construct($execReportInfo, $reportDataView);
        $this->error = $exception;
    }

    public function broadcastWith(): array
    {
        Log::debug('ReportDataViewError [data view ID ' . $this->reportDataView->id . ' ]  ' . PHP_EOL, [$this->error]);

        return (new ReportAndDataViewEvent(
            $this->reportDataView->report_id,
            $this->execReportInfo->instanceId,
            $this->reportDataView->report->name,
            $this->reportDataView->id,
            $this->reportDataView->name,
            [$this->error],
            [],
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
