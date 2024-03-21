<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Report
 *
 * @property int $id
 * @property int $conf_connector_id
 * @property int $user_id
 * @property int $organization_id
 * @property string $name
 * @property string $description
 * @property string|null $description_listing
 * @property string|null $title
 * @property string|null $query_init
 * @property string|null $query_cleanup
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property boolean $has_parameters
 * @property boolean $has_data_views
 * @property int $directory_id
 * @property int $category_id
 * @property boolean $public_access
 * @property string $public_security_hash
 * @property string $public_authorized_referers
 * @property boolean $is_visible
 * @property boolean $auto_refresh
 * @property bool $on_queue
 * @property-read Collection|ReportGroup[] $allowedGroups
 * @property-read int|null $allowed_groups_count
 * @property-read Collection|ReportUser[] $allowedUsers
 * @property-read int|null $allowed_users_count
 * @property-read ConfConnector $confConnector
 * @property-read Organization $organization
 * @property-read Collection|ReportParameter[] $parameters
 * @property-read Collection|ReportDataView[] $dataViews
 * @property-read int|null $parameters_count
 * @property-read User $user
 * @property-read Category $category
 * @property-read Directory $directory
 * @property-read int|null $data_views_count
 * @property-read Collection|ReportUserFavorite[] $favoriteUsers
 * @property-read int|null $favorite_users_count
 * @method static Builder|Report newModelQuery()
 * @method static Builder|Report newQuery()
 * @method static Builder|Report query()
 * @method static Builder|Report whereAutoRefresh($value)
 * @method static Builder|Report whereConfConnectorId($value)
 * @method static Builder|Report whereCreatedAt($value)
 * @method static Builder|Report whereDescription($value)
 * @method static Builder|Report whereDescriptionListing($value)
 * @method static Builder|Report whereId($value)
 * @method static Builder|Report whereName($value)
 * @method static Builder|Report whereOnQueue($value)
 * @method static Builder|Report whereOrganizationId($value)
 * @method static Builder|Report whereQueryCleanup($value)
 * @method static Builder|Report whereQueryInit($value)
 * @method static Builder|Report whereUpdatedAt($value)
 * @method static Builder|Report whereUserId($value)
 * @method static Builder|Report whereCategoryId($value)
 * @method static Builder|Report whereDirectoryId($value)
 * @method static Builder|Report whereHasParameters($value)
 * @method static Builder|Report whereIsVisible($value)
 * @method static Builder|Report whereIsAutoRefresh($value)
 * @method static Builder|Report wherePublicAccess($value)
 * @method static Builder|Report wherePublicAuthorizedReferers($value)
 * @method static Builder|Report wherePublicSecurityHash($value)
 * @method static Builder|Report whereTitle($value)
 * @method static Builder|Report whereHasDataViews($value)
 * @property int $num_runs
 * @property int $num_seconds_all_run
 * @property int $avg_seconds_by_run
 * @method static Builder|Report whereAvgSecondsByRun($value)
 * @method static Builder|Report whereNumRuns($value)
 * @method static Builder|Report whereNumSecondsAllRun($value)
 * @property int $has_cache
 * @method static Builder|Report whereHasCache($value)
 * @property-read Collection<int, ReportCache> $reportCaches
 * @property-read int|null $report_caches_count
 * @property int $has_user_cache
 * @property int $has_job_cache
 * @method static Builder|Report whereHasJobCache($value)
 * @method static Builder|Report whereHasUserCache($value)
 * @property-read Collection<int, CacheJob> $cacheJobs
 * @property-read int|null $cache_jobs_count
 * @property int|null $num_parameter_sets_cached_by_users
 * @property int|null $num_parameter_sets_cached_by_jobs
 * @method static Builder|Report whereNumParameterSetsCachedByJobs($value)
 * @method static Builder|Report whereNumParameterSetsCachedByUsers($value)
 * @mixin Eloquent
 */
class Report extends ApiModel
{
    protected $fillable = [
        'conf_connector_id',
        'user_id',
        'organization_id',
        'category_id',
        'directory_id',
        'name',
        'title',
        'description',
        'description_listing',
        'query_init',
        'query_cleanup',
        'public_access',
        'public_security_hash',
        'public_authorized_referers',
        'is_visible',
        'auto_refresh',
        'on_queue',
        'num_runs',
        'num_seconds_all_run',
        'avg_seconds_by_run',
        'has_cache',
        'has_job_cache',
        'has_user_cache',
        'num_parameter_sets_cached_by_jobs',
        'num_parameter_sets_cached_by_users'
    ];

    public static array $rules = [
        'conf_connector_id'                  => 'required|integer',
        'user_id'                            => 'required|integer|exists:users,id',
        'organization_id'                    => 'integer|exists:organizations,id',
        'category_id'                        => 'required|integer|exists:categories,id',
        'directory_id'                       => 'required|integer|exists:directories,id',
        'name'                               => 'required|string|max:255',
        'title'                              => 'nullable',
        'description'                        => 'nullable',
        'description_listing'                => 'nullable',
        'query_init'                         => 'nullable',
        'query_cleanup'                      => 'nullable',
        'public_access'                      => 'boolean',
        'public_security_hash'               => 'string|max:40',
        'public_authorized_referers.*'       => 'string|distinct',
        'is_visible'                         => 'boolean',
        'on_queue'                           => 'boolean',
        'num_runs'                           => 'integer',
        'num_seconds_all_run'                => 'integer',
        'avg_seconds_by_run'                 => 'integer',
        'has_cache'                          => 'boolean',
        'has_job_cache'                      => 'boolean',
        'has_user_cache'                     => 'boolean',
        'num_parameter_sets_cached_by_jobs'  => 'integer',
        'num_parameter_sets_cached_by_users' => 'integer'
    ];

    protected $casts = [
        'has_parameters' => 'boolean',
        'has_data_views' => 'boolean',
        'is_visible'     => 'boolean',
        'public_access'  => 'boolean',
        'auto_refresh'   => 'boolean',
        'on_queue'       => 'boolean',
        'has_cache'      => 'boolean',
        'has_job_cache'  => 'boolean',
        'has_user_cache' => 'boolean'
    ];

    public function allowedGroups(): HasMany
    {
        return $this->hasMany('\App\Models\ReportGroup');
    }

    public function allowedUsers(): HasMany
    {
        return $this->hasMany('\App\Models\ReportUser');
    }

    public function cacheJobs(): HasMany
    {
        return $this->hasMany('\App\Models\CacheJob');
    }

    public function reportCaches(): HasMany
    {
        return $this->hasMany('\App\Models\ReportCache');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo('\App\Models\Category');
    }

    public function confConnector(): BelongsTo
    {
        return $this->belongsTo('App\Models\ConfConnector');
    }

    public function countDataViewsWithSQLQuery(): int
    {
        $num_views_with_sql_queries = 0;
        foreach ($this->dataViews as $dataView) {

            if (preg_match('`.*select .*`i', preg_replace('`(^--.*)`mi', '', $dataView->query))) {

                $num_views_with_sql_queries++;
            }
        }

        return $num_views_with_sql_queries;
    }

    public function dataViews(): HasMany
    {
        return $this->hasMany('\App\Models\ReportDataView');
    }

    public function directory(): BelongsTo
    {
        return $this->belongsTo('\App\Models\Directory');
    }

    public function favoriteUsers(): HasMany
    {
        return $this->hasMany('\App\Models\ReportUserFavorite')->where('user_id', '=', auth()->user()->id);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo('App\Models\Organization');
    }

    public function parameters(): HasMany
    {
        return $this->hasMany('App\Models\ReportParameter');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }
}
