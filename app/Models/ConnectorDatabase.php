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
 * App\Models\ConnectorDatabase
 *
 * @property int $id
 * @property string $name
 * @property bool $available
 * @method static Builder|ConnectorDatabase newModelQuery()
 * @method static Builder|ConnectorDatabase newQuery()
 * @method static Builder|ConnectorDatabase query()
 * @method static Builder|ConnectorDatabase whereId($value)
 * @method static Builder|ConnectorDatabase whereName($value)
 * @method static Builder|ConnectorDatabase whereAvailable($value)
 * @property-read ConfConnector|null $confConnector
 * @mixin Eloquent
 */
class ConnectorDatabase extends Model
{

    public function confConnector(): HasOne
    {
        return $this->hasOne('App\Models\ConfConnector');
    }
}
