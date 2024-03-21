<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class ReportBasic extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'created_at'          => $this->created_at,
            'description_listing' => $this->description_listing,
            'name'                => $this->name,
        ];
    }
}
