<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\ReportDataView as ReportDataViewModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportDataView implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Queueable;

    protected ReportDataViewModel $reportDataView;
    protected ExecReportInfo $execReportInfo;

    public function __construct(ExecReportInfo $execReportInfo, ReportDataViewModel $reportDataView)
    {
        $this->execReportInfo = $execReportInfo;
        $this->reportDataView = $reportDataView;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        if (!$this->execReportInfo->isAuthenticatedUser()) {

            return new Channel('user.public.' . $this->execReportInfo->websocketPublicUserId);
        }

        return new PrivateChannel('user.' . $this->execReportInfo->currentOrganizationLoggedUser->web_socket_session_id);
    }
}
