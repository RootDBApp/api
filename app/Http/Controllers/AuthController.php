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

namespace App\Http\Controllers;

use App\Http\Resources\User as UserResource;
use App\Models\CurrentOrganizationUserLogged;
use App\Models\OrganizationUser;
use App\Models\User;
use App\Tools\TrackLoggedUserTools;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate(['name' => 'required', 'password' => 'required']);
            $credentials = request(['name', 'password']);

            if (!Auth::attempt($credentials)) {

                return $this->errorResponse('Login error', 'Unable to authenticate with this username and password.', null, 401);
            }

            // Load the User.
            //
            $request->request->add(
                ['user-preferences'    => 1,
                 'organization-users'  => 1,
                 'roles'               => 1,
                 'groups'              => 1,
                 'do-not-display-user' => true,
                 'ui-grants'           => 1,
                ]);
            /** @var User $user */
            $user = User::with(
                ['organizationUsers',
                 'organizationUsers.groups',
                 'organizationUsers.organization',
                 'organizationUsers.organization.confConnectors',
                 'organizationUsers.roles',
                 'organizationUsers.userPreferences'
                ])
                ->where('name', $request->input('name'))
                ->first();


            if (!Hash::check($request->input('password'), $user->password, [])) {

                return $this->errorResponse('Login error', 'Invalid password.', null, 403);
            }

            // Load the OrganizationUser.
            //
            /* @var OrganizationUser $currentOrganizationUser Store the first available OrganizationUser of this User. */
            $currentOrganizationUser = $user->organizationUsers()->first();
            if ($request->input('organization-id')) {

                $currentOrganizationUserLoggedFoundResult = $user->organizationUsers()->firstWhere('organization_id', '=', $request->input('organization-id'));
                if (is_a($currentOrganizationUserLoggedFoundResult, 'App\Models\OrganizationUser')) {

                    $currentOrganizationUser = $currentOrganizationUserLoggedFoundResult;
                } else {

                    $request->request->add(['organization-id' => $currentOrganizationUser->organization->id]);
                }
            }

            if (!isset($currentOrganizationUser) || $currentOrganizationUser->roles->count() === 0) {

                return $this->errorResponse('Login error', 'No role assigned to this user for this organization.', null, 403);
            }

            $currentOrganizationUserLogged = new CurrentOrganizationUserLogged(
                $currentOrganizationUser->id,
                $user->id,
                $currentOrganizationUser->organization_id,
                $currentOrganizationUser->roles->pluck('id')->toArray(),
                $currentOrganizationUser->groups->pluck('id')->toArray()
            );

            session()->put('currentOrganizationLoggedUser', serialize($currentOrganizationUserLogged));

            $user->currentOrganizationLoggedUser = $currentOrganizationUserLogged;
            \auth()->user()->currentOrganizationLoggedUser = $currentOrganizationUserLogged;

            if ($request->input('locale')) {

                $userPreferences = $currentOrganizationUser->userPreferences;
                $userPreferences->lang = $request->input('locale');
                $userPreferences->save();
                session()->put('locale', $userPreferences->lang);
                $user->refresh();
            } else {

                session()->put('locale', 'en');
            }

            TrackLoggedUserTools::flushCacheUserFromUser($user);
            TrackLoggedUserTools::updateCacheUserFromUser($user);

            // Setup what we want in the User resource response.
            //
            // For account creation / deletion from rootdb customer area.
            if ($request->exists('for-ca')) {
                $request->request->add([]);
            } // For frontend
            else {
                $request->request->add(
                    ['conf-connectors'     => 1,
                     'do-not-display-user' => 1,
                     'for-login'           => 1,
                     'groups'              => 1,
                     'organization'        => 1,
                     'organization-user'   => 1,
                     'roles'               => 1,
                     'user-preferences'    => 1,
                    ]);
            }
            $successResponse = $this->successResponse(new UserResource($user), 'Logged.');

            if ($user->first_connection && $user->name !== 'demo') {

                $user->update(['first_connection' => false]);
            }

            return $successResponse;

        } catch (Exception $exception) {

            return $this->exceptionResponse('Login error', $exception->getMessage(), $exception->getTrace(), 403);
        }
    }

    public function logout(Request $request): JsonResponse
    {

        if (is_a(auth()->user(), 'App\Models\User')) {

            TrackLoggedUserTools::flushCacheUserFromUser(auth()->user());
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->successResponse(null, null, 204);
    }
}
