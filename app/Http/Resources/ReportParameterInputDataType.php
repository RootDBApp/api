<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameterInputDataType */
class ReportParameterInputDataType extends JsonResource
{


    #[ArrayShape(['id' => "int", 'connector_database_id' => "int", 'name' => "string", 'type_name' => "string", 'custom_entry' => "int"])]
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'connector_database_id' => $this->connector_database_id,
            'name'                  => $this->name,
            'type_name'             => $this->type_name,
            'custom_entry'          => $this->custom_entry,
        ];
    }
}
