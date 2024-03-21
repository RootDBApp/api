<?php

namespace App\Tools;

use App\Events\APICacheReportUpdateCacheStatus;
use App\Events\ReportCacheUpdated;
use App\Models\CacheJob;
use App\Models\CacheJobDataViewKey;
use App\Models\CacheJobDataViewStats;
use App\Models\CacheJobStatistics;
use App\Models\ExecReportInfo;
use App\Models\ParameterSet;
use App\Models\Report;
use App\Models\ReportCache;
use App\Models\ReportCacheStatus;
use App\Models\ReportDataView;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheReportTools
{
    const CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX = 'crjdvk';
    const CACHE_REFRESH_JOB_DATA_VIEW_TAGS = ['cache-job', 'report', 'data-view'];
    const CACHE_REFRESH_JOB_REPORT_PARAMETERS_KEY_PREFIX = 'crjrpk';
    const CACHE_REFRESH_JOB_REPORT_PARAMETERS_TAGS = ['cache-job', 'report', 'parameters'];

    /**
     * @throws Exception
     */
    public static function checkIfAllDataViewsResultsAreCachedFromCacheJob(CacheJob $cacheJob): bool
    {
        /** @var int[] $data_views */
        $data_views = [];
        $num_data_views_cached = 0;
        $num_parameter_sets = 0;
        $current_input_parameters_hash = '';

        foreach (ReportCache::where('cache_key', 'LIKE', self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX . '-' . $cacheJob->id . '-%')->get() as $reportCache
        ) {

            if (!in_array($reportCache->report_data_view_id, $data_views)) {
                $data_views[] = $reportCache->report_data_view_id;
            }

            if ($current_input_parameters_hash !== $reportCache->input_parameters_hash) {

                $current_input_parameters_hash = $reportCache->input_parameters_hash;
                $num_parameter_sets++;
            }

            if (Cache::tags(self::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->has($reportCache->cache_key)) {
                $num_data_views_cached++;
            }
        }

        if (count($data_views) > 0) {

            return ($num_data_views_cached / count($data_views)) === $num_parameter_sets;
        }

        return false;
    }

    public static function getCacheJobDataViewKeyFromMemcacheKey(string $memcached_key): CacheJobDataViewKey|null
    {
        if (preg_match('`.*' . self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX . '-([0-9]{1,100})-([0-9]{1,100})-.*-([0-9]{1,100})`', $memcached_key, $matches)) {

            return new CacheJobDataViewKey(
                $matches[1],
                $matches[2],
                $matches[3],
            );
        }

        Log::warning('CacheRefreshJobTools::getCacheJobDataViewKey - Unable to get job ID, report ID, data view ID from key', [$memcached_key]);
        return null;
    }

    /**
     * @throws Exception
     */
    public static function getCacheJobStatistics(CacheJob $cacheJob): CacheJobStatistics
    {
        $num_parameter_sets = 0;
        $cache_size_b = 0;
        $current_input_parameters_hash = '';

        foreach (ReportCache::where('cache_key', 'LIKE', self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX . '-' . $cacheJob->id . '-%')->get() as $reportCache) {

            $cacheJobDataViewStats = self::getDataViewResultStats($reportCache->cache_key);
            if ($cacheJobDataViewStats !== false) {

                if ($current_input_parameters_hash !== $reportCache->input_parameters_hash) {
                    $current_input_parameters_hash = $reportCache->input_parameters_hash;
                    $num_parameter_sets++;
                }

                $cache_size_b += abs($cacheJobDataViewStats->item_size_in_bytes);
            }
        }

        return new CacheJobStatistics($num_parameter_sets, $cache_size_b);
    }

    public static function getDataViewKey(int $report_id, int $report_data_view_id, array $parameters): string
    {
        $dataViewKey = self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX
            . '-' . 0
            . '-' . $report_id
            . '-' . self::getInputParametersHash($parameters)
            . '-' . $report_data_view_id;

        Log::debug('CacheReportTools::getDataViewKey - dataViewKey:', [$dataViewKey]);
        return $dataViewKey;
    }

    /**
     * @param CacheJob $cacheJob
     * @param ReportDataView $dataView
     * @param array|ParameterSet[] $parameterSets
     * @return string crjdvk-<job_id>-<report_id>-<md5(parameters)>-<data_view_id>
     */
    public static function getDataViewKeyWithCacheJob(CacheJob $cacheJob, ReportDataView $dataView, array $parameterSets): string
    {
        $parameters = [];
        foreach (collect($parameterSets)->sortBy('name') as $parameterSet) {


            if (is_a($parameterSet, '\App\Models\ParameterSet')) {

                $parameters[] = ['name' => $parameterSet->name, 'value' => $parameterSet->value];
            } else {

                $parameters[] = ['name' => $parameterSet['name'], 'value' => $parameterSet['value']];

            }
        }

        ReportTools::orderInputParameters($parameters);

        return self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX
            . '-' . $cacheJob->id
            . '-' . $cacheJob->report_id
            . '-' . self::getInputParametersHash($parameters)
            . '-' . $dataView->id;
    }

    /**
     * @return string crjdvk-<job_id>-<report_id>-<md5(parameters)>-<data_view_id>
     */
    public static function getDataViewKeyWithExecReportInfo(ExecReportInfo $execReportInfo, ReportDataView $dataView): string
    {
        return self::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX
            . '-' . $execReportInfo->cacheJob->id
            . '-' . $execReportInfo->cacheJob->report_id
            . '-' . self::getInputParametersHash($execReportInfo->inputParameters())
            . '-' . $dataView->id;
    }

    public static function getDataViewResultStats(string $data_view_key): false|CacheJobDataViewStats
    {
        $dataViewResults = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->get($data_view_key);

        if (!is_null($dataViewResults)) {

            $cacheJobDataViewKey = CacheReportTools::getCacheJobDataViewKeyFromMemcacheKey($data_view_key);
            if (is_null($cacheJobDataViewKey)) {

                return false;
            }

            $initialMemoryUsage = memory_get_usage();
            $dummyCopy = Tools::recCopy($dataViewResults);
            $newMemoryUsage = memory_get_usage();
            $itemSizeInBytes = abs($newMemoryUsage - $initialMemoryUsage);
            unset($dummyCopy);

            return new CacheJobDataViewStats(
                count($dataViewResults),
                $itemSizeInBytes
            );
        }

        return false;
    }

    /**
     * Return a md5(json_encode($parameters))
     *
     * @param array $input_parameters
     * @param Report $report
     * @return string
     */
    public static function getInputParameterHashFromRawParameter(array $input_parameters, Report $report): string
    {
        ReportTools::orderInputParameters($input_parameters);
        $input_parameters = ReportTools::orderInputParametersValues($report->parameters(), $input_parameters);
        return CacheReportTools::getInputParametersHash($input_parameters);
    }

    /**
     * Return a md5(json_encode($parameters))
     * This method assume that the parameters name, and values are correctly ordered.
     *
     * @param array $input_parameters
     * @return string
     */
    public static function getInputParametersHash(array $input_parameters): string
    {
        return md5(json_encode($input_parameters));
    }

    public static function getReportParametersKey(int $report_id, int $report_data_view_id, array $parameters): string
    {
        $reportParametersKey = self::CACHE_REFRESH_JOB_REPORT_PARAMETERS_KEY_PREFIX
            . '-' . 0
            . '-' . $report_id
            . '-' . self::getInputParametersHash($parameters)
            . '-' . $report_data_view_id;

        Log::debug('CacheReportTools::getReportParametersKey - reportParametersKey', [$reportParametersKey]);
        return $reportParametersKey;
    }

    /**
     * @return string crjrpk-<job_id>-<report_id>-<md5(parameters)>-<data_view_id>
     */
    public static function getReportParametersKeyWithCacheJob(ExecReportInfo $execReportInfo, ReportDataView $dataView): string
    {
        return self::CACHE_REFRESH_JOB_REPORT_PARAMETERS_KEY_PREFIX
            . '-' . $execReportInfo->cacheJob->id
            . '-' . $execReportInfo->cacheJob->report_id
            . '-' . self::getInputParametersHash($execReportInfo->inputParameters())
            . '-' . $dataView->id;
    }

    public static function getReportCacheStatus(int $report_id): ReportCacheStatus
    {
        $report_has_cache = false;
        $report_has_job_cache = false;
        $report_has_user_cache = false;
        $num_parameter_sets_cached_by_jobs = 0;
        $num_parameter_sets_cached_by_users = 0;

        $allCacheJobsIds = DB::select('select  `cache_job_id`, `input_parameters_hash` from `report_caches` where `report_id` = ? group by  `cache_job_id`, `input_parameters_hash`;', [$report_id]);

        /** @var CacheJob $cacheJob */
        foreach ($allCacheJobsIds as $cacheJob) {

            $report_has_cache = true;
            if (is_null($cacheJob->cache_job_id)) {
                $num_parameter_sets_cached_by_users++;
                $report_has_user_cache = true;
            } else {
                $num_parameter_sets_cached_by_jobs++;
                $report_has_job_cache = true;
            }
        }

        return new ReportCacheStatus($report_id, $report_has_cache, $report_has_job_cache, $report_has_user_cache, $num_parameter_sets_cached_by_jobs, $num_parameter_sets_cached_by_users);
    }

    /**
     * Update cache related columns from `report` table and send results on organization websocket channel.
     *
     * @param Report $report
     * @return Report
     */
    public static function updateReportCacheStatus(Report $report): Report
    {
        $reportCacheStatus = CacheReportTools::getReportCacheStatus($report->id);
        $report->update(
            [
                'has_cache'                          => $reportCacheStatus->has_cache,
                'has_job_cache'                      => $reportCacheStatus->has_job_cache,
                'has_user_cache'                     => $reportCacheStatus->has_user_cache,
                'num_parameter_sets_cached_by_jobs'  => $reportCacheStatus->num_parameter_sets_cached_by_jobs,
                'num_parameter_sets_cached_by_users' => $reportCacheStatus->num_parameter_sets_cached_by_users,
            ]);

        APICacheReportUpdateCacheStatus::dispatch(
            $report->organization_id,
            [$reportCacheStatus]
        );

        return $report;
    }


    public static function updateFrontendReportStateCacheStatus(Report $report): void
    {
        foreach (TrackLoggedUserTools::getLoggedUserOnReport($report) as $loggedUserOnReport) {

            ReportCacheUpdated::dispatch(
                $report->id,
                $loggedUserOnReport->currentOrganizationLoggedUser->web_socket_session_id,
                CacheReportTools::getReportCacheStatus($report->id)
            );
        }
    }
}
