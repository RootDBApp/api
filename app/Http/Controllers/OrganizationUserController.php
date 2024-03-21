<?php

namespace App\Http\Controllers;

use App\Events\APICacheUsersUpdated;
use App\Http\Resources\OrganizationUser as OrganizationUserResource;
use App\Models\OrganizationUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class OrganizationUserController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $queryBuilder = OrganizationUser::with('organization')->with('user');
        if ((int)$request->input('roles') === 1) {

            $queryBuilder->with('roles');
        }

        if ((int)$request->input('organization') > 0) {

            $request->request->add(['groups' => 1]);
            $request->request->add(['roles' => 1]);
            $queryBuilder
                ->with('roles')
                ->with('groups')
                ->where('organization_id', '=', $request->input('organization'));

            if ($request->input('user') > 0) {

                $request->request->add(['user-preferences' => 1]);
                $queryBuilder->where('user_id', '=', $request->input('user'));
            }
        }

        return OrganizationUserResource::collection($queryBuilder->paginate(20));
    }

    public function destroy(Request $request, OrganizationUser $organizationUser): JsonResponse
    {
        $organizationUser->delete();

        $this->_setupRequestForResource($request);
        APICacheUsersUpdated::dispatch(
            $organizationUser->user()->with('organizationUsers')->first()
        );

        return $this->successResponse(null, 'User deleted from the organization.');
    }

    public function show(Request $request, OrganizationUser $organization_user): JsonResponse
    {
        $request->request->add(['user-preferences' => 1]);

        return response()->json(new OrganizationUserResource($organization_user));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'organization_id' => 'required|integer',
                'user_id'         => 'required|integer',
                'role_id'         => 'required|integer',
            ]
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to link the user to the organization.', $validator->errors(), 422);
        }


        $organizationUser = OrganizationUser::create(
            [
                'organization_id' => $request->input('organization_id'),
                'user_id'         => $request->input('user_id'),
                'role_id'         => $request->input('role_id'),
            ]
        );

        $this->_setupRequestForResource($request);
        APICacheUsersUpdated::dispatch(
            $organizationUser->user()->with('organizationUsers')->first()
        );

        return $this->successResponse(new OrganizationUserResource($organizationUser), 'The user has been linked to the organization.');
    }

    public function update(Request $request, OrganizationUser $organizationUser): JsonResponse
    {

        $this->_setupRequestForResource($request);
        APICacheUsersUpdated::dispatch(
            $organizationUser->user()->with('organizationUsers')->first()
        );

        return $this->successResponse(new OrganizationUserResource($organizationUser), "The user's role in the organization has been updated.");
    }

    private function _setupRequestForResource(Request $request)
    {
        $request->request->add(['groups' => 1]);
        $request->request->add(['roles' => 1]);
        $request->request->add(['organization-users' => 1]);
        $request->request->add(['do-not-display-user' => 1]);

    }
}
