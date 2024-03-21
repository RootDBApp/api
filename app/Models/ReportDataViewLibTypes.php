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
