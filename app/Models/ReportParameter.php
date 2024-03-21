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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReportParameter
 *
 * @property int $id
 * @property int $report_id
 * @property int $parameter_input_id
 * @property string $name
 * @property string $variable_name
 * @property bool $following_parameter_next_to_this_one
 * @property string|null $forced_default_value
 * @property int $available_public_access
 * @property-read ReportParameterInput $parameterInput
 * @property-read Report|null $report
 * @method static Builder|ReportParameter newModelQuery()
 * @method static Builder|ReportParameter newQuery()
 * @method static Builder|ReportParameter query()
 * @method static Builder|ReportParameter whereId($value)
 * @method static Builder|ReportParameter whereName($value)
 * @method static Builder|ReportParameter whereParameterInputId($value)
 * @method static Builder|ReportParameter whereReportId($value)
 * @method static Builder|ReportParameter whereVariableName($value)
 * @method static Builder|ReportParameter whereFollowingParameterNextToThisOne($value)
 * @method static Builder|ReportParameter whereForcedDefaultValue($value)
 * @method static Builder|ReportParameter whereAvailablePublicAccess($value)
 * @mixin Eloquent
 */
class ReportParameter extends ApiModel
{
    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'parameter_input_id',
        'name',
        'variable_name',
        'forced_default_value',
        'following_parameter_next_to_this_one',
        'available_public_access'
    ];

    public static array $rules = [
        'report_id'                            => 'required|integer|exists:reports,id|',
        'parameter_input_id'                   => 'required|integer|exists:report_parameter_inputs,id',
        'name'                                 => 'required|string|min:2|max:255',
        'variable_name'                        => 'required|string|min:2|max:255',
        'following_parameter_next_to_this_one' => 'required|boolean',
        'available_public_access'              => 'required|boolean',
    ];

    protected $casts = [
        'following_parameter_next_to_this_one' => 'boolean',
        'available_public_access'              => 'boolean'
    ];

    public function parameterInput(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportParameterInput');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo('\App\Models\Report');
    }
}
