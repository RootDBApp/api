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
use App\Http\Resources\ReportParameterInputType as ParameterInputTypeResource;
use App\Http\Resources\ReportParameterInputDataType as ParameterInputDataTypeResource;
use App\Models\RoleGrants;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportParameterInput */
class ReportParameterInput extends JsonResource
{
    public function toArray($request): array
    {
        $loggedUserHasRoleDev = User::searchIfLoggedUserHasUiRoleGrant(RoleGrants::PERMISSION_EDIT, RoleGrants::RESOURCE_REPORT_PARAMETER_INPUT);

        return [
            'id'                           => $this->id,
            'conf_connector_id'            => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->conf_connector_id;
                }
            ),
            'parameter_input_type_id'      => $this->parameter_input_type_id,
            'parameter_input_data_type_id' => $this->parameter_input_data_type_id,
            'name'                         => $this->name,
            'query'                        => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->query;
                }
            ),
            'query_default_value'          => $this->when($loggedUserHasRoleDev,
                function () {
                    return $this->query_default_value;
                }
            ),
            'default_value'                => $this->when(
                $request->get('parameter-default-value') >= 1,
                function () {

                    return $this->getDefaultValue();
                }),
            'custom_entry'                 => $this->custom_entry,
            'values'                       => $this->when(
                $request->get('parameter-values') >= 1 && !is_null($this->query),
                function () {

                    return $this->getParameterValues();
                }
            ),
            'conf_connector'               => $this->when($loggedUserHasRoleDev,
                function () {
                    return ConfConnectorResource::make($this->confConnector);
                }
            ),
            'parameter_input_type'         => ParameterInputTypeResource::make($this->parameterInputType),
            'parameter_input_data_type'    => ParameterInputDataTypeResource::make($this->parameterInputDataType),
        ];
    }
}
