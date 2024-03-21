<?php

namespace App\Models;

readonly class ReportCacheStatus
{
    public int $report_id;
    public bool $has_cache;
    public bool $has_job_cache;
    public bool $has_user_cache;
    public int $num_parameter_sets_cached_by_jobs;
    public int $num_parameter_sets_cached_by_users;

    public function __construct(int $report_id, bool $has_cache, bool $has_job_cache, bool $has_user_cache, int $num_parameter_sets_cached_by_jobs, int $num_parameter_sets_cached_by_users)
    {
        $this->report_id = $report_id;
        $this->has_cache = $has_cache;
        $this->has_job_cache = $has_job_cache;
        $this->has_user_cache = $has_user_cache;
        $this->num_parameter_sets_cached_by_jobs = $num_parameter_sets_cached_by_jobs;
        $this->num_parameter_sets_cached_by_users = $num_parameter_sets_cached_by_users;

    }

    public function toArray(): array
    {
        return [
            'report_id'                          => $this->report_id,
            'has_cache'                          => $this->has_cache,
            'has_job_cache'                      => $this->has_job_cache,
            'has_user_cache'                     => $this->has_user_cache,
            'num_parameter_sets_cached_by_jobs'  => $this->num_parameter_sets_cached_by_jobs,
            'num_parameter_sets_cached_by_users' => $this->num_parameter_sets_cached_by_users,
        ];
    }
}
