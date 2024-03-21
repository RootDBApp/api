<?php

namespace App\Http\Resources;

use App\Http\Resources\ReportParameterInputType as ParameterInputTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportParameterInput */
class ReportParameterInputBasic extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'parameter_input_type' => ParameterInputTypeResource::make($this->parameterInputType),
        ];
    }
}
