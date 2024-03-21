<?php

namespace App\Http\Controllers;

use App\Http\Resources\Organization as OrganizationResource;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\OrganizationUserRole;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class OrganizationController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->genericAuthorize($request, new Organization(), false);

        if (auth()->user()->id === 1) {

            return OrganizationResource::collection(
                (new Organization())
                    ->with(['organizationUsers', 'organizationUsers.user'])
                    ->withCount('reports')
                    ->paginate(9999)
            );
        }

        return OrganizationResource::collection(
            (new Organization())
                ->whereIn('id',
                          auth()->user()->organizations->map(
                              function (Organization $organization) {

                                  return collect($organization->toArray())->only(['id'])->all();
                              }
                          ))
                ->paginate(9999)
        );
    }

    public function destroy(Request $request, Organization $organization): JsonResponse
    {
        $this->genericAuthorize($request, $organization);

        if ($organization->id === 1) {

            $this->errorResponse("You cannot delete the first organization", '', [], 401);
        }

        $organization->delete();

        return $this->successResponse(null, 'Organization deleted.');
    }

    public function show(Request $request, Organization $organization): JsonResponse
    {
        $this->genericAuthorize($request, $organization);

        return response()->json(new OrganizationResource($organization));
    }

    public function store(Request $request): JsonResponse
    {
        $this->genericAuthorize($request, Organization::make($request->toArray()));

        $validator = Validator::make(
            $request->all(),
            Organization::$rules,
            Organization::$rule_messages,
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the organization.', $validator->errors(), 422);
        }

        $organization = Organization::create(
            [
                'name' => $request->input('name'),
            ]
        );

        // Only super-admin can create an Organization, so we `hard-code` the `user_id` here.
        $organizationUser = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => 1]);
        OrganizationUserRole::create(['organization_user_id' => $organizationUser->id, 'role_id' => Role::ADMIN]);
        OrganizationUserRole::create(['organization_user_id' => $organizationUser->id, 'role_id' => Role::DEV]);
        OrganizationUserRole::create(['organization_user_id' => $organizationUser->id, 'role_id' => Role::VIEWER]);

        if (count((array)$request->input('user_ids')) > 0) {

            foreach ((array)$request->input('user_ids') as $user_id) {

                // Add Users
                OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user_id]);
            }
        }

        return $this->successResponse(new OrganizationResource($organization), 'The organization has been created.');
    }

    public function update(Request $request, Organization $organization): JsonResponse
    {
        $this->genericAuthorize($request, $organization);

        $validator = Validator::make(
            $request->all(),
            Organization::$rules,
            Organization::$rule_messages
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request Error', 'Unable to update the organization.', $validator->errors(), 422);
        }

        $organization->update($request->only(['name']));

        if (count((array)$request->input('user_ids')) > 0) {

            foreach ((array)$request->input('user_ids') as $user_id) {

                // Add Users
                if (!OrganizationUser::where('organization_id', '=', $organization->id)->where('user_id', '=', $user_id)->exists()) {

                    $organizationUser = OrganizationUser::create(['organization_id' => $organization->id, 'user_id' => $user_id]);
                    OrganizationUserRole::create(['organization_user_id' => $organizationUser->id, 'role_id' => Role::VIEWER]);
                }

                // Remove Users
                /** @var OrganizationUser $organizationUser */
                foreach (OrganizationUser::where('organization_id', '=', $organization->id)->where('user_id', '!=', 1)->get() as $organizationUser) {

                    if (!in_array($organizationUser->user_id, (array)$request->input('user_ids'))) {

                        OrganizationUser::where('organization_id', '=', $organization->id)->where('user_id', '=', $organizationUser->user_id)->delete();
                    }
                }
            }
        }

        return $this->successResponse(new OrganizationResource($organization), 'The organization has been updated.');
    }
}
