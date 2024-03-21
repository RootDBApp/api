<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\Role;
use App\Models\User;
use App\Tools\CommonTranslation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class UserPolicy extends CommonPolicy
{
    use HandlesAuthorization;

    public function show(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::show($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        // Exclude inactive User if current logged User have not the ADMIN role.
        if ($model->is_active === false &&
            ((auth()->user()->id !== 1 && !in_array(Role::ADMIN, auth()->user()->currentOrganizationLoggedUser->roles)))
        ) {

            $response = Response::deny(CommonTranslation::unableToViewResource, 401);
        }

        // Only super-admin can display the super-admin User.
        if ($model->id === 1 && auth()->user()->id !== 1) {

            $response = Response::deny(CommonTranslation::unableToViewResource, 401);
        }

        return $response;
    }

    public function store(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::store($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        return Response::allow('', 200);
    }

    public function update(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::show($user, $model, $request, $checkOrganizationUser);
        if ($response->allowed()) {

            return $response;
        }

        // Allow self update user profile.
        if ($user->id === $model->id) {

            return Response::allow('', 200);
        }

        return Response::deny(CommonTranslation::unableToUpdateResource, 401);
    }
}
