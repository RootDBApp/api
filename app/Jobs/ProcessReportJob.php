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

use App\Events\ReportError;
use App\Events\ReportRunEnd;
use App\Events\ReportRunStart;
use App\Models\ExecReportInfo;
use App\Models\Report;
use App\Services\CacheService;
use App\Services\ConnectorService;
use App\Tools\ReportTools;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Database\QueryException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class ProcessReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Report $report;
    private ExecReportInfo $execReportInfo;

    #[NoReturn]
    public function __construct(Report $report, ExecReportInfo $execReportInfo)
    {
        $this->report = $report;
        $this->execReportInfo = $execReportInfo;
    }

    /**
     * @throws Exception
     */
    public function handle(ConnectorService $connectorService, CacheService $cacheService): void
    {
        // From frontend
        if ($this->execReportInfo->instanceId > 0) {

            ReportRunStart::dispatch($this->execReportInfo, $this->report);

            if ($this->report->has_cache && $this->execReportInfo->useCache === true) {

                $this->execReportInfo->reportCacheInfo = $cacheService->checkIfAllDataViewsResultsAreCached($this->execReportInfo, $this->report);

                Log::debug('ProcessReportJob::job - All data views results are cached for this set of parameters ?', [$this->execReportInfo->reportCacheInfo->cached]);
            }
        }

        $ms_start = microtime(true);

        try {
            $connection = $connectorService->getInstance($this->report->confConnector, $this->report->on_queue)->getConnection();

            if (!$this->execReportInfo->reportCacheInfo->cached) {

                // Input parameters setup, as real SQL variables, if available for this connector.
                $connectorService->getInstance($this->report->confConnector, $this->report->on_queue)->setInputParameterVariables($this->execReportInfo, $connection);
            }

            // Run report init query
            if (mb_strlen($this->report->query_init) > 0 && !$this->execReportInfo->reportCacheInfo->cached) {

                $query_init = ReportTools::SQLQueryCommentCleanup($this->report->query_init);
                if (!is_null($query_init)) {

                    // If connector does not handle real SQL variable, we have to set up it now.
                    $query_init = $connectorService->getInstance($this->report->confConnector)->replaceInputParameterVariables($this->execReportInfo, $query_init);
                    Log::debug('ProcessReportJob::job -process [report ID ' . $this->report->id . ' ] init query : ' . $this->report->query_init . PHP_EOL);
                    $connection->unprepared($query_init);
                }
            }

            // All Report Data Views queries
            foreach ($this->report->dataViews as $dataView) {

                Log::debug('ProcessReportJob::job -process [report ID ' . $this->report->id . ' ] start process [data view ID : ' . $dataView->id . ' ] ' . PHP_EOL);
                ProcessReportDataViewJob::dispatchSync($dataView, $this->execReportInfo, true);
            }

            // Run report cleanup query
            Log::debug('ProcessReportJob::job -process [report ID ' . $this->report->id . ' ] cleanup query : ' . $this->report->query_cleanup . PHP_EOL);
            if (mb_strlen($this->report->query_cleanup) > 0 && !$this->execReportInfo->reportCacheInfo->cached) {

                $query_cleanup = ReportTools::SQLQueryCommentCleanup($this->report->query_cleanup);
                if (!is_null($query_cleanup)) {

                    // If connector does not handle real SQL variable, we have to set up it now.
                    $connectorService->getInstance($this->report->confConnector, $this->report->on_queue)->replaceInputParameterVariables($this->execReportInfo, $query_cleanup);
                    $connection->unprepared($query_cleanup);
                }
            }
        } catch (QueryException  $exception) {

            if ($this->execReportInfo->instanceId > 0) {

                ReportError::dispatch($this->execReportInfo, $this->report, $exception->getMessage());
            }
        }

        $ms_elapsed = microtime(true) - $ms_start;

        if ($this->execReportInfo->instanceId > 0) {

            ReportRunEnd::dispatch($this->execReportInfo, $this->report, $ms_elapsed);
        }

//        $num_seconds_all_run = $this->report->num_seconds_all_run + $ms_elapsed;
//
//        if (!$this->execReportInfo->reportCacheInfo->cached) {
//
//            $this->report->update(
//                [
//                    'num_runs'            => ($this->report->num_runs + 1),
//                    'num_seconds_all_run' => $num_seconds_all_run,
//                    'avg_seconds_by_run'  => $num_seconds_all_run / ($this->report->num_runs + 1),
//                ]
//            );
//        }
    }
}
