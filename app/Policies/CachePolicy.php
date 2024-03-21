<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class CachePolicy extends CommonPolicy
{
    use HandlesAuthorization;

    public function index(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        return Response::allow();
    }
}
