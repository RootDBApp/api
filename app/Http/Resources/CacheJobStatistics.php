<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/** @mixin \App\Models\CacheJobStatistics */
class CacheJobStatistics extends JsonResource
{
    public function toArray(Request $request): array|JsonSerializable|Arrayable
    {
        return parent::toArray($request);
    }
}
