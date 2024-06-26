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

use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Directory as DirectoryResource;
use App\Http\Resources\ReportGroup as ReportGroupResource;
use App\Http\Resources\ReportUser as ReportUserResource;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * Used to return only report's data which are the same for everyone, and for listing.
 *
 * @mixin \App\Models\Report
 */
class LightReport extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'allowed_groups'                     => $this->when((int)$request->get('allowed-groups') === 1,
                function () {
                    return ReportGroupResource::collection($this->allowedGroups);
                }
            ),
            'allowed_users'                      => $this->when((int)$request->get('allowed-users') === 1,
                function () {
                    return ReportUserResource::collection($this->allowedUsers);
                }
            ),
            'category'                           => CategoryResource::make($this->category),
            'conf_connector_id'                  => $this->conf_connector_id,
            'created_at'                         => $this->created_at,
            'description'                        => $this->description,
            'description_listing'                => $this->description_listing,
            'directory'                          => DirectoryResource::make($this->directory),
            'favorite'                           => $this->when(
                (int)$request->get('favorite') === 1,
                function () {
                    return count($this->favoriteUsers) > 0;
                }),
            'has_cache'                          => $this->has_cache,
            'has_job_cache'                      => $this->has_job_cache,
            'has_user_cache'                     => $this->has_user_cache,
            'has_parameters'                     => $this->has_parameters,
            'has_data_views'                     => $this->has_data_views,
            'num_parameter_sets_cached_by_jobs'  => $this->num_parameter_sets_cached_by_jobs,
            'num_parameter_sets_cached_by_users' => $this->num_parameter_sets_cached_by_users,
            'id'                                 => $this->id,
            'is_visible'                         => $this->is_visible,
            'name'                               => $this->name,
            'organization_id'                    => $this->organization_id,
            'updated_at'                         => $this->updated_at,
            'user_id'                            => $this->user_id,
        ];
    }
}
