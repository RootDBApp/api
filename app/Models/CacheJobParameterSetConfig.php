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

use DateInterval;
use DateTime;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;


/**
 * App\Models\CacheJobParameterSetConfig
 *
 * @property int $id
 * @property int $cache_job_id
 * @property int $report_parameter_id
 * @property string $value
 * @property string|null $date_start_from_values {values: [default, 1-week, 2-weeks, 3-weeks, 4-weeks, 1-month, 2-months, 4-months, 5-months, 6-months, 1-year, 2-years, 3-years, 4-years, 5-years]}
 * @property string|null $select_values {values: []} - it will generate one query for each value.
 * @property string|null $multi_select_values {values: []} - generally used with IN (x,y,z) in WHERE statement.
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read CacheJob $cacheJob
 * @property-read ReportParameter $parameter
 * @method static Builder|CacheJobParameterSetConfig newModelQuery()
 * @method static Builder|CacheJobParameterSetConfig newQuery()
 * @method static Builder|CacheJobParameterSetConfig query()
 * @method static Builder|CacheJobParameterSetConfig whereCacheJobId($value)
 * @method static Builder|CacheJobParameterSetConfig whereCreatedAt($value)
 * @method static Builder|CacheJobParameterSetConfig whereDateStartFromValues($value)
 * @method static Builder|CacheJobParameterSetConfig whereId($value)
 * @method static Builder|CacheJobParameterSetConfig whereMultiSelectValues($value)
 * @method static Builder|CacheJobParameterSetConfig whereReportParameterId($value)
 * @method static Builder|CacheJobParameterSetConfig whereSelectValues($value)
 * @method static Builder|CacheJobParameterSetConfig whereUpdatedAt($value)
 * @method static Builder|CacheJobParameterSetConfig whereValue($value)
 * @mixin Eloquent
 */
class CacheJobParameterSetConfig extends ApiModel
{
    public $timestamps = true;

    protected $fillable = [
        'cache_job_id',
        'report_parameter_id',
        'value',
        'date_start_from_values',
        'select_values',
        'multi_select_values',
    ];

    public static array $rules = [
        'cache_job_id'           => 'required|integer|exists:cache_jobs,id',
        'report_parameter_id'    => 'required|integer|exists:report_parameters,id',
        'value'                  => 'nullable|string',
        'date_start_from_values' => 'nullable|json',
        'select_values'          => 'required|json',
        'multi_select_values'    => 'nullable|json',
    ];

    public function cacheJob(): BelongsTo
    {
        return $this->belongsTo('\App\Models\CacheJob', 'cache_job_id', 'id', 'cache_jobs');

    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportParameter', 'report_parameter_id', 'id', 'report_parameters');
    }

    /**
     * @return array With all possible values for this parameter.
     * @throws Exception
     */
    public function getValues(): array
    {

        $allValues = [];
        $parameter_value = $this->parameter->parameterInput->getDefaultValue();

        switch ($this->parameter->parameterInput->parameterInputType->name) {

            // If it's a DATE, maybe the dev selected more than the default date to run for the cache generation.
            case 'date':

                foreach (json_decode($this->date_start_from_values)->values as $back_to) {

                    $matches = [];
                    $dateTime = new DateTime($parameter_value);

                    switch (true) {

                        case preg_match('`^([0-9])-week.*`', $back_to, $matches):

                            $dateTime->sub(new DateInterval('P' . $matches[1] . 'W'));
                            break;

                        case preg_match('`^([0-9])-month.*`', $back_to, $matches):

                            $dateTime->sub(new DateInterval('P' . $matches[1] . 'M'));
                            break;

                        case preg_match('`^([0-9])-year.*`', $back_to, $matches):

                            $dateTime->sub(new DateInterval('P' . $matches[1] . 'Y'));
                            break;
                    }

                    $allValues[] = $dateTime->format('Y-m-d');
                }
                break;

            // If it's a SELECT, maybe the dev want to generate cache for multiple value.
            case 'select':
                foreach (json_decode($this->select_values)->values as $value) {

                    $allValues[] = $value;
                }

                break;

            case 'multi-select':

                $allValues[] = implode(',', json_decode($this->multi_select_values)->values);
                break;

            default:

                $allValues[] = $this->value;
                break;
        }

        return $allValues;
    }
}
