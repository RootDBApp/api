<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReportParameterSets */
class ReportParameterSets extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'from_cache_job'    => $this->from_cache_job,
            'cache_job_id'      => $this->cache_job_id,
            'cached_at'         => $this->cached_at->format('Y-m-d h:i:s'),
            'report_parameters' => $this->report_parameters,
        ];
    }
}
