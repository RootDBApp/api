<?php

namespace App\Http\Resources;

use App\Http\Resources\PublicReportParameter as ReportParameterResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Report */
class PublicReport extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                 => $this->id,
            'instance_id'                        => $request->get('instanceId'),
            'name'                               => $this->name,
            'title'                              => $this->title,
            'description'                        => $this->description,
            'has_parameters'                     => $this->has_parameters,
            'parameters'                         => ReportParameterResource::collection($this->parameters),
            'has_cache'                          => $this->has_cache,
            'has_job_cache'                      => $this->has_job_cache,
            'has_user_cache'                     => $this->has_user_cache,
            'num_parameter_sets_cached_by_jobs'  => $this->num_parameter_sets_cached_by_jobs,
            'num_parameter_sets_cached_by_users' => $this->num_parameter_sets_cached_by_users,

        ];
    }
}
