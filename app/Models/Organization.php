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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|ConfConnector[] $confConnectors
 * @property-read Collection|OrganizationUser[] $organizationUsers
 * @property-read Collection|Report[] $reports
 * @property-read Collection|UserPreferences[] $userPreferences
 * @property-read int|null $conf_connectors_count
 * @property-read int|null $organization_users_count
 * @property-read int|null $reports_count
 * @property-read int|null $user_preferences_count
 * @method static Builder|Organization newModelQuery()
 * @method static Builder|Organization newQuery()
 * @method static Builder|Organization query()
 * @method static Builder|Organization whereCreatedAt($value)
 * @method static Builder|Organization whereId($value)
 * @method static Builder|Organization whereName($value)
 * @method static Builder|Organization whereUpdatedAt($value)
 * @property-read Collection|\App\Models\ServiceMessage[] $serviceMessages
 * @property-read int|null $service_messages_count
 * @mixin Eloquent
 */
class Organization extends ApiModel
{
    protected $fillable = [
        'name',
    ];

    public static $rules = [
        'name' => 'required|between:2,255',
    ];

    public function confConnectors(): HasMany
    {
        return $this->hasMany('App\Models\ConfConnector');
    }

    public function organizationUsers(): HasMany
    {
        return $this->hasMany('App\Models\OrganizationUser');
    }

    public function reports(): HasMany
    {
        return $this->hasMany('App\Models\Report');
    }

    public function serviceMessages(): BelongsToMany
    {
        return $this->belongsToMany('\App\Models\ServiceMessage');
    }

    public function userPreferences(): HasMany
    {
        return $this->HasMany('App\Models\UserPreferences');
    }
}
