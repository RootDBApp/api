<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

readonly class ReportParameterSets
{
    public bool $from_cache_job;
    public int $cache_job_id;
    public Carbon $cached_at;
    public AnonymousResourceCollection $report_parameters;

    public function __construct(bool $from_cache_job, int $cache_job_id, Carbon $cached_at, AnonymousResourceCollection $report_parameters)
    {
        $this->from_cache_job = $from_cache_job;
        $this->cache_job_id = $cache_job_id;
        $this->cached_at = $cached_at;
        $this->report_parameters = $report_parameters;
    }
}
