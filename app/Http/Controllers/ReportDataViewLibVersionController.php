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

use App\Http\Resources\ReportDataViewLibVersion as ReportDataViewLibVersionResource;
use App\Models\ReportDataViewLibVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReportDataViewLibVersionController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        // To display ReportDataViewLib resource.
        $request->request->add(['report_data_view_lib' => 1]);

        return ReportDataViewLibVersionResource::collection(
            ReportDataViewLibVersion::with('reportDataViewLib')
                ->paginate(2000)
        );
    }

    public function show(Request $request, ReportDataViewLibVersion $reportDataViewLibVersion): JsonResponse
    {
        // To display ReportDataViewLib resource.
        $request->request->add(['report_data_view_lib' => 1]);

        return response()->json(new ReportDataViewLibVersionResource($reportDataViewLibVersion));
    }
}
