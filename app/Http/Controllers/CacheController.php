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

/*
 * Use by front to cache data in web-browser local storage..
 */

namespace App\Http\Controllers;

use App\Http\Resources\Category as CategoryResource;
use App\Http\Resources\Directory as DirectoryResource;
use App\Http\Resources\PrimeReactTree as PrimeReactTreeResource;
use App\Http\Resources\ReportDataViewLibVersion as ReportDataViewLibVersionResource;
use App\Http\Resources\ReportDataViewLibTypes as ReportDataViewLibTypesResource;
use App\Http\Resources\ReportParameterInput as ReportParameterInputResource;
use App\Http\Resources\Role as RoleResource;
use App\Http\Resources\ServiceMessage as ServiceMessageResource;
use App\Models\Cache;
use App\Models\Category;
use App\Models\Directory;
use App\Models\ReportDataViewLibTypes;
use App\Models\ReportDataViewLibVersion;
use App\Models\ReportParameterInput;
use App\Models\Role;
use App\Models\ServiceMessage;
use App\Tools\PrimeReactTreeDirectoryTools;
use App\Tools\ReportTools;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CacheController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new Cache(), false, 'index');

        // To display all related report's resources relations.
        $request->request->add(
            [
                'favorite'                => 1,
                'allowed-groups'          => 1,
                'report_data_view_lib'    => 1, // To display ReportDataViewLib resource.
                'parameter-default-value' => 1
            ]
        );

        $collections = [
            'directories' => DirectoryResource::collection(
                (new Directory())
                    ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                    ->paginate(2000)
            ),

            'directoriesPrimeReactTree' => PrimeReactTreeResource::collection(
                (new PrimeReactTreeDirectoryTools(
                    (new Directory())->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->get()
                ))->getPrimeReactTree()->all()
            ),

            'categories' => CategoryResource::collection(
                (new Category())
                    ->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                    ->paginate(2000)
            ),

            'parameterInputs' => ReportParameterInputResource::collection(
                (new ReportParameterInput())
                    ->with('confConnector')
                    ->with('parameterInputType')
                    ->with('parameterInputDataType')
                    ->whereRelation('confConnector', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                    ->orderBy('name')
                    ->paginate(2000)
            ),

            'reports' => ReportTools::getReportCollection($request, auth()->user()),

            'reportDataViewLibVersions' => ReportDataViewLibVersionResource::collection(
                (new ReportDataViewLibVersion())
                    ->with('reportDataViewLib')
                    ->paginate(2000)
            ),

            'reportDataViewLibTypes' => ReportDataViewLibTypesResource::collection(
                (new ReportDataViewLibTypes())->orderBy('name')->paginate(2000)
            ),

            'roles' => RoleResource::collection(Role::all()),

            'serviceMessages' => ServiceMessageResource::collection(
                ServiceMessage::with('organizations')
                    ->whereRelation('organizations', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                    ->orderBy('created_at')
                    ->paginate(2000)
            )
        ];

        return response()->json($collections);
    }
}
