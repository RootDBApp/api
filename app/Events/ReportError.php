<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\Report as ReportModel;
use App\Models\ReportAndDataViewEvent;
use Illuminate\Support\Facades\Log;

class ReportError extends Report
{
    protected string $error;

    public function __construct(ExecReportInfo $execReportInfo, ReportModel $report, string $exception)
    {
        parent::__construct($execReportInfo, $report);
        $this->error = $exception;
    }

    public function broadcastWith(): array
    {
        Log::debug('ReportError [report ID ' . $this->report->id . ' ]  ' . PHP_EOL, [$this->error]);

        return (new ReportAndDataViewEvent(
            $this->report->id,
            $this->execReportInfo->instanceId,
            $this->report->name,
            0,
            '',
            [$this->error],
            [],
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
