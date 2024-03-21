<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class OrganizationPolicy extends CommonPolicy
{
    use HandlesAuthorization;

    public function store(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::store($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        return Response::allow('', 200);
    }
}
