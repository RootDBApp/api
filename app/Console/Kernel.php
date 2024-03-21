<?php

namespace App\Console;

use App\Enums\EnumFrequency;
use App\Jobs\CacheRefreshJob;
use App\Models\CacheJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Rootdb\ApiCommon\Enum\LicenseType;

class Kernel extends ConsoleKernel
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        $schedule->command('telescope:prune')->environments(['local'])->daily();
        $schedule->command('report:cache-cleanup')->hourlyAt(30);

        //
        // Cache refresh jobs
        //
        /** @var CacheJob $cacheJob */
        foreach (CacheJob::with(
            [
                'report',
                'cacheJobParameterSetConfigs',
                'cacheJobParameterSetConfigs.parameter',
                'cacheJobParameterSetConfigs.parameter.parameterInput',
                'cacheJobParameterSetConfigs.parameter.parameterInput.parameterInputType'
            ])->where('activated', '=', true)->get()->all() as $cacheJob
        ) {

            switch ($cacheJob->frequency) {

                case EnumFrequency::MONTHLY_ON:

                    $schedule->call(function () use ($cacheJob) {
                        CacheRefreshJob::dispatch($cacheJob)->afterResponse();
                    })->name('Cache Job ID: ' . $cacheJob->id . ' | report ID: ' . $cacheJob->report->id)
                        ->monthlyOn($cacheJob->at_day, $cacheJob->at_time);
                    break;

                case EnumFrequency::WEEKLY_ON:

                    $schedule->call(
                        function () use ($cacheJob) {
                            CacheRefreshJob::dispatch($cacheJob)->afterResponse();
                        }
                    )->name('Cache Job ID: ' . $cacheJob->id . ' | report ID: ' . $cacheJob->report->id)
                        ->weeklyOn($cacheJob->at_weekday->value, $cacheJob->at_time->format('H:i:s'));
                    break;

                case EnumFrequency::DAILY_AT:

                    $schedule->call(
                        function () use ($cacheJob) {
                            CacheRefreshJob::dispatch($cacheJob)->afterResponse();
                        }
                    )->name('Cache Job ID: ' . $cacheJob->id . ' | report ID: ' . $cacheJob->report->id)
                        ->dailyAt($cacheJob->at_time->format('H:i'));
                    break;

                case EnumFrequency::HOURLY_AT:

                    $schedule->call(
                        function () use ($cacheJob) {
                            CacheRefreshJob::dispatch($cacheJob)->afterResponse();
                        }
                    )->name('Cache Job ID: ' . $cacheJob->id . ' | report ID: ' . $cacheJob->report->id)
                        ->hourlyAt($cacheJob->at_minute);
                    break;

                // For all  every* frequencies.
                default:

                    $frequency = $cacheJob->frequency->value;
                    $schedule->call(
                        function () use ($cacheJob) {
                            CacheRefreshJob::dispatch($cacheJob)->afterResponse();
                        }
                    )->name('Cache Job ID: ' . $cacheJob->id . ' | report ID: ' . $cacheJob->report->id)
                        ->$frequency();
                    break;
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
