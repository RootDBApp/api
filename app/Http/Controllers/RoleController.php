<?php

namespace App\Http\Controllers;

use App\Http\Resources\Role as RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Validator;

class RoleController extends ApiController
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $response = $this->genericAuthorize('index', $request, Role::class);
        if (is_a($response, 'Illuminate\Http\JsonResponse')) {
            return $response;
        }
        return RoleResource::collection(Role::paginate(20));
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        $jsonResponse = $this->genericAuthorize('destroy', $request, $role);
        if (is_a($jsonResponse, 'Illuminate\Http\JsonResponse')) {
            return $jsonResponse;
        }

        $role->delete();

        return $this->successResponse(null, 'Role deleted.');
    }

    public function show(Request $request, Role $role): JsonResponse
    {
        $jsonResponse = $this->genericAuthorize('show', $request, $role);
        if (is_a($jsonResponse, 'Illuminate\Http\JsonResponse')) {
            return $jsonResponse;
        }

        return response()->json(new RoleResource($role));
    }

    public function store(Request $request): JsonResponse
    {
        $jsonResponse = $this->genericAuthorize('store', $request, Role::class);
        if (is_a($jsonResponse, 'Illuminate\Http\JsonResponse')) {
            return $jsonResponse;
        }


        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|between:2,255',
            ]
        );

        if ($validator->fails()) {

            return $this->errorResponse('Request error', 'Unable to store the role.', $validator->errors(), 422);
        }


        $role = Role::create(
            [
                'name' => $request->input('name'),
            ]
        );

        return $this->successResponse(new RoleResource($role), 'The role has been created.');
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $jsonResponse = $this->genericAuthorize('update', $request, $role);
        if (is_a($jsonResponse, 'Illuminate\Http\JsonResponse')) {
            return $jsonResponse;
        }

        $role->update($request->only(['name']));

        return $this->successResponse(new RoleResource($role), 'The role has been updated.');
    }
}
