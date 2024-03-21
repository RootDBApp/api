<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ReportUserFavorite
 *
 * @property int $id
 * @property int $report_id
 * @property int $user_id
 * @property-read Report $report
 * @property-read User $user
 * @method static Builder|ReportUserFavorite newModelQuery()
 * @method static Builder|ReportUserFavorite newQuery()
 * @method static Builder|ReportUserFavorite query()
 * @method static Builder|ReportUserFavorite whereId($value)
 * @method static Builder|ReportUserFavorite whereReportId($value)
 * @method static Builder|ReportUserFavorite whereUserId($value)
 * @mixin Eloquent
 */
class ReportUserFavorite extends ApiModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'user_id',
    ];

    public static array $rules = [
        'report_id' => 'required|integer|exists:reports,id',
        'user_id' => 'required|integer|exists:users,id',
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
