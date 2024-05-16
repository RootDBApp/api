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

use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

/** @mixin \App\Models\ConfConnector */
class ConfConnector extends JsonResource
{
    public function toArray($request): array
    {
        $forAdmin = User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_CONF_CONNECTOR) && $request->exists('for-admin');

        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'connector_database_id' => $this->connector_database_id,
            'organization_id'       => $this->organization_id,
            'use_ssl'               => $this->use_ssl,
            'host'                  => $this->when($forAdmin, $this->host),
            'port'                  => $this->when($forAdmin, $this->port),
            'database'              => $this->when($forAdmin, $this->database),
            'username'              => $this->when($forAdmin, $this->username),
            'password'              => $this->when($forAdmin, Crypt::decrypt($this->password)),
            'timeout'               => $this->when($forAdmin, $this->timeout),
            'raw_grants'            => $this->when($forAdmin, $this->raw_grants),
            'ssl_cipher'            => $this->when($forAdmin, $this->ssl_cipher),
            'ssl_ca'                => $this->when($forAdmin, $this->ssl_ca),
            'ssl_key'               => $this->when($forAdmin, $this->ssl_key),
            'ssl_cert'              => $this->when($forAdmin, $this->ssl_cert),
            'seems_ok'              => $this->when($forAdmin, $this->seems_ok),
            'global'                => $this->global,
            //            'reports'                  => $this->when($forAdmin,
            //                function () {
            //                    return ReportBasic::collection($this->reports);
            //                }
            //            ),
            //            'report_parameter_inputs' => $this->when($forAdmin,
            //                function () {
            //                    return ReportParameterInputBasic::collection($this->reportParameterInputs);
            //                }
            //            ),
        ];
    }
}
