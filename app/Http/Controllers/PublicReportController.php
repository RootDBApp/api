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

use App\Models\ExecReportInfo;
use App\Models\Report;
use App\Services\ConnectorService;
use App\Tools\CommonTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\PublicReport as ReportResource;
use Illuminate\Support\Facades\Log;

class PublicReportController extends PublicApiController
{
    public function show(Request $request, int $report_id): JsonResponse
    {
        // 1 - check security hash.
        if (!$request->get('sh')) {

            Log::warning('Missing security hash parameter ( sh=xyz )' . PHP_EOL);

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        // 2 - check if public access authorized & is visible
        $report = Report::query()
            ->with('confConnector')
            ->with('parameters')
            ->where('public_access', '=', 1)
            ->where('is_visible', '=', '1')
            ->find($report_id);

        if (!is_a($report, 'App\Models\Report')) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'Report not found, or not visible, or public access not activated.', [], 404);
        }

        if ($this->checkPermission($request, $report) === false) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        return response()->json(new ReportResource($report));
    }

    public function run(Report $report, ConnectorService $connectorService, Request $request): JsonResponse
    {
        $connectorInstance = $connectorService->getInstance($report->confConnector);
        $result = $connectorInstance->execReport(
            $report,
            new ExecReportInfo(
                (array)$request->get('parameters'),
                $request->get('instanceId'),
                null,
                ($request->exists('wspuid') ? $request->get('wspuid') : null),
                null,
                $request->get('useCache')
            )
        );
        if ($result === false) {

            return $this->errorResponse('Error', 'Unable to dispatch report.', [], 503);
        }

        return $this->successResponse(null, 'Report successfully dispatched.');
    }

    public function parametersSetsInCache(Request $request, Report $report): JsonResponse
    {
        $request->request->add(
            [
                'parameter-default-value' => 1,
                'parameter-values'        => 1
            ]
        );

        return response()->json($request->get('type') === 'job' ? ReportController::_getCacheJobReportParameterSets($request, $report) : ReportController::_getUserCacheReportParameterSets($report));
    }
}
