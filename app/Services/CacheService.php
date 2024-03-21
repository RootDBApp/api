<?php

namespace App\Services;

use App\Enums\EnumCacheType;
use App\Http\Resources\UIGrantsResource;
use App\Models\ReportCache;
use App\Models\ExecReportInfo;
use App\Models\Report;
use App\Models\ReportCacheInfo;
use App\Models\ReportDataView;
use App\Models\RoleGrants;
use App\Models\User;
use App\Tools\CacheReportTools;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\InvalidArgumentException;

// It's a service but also a Singleton because we need it in Http\Resources\* files.
class CacheService
{
    public Collection $roleGrantsCollection;
    private string $role_grants_cache_key = '';
    private bool $initialized = false;

    public function __construct()
    {
        $this->roleGrantsCollection = new Collection();
        $this->init();
    }

    public function init(): void
    {
        // Role
        //
        //Cache::delete($this->role_grants_cache_key);
        if (Cache::has($this->role_grants_cache_key)) {

            $this->roleGrantsCollection = new Collection(Cache::get($this->role_grants_cache_key));

        } else {

            Log::debug('[CacheService::init] We need to refresh the Role grants cache.');
            $this->roleGrantsCollection = RoleGrants::all();
            try {
                Cache::set($this->role_grants_cache_key, $this->roleGrantsCollection, 3360);
            } catch (InvalidArgumentException $e) {

                Log::error('[CacheService::init]', [$e]);
            }
        }

        if ($this->roleGrantsCollection->count() === 0) {

            Log::error('[CacheService::init] Unable to get / generate the Role Grants cache.');
        } else {

            $this->initialized = true;
        }
    }

    public function getUiRoleGrants(User $user): AnonymousResourceCollection|false
    {
        $ui_role_grants = [];
        $current_route = '';
        $current_route_ui_edit = false;

        // when someone is displaying an embedded report, on the same domain where RootDB is installed, and the web-browser
        // contains an inactive session of a dev user.
        if (!isset($user->currentOrganizationLoggedUser->roles)) {

            return false;
        }

        /** @var RoleGrants $roleGrant */
        foreach ($this->roleGrantsCollection->whereIn('role_id', $user->currentOrganizationLoggedUser->roles) as $roleGrant) {

            $looped_route = str_replace('-', '_', $roleGrant->route_label);

            // First loop.
            if ($current_route === '') {
                $current_route = $looped_route;
            }

            if ($current_route !== $looped_route) {

                $ui_role_grants[$current_route]['edit'] = $current_route_ui_edit;

                $current_route = $looped_route;
                $current_route_ui_edit = false;
            }

            if ($current_route_ui_edit === false && $roleGrant->ui_edit === true) {

                $current_route_ui_edit = true;
            }
        }

        // Last looped grant
        $ui_role_grants[$current_route]['edit'] = $current_route_ui_edit;

        return UIGrantsResource::collection($ui_role_grants);
    }

    public function cacheReportResultsFromUser(Report $report, int $report_data_view_id, array $results, array $parameters, int $ttl = 3600): false|string
    {
        Log::debug('CacheService::cacheReportResults - Report ID: ' . $report->id . ' | Data View ID: ' . $report_data_view_id . ' | storing results in cache...', ['nb_results', count($results)]);

        $key = CacheReportTools::getDataViewKey($report->id, $report_data_view_id, $parameters);
        $areResultsCached = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)
            ->put($key, $results, $ttl);

        if ($areResultsCached === false) {

            Log::warning('CacheService::cacheReportResults - Unable to store the results in cache. (you should probably increase memcached\'s max-item-size )');
            return false;
        }

        Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_TAGS)
            ->put(CacheReportTools::getReportParametersKey($report->id, $report_data_view_id, $parameters),
                  json_encode($parameters),
                  $ttl
            );

        return $key;
    }

    public function cacheReportResultsFromJob(ExecReportInfo $execReportInfo, ReportDataView $dataView, array $results): false|string
    {
        Log::debug('CacheService::cacheReportResultsFromJob - Cache job ID: ' . $execReportInfo->cacheJob->id . ' | Report ID: ' . $execReportInfo->cacheJob->report_id . ' | Data View ID: ' . $dataView->id . '| storing results in cache...', ['nb_results', count($results)]);

        $key = CacheReportTools::getDataViewKeyWithExecReportInfo($execReportInfo, $dataView);
        $areResultsCached = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)
            ->put($key, $results, $execReportInfo->cacheJob->ttl);

        if ($areResultsCached === false) {

            Log::warning('CacheService::cacheReportResultsFromJob - Unable to store the results in cache. (you should probably increase memcached\'s max-item-size )');
            return false;
        }

        Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_TAGS)
            ->put(CacheReportTools::getReportParametersKeyWithCacheJob($execReportInfo, $dataView),
                  json_encode($execReportInfo->rawInputParameters()),
                  $execReportInfo->cacheJob->ttl
            );

        return $key;
    }

    /**
     * /!\ We update here ExecReportInfo::$dataViewKeys
     *
     * @param ExecReportInfo $execReportInfo
     * @param Report $report
     * @return ReportCacheInfo
     */
    public function checkIfAllDataViewsResultsAreCached(ExecReportInfo &$execReportInfo, Report $report): ReportCacheInfo
    {
        $num_views_with_cache = 0;

        Log::debug('Parameters: ', [$execReportInfo->inputParametersFlattened()]);
        Log::debug('Parameters hash: ', [CacheReportTools::getInputParametersHash($execReportInfo->inputParameters())]);

        $cached_at = new \DateTime();
        $cache_type = EnumCacheType::JOB;

        foreach (ReportCache::where('input_parameters_hash', '=', CacheReportTools::getInputParametersHash($execReportInfo->inputParameters()))
            ->where('report_id', '=', $report->id)
            ->get() as $reportCache
        ) {

            Log::debug('Looped cache-report key from database: ' . $reportCache->cache_key, []);
            if (Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->has($reportCache->cache_key)) {

                Log::debug('Found in cache.', []);
                $execReportInfo->dataViewKeys[$reportCache->report_data_view_id] = $reportCache->cache_key;
                $num_views_with_cache++;

                $cache_type = $reportCache->cache_type;
                $cached_at = $reportCache->updated_at;
            }
        }

        return new ReportCacheInfo(
            $num_views_with_cache === $report->dataViews->count(),
            $cached_at,
            $cache_type
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteCache(string $key): bool
    {
        Log::debug('CacheService::deleteCache | key : ' . $key);

        return Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->delete($key);
    }

    public function getDataViewResults(ExecReportInfo $execReportInfo, ReportDataView $dataView): array|null
    {
        if (!isset($execReportInfo->dataViewKeys[$dataView->id])) {

            Log::warning('CacheService::getDataViewResults - Unable to get data view key.', [$execReportInfo->dataViewKeys]);
            return null;
        }

        return Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->get($execReportInfo->dataViewKeys[$dataView->id]);
    }

    public function getInstance(): CacheService
    {
        return $this->_getSingleton();
    }

    private function _getSingleton(): CacheService|bool
    {
        try {

            /** @var CacheService $cacheServiceInstance */
            $cacheServiceInstance = app()->make('App\Services\CacheService');
            if ($cacheServiceInstance->initialized === false) {

                $cacheServiceInstance->init();
            }

            return $cacheServiceInstance;

        } catch (BindingResolutionException $e) {

            Log::debug($e);
        }

        return false;
    }
}
