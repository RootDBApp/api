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

namespace App\Http\Controllers;

use App\Events\CacheJobUpdated;
use App\Jobs\CacheRefreshJob;
use App\Models\CacheJob;
use App\Http\Resources\CacheJob as CacheJobResource;
use App\Models\CacheJobParameterSetConfig;
use App\Models\ReportCache;
use App\Services\CacheService;
use App\Tools\CacheReportTools;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Psr\SimpleCache\InvalidArgumentException;


class CacheJobController extends ApiController
{
    /**
     * @throws InvalidArgumentException
     */
    public function deleteCache(Request $request, CacheService $cacheService, CacheJob $cacheJob): JsonResponse
    {
        $this->genericAuthorize($request, $cacheJob, true, 'update');

        /** @var ReportCache $reportCache */
        foreach (ReportCache::whereCacheJobId($cacheJob->id)->get() as $reportCache) {

            Log::debug('Removing from cache key ', [$reportCache->cache_key]);
            $cacheService->deleteCache($reportCache->cache_key);
        }

        Log::debug('Cleaning report_cache table.');
        ReportCache::whereCacheJobId($cacheJob->id)->delete();

        $cacheJob->update(
            [
                'last_run'                => null,
                'last_run_duration'       => null,
                'last_num_parameter_sets' => null,
                'last_cache_size_b'       => null,
                'running'                 => false
            ]
        );
        CacheJobUpdated::dispatch($cacheJob->report->organization_id, $cacheJob);

        CacheReportTools::updateReportCacheStatus($cacheJob->report);
        CacheReportTools::updateFrontendReportStateCacheStatus($cacheJob->report);

        return $this->successResponse(null, 'Job cache deleted.');
    }

    public function destroy(Request $request, CacheJob $cacheJob): JsonResponse
    {
        $this->genericAuthorize($request, $cacheJob);

        $cacheJob->delete();

        return $this->successResponse(null, 'Cache job deleted.');
    }

    /**
     * @throws Exception
     */
    public function index(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, new CacheJob(), false);

        $cacheJobs = CacheJob::with(['cacheJobParameterSetConfigs', 'report.dataViews'])->where('report_id', '=', $request->get('report-id'))->get();

        /** @var CacheJob $cacheJob */
        foreach ($cacheJobs as $cacheJob) {

            if (!is_null($cacheJob->last_run) && CacheReportTools::checkIfAllDataViewsResultsAreCachedFromCacheJob($cacheJob) === false) {

                $cacheJob->update(
                    [
                        'last_run'                => null,
                        'last_run_duration'       => null,
                        'last_num_parameter_sets' => null,
                        'last_cache_size_b'       => null,
                        'running'                 => false
                    ]);
            }
        }

        return response()->json(CacheJobResource::collection($cacheJobs));
    }

    public function run(Request $request, CacheJob $cacheJob): JsonResponse
    {
        $this->genericAuthorize($request, $cacheJob, true, 'update');

        CacheRefreshJob::dispatch($cacheJob)->afterResponse();
        return $this->successResponse(null, 'Cache job successfully dispatched.');
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, CacheJob::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            CacheJob::rules(),
            CacheJob::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the cache job.', $validator->errors(), 422);
        }

        $cacheJob = CacheJob::create($request->all());


        $this->updateParameterSetConfigs($request, $cacheJob->id);

        return $this->successResponse(new CacheJobResource($cacheJob), 'The cache job has been created successfully.');
    }

    public function update(Request $request, CacheJob $cacheJob): JsonResponse
    {
        $this->genericAuthorize($request, $cacheJob);

        $validator = Validator::make(
            $request->all(),
            CacheJob::rules(),
            CacheJob::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the cache job.', $validator->errors(), 422);
        }

        $cacheJob->update($request->toArray());

        $this->updateParameterSetConfigs($request);

        return $this->successResponse(new CacheJobResource($cacheJob), 'The cache job has been updated.');
    }

    private function updateParameterSetConfigs(Request $request, int $cache_job_id = 0): void
    {
        foreach ($request->get('cache_job_parameter_set_configs') as $cache_job_parameter_set_config) {

            unset($cache_job_parameter_set_config['created_at'], $cache_job_parameter_set_config['updated_at']);

            if ($cache_job_id > 0) {
                $cache_job_parameter_set_config['cache_job_id'] = $cache_job_id;
            }

            //
            // Order all values of SELECT and MULTI-SELECT parameters
            //
            if (count((array)$cache_job_parameter_set_config['select_values']['values']) > 0) {

                $select_values = (array)$cache_job_parameter_set_config['select_values']['values'];
                sort($select_values);
                $cache_job_parameter_set_config['select_values']['values'] = $select_values;
            }

            if (count((array)$cache_job_parameter_set_config['multi_select_values']['values']) > 0) {

                $multi_select_values = (array)$cache_job_parameter_set_config['multi_select_values']['values'];
                sort($multi_select_values);
                $cache_job_parameter_set_config['multi_select_values']['values'] = $multi_select_values;
            }

            $cache_job_parameter_set_config['date_start_from_values'] = json_encode($cache_job_parameter_set_config['date_start_from_values']);
            $cache_job_parameter_set_config['select_values'] = json_encode($cache_job_parameter_set_config['select_values']);
            $cache_job_parameter_set_config['multi_select_values'] = json_encode($cache_job_parameter_set_config['multi_select_values']);

            $validator = Validator::make(
                $cache_job_parameter_set_config,
                CacheJobParameterSetConfig::$rules,
                CacheJobParameterSetConfig::$rule_messages
            );

            if ($validator->fails()) {

                $this->errorResponse('Request error', 'Unable to update the cache job parameter config set.', $validator->errors(), 422);
                return;
            }

            if ($cache_job_parameter_set_config['id'] === 0) {

                CacheJobParameterSetConfig::create($cache_job_parameter_set_config);
            } else {

                $cacheJobParameterSetConfig = CacheJobParameterSetConfig::whereId($cache_job_parameter_set_config['id']);
                if (!is_null($cacheJobParameterSetConfig)) {

                    $cacheJobParameterSetConfig->update($cache_job_parameter_set_config);
                }
            }
        }
    }
}
