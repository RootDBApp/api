<?php

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\Report as ReportModel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Report implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Queueable;

    protected ReportModel $report;
    protected ExecReportInfo $execReportInfo;

    public function __construct(ExecReportInfo $execReportInfo, ReportModel $report)
    {
        $this->execReportInfo = $execReportInfo;
        $this->report = $report;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        if (!$this->execReportInfo->isAuthenticatedUser()) {

            return new Channel('user.public.' . $this->execReportInfo->websocketPublicUserId);
        }
        return new PrivateChannel('user.' . $this->execReportInfo->currentOrganizationLoggedUser->web_socket_session_id);
    }
}
