<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameterInputDataType */
class PublicReportParameterInputDataType extends JsonResource
{

    #[ArrayShape(['id' => "int", 'name' => "string", 'type_name' => "string", 'custom_entry' => "int"])]
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'type_name'    => $this->type_name,
        ];
    }
}
