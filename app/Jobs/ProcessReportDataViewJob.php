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

use App\Enums\EnumCacheType;
use App\Events\ReportDataViewError;
use App\Events\ReportDataViewResultsReceived;
use App\Events\ReportDataViewRunEnd;
use App\Events\ReportDataViewRunStart;
use App\Models\ReportCache;
use App\Models\ExecReportInfo;
use App\Models\ReportDataView;
use App\Services\CacheService;
use App\Services\ConnectorService;
use App\Tools\CacheReportTools;
use App\Tools\ReportTools;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;

class ProcessReportDataViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ReportDataView $dataView;
    private bool $from_report;
    private ExecReportInfo $execReportInfo;

    public function __construct(ReportDataView $dataView, ExecReportInfo $execReportInfo, bool $from_report = false)
    {
        $this->execReportInfo = $execReportInfo;
        $this->dataView = $dataView;
        $this->from_report = $from_report;
    }

    public function handle(ConnectorService $connectorService, CacheService $cacheService): void
    {
        Log::debug('ProcessReportDataViewJob::handle - Process [ data view  ID ' . $this->dataView->id . ' ] guest ? ' . ($this->execReportInfo->isAuthenticatedUser() ? 'yes' : 'no'));

        $ms_start = microtime(true);
        if ($this->execReportInfo->instanceId > 0) {

            ReportDataViewRunStart::dispatch($this->execReportInfo, $this->dataView);
        }

        try {
            $connection = $connectorService->getInstance($this->dataView->report->confConnector, (!$this->from_report && $this->dataView->on_queue))->getConnection();

            // Input parameters setup.
            if ($this->from_report === false) {

                // Input parameters setup, as real SQL variables, if available for this connector.
                $connectorService->getInstance($this->dataView->report->confConnector, (!$this->from_report && $this->dataView->on_queue))->setInputParameterVariables($this->execReportInfo, $connection);

                if ($this->dataView->report->has_cache && $this->execReportInfo->useCache === true) {

                    // @todo, check only for data view memcached key
                    $this->execReportInfo->reportCacheInfo = $cacheService->checkIfAllDataViewsResultsAreCached($this->execReportInfo, $this->dataView->report);

                    Log::debug('ProcessReportJob::job - All data views results are cached for this set of parameters ?', [$this->execReportInfo->reportCacheInfo->cached]);
                }
            }

            // Run report init query
            if ($this->from_report === false && mb_strlen($this->dataView->report->query_init) > 0) {

                $query_init = ReportTools::SQLQueryCommentCleanup($this->dataView->report->query_init);
                if (!is_null($query_init)) {

                    // If connector does not handle real SQL variable, we have to set up it now.
                    $query_init = $connectorService->getInstance($this->dataView->report->confConnector, (!$this->from_report && $this->dataView->on_queue))->replaceInputParameterVariables($this->execReportInfo, $query_init);
                    Log::debug('ProcessReportDataViewJob::handle - Process [ data view  ID ' . $this->dataView->id . ' ] init query : ' . $query_init . PHP_EOL);
                    $connection->unprepared($query_init);
                }
            }

            $query = null;
            if (!is_null($this->dataView->query)) {

                $query = ReportTools::SQLQueryCommentCleanup($this->dataView->query);
            }
            if (!is_null($query)) {

                // If connector does not handle real SQL variable, we have to set up it now.
                $query = $connectorService->getInstance($this->dataView->report->confConnector, (!$this->from_report && $this->dataView->on_queue))->replaceInputParameterVariables($this->execReportInfo, $query);

                if (!$this->execReportInfo->reportCacheInfo->cached) {

                    Log::debug('ProcessReportDataViewJob::handle - Process [ data view  ID ' . $this->dataView->id . ' ] query : ' . $query . PHP_EOL);
                }

                // Report Data View query
                // Run by chunk only when we are running from the frontend.
                if ($this->dataView->by_chunk === true && $this->execReportInfo->instanceId > 0) {

                    $chunk_results = [];
                    $i = 1;

                    if ($this->execReportInfo->reportCacheInfo->cached) {

                        $results = $cacheService->getDataViewResults($this->execReportInfo, $this->dataView);
                        if (is_null($results)) {

                            ReportDataViewError::dispatch($this->execReportInfo, $this->dataView, 'There was an issue getting results from cache, for dataView ID' . $this->dataView->id);
                        } else {
                            Log::debug('ProcessReportDataViewJob::handle - got results from cache.');
                            ReportDataViewResultsReceived::dispatch($this->execReportInfo, $this->dataView, $results);
                        }
                    } else {

                        $results = $connection->select($query);
                    }

                    foreach ($results as $result) {

                        $chunk_results[] = $result;

                        if ($i === $this->dataView->chunk_size) {

                            //Log::debug('chunk_results', [$chunk_results]);
                            ReportDataViewResultsReceived::dispatch($this->execReportInfo, $this->dataView, $chunk_results);

                            $chunk_results = [];
                            $i = 1;
                            usleep(100000);
                        } else {

                            $i++;
                        }
                    }

                    // Send remaining results
                    if (count($chunk_results) > 0) {

                        //Log::debug('chunk_results', [$chunk_results]);
                        ReportDataViewResultsReceived::dispatch($this->execReportInfo, $this->dataView, $chunk_results);
                    }

                }// All results in a raw, or we were not able to activate the results by chunks.
                else {

                    if ($this->execReportInfo->instanceId > 0) {

                        if ($this->execReportInfo->reportCacheInfo->cached) {

                            $results = $cacheService->getDataViewResults($this->execReportInfo, $this->dataView);
                            if (is_null($results)) {

                                ReportDataViewError::dispatch($this->execReportInfo, $this->dataView, 'There was an issue getting results from cache, for dataView ID' . $this->dataView->id);
                            } else {
                                Log::debug('ProcessReportDataViewJob::handle - got results from cache.');
                                ReportDataViewResultsReceived::dispatch($this->execReportInfo, $this->dataView, $results);
                            }
                        } else {

                            ReportDataViewResultsReceived::dispatch($this->execReportInfo, $this->dataView, $connection->select($query));
                        }
                    } // We are running from CLI.
                    else {

                        $this->_storeInCache($cacheService, $connection, $query);
                    }
                }
            } else {

                // We are running from CLI, let's cache an empty results.
                if ((int)$this->execReportInfo->instanceId === 0) {
                    $this->_storeInCache($cacheService, $connection, '');
                }
            }

            // Run report cleanup query
            if ($this->from_report === false && mb_strlen($this->dataView->report->query_cleanup) > 0) {

                $query_cleanup = ReportTools::SQLQueryCommentCleanup($this->dataView->report->query_cleanup);
                if (!is_null($query_cleanup)) {

                    // If connector does not handle real SQL variable, we have to set up it now.
                    $connectorService->getInstance($this->dataView->report->confConnector, (!$this->from_report && $this->dataView->on_queue))->replaceInputParameterVariables($this->execReportInfo, $query_cleanup);
                    Log::debug('process [ data view  ID ' . $this->dataView->id . ' ] cleanup query : ' . $query_cleanup . PHP_EOL);
                    $connection->unprepared($this->dataView->report->query_cleanup);
                }
            }
        } catch (QueryException  $exception) {

            if ($this->execReportInfo->instanceId > 0) {

                ReportDataViewError::dispatch($this->execReportInfo, $this->dataView, $exception->getMessage());
            }
        }

        $ms_elapsed = microtime(true) - $ms_start;

        if ($this->execReportInfo->instanceId > 0) {

            ReportDataViewRunEnd::dispatch($this->execReportInfo, $this->dataView, $ms_elapsed);
        }

        $num_seconds_all_run = $this->dataView->num_seconds_all_run + $ms_elapsed;

        if (!$this->execReportInfo->reportCacheInfo->cached) {

            $this->dataView->update(
                [
                    'num_runs'            => ($this->dataView->num_runs + 1),
                    'num_seconds_all_run' => $num_seconds_all_run,
                    'avg_seconds_by_run'  => $num_seconds_all_run / ($this->dataView->num_runs + 1)
                ]
            );
        }
    }

    private function _storeInCache(CacheService $cacheService, Connection $connection, string $query): void
    {

        $key = $cacheService->cacheReportResultsFromJob(
            $this->execReportInfo,
            $this->dataView,
            mb_strlen($query) > 0 ? $connection->select($query) : []
        );

        if ($key === false) {

            $this->execReportInfo->cacheJob->report->update(['has_cache' => false]);
            $this->execReportInfo->cacheJob->where('cache_key', 'LIKE', CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX . '-' . $this->execReportInfo->cacheJob->id . '-' . $this->execReportInfo->cacheJob->report_id . '-%-' . $this->dataView->id)->delete();
            Log::warning('Error caching - There was an issue caching the results.');
        } else {

            $reportCache = new ReportCache(
                ['cache_job_id'          => $this->execReportInfo->cacheJob->id,
                 'cache_key'             => $key,
                 'input_parameters_hash' => CacheReportTools::getInputParametersHash($this->execReportInfo->inputParameters()),
                 'report_id'             => $this->execReportInfo->cacheJob->report->id,
                 'report_data_view_id'   => $this->dataView->id,
                 'cache_type'            => EnumCacheType::JOB->value,
                 ''
                ]
            );

            $reportCache->save();
        }
    }
}
