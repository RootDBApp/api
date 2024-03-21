<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ReportGroup
 *
 * @property int        $id
 * @property int        $report_id
 * @property int        $group_id
 * @property-read Group $group
 * @method static Builder|ReportGroup newModelQuery()
 * @method static Builder|ReportGroup newQuery()
 * @method static Builder|ReportGroup query()
 * @method static Builder|ReportGroup whereGroupId($value)
 * @method static Builder|ReportGroup whereId($value)
 * @method static Builder|ReportGroup whereReportId($value)
 * @property-read \App\Models\Report $report
 * @mixin Eloquent
 */
class ReportGroup extends ApiModel
{

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'group_id',
    ];

    public static $rules = [
        'report_id' => 'required|integer|exists:reports,id',
        'group_id'  => 'required|integer|exists:groups,id|unique:report_groups',
    ];

    /**
     * @return BelongsTo|HasOne
     */
    public function group()
    {
        return $this->belongsTo('\App\Models\Group');
    }

    public function report()
    {
        return $this->belongsTo('\App\Models\Report');
    }
}
