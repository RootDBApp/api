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

use App\Events\ReportDataView;
use App\Http\Resources\Organization;
use App\Http\Resources\UserPreferences;
use App\Models\ApiModel;
use App\Models\CacheJob;
use App\Models\Category;
use App\Models\ConfConnector;
use App\Models\DraftQueries;
use App\Models\Draft;
use App\Models\Report;
use App\Models\ReportDataViewJs;
use App\Models\ReportParameterInput;
use App\Models\RoleGrants;
use App\Models\ServiceMessage;
use App\Models\User;
use App\Services\CacheService;
use App\Tools\CommonTranslation;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CommonPolicy
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        if (session('currentOrganizationLoggedUser')) {

            auth()->user()->currentOrganizationLoggedUser = unserialize(session('currentOrganizationLoggedUser'));
        }
    }

    public function before(User $user, string $ability): Response|bool|null
    {
        if ($user->is_super_admin) {

            // @todo probably useless now
            if ($ability === 'store') {

                return null;
            }

            return true;
        }

        return null;
    }

    public function index(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        if (is_null($user->currentOrganizationLoggedUser)) {

            return Response::deny(CommonTranslation::unableToListResources, 401);
        }

        return $this->_checkRoleGrants($request, $user, $model, 'index', $checkOrganizationUser);
    }

    public function store(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        if (is_null($user->currentOrganizationLoggedUser)) {

            return Response::deny(CommonTranslation::unableToCreateResource, 401);
        }

        return $this->_checkRoleGrants($request, $user, $model, 'store', $checkOrganizationUser);
    }

    public function destroy(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        if (is_null($user->currentOrganizationLoggedUser)) {

            return Response::deny(CommonTranslation::unableToListResources, 401);
        }

        return $this->_checkRoleGrants($request, $user, $model, 'destroy', $checkOrganizationUser);
    }

    public function show(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        if (is_null($user->currentOrganizationLoggedUser)) {

            return Response::deny(CommonTranslation::unableToListResources, 401);
        }

        return $this->_checkRoleGrants($request, $user, $model, 'show', $checkOrganizationUser);
    }

    public function update(User $user, ApiModel|User $model, Request $request, bool $checkOrganizationUser = true): Response
    {
        if (is_null($user->currentOrganizationLoggedUser)) {

            return Response::deny(CommonTranslation::unableToListResources, 401);
        }

        return $this->_checkRoleGrants($request, $user, $model, 'update', $checkOrganizationUser);
    }

    private function _checkRoleGrants(
        Request                                                                                                                                                                          $request,
        User                                                                                                                                                                             $user,
        ApiModel|CacheJob|Category|ConfConnector|Draft|DraftQueries|Organization|Report|ReportDataView|ReportDataViewJs|ReportParameterInput|ServiceMessage|User|UserPreferences $model,
        string                                                                                                                                                                           $ability,
        bool                                                                                                                                                                             $checkOrganizationUser
    ): Response
    {
        $route_name = explode('.', Route::currentRouteName())[0];

        //Log::debug('$route_name                                                                          ', [$route_name]);
        //Log::debug('$ability                                                                             ', [$ability]);
        //Log::debug('$model                                                                               ', [$model]);

        // Loop Resource's Grant by Role.
        $check_result = $this->cacheService->roleGrantsCollection->filter(
            function (RoleGrants $roleGrant) use ($request, $user, $model, $route_name, $ability, $checkOrganizationUser) {

                //Log::debug('$roleGrant->route_name                                                         ', [$roleGrant->route_name]);
                //Log::debug('$roleGrant->route_name !== $route_name                                         ', [$roleGrant->route_name !== $route_name]);

                if ($roleGrant->route_name != $route_name) {

                    return false;
                }

                // Is logged User, in the current OrganizationUser, has the looped Resource's Grant Role
                $roleFound = false;
                foreach ($user->currentOrganizationLoggedUser->roles as $role_id) {
                    if ($role_id === $roleGrant->role_id) {
                        $roleFound = true;
                        break;
                    }
                }

                if (!$roleFound) {

                    return false;
                }

                //$role = $user->currentOrganizationUserLogged->roles->filter(
                //    function (int $role_id) use ($roleGrant) {
                //
                //        return $roleGrant->role_id === $role_id;
                //    });
                //
                //if (!$role->containsOneItem()) {
                //
                //    return false;
                //}
                $model_organization_id = 0;

                // If model is directly an Organization !
                if ($model_organization_id === 0 && is_a($model, '\App\Models\Organization')) {

                    //Log::debug('From $model->id');
                    $model_organization_id = $model->id;
                }
                //
                // If model is a User model, then check if it belongs to the current logged User's OrganizationUser
                // using the `organization_id` field POST/PUTed.
                else if (is_a($model, '\App\Models\User')) {

                    $model_organization_id = 0;
                    if ($ability === 'store' || $ability === 'update') {

                        //Log::debug('From (int)$request->get(\'organization_id\')');
                        $model_organization_id = (int)$request->get('organization_id');

                    } // For all others abilities, the User is created, so it has Organizations.
                    else if ($model->organizations->contains('id', '=', $user->currentOrganizationLoggedUser->organization_id)) {

                        //Log::debug('From $user->currentOrganizationLoggedUser->organization_id');
                        $model_organization_id = $user->currentOrganizationLoggedUser->organization_id;
                    }
                }
                //
                // If model is linked to a ConfConnector
                else if ($model_organization_id === 0 && !is_a($model, '\App\Models\Report') && isset($model->confConnector) || is_a($model->confConnector, '\App\Models\ConfConnector')) {

                    //Log::debug('From $model->confConnector->organization_id');
                    $model_organization_id = $model->confConnector->organization_id;
                }
                //
                // If model is linked to a ReportDataView
                else if ($model_organization_id === 0 && !is_a($model, '\App\Models\ReportDataView') && isset($model->reportDataView) || is_a($model->reportDataView, '\App\Models\ReportDataView')) {

                    //Log::debug('From $model->reportDataView->report->organization_id');
                    $model_organization_id = $model->reportDataView->report->organization_id;
                }
                //
                //  If model is linked to a Report
                else if ($model_organization_id === 0 && isset($model->report) && is_a($model->report, '\App\Models\Report')) {

                    //Log::debug('From $model->report->organization_id');
                    $model_organization_id = $model->report->organization_id;
                }
                //
                // If model is linked to a Draft
                else if ($model_organization_id === 0 && !is_a($model, '\App\Models\Draft') && isset($model->queriesDraft) || is_a($model->draft, '\App\Models\Draft')) {

                    //Log::debug('From $model->draft->confConnector->organization_id');
                    $model_organization_id = $model->draft->confConnector->organization_id;
                }
                //
                // By default, take the current organization ID
                else {

                    //Log::debug('From $user->currentOrganizationLoggedUser->organization_id');
                    $model_organization_id = $user->currentOrganizationLoggedUser->organization_id;
                }


                //Log::debug('----------------------------------');
                //Log::debug('$roleGrant                                                                      ', [$roleGrant]);
                //Log::debug('$checkOrganizationUser                                                          ', [$checkOrganizationUser]);
                //Log::debug('$roleGrant->organization_user_bound                                             ', [$roleGrant->organization_user_bound]);
                //Log::debug('$model_organization_id                                                          ', [$model_organization_id]);
                //Log::debug('$user->currentOrganizationLoggedUser->organization_id                           ', [$user->currentOrganizationLoggedUser->organization_id]);
                //Log::debug('(bool)$roleGrant->{$ability} === true                                           ', [(bool)$roleGrant->{$ability} === true]);
                //Log::debug('$checkOrganizationUser === true                                                 ', [$checkOrganizationUser === true]);
                //Log::debug('(bool)$roleGrant->organization_user_bound === true                              ', [$roleGrant->organization_user_bound === true]);
                //Log::debug('$model_organization_id === $user->currentOrganizationLoggedUser->organization_id', [$model_organization_id === $user->currentOrganizationLoggedUser->organization_id]);
                //Log::debug('(bool)$roleGrant->organization_user_bound === false                             ', [$roleGrant->organization_user_bound === false]);
                //Log::debug('$checkOrganizationUser === false                                                ', [$checkOrganizationUser === false]);

                return (bool)$roleGrant->{$ability} === true
                    && (($checkOrganizationUser === true && $roleGrant->organization_user_bound === true
                            && $model_organization_id === $user->currentOrganizationLoggedUser->organization_id)
                        || $roleGrant->organization_user_bound == false
                        || $checkOrganizationUser === false
                    );

                //Log::debug('$role_grant_check                                                               ', [$role_grant_check]);
                //return $role_grant_check;
            });

        if (is_null($check_result->first())) {

            return match ($ability) {
                'index'   => Response::deny(CommonTranslation::unableToListResources, 401),
                'destroy' => Response::deny(CommonTranslation::unableToDeleteResource, 401),
                'show'    => Response::deny(CommonTranslation::unableToViewResource, 401),
                'store'   => Response::deny(CommonTranslation::unableToCreateResource, 401),
                'update'  => Response::deny(CommonTranslation::unableToUpdateResource, 401),
                default   => Response::deny(CommonTranslation::accessDenied, 401),
            };
        }

        return Response::allow();
    }
}
