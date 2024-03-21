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
