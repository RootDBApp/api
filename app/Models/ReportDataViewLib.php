<?php

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
