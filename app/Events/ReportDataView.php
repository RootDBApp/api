<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
