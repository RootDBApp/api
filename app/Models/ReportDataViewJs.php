<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ReportDataViewJs
 *
 * @property int $id
 * @property int $report_data_view_id
 * @property int $report_data_view_lib_version_id
 * @property string|null $json_form
 * @property string|null $js_register
 * @property string|null $js_code
 * @property string|null $js_init
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ReportDataViewLib $reportDataViewLib
 * @property-read ReportDataViewLibVersion $reportDataViewLibVersion
 * @property-read ReportDataView $reportDataView
 * @method static Builder|ReportDataViewJs newModelQuery()
 * @method static Builder|ReportDataViewJs newQuery()
 * @method static Builder|ReportDataViewJs query()
 * @method static Builder|ReportDataViewJs whereCreatedAt($value)
 * @method static Builder|ReportDataViewJs whereId($value)
 * @method static Builder|ReportDataViewJs whereJsRegister($value)
 * @method static Builder|ReportDataViewJs whereJsCode($value)
 * @method static Builder|ReportDataViewJs whereJsInit($value)
 * @method static Builder|ReportDataViewJs whereJsonForm($value)
 * @method static Builder|ReportDataViewJs whereReportDataViewId($value)
 * @method static Builder|ReportDataViewJs whereReportDataViewLibVersionId($value)
 * @method static Builder|ReportDataViewJs whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ReportDataViewJs extends ApiModel
{
    use Compoships;

    protected $fillable = [
        'id',
        'report_data_view_id',
        'report_data_view_lib_version_id',
        'json_form',
        'js_register',
        'js_code',
        'js_init',
    ];

    public static array $rules = [
        'report_data_view_id'             => 'required|integer',
        'report_data_view_lib_version_id' => 'required|integer',
        'json_form'                       => 'nullable',
        'js_register'                     => 'nullable',
        'js_code'                         => 'nullable',
        'js_init'                         => 'nullable',
    ];

    public function reportDataView(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportDataView');
    }

    public function reportDataViewLibVersion(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportDataViewLibVersion');
    }
}
