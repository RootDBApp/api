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

use App\Events\APICacheReportUpdated;
use App\Http\Resources\ReportDataView as ReportDataViewResource;
use App\Http\Resources\ReportDataViewForListing as ReportDataViewForListingResource;
use App\Models\ExecReportInfo;
use App\Models\Report;
use App\Models\ReportDataView;
use App\Models\ReportDataViewLibTypes;
use App\Models\Role;
use App\Models\User;
use App\Services\CacheService;
use App\Services\ConnectorService;
use App\Tools\CommonTranslation;
use App\Tools\ReportTools;
use App\Tools\UserTools;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Validator;

class ReportDataViewController extends ApiController
{
    public function index(CacheService $cacheService, Request $request): AnonymousResourceCollection|JsonResponse|null
    {
        $this->genericAuthorize($request, new ReportDataView(), false);

        $queryBuilder = ReportDataView::query();

        if ((int)$request->input('report-id') >= 1) {

            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));

            if ((int)$request->input('for-listing') === 0) {

                $queryBuilder->with('reportDataViewLibVersion');
                $queryBuilder->with('reportDataViewJs');
            }

        } else {

            $queryBuilder->whereRelation('report', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id);

            $request->request->add(['report-id' => 0]);
        }

        if (auth()->user()) {

            if (!UserTools::checkIfUserAuthorized($cacheService, 'report-data-view', 'ui_edit')) {

                $queryBuilder->where('is_visible', '=', 1);
            }
        }

        if ((int)$request->input('for-listing') === 1) {

            return ReportDataViewForListingResource::collection($queryBuilder->paginate(100));
        }

        return ReportDataViewResource::collection($queryBuilder->paginate(100));
    }

    public function destroy(Request $request, ReportDataView $reportDataView): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataView);

        $reportDataView->delete();

        $this->_reportUpdate($reportDataView);

        return $this->successResponse(null, 'Data view deleted.');
    }

    public function show(Request $request, ReportDataView $reportDataView): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataView);

        if (auth()->user()->is_super_admin === false && ReportTools::checkShowPermissionByReport($reportDataView->report, auth()->user()) === false) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        if (User::searchIfLoggedUserHasRole(Role::DEV)) {

            $request->request->add([]);
        }


        return response()->json(new ReportDataViewResource($reportDataView));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, ReportDataView::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            ReportDataView::$rules,
            ReportDataView::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the data view.', $validator->errors(), 422);
        }

        $sql_template = '-- Use   @<variable_name>   to get your input parameter value.
-- Use   find_in_set(<pattern>, <strlist>))   function to use a list of selected values from an input paramter. Ex: SELECT ... WHERE ( @<variable_name> = \'\'  OR find_in_set(<column_name>, @<variable_name>) )
-- CTRL+ENTER  (CMD+ENTER @mac) - to save and execute the query.
-- ALT+SHIFT+V                  - toggle editor fullscreen mode. (because ALT+SHIFT+F will toggle Firefox file menu :/ )
';

        // Handle template files.
        if ($request->exists('report_data_view_lib_type_id') && $request->input('type') === ReportDataView::GRAPH) {

            Log::debug('We have a chart model to use', [$request->get('report_data_view_lib_type_id')]);
            $reportDataViewLibType = ReportDataViewLibTypes::find($request->get('report_data_view_lib_type_id'));
            if (is_a($reportDataViewLibType, '\App\Models\ReportDataViewLibTypes')) {

                Log::debug('Chart model', [$reportDataViewLibType->name]);
                $file_name = 'templates/data_view_lib_versions/' . $request->get('report_data_view_lib_version_id') . '/' . $reportDataViewLibType->label . '.sql';
                try {
                    if (Storage::disk('local')->exists($file_name)) {

                        Log::debug('Using template', [$file_name]);
                        $sql_template .= Storage::disk('local')->get($file_name);
                    }
                } catch (Exception $exception) {

                    abort(500, $exception->getMessage());
                }
            }
        } else if ($request->input('type') === ReportDataView::INFO) {

            Log::debug('We have a info widget to setup', [$request->get('report_data_view_lib_type_id')]);
            $file_name = 'templates/report_data_view_lib_types/' . ReportDataView::INFO . '/default.sql';
            try {
                if (Storage::disk('local')->exists($file_name)) {

                    Log::debug('Using template', [$file_name]);
                    $sql_template .= Storage::disk('local')->get($file_name);
                }
            } catch (Exception $exception) {

                abort(500, $exception->getMessage());
            }
        }

        $request->request->add(['query' => $sql_template]);

        $reportDataView = ReportDataView::create($request->toArray());

        $this->_reportUpdate($reportDataView);

        return $this->successResponse(new ReportDataViewResource($reportDataView), 'The data view has been created.');
    }

    public function update(Request $request, ReportDataView $reportDataView): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataView);

        $request->request->add(['report_id' => $reportDataView->report_id]);

        $validator = Validator::make(
            $request->all(),
            ReportDataView::$rules,
            ReportDataView::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to update the data view.', $validator->errors(), 422);
        }

        $reportDataView->update($request->toArray());

        $this->_reportUpdate($reportDataView);

        // To display linked resources
        $request->request->add(['report-id' => $reportDataView->report_id]);

        return $this->successResponse(new ReportDataViewResource($reportDataView), 'The data view has been updated.');
    }

    public function updateQuery(Request $request, ReportDataView $reportDataView): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataView, true, 'update');

        $reportDataView->update(['query' => $request->get('query')]);

        return $this->successResponse(new ReportDataViewResource($reportDataView), 'The data view has been updated.');
    }

    public function run(ReportDataView $reportDataView, ConnectorService $connectorService, Request $request): JsonResponse
    {
        $this->genericAuthorize($request, $reportDataView, true, 'index');

        if (auth()->user()->is_super_admin === false && ReportTools::checkShowPermissionByReport($reportDataView->report, auth()->user()) === false) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to run this report.', [], 401);
        }

        $connectorInstance = $connectorService->getInstance($reportDataView->report->confConnector);
        $result = $connectorInstance->execReportDataView(
            $reportDataView,
            new ExecReportInfo(
                (array)$request->get('parameters'),
                $request->get('instanceId'),
                auth()->user()->currentOrganizationLoggedUser,
                null,
                null,
                $request->get('useCache')
            )
        );

        if ($result === false) {

            return $this->errorResponse('Error', 'Unable to dispatch the data view.', [], 417);
        }

        return $this->successResponse(null, 'Data view successfully dispatched.');
    }

    private function _reportUpdate(ReportDataView $reportDataView): void
    {
        $report = Report::findOrFail($reportDataView->report_id);
        if ($report) {

            $report->has_data_views = $report->dataViews()->count() > 0;
            $report->update();
            APICacheReportUpdated::dispatch($report);
        }
    }
}
