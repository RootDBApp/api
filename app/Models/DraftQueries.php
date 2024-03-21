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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\DraftQueries
 *
 * @property int $id
 * @property int $draft_id
 * @property string|null $queries
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Draft|null $draft
 * @method static Builder|DraftQueries newModelQuery()
 * @method static Builder|DraftQueries newQuery()
 * @method static Builder|DraftQueries query()
 * @method static Builder|DraftQueries whereCreatedAt($value)
 * @method static Builder|DraftQueries whereDraftId($value)
 * @method static Builder|DraftQueries whereId($value)
 * @method static Builder|DraftQueries whereQueries($value)
 * @method static Builder|DraftQueries whereUpdatedAt($value)
 * @mixin Eloquent
 */
class DraftQueries extends ApiModel
{
    protected $fillable = [
        'draft_id',
        'queries'
    ];

    public static array $rules = [
        'draft_id' => 'integer|exists:drafts,id',
    ];


    public function draft(): BelongsTo
    {
        return $this->belongsTo('App\Models\Draft');
    }
}
