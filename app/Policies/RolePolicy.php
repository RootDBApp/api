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
