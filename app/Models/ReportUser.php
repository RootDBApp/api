<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReportUser
 *
 * @property int $id
 * @property int $report_id
 * @property int $user_id
 * @property-read User $user
 * @property-read Report $report
 * @method static Builder|ReportUser newModelQuery()
 * @method static Builder|ReportUser newQuery()
 * @method static Builder|ReportUser query()
 * @method static Builder|ReportUser whereId($value)
 * @method static Builder|ReportUser whereReportId($value)
 * @method static Builder|ReportUser whereUserId($value)
 * @mixin Eloquent
 */
class ReportUser extends ApiModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'user_id',
    ];

    public static array $rules = [
        'report_id' => 'required|integer|exists:reports,id',
        'user_id' => 'required|integer|exists:users,id|unique:report_users',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('\App\Models\User');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo('\App\Models\Report');
    }
}
