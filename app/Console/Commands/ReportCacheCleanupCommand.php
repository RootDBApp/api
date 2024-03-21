<?php

namespace App\Console\Commands;

use App\Enums\EnumCacheType;
use App\Events\APICacheReportUpdateCacheStatus;
use App\Models\Report;
use App\Models\ReportCache;
use App\Models\ReportCacheStatus;
use App\Tools\CacheReportTools;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportCacheCleanupCommand extends Command
{
    protected $signature = 'report:cache-cleanup';
    protected $description = 'Check report_caches\'s keys and remove the ones not valid anymore.';

    public function handle(): void
    {
        $this->info('Get reports cache status...');
        $report_cache_status = [];
        /** @var Report $report */
        foreach (Report::get(['id', 'has_cache'])->all() as $report) {

            $report_cache_status[$report->id] = (boolean)$report->has_cache;
        }

        $this->info(count($report_cache_status) . ' reports.');


        $this->info('Checking memcached keys...');
        /** @var ReportCache $reportCache */
        foreach (ReportCache::all() as $reportCache) {

            if (!Cache::tags(CacheReportTools::CACHE_REFRESH_JOB_DATA_VIEW_TAGS)->has($reportCache->cache_key)) {

                $this->info('Removing invalid key: ' . $reportCache->cache_key);
                $reportCache->delete();
            }
        }

        $this->info('Updating report cache status...');
        /** @var ReportCacheStatus[][] $report_cache_by_organization_to_update */
        $report_cache_by_organization_to_update = [];
        $reports_ids_without_cache = [];
        $reports_ids_with_cache = [];

        foreach (DB::select('SELECT r.id AS report_id, r.organization_id, GROUP_CONCAT(DISTINCT rc.cache_type) AS cache_type FROM reports r LEFT JOIN report_caches rc on r.id = rc.report_id  GROUP BY r.id, r.organization_id') as $report) {

            if (is_null($report->cache_type)) {

                if (!in_array($report->report_id, $reports_ids_without_cache)) {

                    $reports_ids_without_cache[] = $report->report_id;

                    if ($report_cache_status[$report->report_id] === true) {

                        $report_cache_by_organization_to_update[$report->organization_id][$report->report_id] =
                            new ReportCacheStatus($report->report_id, false, false, false, 0, 0);
                    }
                }
            } else {

                if (!in_array($report->report_id, $reports_ids_with_cache)) {

                    $reports_ids_with_cache[] = $report->report_id;

                    $report_cache_by_organization_to_update[$report->organization_id][$report->report_id] =
                        CacheReportTools::getReportCacheStatus($report->report_id);
                }
            }

        }

        $this->info(count($reports_ids_without_cache) . ' report(s) without caches jobs.');
        $this->info(count($reports_ids_with_cache) . ' report(s) with cache jobs.');
        $this->info(count($report_cache_by_organization_to_update) . ' report(s) to update.');

        if (count($report_cache_by_organization_to_update) > 0) {

            Report::whereIn('id', $reports_ids_without_cache)->update(['has_cache' => false, 'has_job_cache' => false, 'has_user_cache' => false]);

            if (App::environment() !== 'testing') {

                /**
                 * @var int $organization_id
                 * @var ReportCacheStatus[] $allReportCacheStatus
                 */
                foreach ($report_cache_by_organization_to_update as $organization_id => $allReportCacheStatus) {

                    foreach ($allReportCacheStatus as $reportCacheStatus) {

                        Report::where('id', '=', $reportCacheStatus->report_id)
                            ->update(
                                [
                                    'has_cache'                          => $reportCacheStatus->has_cache,
                                    'has_job_cache'                      => $reportCacheStatus->has_job_cache,
                                    'has_user_cache'                     => $reportCacheStatus->has_user_cache,
                                    'num_parameter_sets_cached_by_jobs'  => $reportCacheStatus->num_parameter_sets_cached_by_jobs,
                                    'num_parameter_sets_cached_by_users' => $reportCacheStatus->num_parameter_sets_cached_by_users,
                                ]);
                    }

                    APICacheReportUpdateCacheStatus::dispatch($organization_id, $allReportCacheStatus);
                }
            }

        } else {

            $this->info('Nothing to update.');
        }

        $this->info('Done.');
    }
}
