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

use App\Enums\EnumCacheType;
use App\Events\APICacheReportCreated;
use App\Events\APICacheReportDeleted;
use App\Events\APICacheReportUpdated;
use App\Events\ReportCacheUpdated;
use App\Http\Resources\Report as ReportResource;
use App\Http\Resources\ReportParameter as ReportParameterResource;
use App\Models\ReportParameterSets;
use App\Http\Resources\ReportParameterSets as ReportParameterSetsResource;
use App\Models\CacheJob;
use App\Models\ReportCache;
use App\Models\ExecReportInfo;
use App\Models\Report;
use App\Models\ReportDataView;
use App\Models\ReportGroup;
use App\Models\ReportLiveUserConfig;
use App\Models\ReportParameter;
use App\Models\ReportUser;
use App\Services\CacheService;
use App\Services\ConnectorService;
use App\Tools\CacheReportTools;
use App\Tools\CommonTranslation;
use App\Tools\ReportTools;
use App\Tools\Tools;
use App\Tools\TrackLoggedUserTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\SimpleCache\InvalidArgumentException;
use Validator;

class ReportController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse|null
    {
        $this->genericAuthorize($request, new Report(), false);

        // To display all related resource relations in JSON response.
        $request->request->add(
            [
                'favorite' => 1,
            ]
        );

        return response()->json(ReportTools::getReportCollection($request, auth()->user()));
    }

    /**
     * Cache current results in user cache.
     */
    public function cacheResults(Request $request, Report $report, CacheService $cacheService): JsonResponse
    {
        $this->genericAuthorize($request, $report, false, 'index');

        $rawInputParameters = (array)$request->get('parameters');
        $inputParameters = (array)$request->get('parameters');
        ReportTools::orderInputParameters($inputParameters);
        $inputParameters = ReportTools::orderInputParametersValues($report->parameters(), $inputParameters);

        Log::debug('Parameters hash: ', [CacheReportTools::getInputParametersHash($inputParameters)]);

        $report->reportCaches()
            ->where('input_parameters_hash', '=', CacheReportTools::getInputParametersHash($inputParameters))
            ->where('report_id', '=', $report->id)
            ->where('cache_job_id', '=', null)
            ->delete();

        /** @var $cacheReports */
        $cacheReports = [];

        foreach ($request->get('results') as $dataViewResult) {

            $key = $cacheService->cacheReportResultsFromUser(
                $report,
                (int)$dataViewResult['reportDataViewId'],
                (array)$dataViewResult['results'],
                $rawInputParameters,
                $request->get('ttl')
            );

            if ($key === false) {

                $report->reportCaches()
                    ->where('input_parameters_hash', '=', CacheReportTools::getInputParametersHash($inputParameters))
                    ->where('report_id', '=', $report->id)
                    ->delete();

                return $this->errorResponse('Error caching', 'There was an issue while caching the results.', [], 500);
            }

            $cacheReport = new ReportCache(
                [
                    'cache_key'             => $key,
                    'input_parameters_hash' => CacheReportTools::getInputParametersHash($inputParameters),
                    'report_id'             => $report->id,
                    'report_data_view_id'   => (int)$dataViewResult['reportDataViewId'],
                    'cache_type'            => EnumCacheType::USER->value
                ]
            );

            $cacheReports[] = $cacheReport->toArray();
        }

        $report->reportCaches()->createMany(collect($cacheReports));

        $report = CacheReportTools::updateReportCacheStatus($report);
        ReportCacheUpdated::dispatch(
            $report->id,
            auth()->user()->currentOrganizationLoggedUser->web_socket_session_id,
            CacheReportTools::getReportCacheStatus($report->id)
        );

        return $this->successResponse(null, 'Results cached.');
    }

    public function close(Request $request, string $reportIdAndInstanceId): void
    {
        // Needed to have currentOrganizationLoggedUser initialized.
        $this->genericAuthorize($request, new Report(), true, 'index');

        auth()->user()->currentOrganizationLoggedUser->removeReportLiveUserConfig($reportIdAndInstanceId);
        TrackLoggedUserTools::updateSessionAndCacheFromUser(auth()->user());

        Log::debug('close', [auth()->user()->currentOrganizationLoggedUser->getReportLiveConfigs()]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteUserCache(Request $request, Report $report, CacheService $cacheService): JsonResponse
    {
        $this->genericAuthorize($request, $report, false, 'index');

        if ($request->exists('parameters')) {

            $input_parameters_hash = CacheReportTools::getInputParameterHashFromRawParameter((array)$request->get('parameters'), $report);
            Log::debug('Delete user cache, only for a set of parameter.', [$input_parameters_hash]);

            /** @var ReportCache $reportCache */
            foreach ($report->reportCaches()
                ->where('input_parameters_hash', '=', $input_parameters_hash)
                ->where('report_id', '=', $report->id)
                ->get()->all() as $reportCache) {

                if ($cacheService->deleteCache($reportCache->cache_key) === false) {

                    return $this->errorResponse('Error while deleting user cache', '', [], 500);
                }

                $reportCache->delete();
            }

        } else {

            /** @var ReportCache $reportCache */
            foreach ($report->reportCaches()
                ->where('cache_key', 'LIKE', CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX . '-0%')
                ->where('report_id', '=', $report->id)
                ->get()->all() as $reportCache) {


                if ($cacheService->deleteCache($reportCache->cache_key) === false) {

                    return $this->errorResponse('Error while deleting user cache', '', [], 500);
                }

                $reportCache->delete();
            }
        }

        $report = CacheReportTools::updateReportCacheStatus($report);

        ReportCacheUpdated::dispatch(
            $report->id,
            auth()->user()->currentOrganizationLoggedUser->web_socket_session_id,
            CacheReportTools::getReportCacheStatus($report->id)
        );

        return $this->successResponse(null, 'User cached deleted.');
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        $this->genericAuthorize($request, $report);

        if (App::environment() !== 'testing') {

            APICacheReportDeleted::dispatch($report);
        }

        $report->delete();

        return $this->successResponse(null, 'Report deleted.');
    }

    public function run(Report $report, ConnectorService $connectorService, Request $request): JsonResponse
    {
        $this->genericAuthorize($request, $report, true, 'index');

        if (ReportTools::checkShowPermissionByReport($report, auth()->user()) === false) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to run this report.', [], 401);
        }

        $connectorInstance = $connectorService->getInstance($report->confConnector);
        $result = $connectorInstance->execReport(
            $report,
            new ExecReportInfo(
                (array)$request->get('parameters'),
                $request->get('instanceId'),
                auth()->user()->currentOrganizationLoggedUser,
                null,
                null,
                $request->get('useCache'),
                true
            )
        );

        if ($result === false) {

            return $this->errorResponse('Error', 'Unable to dispatch report.', [], 503);
        }

        return $this->successResponse(null, 'Report successfully dispatched.');
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        $this->genericAuthorize($request, $report);

        if (ReportTools::checkShowPermissionByReport($report, auth()->user()) === false) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        auth()->user()->currentOrganizationLoggedUser->addReportLiveUserConfig(
            new ReportLiveUserConfig($report->id, $report->id . '_' . $request->get('instanceId'), $report->auto_refresh)
        );
        TrackLoggedUserTools::updateSessionAndCacheFromUser(auth()->user());

        Log::debug('show', [auth()->user()->currentOrganizationLoggedUser->getReportLiveConfigs()]);

        $request->request->add([
                                   'parameters'              => 1,
                                   'parameter-default-value' => 1,
                                   'parameter-values'        => 1,
                               ]);

        return response()->json(new ReportResource($report));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Report::make($request->toArray()));

        $request->request->add(['user_id' => auth()->user()->id]);

        $validator = Validator::make(
            $request->all(),
            Report::$rules,
            Report::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the report.', $validator->errors(), 422);
        }

        // We do not set this below when creating a report, so no need to handle it properly here.
        $request->request->set('public_authorized_referers', '');
        $request->request->set('public_security_hash', (string)Str::of(Tools::getRandomChars(20))->pipe('sha1'));

        $request->request->add(['query_init' => '-- You can write multiple queries here, to setup all temporary tables and variables you need for all data view\'s queries.']);
        $report = Report::create($request->toArray());

        $this->_updateUsersAndGroups($request, $report);

        if (App::environment() !== 'testing') {

            APICacheReportCreated::dispatch($report);
        }

        // To display all related resource relations in JSON response.
        $request->request->add(
            [
                'allowed-groups' => 1,
                'allowed-users'  => 1,
            ]
        );

        return $this->successResponse(new ReportResource($report), 'The report has been created.');
    }

    public function update(Request $request, Report $report): JsonResponse
    {
        $request->request->add(['user_id' => $report->user_id]);
        $request->request->remove('public_security_hash');

        $this->genericAuthorize($request, $report);

        $validator = Validator::make(
            $request->all(),
            Report::$rules,
            Report::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the report.', $validator->errors(), 422);
        }

        if ($public_authorized_referers = (array)$request->get('public_authorized_referers')) {

            $request->request->set('public_authorized_referers', implode(',', $public_authorized_referers));
        } else {

            $request->request->set('public_authorized_referers', '');
        }

        $report->update($request->toArray());

        ReportGroup::whereReportId($report->id)->delete();
        ReportUser::whereReportId($report->id)->delete();
        $this->_updateUsersAndGroups($request, $report);

        // To display  resources.
        $request->request->add(
            [
                'allowed-groups' => 1,
                'allowed-users'  => 1,
            ]);

        if (App::environment() !== 'testing') {

            APICacheReportUpdated::dispatch($report);
        }

        // To display  resources.
        $request->request->add(
            [
                'parameters'       => 1,
                'parameter-values' => 1,
            ]);

        return $this->successResponse(new ReportResource($report), 'The report has been updated.');
    }

    public function updateDataViewsLayout(Request $request, Report $report): JsonResponse
    {
        $this->genericAuthorize($request, $report, true, 'update');

        // @todo - should protect this one.
        foreach ($request->get('layouts') as $layout) {

            ReportDataView::where('id', $layout['dataViewId'])->update(['position' => $layout['layout']]);
        }
        return $this->successResponse($request->get('layouts'), 'The data views layout has been updated.');
    }

    public function parametersSetsInCache(Request $request, Report $report): JsonResponse
    {
        $this->genericAuthorize($request, $report, true, 'show');

        $request->request->add(
            [
                'parameter-default-value' => 1,
                'parameter-values'        => 1
            ]
        );

        return response()->json($request->get('type') === 'job' ? $this->_getCacheJobReportParameterSets($request, $report) : $this->_getUserCacheReportParameterSets($report));
    }

    public function updateVisibility(Request $request, Report $report)
    {
        $this->genericAuthorize($request, $report, true, 'update');

        $report->update(['is_visible' => $request->get('is_visible')]);

        if (App::environment() !== 'testing') {

            APICacheReportUpdated::dispatch($report);
        }

        return $this->successResponse(new ReportResource($report), 'The report has been updated.');
    }

    public function updateQueries(Request $request, Report $report): JsonResponse
    {
        $this->genericAuthorize($request, $report, true, 'update');

        $report->update(['query_init'    => $request->get('query_init'),
                         'query_cleanup' => $request->get('query_cleanup')]
        );

        return $this->successResponse(new ReportResource($report), 'The report has been updated.');
    }

    private function _updateUsersAndGroups(Request $request, Report $report)
    {
        // Add into table `report_groups`
        if (is_array($request->input('group_ids'))) {
            foreach ($request->input('group_ids') as $group_id) {
                ReportGroup::create(['report_id' => $report->id, 'group_id' => (int)$group_id])->save();
            }
        }

        // Add into table `report_users`
        if (is_array($request->input('user_ids'))) {
            foreach ($request->input('user_ids') as $user_id) {
                ReportUser::create(['report_id' => $report->id, 'user_id' => (int)$user_id])->save();
            }
        }
    }

    public static function _getUserCacheReportParameterSets(Report $report): array
    {
        /** @var ReportParameterSetsResource[] $allCacheJobParametersSets */
        $allCacheJobParametersSets = [];

        $current_parameter_hash = '';
        /** @var ReportCache $reportCache */
        foreach (ReportCache::with(['report', 'reportDataView'])
            ->select(['cache_key', 'input_parameters_hash', 'updated_at'])
            ->where('cache_key', 'LIKE', 'crjdvk-0-%')
            ->where('report_id', '=', $report->id)
            ->orderBy('report_id')
            ->orderBy('input_parameters_hash')
            ->whereNull('cache_job_id')
            ->get() as $reportCache) {

            if ($reportCache->input_parameters_hash == $current_parameter_hash) {

                continue;
            }
            $current_parameter_hash = $reportCache->input_parameters_hash;

            $cacheReportParameters = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_TAGS)
                ->get(
                    str_replace(
                        CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX,
                        CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_KEY_PREFIX,
                        $reportCache->cache_key
                    )
                );

            if (is_null($cacheReportParameters)) {
                continue;
            }

            $reportParameterSets = new ReportParameterSets(
                false,
                0,
                $reportCache->updated_at,
                self::_getReportParameterSets($report, json_decode($cacheReportParameters))
            );

            $allCacheJobParametersSets[] = ReportParameterSetsResource::make($reportParameterSets);

        }

        return $allCacheJobParametersSets;
    }

    public static function _getCacheJobReportParameterSets(Request $request, Report $report): array
    {
        /** @var ReportParameterSetsResource[] $allCacheJobParametersSets */
        $allCacheJobParametersSets = [];

        $cacheJobs = CacheJob::with(['cacheJobParameterSetConfigs', 'report.dataViews'])->where('report_id', '=', $report->id)->get();

        /** @var CacheJob $cacheJob */
        foreach ($cacheJobs as $cacheJob) {
            foreach ($cacheJob->getAllCacheJobParameterSets() as $cacheJobParameterSets) {
                foreach ($cacheJobParameterSets->getAllParametersSets() as $parametersSets) {

                    // No need to loop all data views.
                    $data_view_key = CacheReportTools::getDataViewKeyWithCacheJob($cacheJob, $cacheJob->report->dataViews[0], $parametersSets);
                    /** @var array<object> $cacheReportParameters */
                    $cacheReportParameters = Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_TAGS)
                        ->get(
                            str_replace(
                                CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_KEY_PREFIX,
                                CacheReportTools::CACHE_REFRESH_JOB_REPORT_PARAMETERS_KEY_PREFIX,
                                $data_view_key
                            )
                        );

                    if (is_null($cacheReportParameters)) {
                        continue;
                    }

                    $reportParameterSets = new ReportParameterSets(
                        true,
                        $cacheJob->id,
                        $cacheJob->updated_at,
                        self::_getReportParameterSets($report, json_decode($cacheReportParameters))
                    );

                    $allCacheJobParametersSets[] = ReportParameterSetsResource::make($reportParameterSets);
                }
            }
        }

        return $allCacheJobParametersSets;
    }

    /**
     * @param Report $report
     * @param array $cacheReportParameters
     * @return AnonymousResourceCollection
     */
    public static function _getReportParameterSets(Report $report, array $cacheReportParameters): AnonymousResourceCollection
    {
        /** @var ReportParameter[] $reportParameters */
        $reportParameters = [];

        foreach ($cacheReportParameters as $cacheReportParameter) {

            $reportParameter = null;
            $reportParameter = clone $report->parameters->where('variable_name', '=', $cacheReportParameter->name)->first();
            $reportParameter->parameterInput->default_value = $cacheReportParameter->value;
            $reportParameters[] = $reportParameter;
        }

        return ReportParameterResource::collection($reportParameters);
    }
}
