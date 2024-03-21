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

use App\Http\Resources\PublicReportDataView as ReportDataViewResource;
use App\Models\ReportDataView;
use App\Tools\CommonTranslation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicReportDataViewController extends PublicApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if (!$request->get('sh')) {

            return $this->errorResponse(CommonTranslation::accessDenied, 'You are not granted to view this report.', [], 401);
        }

        $queryBuilder = ReportDataView::query();

        if ((int)$request->input('report-id') >= 1) {


            $queryBuilder->where('report_id', '=', (int)$request->input('report-id'));
            $queryBuilder->where('is_visible', '=', 1);
            $queryBuilder->with('reportDataViewLibVersion');
            $queryBuilder->with('reportDataViewJs');

        } else {

            // In any cases, display the report-id field.
            $request->request->add(['report-id' => 0]);
        }

        return ReportDataViewResource::collection($queryBuilder->paginate(20));
    }
}
