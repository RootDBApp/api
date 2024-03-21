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

use App\Enums\EnumCacheType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;

/**
 * App\Models\CacheReport
 *
 * @property int $id
 * @property string $cache_key
 * @property int $report_id
 * @property string $input_parameters_hash
 * @property int $report_data_view_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Report $report
 * @property-read ReportDataView $reportDataView
 * @method static Builder|ReportCache newModelQuery()
 * @method static Builder|ReportCache newQuery()
 * @method static Builder|ReportCache query()
 * @method static Builder|ReportCache whereCreatedAt($value)
 * @method static Builder|ReportCache whereId($value)
 * @method static Builder|ReportCache whereInputParametersHash($value)
 * @method static Builder|ReportCache whereCachedKey($value)
 * @method static Builder|ReportCache whereReportDataViewId($value)
 * @method static Builder|ReportCache whereReportId($value)
 * @method static Builder|ReportCache whereUpdatedAt($value)
 * @property EnumCacheType $cache_type
 * @method static Builder|ReportCache whereCacheKey($value)
 * @method static Builder|ReportCache whereCacheType($value)
 * @property int|null $cache_job_id
 * @property-read CacheJob|null $cacheJob
 * @method static Builder|ReportCache whereCacheJobId($value)
 * @mixin Eloquent
 */
class ReportCache extends ApiModel
{
    public $timestamps = true;

    protected $fillable = [
        'cache_job_id',
        'cache_key',
        'input_parameters_hash',
        'report_id',
        'report_data_view_id',
        'cache_type'
    ];

    public static function rules(): array
    {
        return [
            'cache_job_id'          => 'integer|exists:cache_jobs,id',
            'cache_key'             => 'required|text',
            'input_parameters_hash' => 'required|text',
            'report_id'             => 'required|integer|exists:reports,id',
            'report_data_view_id'   => 'required|integer|exists:report_data_views,id',
            'cache_type'            => [new Enum(EnumCacheType::class)],
        ];
    }

    protected $casts = [
        'cache_type' => EnumCacheType::class
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo('App\Models\Report');
    }

    public function reportDataView(): BelongsTo
    {
        return $this->belongsTo('App\Models\ReportDataView');
    }

    public function cacheJob(): BelongsTo
    {
        return $this->belongsTo('App\Models\CacheJob');
    }
}
