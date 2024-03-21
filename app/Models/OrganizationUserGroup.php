<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\GroupOrganizationUser
 *
 * @property int $id
 * @property int $organization_user_id
 * @property int $group_id
 * @property-read Group|null $group
 * @property-read OrganizationUser|null $organizationUser
 * @method static Builder|OrganizationUserGroup newModelQuery()
 * @method static Builder|OrganizationUserGroup newQuery()
 * @method static Builder|OrganizationUserGroup query()
 * @method static Builder|OrganizationUserGroup whereGroupId($value)
 * @method static Builder|OrganizationUserGroup whereId($value)
 * @method static Builder|OrganizationUserGroup whereOrganizationUserId($value)
 * @mixin Eloquent
 */
class OrganizationUserGroup extends Model
{
    public $timestamps = false;

    protected $table = 'organization_user_group';

    protected $fillable = [
        'organization_user_id',
        'group_id',
    ];

    public function group(): HasOne
    {
        return $this->hasOne('App\Models\Group', 'id', 'group_id');
    }

    public function organizationUser(): HasOne
    {
        return $this->hasOne('App\Models\OrganizationUser', 'id', 'organization_user_id')
            ->with('user')
            ->with('organization')
            ->with('roles');
    }
}
