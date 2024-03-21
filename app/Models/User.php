<?php

namespace App\Models;

use App\Services\CacheService;
use App\Tools\TrackLoggedUserTools;
use Database\Factories\UserFactory;
use Eloquent;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * App\Models\User
 *
 * @property int $id
 * @property int|null $user_preferences_id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $lastname
 * @property string|null $firstname
 * @property string $password
 * @property bool $is_super_admin
 * @property bool $is_active
 * @property bool $reset_password
 * @property bool $first_connection
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection|OrganizationUser[] $organizationUsers
 * @property-read int|null $organization_users_count
 * @property-read Collection|Organization[] $organizations
 * @property-read int|null $organizations_count
 * @property-read Collection|PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @property-read UserPreferences|null $userPreferences
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereIsActive($value)
 * @method static Builder|User whereIsSuperAdmin($value)
 * @method static Builder|User whereResetPassword($value)
 * @method static Builder|User whereFirstConnection($value)
 * @method static Builder|User whereLastname($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUserPreferencesId($value)
 * @method static UserFactory factory(...$parameters)
 * @property-read int|null $user_preferences_count
 * @mixin Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public CurrentOrganizationUserLogged|null $currentOrganizationLoggedUser = null;

    protected $fillable = [
        'name',
        'email',
        'lastname',
        'firstname',
        'password',
        'is_super_admin',
        'is_active',
        'reset_password',
        'first_connection'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin'    => 'boolean',
        'is_active'         => 'boolean',
        'reset_password'    => 'boolean',
        'first_connection'  => 'boolean',
    ];

    public static array $rules = [
        'email'           => 'nullable|email',
        'firstname'       => 'nullable',
        'lastname'        => 'nullable',
        'password'        => 'required|between:5,255',
        'organization_id' => 'required|integer',
        'role_ids'        => 'required|array'
    ];

    public function organizations(): BelongsToMany
    {
        // Laravel automatically use a table named "organization_user" (from model's name in alphabetical order)
        return $this->belongsToMany('App\Models\Organization');
    }

    public function organizationUsers(): HasMany
    {
        //return $this->hasMany('App\Models\OrganizationUser', 'id', 'user_id');
        return $this->hasMany('App\Models\OrganizationUser');
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany('App\Models\UserPreferences');
    }

    public static function changeCurrentOrganizationUserLogged(User $user, Request $request): OrganizationUserCheck
    {
        // We should have a `organization-id` in the URL parameters in order to change the current Organization.
        if (!$request->exists('organization-id')) {

            return new OrganizationUserCheck(false, null, Response::deny(trans('Missing `organization ID.'), 400));
        }

        // We now check if this current logged User have access to the requested Organization.
        $organizationUser = null;
        foreach ($user->organizationUsers as $organizationUserLooped) {

            if ($organizationUserLooped->organization_id === (int)$request->get('organization-id')) {

                $organizationUser = $organizationUserLooped;
                break;
            }
        }

        if (is_null($organizationUser)) {

            return new OrganizationUserCheck(false, null, Response::deny(trans('You are not granted to administrate a resource for this organization.'), 401));
        }

        $user->currentOrganizationLoggedUser = new CurrentOrganizationUserLogged(
            $organizationUser->id,
            $user->id,
            $organizationUser->organization_id,
            $organizationUser->roles->pluck('id')->toArray(),
            $organizationUser->groups->pluck('id')->toArray()
        );
        session()->put('currentOrganizationLoggedUser', serialize($user->currentOrganizationLoggedUser));
        TrackLoggedUserTools::updateSessionAndCacheFromUser($user);

        return new OrganizationUserCheck(true, $organizationUser, null);
    }

    public static function searchIfLoggedUserHasRole(int $roleId): bool
    {
        if (!isset(auth()->user()->currentOrganizationLoggedUser->roles)) {

            //Log::warning('User::searchIfLoggedUserHasRole - no roles defined.', [auth()->user()]);
            return false;
        }

        return in_array($roleId, auth()->user()->currentOrganizationLoggedUser->roles);
    }

    /**
     * @param string $permission RoleGrants::PERMISSION_*
     * @param string $resource_name RoleGrants:: RESOURCE_*
     * @return bool
     */
    public static function searchIfLoggedUserHasUiRoleGrant(string $permission, string $resource_name): bool
    {
        if (auth()->guest()) {

            return false;
        }

        $cacheService = (new CacheService())->getInstance();
        $grantCollection = $cacheService->getUiRoleGrants(auth()->user());
        if ($grantCollection === false) {

            return false;
        }

        $has_role_grant = false;
        foreach ($grantCollection as $resource => $ui_grant) {

            if ($resource_name === $resource && $ui_grant[$permission] === true) {

                $has_role_grant = true;
                break;
            }
        }

        return $has_role_grant;
    }
}
