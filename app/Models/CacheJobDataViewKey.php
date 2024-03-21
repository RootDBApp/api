<?php

namespace App\Models;

class CacheJobDataViewKey
{
    public readonly int $job_id;
    public readonly int $report_id;
    public readonly int $data_view_id;

    public function __construct(int $job_id, int $report_id, int $data_view_id)
    {
        $this->job_id = $job_id;
        $this->report_id = $report_id;
        $this->data_view_id = $data_view_id;
    }
}
