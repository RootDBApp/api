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
