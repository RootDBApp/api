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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Directory
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $organization_id
 * @property-read Organization|null $organization
 * @method static Builder|Directory newModelQuery()
 * @method static Builder|Directory newQuery()
 * @method static Builder|Directory query()
 * @method static Builder|Directory whereDescription($value)
 * @method static Builder|Directory whereId($value)
 * @method static Builder|Directory whereName($value)
 * @method static Builder|Directory whereCreatedAt($value)
 * @method static Builder|Directory whereUpdatedAt($value)
 * @method static Builder|Directory whereParentId($value)
 * @method static Builder|Directory whereOrganizationId($value)
 * @mixin Eloquent
 */
class Directory extends ApiModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'organization_id',
        'parent_id',
        'description',
    ];

    public static array $rules = [
        'name'            => 'required|between:2,255',
        'organization_id' => 'integer|exists:organizations,id',
        'parent_id'       => 'nullable|integer|exists:directories,id',
        'description'     => ''
    ];

    public function organization(): HasOne
    {
        return $this->hasOne('App\Models\Organization');
    }
}
