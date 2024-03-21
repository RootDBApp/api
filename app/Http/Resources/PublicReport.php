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

use App\Http\Resources\PublicReportParameter as ReportParameterResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class PublicReport extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                 => $this->id,
            'instance_id'                        => $request->get('instanceId'),
            'name'                               => $this->name,
            'title'                              => $this->title,
            'description'                        => $this->description,
            'has_parameters'                     => $this->has_parameters,
            'parameters'                         => ReportParameterResource::collection($this->parameters),
            'has_cache'                          => $this->has_cache,
            'has_job_cache'                      => $this->has_job_cache,
            'has_user_cache'                     => $this->has_user_cache,
            'num_parameter_sets_cached_by_jobs'  => $this->num_parameter_sets_cached_by_jobs,
            'num_parameter_sets_cached_by_users' => $this->num_parameter_sets_cached_by_users,

        ];
    }
}
