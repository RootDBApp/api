<?php

namespace App\Http\Resources;

use App\Http\Resources\ReportParameterInput as ParameterInputResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportParameter */
class ReportParameter extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                   => $this->id,
            'report_id'                            => $this->report_id,
            'parameter_input_id'                   => $this->parameter_input_id,
            'name'                                 => $this->name,
            'variable_name'                        => $this->variable_name,
            'parameter_input'                      => ParameterInputResource::make($this->parameterInput),
            'following_parameter_next_to_this_one' => $this->following_parameter_next_to_this_one,
            'forced_default_value'                 => $this->forced_default_value,
            'available_public_access'              => $this->available_public_access
        ];
    }
}
