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

namespace App\Console;

use App\Enums\EnumFrequency;
use App\Jobs\CacheRefreshJob;
use App\Models\CacheJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
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
