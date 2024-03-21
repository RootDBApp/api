<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CacheJobParameterSetConfig */
class CacheJobParameterSetConfig extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'cache_job_id'           => $this->cache_job_id,
            'report_parameter_id'    => $this->report_parameter_id,
            'value'                  => $this->value,
            'date_start_from_values' => $this->date_start_from_values,
            'select_values'          => $this->select_values,
            'multi_select_values'    => $this->multi_select_values,
        ];
    }
}
