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

use App\Models\ReportCacheStatus;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReportCacheUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $report_id;
    private string $websocket_id;
    private ReportCacheStatus $cache_status;


    public function __construct(int $report_id, string $websocket_id, ReportCacheStatus $cache_status)
    {
        $this->report_id = $report_id;
        $this->websocket_id = $websocket_id;
        $this->cache_status = $cache_status;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new PrivateChannel('user.' . $this->websocket_id);

    }

    public function broadcastWith(): array
    {
        return ($this->cache_status)->toArray();
    }
}
