<?php

namespace App\Models;

use App\Enums\EnumFrequency;
use App\Enums\EnumWeekday;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as CollectionAlias;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;


/**
 * App\Models\CacheJob
 *
 * @property int $id
 * @property int $report_id
 * @property EnumFrequency $frequency
 * @property int|null $at_minute
 * @property Carbon|null $at_time
 * @property EnumWeekday|null $at_weekday
 * @property int|null $at_day
 * @property int $ttl In seconds.
 * @property Carbon|null $last_run
 * @property int|null $last_run_duration In seconds.
 * @property int|null $last_num_parameter_sets
 * @property int|null $last_cache_size_b
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property bool $activated
 * @property bool $running
 * @property-read EloquentCollection<int, CacheJobParameterSetConfig> $cacheJobParameterSetConfigs
 * @property-read int|null $cache_job_parameter_set_configs_count
 * @property-read Report $report
 * @method static Builder|CacheJob newModelQuery()
 * @method static Builder|CacheJob newQuery()
 * @method static Builder|CacheJob query()
 * @method static Builder|CacheJob whereActivated($value)
 * @method static Builder|CacheJob whereAtDay($value)
 * @method static Builder|CacheJob whereAtMinute($value)
 * @method static Builder|CacheJob whereAtTime($value)
 * @method static Builder|CacheJob whereAtWeekday($value)
 * @method static Builder|CacheJob whereCreatedAt($value)
 * @method static Builder|CacheJob whereFrequency($value)
 * @method static Builder|CacheJob whereId($value)
 * @method static Builder|CacheJob whereLastCacheSizeB($value)
 * @method static Builder|CacheJob whereLastNumParameterSets($value)
 * @method static Builder|CacheJob whereLastRun($value)
 * @method static Builder|CacheJob whereLastRunDuration($value)
 * @method static Builder|CacheJob whereReportId($value)
 * @method static Builder|CacheJob whereRunning($value)
 * @method static Builder|CacheJob whereTtl($value)
 * @method static Builder|CacheJob whereUpdatedAt($value)
 * @property string $periodicity
 * @method static Builder|CacheJob wherePeriodicity($value)
 * @mixin Eloquent
 */
class CacheJob extends ApiModel
{
    public $timestamps = true;

    protected $fillable = [
        'report_id',
        'frequency',
        'at_minute',
        'at_time',
        'at_weekday',
        'at_day',
        'last_run',
        'last_run_duration',
        'last_num_parameter_sets',
        'last_cache_size_b',
        'activated',
        'running',
        'ttl'
    ];

    public static function rules(): array
    {
        return [
            'report_id'  => 'required|integer|exists:reports,id',
            'frequency'  => [new Enum(EnumFrequency::class)],
            'at_minute'  => 'nullable|integer|min:1|max:60',
            'at_time'    => 'nullable|date',
            'at_weekday' => [new Enum(EnumWeekday::class)],
            'at_day'     => 'nullable|integer|min:1|max:31',
            'activated'  => 'required|boolean'
        ];
    }

    protected $casts = [
        'frequency'  => EnumFrequency::class,
        'at_time'    => 'datetime',
        'at_weekday' => EnumWeekday::class,
        'last_run'   => 'datetime',
        'activated'  => 'boolean',
        'running'    => 'boolean'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo('App\Models\Report');
    }

    public function cacheJobParameterSetConfigs(): HasMany
    {
        return $this->hasMany('App\Models\CacheJobParameterSetConfig');
    }

    /**
     * @return CollectionAlias<CacheJobParameterSets>
     * @throws Exception
     */
    public function getAllCacheJobParameterSets(): CollectionAlias
    {

        $allCacheJobParameterSets = collect();

        foreach ($this->cacheJobParameterSetConfigs as $cacheJobParameterSetConfig) {

            $cacheJobParameterSets = $allCacheJobParameterSets->first(function (CacheJobParameterSets $cacheJobParameterSets) use ($cacheJobParameterSetConfig) {
                return $cacheJobParameterSets->cacheJob->id === $cacheJobParameterSetConfig->cache_job_id;
            });

            if (is_null($cacheJobParameterSets)) {
                $cacheJobParameterSets = new CacheJobParameterSets($cacheJobParameterSetConfig->cacheJob);
                $allCacheJobParameterSets->push($cacheJobParameterSets);
            }

            $cacheJobParameterSets->addCacheJobParameterSetConfig($cacheJobParameterSetConfig);
        }

        Log::debug("CacheJob::getAllCacheJobParameterSets - number of cache job parameters to run: ", [$allCacheJobParameterSets->count()]);

        return $allCacheJobParameterSets->values();
    }
}


