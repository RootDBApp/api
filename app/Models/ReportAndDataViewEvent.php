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

use App\Enums\EnumCacheType;

class ReportAndDataViewEvent
{
    private int $report_id;
    private string $instance_id;
    private string $report_name;
    private int $data_view_id;
    private string $data_view_name;
    private array $errors;
    private array $results;
    private float $ms_elapsed;
    private ReportCacheInfo $reportCacheInfo;

    public function __construct(int $report_id, string $instance_id, string $report_name, int $data_view_id, string $data_view_name, array $errors, array $results, float $ms_elapsed, ReportCacheInfo $reportCacheInfo)
    {
        $this->report_id = $report_id;
        $this->instance_id = $instance_id;
        $this->report_name = $report_name;
        $this->data_view_id = $data_view_id;
        $this->data_view_name = $data_view_name;
        $this->errors = $errors;
        $this->results = $results;
        $this->ms_elapsed = $ms_elapsed;
        $this->reportCacheInfo = $reportCacheInfo;
    }

    public function toArray(): array
    {
        return [
            'report_id'          => $this->report_id,
            'instance_id'        => $this->instance_id,
            'report_name'        => $this->report_name,
            'data_view_id'       => $this->data_view_id,
            'data_view_name'     => $this->data_view_name,
            'errors'             => $this->errors,
            'results'            => $this->results,
            'results_from_cache' => $this->reportCacheInfo->cached,
            'results_cache_type' => $this->reportCacheInfo->cacheType,
            'results_cached_at'  => $this->reportCacheInfo->cachedAt->format('Y-m-d h:i:s'),
            'ms_elapsed'         => $this->ms_elapsed,
        ];
    }
}
