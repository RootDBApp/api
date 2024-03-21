<?php

namespace App\Policies;

use App\Models\ApiModel;
use App\Models\DraftQueries;
use App\Models\User;
use App\Tools\CommonTranslation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

class DraftQueriesPolicy extends CommonPolicy
{
    use HandlesAuthorization;

    public function store(User $user, ApiModel|DraftQueries|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::show($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        if ($model->draft->user_id === auth()->user()->id) {

            return Response::allow('', 200);
        }

        return Response::deny(CommonTranslation::unableToCreateResource, 401);
    }

    public function update(User $user, ApiModel|DraftQueries|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::update($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        if ((int)$request->input('draft_id') === $model->draft->id
            && $model->draft->user_id === auth()->user()->id) {

            return Response::allow('', 200);
        }


        return Response::deny(CommonTranslation::unableToCreateResource, 401);
    }

    public function destroy(User $user, ApiModel|DraftQueries|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        $response = parent::destroy($user, $model, $request, $checkOrganizationUser);
        if ($response->denied()) {

            return $response;
        }

        if ($model->draft->user_id === auth()->user()->id) {

            return Response::allow('', 200);
        }

        return Response::deny(CommonTranslation::unableToDeleteResource, 401);
    }
}
