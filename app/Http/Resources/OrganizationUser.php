<?php

namespace App\Http\Resources;

use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\Organization as OrganizationResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserPreferences as UserPreferencesResource;
use App\Http\Resources\Role as RoleResource;
use App\Services\CacheService;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\NoReturn;

/** @mixin \App\Models\OrganizationUser */
class OrganizationUser extends JsonResource
{
    private CacheService $cacheService;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->cacheService = (new CacheService())->getInstance();
    }

    #[NoReturn]
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->user_id,
            'organization_id' => $this->organization_id,

            'groups' => $this->when(
                (int)$request->get('groups') === 1,
                function () {
                    return GroupResource::collection($this->groups);
                }
            ),

            'group_ids' => $this->when(
                (int)$request->get('groups') === 1,
                function () {
                    return $this->groups->map(function (\App\Models\Group $group, $key) {
                        return $group->id;
                    });
                }
            ),

            'organization' => $this->when(
                (int)$request->get('organization') === 1,
                function () {
                    return OrganizationResource::make($this->organization);
                }
            ),

            'roles' => $this->when(
                (int)$request->get('roles') === 1,
                function () {
                    return RoleResource::collection($this->roles);
                }
            ),

            'role_ids' => $this->when(
                (int)$request->get('roles') === 1,
                function () {
                    return $this->roles->map(function (\App\Models\Role $role, $key) {
                        return $role->id;
                    });
                }
            ),

            'ui_grants' => $this->when(
                true, // (bool)$request->get('for-login') === true
                function () {

                    return $this->cacheService->getUiRoleGrants(auth()->user());
                }
            ),

            // do-not-display-user used into UserController@index
            'user'      => $this->when(
                (bool)$request->get('do-not-display-user') === false,
                function () {
                    return UserResource::make($this->user);
                }
            ),

            'user_preferences' => $this->when(
                (int)$request->get('user-preferences') === 1,
                function () {
                    return UserPreferencesResource::make($this->userPreferences);
                }
            ),
        ];
    }
}

