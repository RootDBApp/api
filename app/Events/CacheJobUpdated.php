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

use App\Models\CacheJob;
use App\Tools\Tools;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CacheJobUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $organization_id;
    private CacheJob $cacheJob;

    public function __construct(int $organization_id, CacheJob $cacheJob)
    {
        $this->organization_id = $organization_id;
        $this->cacheJob = $cacheJob;
    }

    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new Channel('organization.' . $this->organization_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'                      => $this->cacheJob->id,
            'running'                 => $this->cacheJob->running,
            'last_run'                => $this->cacheJob->last_run,
            'last_run_duration'       => $this->cacheJob->last_run_duration,
            'last_num_parameter_sets' => $this->cacheJob->last_num_parameter_sets,
            'last_cache_size'         => Tools::formatBytes($this->cacheJob->last_cache_size_b),
        ];
    }
}
