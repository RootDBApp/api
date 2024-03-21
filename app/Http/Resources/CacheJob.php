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

namespace App\Http\Resources;

use App\Http\Resources\CacheJobParameterSetConfig as CacheJobParameterSetConfigResource;
use App\Tools\Tools;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CacheJob */
class CacheJob extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                              => $this->id,
            'report_id'                       => $this->report_id,
            'frequency'                       => $this->frequency,
            'at_minute'                       => $this->at_minute,
            'at_time'                         => $this->at_time,
            'at_weekday'                      => $this->at_weekday,
            'at_day'                          => $this->at_day,
            'ttl'                             => $this->ttl,
            'activated'                       => $this->activated,
            'running'                         => $this->running,
            'last_run'                        => $this->last_run,
            'last_run_duration'               => $this->last_run_duration,
            'last_num_parameter_sets'         => $this->last_num_parameter_sets,
            'last_cache_size_b'               => $this->last_cache_size_b,
            'last_cache_size'                 => Tools::formatBytes($this->last_cache_size_b),
            'cache_job_parameter_set_configs' => CacheJobParameterSetConfigResource::collection($this->cacheJobParameterSetConfigs),
        ];
    }
}
