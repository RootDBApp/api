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

use Awobaz\Compoships\Compoships;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\ReportDataView
 *
 * @property int $id
 * @property int $report_id
 * @property int $type 1: table, 2: graph
 * @property string $name
 * @property string|null $title
 * @property string|null $description
 * @property int $description_display_type 1: overlay, 2: text
 * @property boolean $by_chunk
 * @property integer $chunk_size
 * @property string|null $query
 * @property int|null $max_width
 * @property string $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $report_data_view_lib_version_id
 * @property int $is_visible
 * @property bool $on_queue
 * @property-read Report $report
 * @property-read ReportDataViewLibVersion $reportDataViewLibVersion
 * @property-read ReportDataViewJs $reportDataViewJs
 * @method static Builder|ReportDataView newModelQuery()
 * @method static Builder|ReportDataView newQuery()
 * @method static Builder|ReportDataView query()
 * @method static Builder|ReportDataView whereByChunk($value)
 * @method static Builder|ReportDataView whereChunkSize($value)
 * @method static Builder|ReportDataView whereCreatedAt($value)
 * @method static Builder|ReportDataView whereId($value)
 * @method static Builder|ReportDataView whereIsVisible($value)
 * @method static Builder|ReportDataView whereMaxWidth($value)
 * @method static Builder|ReportDataView whereOnQueue($value)
 * @method static Builder|ReportDataView whereQuery($value)
 * @method static Builder|ReportDataView whereReportId($value)
 * @method static Builder|ReportDataView whereTitle($value)
 * @method static Builder|ReportDataView whereType($value)
 * @method static Builder|ReportDataView whereUpdatedAt($value)
 * @method static Builder|ReportDataView wherePosition($value)
 * @method static Builder|ReportDataView whereReportDataViewLibVersionId($value)
 * @property int $num_runs
 * @property int $num_seconds_all_run
 * @property int $avg_seconds_by_run
 * @method static Builder|Report whereAvgSecondsByRun($value)
 * @method static Builder|Report whereNumRuns($value)
 * @method static Builder|Report whereNumSecondsAllRun($value)
 * @mixin Eloquent
 */
class ReportDataView extends ApiModel
{
    use Compoships;

    public const TABLE = 1;
    public const GRAPH = 2;
    public const CRON = 3;
    public const INFO = 4;
    public const TREND = 5;

    protected $fillable = [
        'report_id',
        'type',
        'name',
        'title',
        'description',
        'description_display_type',
        'query',
        'by_chunk',
        'chunk_size',
        'max_width',
        'position',
        'report_data_view_lib_version_id',
        'is_visible',
        'on_queue',
        'num_runs',
        'num_seconds_all_run',
        'avg_seconds_by_run'
    ];

    public static array $rules = [
        'report_id'                       => 'required|integer|exists:reports,id',
        'type'                            => 'required|integer|in:1,2,3,4,5,6',
        'name'                            => 'required|string',
        'title'                           => 'string|nullable',
        'description'                     => 'string|nullable',
        'description_display_type'        => 'integer',
        'query'                           => 'string|nullable', // [0-9]{1,2}
        'by_chunk'                        => 'boolean',
        'chunk_size'                      => 'integer',
        'max_width'                       => 'nullable|integer',
        'position'                        => 'nullable|string',
        'report_data_view_lib_version_id' => 'required|integer',
        'is_visible'                      => 'boolean',
        'on_queue'                        => 'boolean',
        'num_runs'                        => 'integer',
        'num_seconds_all_run'             => 'integer',
        'avg_seconds_by_run'              => 'integer'
    ];

    protected $casts = [
        'by_chunk'                 => 'boolean',
        'is_visible'               => 'boolean',
        'on_queue'                 => 'boolean',
        'description_display_type' => 'string'
    ];

    public function cacheReports(): HasMany
    {
        return $this->hasMany('\App\Models\ReportCache');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo('App\Models\Report');
    }

    public function reportDataViewLibVersion(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportDataViewLibVersion');
    }

    public function reportDataViewJs(): BelongsTo
    {
        return $this->belongsTo('App\Models\ReportDataViewJs',
                                ['id', 'report_data_view_lib_version_id'],
                                ['report_data_view_id', 'report_data_view_lib_version_id']
        );
    }
}
