<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\UserPreferences
 *
 * @property int $id
 * @property string|null $lang
 * @property string|null $theme
 * @property int $organization_user_id
 * @property-read OrganizationUser|null $organizationUser
 * @method static Builder|UserPreferences newModelQuery()
 * @method static Builder|UserPreferences newQuery()
 * @method static Builder|UserPreferences query()
 * @method static Builder|UserPreferences whereId($value)
 * @method static Builder|UserPreferences whereLang($value)
 * @method static Builder|UserPreferences whereTheme($value)
 * @method static Builder|OrganizationUserGroup whereOrganizationUserId($value)
 * @mixin Eloquent
 */
class UserPreferences extends ApiModel
{
    public $timestamps = false;

    protected $fillable = [
        'organization_user_id',
        'lang',
        'theme'
    ];

    public static array $rules = [
        'organization_user_id' => 'integer|exists:organization_user,id',
        'lang'                 => 'required|max:2',
        'theme'                => 'required|between:1,100',
    ];

    public function organizationUser(): HasOne
    {
        return $this->hasOne('App\Models\OrganizationUser', 'id', 'organization_user_id');
    }
}
