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

namespace App\Tools;

use App\Http\Resources\User as UserResource;
use App\Models\Role;
use App\Models\RoleGrants;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserTools
{
    public static function getUserCollection(): AnonymousResourceCollection
    {
        $request = request();

        // @todo - still in use somewhere ?
        if ((int)$request->input('organization-users') === 1) {

            // To display Role, Organization, Group in OrganizationUser resource.
            $request->request->add(['roles' => 1, 'groups' => 1, 'do-not-display-user' => true]);
        }

        // User management is _only_ for role ADMIN or super-admin User
        $where_is_active_operator = '=';
        $where_is_active_value = 1;
        $where_user_id_operator = '>=';
        $where_user_id_value = 1;

        if ($request->exists('for-admin') &&
            (auth()->user()->id === 1 || in_array(Role::ADMIN, auth()->user()->currentOrganizationLoggedUser->roles))
        ) {

            $where_is_active_operator = '>=';
            $where_is_active_value = 0;
            $where_user_id_operator = '!=';
            $where_user_id_value = auth()->user()->id;

            $request->request->add(
                [// organization-id -> for User resource -> organization_users and return only one OrganizationUser.
                 'organization-id'     => auth()->user()->currentOrganizationLoggedUser->organization_id,
                 'organization-user'   => 1,
                 'roles'               => 1,
                 'groups'              => 1,
                 'do-not-display-user' => true
                ]);
        } else {

            $request->request->add(
                [
                    'organization-id'     => auth()->user()->currentOrganizationLoggedUser->organization_id,
                    'organization-user'   => 1,
                    'roles'               => 1,
                    'do-not-display-user' => true
                ]);
        }

        // Only used by super-admin, for OrganizationUsers administration.
        if ($request->exists('for-dropdown') && auth()->user()->id === 1) {

            return UserResource::collection(
                (new User())
                    ->where('id', '!=', 1)
                    ->orderBy('name')
                    ->paginate(9999)
            );
        }

        return UserResource::collection(
            (new User())
                ->with(['organizationUsers',
                        'organizationUsers.groups',
                        'organizationUsers.roles',
                        'organizationUsers.organization',
                        'organizationUsers.organization.confConnectors',
                        'organizationUsers.user'
                       ])
                ->whereRelation('organizationUsers', 'organization_id', '=', auth()->user()->currentOrganizationLoggedUser->organization_id)
                ->where('is_active', $where_is_active_operator, $where_is_active_value)
                ->where('id', '!=', 1)
                ->where('id', $where_user_id_operator, $where_user_id_value)
                ->orderBy('name')
                ->paginate($request->exists('for-admin') ? 10 : 9999)
        );

    }

    public static function checkIfUserAuthorized(CacheService $cacheService, string $route_name, string $ability): bool
    {
        $roleGrant = $cacheService->roleGrantsCollection->search(

            function (RoleGrants $roleGrant) use ($route_name, $ability) {

                if (in_array($roleGrant->role_id, auth()->user()->currentOrganizationLoggedUser->roles)
                    && $roleGrant->route_name === $route_name) {

                    return (int)$roleGrant->{$ability} === 1;
                }
            }
        );

        return !($roleGrant === false);
    }
}
