<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\User;
use App\Tools\CommonTranslation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class RolePolicy extends CommonPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): Response|bool|null
    {
        if ($user->is_super_admin === true &&
            ($ability === 'destroy' || $ability === 'store')) {

            if ($ability === 'destroy') {
                return Response::deny(CommonTranslation::unableToDeleteResource, 401);
            }

            return Response::deny(CommonTranslation::unableToCreateResource, 401);
        }

        return null;
    }

    public function destroy(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        return Response::deny(CommonTranslation::unableToDeleteResource, 401);
    }

    public function store(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        return Response::deny(CommonTranslation::unableToCreateResource, 401);
    }
}
