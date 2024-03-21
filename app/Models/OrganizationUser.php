<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\OrganizationUser
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property-read int|null $groups_count
 * @property-read int|null $roles_count
 * @property-read int|null $user_preferences_count
 * @property-read User|null $user
 * @property-read Organization|null $organization
 * @property-read Collection|Group[] $groups
 * @property-read Collection|null $roles
 * @property-read Collection|null $userPreferences
 * @method static Builder|OrganizationUser newModelQuery()
 * @method static Builder|OrganizationUser newQuery()
 * @method static Builder|OrganizationUser query()
 * @method static Builder|OrganizationUser whereId($value)
 * @method static Builder|OrganizationUser whereOrganizationId($value)
 * @method static Builder|OrganizationUser whereRoleId($value)
 * @method static Builder|OrganizationUser whereUserId($value)
 * @method static Builder|UserPreferences whereUserPreferences($value)
 * @mixin Eloquent
 */
class OrganizationUser extends Model
{
    public $timestamps = false;

    protected $table = 'organization_user';

    protected $fillable = [
        'organization_id',
        'user_id',
    ];

    public function organization(): HasOne
    {
        return $this->hasOne('App\Models\Organization', 'id', 'organization_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function userPreferences(): HasOne
    {
        return $this->hasOne('App\Models\UserPreferences');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Group', 'organization_user_group');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Role', 'organization_user_role');
    }
}
