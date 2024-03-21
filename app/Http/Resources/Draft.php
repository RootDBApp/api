<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\Draft */
class Draft extends JsonResource
{

    #[ArrayShape(['id' => "int", 'draft_queries' => "\Illuminate\Http\Resources\Json\AnonymousResourceCollection"])]
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'draft_queries' => DraftQueries::collection($this->draftQueries)
        ];
    }
}
