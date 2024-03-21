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

namespace App\Jobs;

use App\Events\CacheJobUpdated;
use App\Events\ReportCacheUpdated;
use App\Models\ReportCache;
use App\Models\ExecReportInfo;
use App\Models\CacheJob;
use App\Services\CacheService;
use App\Services\ConnectorService;
use App\Tools\CacheReportTools;
use App\Tools\ReportTools;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CacheRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public readonly CacheJob $cacheJob;

    public function __construct(CacheJob $cacheJob)
    {
        $this->cacheJob = $cacheJob;
    }

    /**
     * @throws Exception
     */
    public function handle(ConnectorService $connectorService, CacheService $cacheService): void
    {
        $this->cacheJob->update(['running' => true]);
        CacheJobUpdated::dispatch($this->cacheJob->report->organization_id, $this->cacheJob);

        Log::debug('CacheRefreshJob::handle - Cache job ID: ' . $this->cacheJob->id . ' | report ID: ' . $this->cacheJob->report->id,
                   [$this->cacheJob->frequency->value, $this->cacheJob->at_minute, $this->cacheJob->at_time, $this->cacheJob->at_day]);

        sleep(2);
        $ms_start = microtime(true);

        $connectorInstance = $connectorService->getInstance($this->cacheJob->report->confConnector);

        if ($this->cacheJob->getAllCacheJobParameterSets()->count() == 0) {

            ReportCache::where('input_parameters_hash', '=', CacheReportTools::getInputParametersHash([]))
                ->where('report_id', '=', $this->cacheJob->report_id)
                ->delete();

            $this->cacheJob->report->update(['has_cache' => ReportCache::whereReportId($this->cacheJob->report->id)->count() > 0]);

            Log::debug('CacheRefreshJob::handle - no set of parameters to use for this cache refresh job:');
            $connectorInstance->execReport(
                $this->cacheJob->report,
                new ExecReportInfo(
                    [],
                    0, // 0 means we are running from CLI. (cron task)
                    null,
                    null,
                    $this->cacheJob,
                    false,
                    false,
                )
            );

        } else {

            foreach ($this->cacheJob->getAllCacheJobParameterSets() as $cacheJobParameterSets) {

                ReportCache::where('cache_job_id', '=', $this->cacheJob->id)->delete();
                $this->cacheJob->report->update(['has_cache' => ReportCache::whereReportId($this->cacheJob->report->id)->count() > 0]);

                foreach ($cacheJobParameterSets->getAllParametersSets() as $parametersSet) {

                    $inputParameters = [];
                    foreach ($parametersSet as $parameterSet) {

                        $inputParameters[] = ['name' => $parameterSet->name, 'value' => $parameterSet->value];
                    }

                    $execReportInfo = new ExecReportInfo(
                        $inputParameters,
                        0, // Zero means we are running from CLI. (cron task)
                        null,
                        null,
                        $this->cacheJob,
                        false,
                        false,
                    );
                    $execReportInfo->orderInputParametersValues($this->cacheJob->report);

                    Log::debug('Running this parameters set: [ ' . ReportTools::flattenInputParameters($execReportInfo->inputParameters()) . ' ]');
                    $connectorInstance->execReport($this->cacheJob->report, $execReportInfo);
                }
            }
        }

        $cacheJobStatistics = CacheReportTools::getCacheJobStatistics($this->cacheJob);
        $this->cacheJob->update(
            [
                'last_run'                => Carbon::now(),
                'last_run_duration'       => microtime(true) - $ms_start,
                'last_num_parameter_sets' => $cacheJobStatistics->num_parameter_sets,
                'last_cache_size_b'       => $cacheJobStatistics->cache_size_b,
                'running'                 => false
            ]
        );

        CacheJobUpdated::dispatch($this->cacheJob->report->organization_id, $this->cacheJob);
        CacheReportTools::updateReportCacheStatus($this->cacheJob->report);
        CacheReportTools::updateFrontendReportStateCacheStatus($this->cacheJob->report);
    }
}
