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

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrganizationUser
 *
 * @property int $id
 * @property int $organization_user_id
 * @property int $role_id
 * @property-read OrganizationUser|null $organizationUser
 * @property-read Role|null $role
 * @method static Builder|OrganizationUserRole newModelQuery()
 * @method static Builder|OrganizationUserRole newQuery()
 * @method static Builder|OrganizationUserRole query()
 * @method static Builder|OrganizationUserRole whereId($value)
 * @method static Builder|OrganizationUserRole whereOrganizationUserId($value)
 * @method static Builder|OrganizationUserRole whereRoleId($value)
 * @mixin Eloquent
 */
class OrganizationUserRole extends Model
{
    public $timestamps = false;

    protected $table = 'organization_user_role';

    protected $fillable = [
        'organization_user_id',
        'role_id',
    ];

    public function role(): HasOne
    {
        return $this->hasOne('App\Models\Role', 'id', 'role_id');
    }

    public function organizationUser(): HasOne
    {
        return $this->hasOne('App\Models\OrganizationUser', 'id', 'organization_user_id');
    }

}
