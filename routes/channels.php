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

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Public channels
|--------------------------------------------------------------------------
*/

// Used when a report is displayed from another host.
Broadcast::channel('public.user.{user_id}', function ($public_user_id) {

    return true;
},                 ['guards' => ['sanctum']]);

/*
|--------------------------------------------------------------------------
| Private channels
|--------------------------------------------------------------------------
*/

// * send results to view / reports with auto-refresh enabled.
// * send updated API data like groups, reports, view, parameters & co.
Broadcast::channel('organization.{organization_id}', function (User $user, int $organization_id) {

    return $user->currentOrganizationLoggedUser->organization_id === $organization_id;
},                 ['guards' => ['sanctum']]);

// * send results to views
// * send results to SQL console (for dev only)
Broadcast::channel('user.{web_socket_session_id}', function (User $user, string $web_socket_session_id) {


    if (session()->get('currentOrganizationLoggedUser') !== null) {

        return unserialize(session()->get('currentOrganizationLoggedUser'))->web_socket_session_id === $web_socket_session_id;
    }

    return false;
},                 ['guards' => ['sanctum']]);
