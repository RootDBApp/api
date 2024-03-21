<?php

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
