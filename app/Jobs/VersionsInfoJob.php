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

namespace App\Jobs;

use App\Enums\EnumVersion;
use App\Events\VersionInfosEvent;
use App\Models\User;
use App\Models\VersionInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHLAK\SemVer\Exceptions\InvalidVersionException;
use PHLAK\SemVer\Version;

class VersionsInfoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        /** @var VersionInfo[] $versionInfos */
        $versionInfos = [];

        // 2022-02 - we consider API+frontend as a unique package, using same version.
        $versionInfos[] = $this->_fetchAvailableUpdate()->toArray();
        //$versionInfos[] = $this->_fetchAvailableUpdate(VersionType::API)->toArray();
        //$versionInfos[] = $this->_fetchAvailableUpdate(VersionType::Frontend)->toArray();

        VersionInfosEvent::dispatch($this->user, $versionInfos);
    }

    private function _fetchAvailableUpdate(): VersionInfo
    {
        $versionConfig = new Version();

        try {

            $versionConfig = new Version(config('app.version'));
        } catch (InvalidVersionException $exception) {

            Log::error('Invalid versionConfig', [$exception]);
        }

        $versionInfo = new VersionInfo(
            EnumVersion::rootdb,
            $versionConfig
        );

        $response = Http::timeout(5)->get('https://builds.rootdb.fr/rootdb/latest');
        if ($response->status() !== 200) {

            Log::warning('Response fetch latest version', [$response]);
            return $versionInfo;
        }

        $latestAvailableVersionInfo = new Version();

        try {

            $lines = explode("\n", $response->body());

            // Check if it's a public release
            if ($lines[2]) {

                $version = $lines[0] ?? $versionConfig;
                $versionInfo->url_release_note = $lines[1] ?? '';

                $latestAvailableVersionInfo = new Version($version);
            } else {
                $latestAvailableVersionInfo->setVersion($versionConfig);
            }
        } catch (InvalidVersionException $exception) {

            Log::error('Invalid latestAvailableVersionInfo', [$exception]);
        }

        $versionInfo->update_version_available = $latestAvailableVersionInfo;
        if ($latestAvailableVersionInfo->gt($versionInfo->version)) {

            $versionInfo->update_available = true;
        }

        return $versionInfo;
    }
}
