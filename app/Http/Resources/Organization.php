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

use App\Http\Resources\ConfConnector as ConfConnectorResource;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\Organization */
class Organization extends JsonResource
{
    #[ArrayShape(['id' => "int", 'name' => "string", 'reports_count' => "\Illuminate\Http\Resources\MissingValue|mixed", 'conf_connectors' => "\Illuminate\Http\Resources\MissingValue|mixed", 'organization_users' => "\Illuminate\Http\Resources\MissingValue|mixed", 'user_preferences' => "\Illuminate\Http\Resources\MissingValue|mixed"])]
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'reports_count'      => $this->when(
                auth()->user()->id === 1 && $request->exists('for-admin'),
                function () {
                    return $this->reports_count;
                }
            ),
            'conf_connectors'    => $this->when(
                (int)$request->get('conf-connectors') === 1,
                function () {
                    return ConfConnectorResource::collection($this->confConnectors);
                }
            ),
            'organization_users' => $this->when(
                auth()->user()->id === 1 && (bool)$request->exists('do-not-display-user') === false,
                function () {
                    return OrganizationUser::collection($this->organizationUsers->whereNotIn('user_id', [1]));
                }
            )
        ];
    }
}
