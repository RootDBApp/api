<?php

namespace App\Http\Resources;

use App\Http\Resources\ReportParameterInputType as ParameterInputTypeResource;
use App\Http\Resources\PublicReportParameterInputDataType as ParameterInputDataTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameterInput */
class PublicReportParameterInput extends JsonResource
{
    #[ArrayShape(['id' => "int", 'name' => "string", 'default_value' => "string", 'custom_entry' => "int", 'values' => "\Illuminate\Http\Resources\MissingValue|mixed", 'parameter_input_type' => "\App\Http\Resources\ReportParameterInputType", 'parameter_input_data_type' => "\App\Http\Resources\ReportParameterInputDataType"])]
    public function toArray($request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'default_value'             => $this->getDefaultValue(),
            'values'                    => $this->getParameterValues(),
            'parameter_input_type'      => ParameterInputTypeResource::make($this->parameterInputType),
            'parameter_input_data_type' => ParameterInputDataTypeResource::make($this->parameterInputDataType),
        ];
    }
}
