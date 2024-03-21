<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/** @mixin \App\Models\DraftQueries */
class DraftQueries extends JsonResource
{

    #[ArrayShape(['id' => "int", 'queries' => "null|string"])]
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'queries' => $this->queries
        ];
    }
}
