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

namespace App\Http\Resources;

use App\Http\Resources\OrganizationUser as OrganizationUserResource;
use App\Models\OrganizationUser;
use App\Models\Role;
use App\Models\User as ModelUser;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ModelUser */
class User extends JsonResource
{
    public function __construct(ModelUser $user)
    {
        parent::__construct($user);
    }

    public function toArray($request): array
    {
        $forAdmin = ModelUser::searchIfLoggedUserHasRole(Role::ADMIN) && $request->exists('for-admin');
        $forDropDown = $request->exists('for-dropdown');

        return [
            // Login and admin
            //
            'email'              => $this->when(!$forDropDown && $forAdmin || $request->exists('from-profil-form') || $request->exists('for-login'), $this->email),
            'email_verified_at'  => $this->when(!$forDropDown && $forAdmin || $request->exists('from-profil-form') || $request->exists('for-login'), $this->email_verified_at),
            // Always returned (except for dropdown)
            'firstname'          => $this->when(!$forDropDown, $this->firstname),
            'lastname'           => $this->when(!$forDropDown, $this->lastname),
            'id'                 => $this->id,
            'is_active'          => $this->when(!$forDropDown, $this->is_active),
            'reset_password'     => $this->when(!$forDropDown, $this->reset_password),
            'first_connection'   => $this->when(!$forDropDown, $this->first_connection),
            'name'               => $this->name,
            // For now, only `user.id = 1` is a super-admin.
            //'is_super_admin'            => $this->is_super_admin,
            'organization_user'  => $this->when(
                !$forDropDown && (int)$request->get('organization-user') === 1,
                function () use ($request) {

                    if ((int)$request->get('organization-id') > 0) {
                        return OrganizationUserResource::make(
                            $this->organizationUsers->filter(
                                function (OrganizationUser $organizationUser) use ($request) {

                                    return $organizationUser->organization_id === (int)$request->get('organization-id');
                                }
                            )->first(),
                        );
                    }

                    // @todo should return a resource entity instead af a collection here. ::make(model->first())
                    // Check why dropdown on front-end use that.
                    return OrganizationUserResource::collection($this->currentOrganizationLoggedUser);
                }
            ),

            'organization_users'    => $this->when(
                !$forDropDown && (int)$request->get('organization-users') === 1,
                function () use ($request) {

                    return OrganizationUserResource::collection($this->organizationUsers);
                }
            ),

            // @todo fix this mess, because can be null
            'web_socket_session_id' => auth()->user()->currentOrganizationLoggedUser->web_socket_session_id
        ];
    }
}
