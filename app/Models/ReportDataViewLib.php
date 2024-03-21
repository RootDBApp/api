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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\ReportDataViewLibs
 *
 * @property int $id
 * @property string $type 1: table, 2: graph
 * @property string $name
 * @property string|null $url_website
 * @property int $default
 * @property-read Collection|ReportDataViewLibVersion[] $reportDataViewLibVersions
 * @property-read int|null $report_data_view_lib_versions_count
 * @method static Builder|ReportDataViewLib newModelQuery()
 * @method static Builder|ReportDataViewLib newQuery()
 * @method static Builder|ReportDataViewLib query()
 * @method static Builder|ReportDataViewLib whereDefault($value)
 * @method static Builder|ReportDataViewLib whereId($value)
 * @method static Builder|ReportDataViewLib whereName($value)
 * @method static Builder|ReportDataViewLib whereType($value)
 * @method static Builder|ReportDataViewLib whereUrlWebsite($value)
 * @mixin Eloquent
 */
class ReportDataViewLib extends ApiModel
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'default' => 'boolean'
    ];

    public function reportDataViewLibVersions(): HasMany
    {
        return $this->hasMany('\App\Models\ReportDataViewLibVersion');
    }
}
