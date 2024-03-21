<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReportDataViewLibTypes
 *
 * @property int $id
 * @property int $report_data_view_lib_version_id
 * @property string $label
 * @property string $name
 * @property-read ReportDataViewLibVersion $reportDataViewLibVersion
 * @method static Builder|ReportDataViewLibTypes newModelQuery()
 * @method static Builder|ReportDataViewLibTypes newQuery()
 * @method static Builder|ReportDataViewLibTypes query()
 * @method static Builder|ReportDataViewLibTypes whereId($value)
 * @method static Builder|ReportDataViewLibTypes whereLabel($value)
 * @method static Builder|ReportDataViewLibTypes whereName($value)
 * @method static Builder|ReportDataViewLibTypes whereReportDataViewLibId($value)
 * @method static Builder|ReportDataViewLibTypes whereReportDataViewLibVersionId($value)
 * @mixin Eloquent
 */
class ReportDataViewLibTypes extends ApiModel
{

    public $timestamps = false;

    public function reportDataViewLibVersion(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportDataViewLibVersion');
    }
}
