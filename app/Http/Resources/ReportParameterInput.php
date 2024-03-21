<?php

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
