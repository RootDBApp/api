<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ServiceMessage */
class ServiceMessage extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'contents'           => $this->contents,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,

            'organizations' => Organization::collection($this->whenLoaded('organizations')),
        ];
    }
}
