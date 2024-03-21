<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReportDataViewLibVersion
 *
 * @property int $id
 * @property int $report_data_view_lib_id
 * @property string $major_version
 * @property string $version
 * @property string $url_documentation
 * @property bool $default
 * @property-read string $name
 * @property-read ReportDataViewLib $reportDataViewLib
 * @method static Builder|ReportDataViewLibVersion newModelQuery()
 * @method static Builder|ReportDataViewLibVersion newQuery()
 * @method static Builder|ReportDataViewLibVersion query()
 * @method static Builder|ReportDataViewLibVersion whereDefault($value)
 * @method static Builder|ReportDataViewLibVersion whereId($value)
 * @method static Builder|ReportDataViewLibVersion whereMajorVersion($value)
 * @method static Builder|ReportDataViewLibVersion whereReportDataViewLibId($value)
 * @method static Builder|ReportDataViewLibVersion whereUrlDocumentation($value)
 * @method static Builder|ReportDataViewLibVersion whereVersion($value)
 * @mixin Eloquent
 */
class ReportDataViewLibVersion extends ApiModel
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'default' => 'boolean'
    ];

    public function reportDataViewLib(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportDataViewLib');
    }
}
