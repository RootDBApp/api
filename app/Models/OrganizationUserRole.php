<?php

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
