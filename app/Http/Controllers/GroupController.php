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

use App\Events\APICacheGroupsUpdated;
use App\Models\Group;
use App\Http\Resources\Group as GroupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\App;
use Validator;

class GroupController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $this->genericAuthorize($request, new Group(), false);

        return GroupResource::collection((new Group)->where('organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)->paginate(5000));
    }

    public function destroy(Request $request, Group $group): JsonResponse
    {
        $this->genericAuthorize($request, $group);

        $group->delete();

        if (App::environment() !== 'testing') {

            APICacheGroupsUpdated::dispatch($group->organization_id);
        }

        return $this->successResponse(null, 'Group deleted.');
    }

    public function show(Request $request, Group $group): JsonResponse
    {
        $this->genericAuthorize($request, $group);

        return response()->json(new GroupResource($group));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Group::make($request->toArray()));
        $request->request->add(['organization_id' => auth()->user()->currentOrganizationLoggedUser->organization_id]);

        $validator = Validator::make(
            $request->all(),
            Group::$rules
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the group.', $validator->errors(), 422);
        }

        $group = Group::create($request->all());

        if (App::environment() !== 'testing') {

            APICacheGroupsUpdated::dispatch($group->organization_id);
        }

        return $this->successResponse(new GroupResource($group), 'The group has been created.');
    }

    public function update(Request $request, Group $group): JsonResponse
    {
        $this->genericAuthorize($request, $group);

        $group->update($request->all());

        if (App::environment() !== 'testing') {

            APICacheGroupsUpdated::dispatch($group->organization_id);
        }

        return $this->successResponse(new GroupResource($group), 'The group has been updated.');
    }
}
