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
