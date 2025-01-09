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

use App\Models\ReportDataView;
use App\Models\ReportDataViewJs;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportDataViewJsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $web_socket_session_id;
    private ReportDataViewJs $reportDataViewJs;

    public function __construct(string $web_socket_session_id, ReportDataViewJs $reportDataViewJs)
    {
        $this->web_socket_session_id = $web_socket_session_id;
        $this->reportDataViewJs = $reportDataViewJs;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->web_socket_session_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'                         => $this->reportDataViewJs->id,
            'report_data_view_id'        => $this->reportDataViewJs->report_data_view_id,
            'report_data_view'           => [
                'report_id' => $this->reportDataViewJs->reportDataView->report_id,
            ],
            'json_runtime_configuration' => $this->reportDataViewJs->json_runtime_configuration,
        ];
    }
}
