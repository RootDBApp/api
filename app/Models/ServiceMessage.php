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
use Illuminate\Support\Carbon;

/**
 * App\Models\ServiceMessage
 *
 * @property int $id
 * @property string $title
 * @property string $contents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Organization[] $organizations
 * @property-read int|null $organization_count
 * @method static Builder|ServiceMessage newModelQuery()
 * @method static Builder|ServiceMessage newQuery()
 * @method static Builder|ServiceMessage query()
 * @method static Builder|ServiceMessage whereContents($value)
 * @method static Builder|ServiceMessage whereCreatedAt($value)
 * @method static Builder|ServiceMessage whereId($value)
 * @method static Builder|ServiceMessage whereTitle($value)
 * @method static Builder|ServiceMessage whereUpdatedAt($value)
 * @property-read int|null $organizations_count
 * @mixin Eloquent
 */
class ServiceMessage extends ApiModel
{
    protected $fillable = [
        'title',
        'contents',
    ];

    public static array $rules = [
        'title'    => 'required|between:1,255',
        'contents' => 'required|between:1,999999',
    ];

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class);
    }
}
