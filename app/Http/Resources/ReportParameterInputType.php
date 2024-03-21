<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\ReportParameterInputType */
class ReportParameterInputType extends JsonResource
{

    #[ArrayShape(['id' => "int", 'name' => "string"])]
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'query' => $this->query,
        ];
    }
}
