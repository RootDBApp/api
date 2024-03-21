<?php

namespace App\Http\Resources;

use App\Http\Resources\PublicReportParameterInput as ParameterInputResource;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameter */
class PublicReportParameter extends JsonResource
{
    #[ArrayShape(['id' => "int", 'report_id' => "int", 'parameter_input_id' => "int", 'name' => "string", 'variable_name' => "string", 'parameter_input' => "\App\Http\Resources\ReportParameterInput", 'following_parameter_next_to_this_one' => "bool", 'forced_default_value' => "null|string"])]
    public function toArray($request): array
    {
        return [
            'id'                                   => $this->id,
            'report_id'                            => $this->report_id,
            'name'                                 => $this->name,
            'variable_name'                        => $this->variable_name,
            'parameter_input'                      => ParameterInputResource::make($this->parameterInput),
            'following_parameter_next_to_this_one' => $this->following_parameter_next_to_this_one,
            'forced_default_value'                 => $this->forced_default_value,
            'available_public_access'              => $this->available_public_access
        ];
    }
}
