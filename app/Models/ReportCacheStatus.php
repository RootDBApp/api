<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
