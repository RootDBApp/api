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

/**
 * App\Models\ParameterInputDataType
 *
 * @property int $id
 * @property int $connector_database_id
 * @property string $name
 * @property string $type_name
 * @property int $custom_entry
 * @method static Builder|ReportParameterInputDataType newModelQuery()
 * @method static Builder|ReportParameterInputDataType newQuery()
 * @method static Builder|ReportParameterInputDataType query()
 * @method static Builder|ReportParameterInputDataType whereConnectorDatabaseId($value)
 * @method static Builder|ReportParameterInputDataType whereCustomEntry($value)
 * @method static Builder|ReportParameterInputDataType whereId($value)
 * @method static Builder|ReportParameterInputDataType whereName($value)
 * @method static Builder|ReportParameterInputDataType whereTypeName($value)
 * @mixin Eloquent
 */
class ReportParameterInputDataType extends Model
{
}
