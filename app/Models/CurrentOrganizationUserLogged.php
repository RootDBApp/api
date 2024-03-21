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

use Illuminate\Support\Collection;

class CurrentOrganizationUserLogged
{
    public readonly int $organization_user_id;
    public readonly int $user_id;
    public readonly int $organization_id;
    public readonly array $roles;
    public readonly array $groups;
    public readonly string $web_socket_session_id;
    private Collection $reportLiveConfigs;

    public function __construct(int $organization_user_id, int $user_id, int $organization_id, array $roles, array $groups)
    {
        $this->organization_user_id = $organization_user_id;
        $this->user_id = $user_id;
        $this->organization_id = $organization_id;
        $this->roles = $roles;
        $this->groups = $groups;
        $this->reportLiveConfigs = new Collection([]);
        $this->web_socket_session_id = $user_id . rand(100000, 999999);
    }

    public function addReportLiveUserConfig(ReportLiveUserConfig $reportLiveUserConfig): void
    {
        if (!$this->reportLiveConfigs->contains('report_id', '=', $reportLiveUserConfig->report_id_and_instance_id)) {

            $this->reportLiveConfigs->add($reportLiveUserConfig);
        }
    }

    public function removeReportLiveUserConfig(string $reportIdAndInstanceId)
    {
        /**
         * @var ReportLiveUserConfig $reportLiveConfig
         */
        foreach ($this->reportLiveConfigs as $key => $reportLiveConfig) {

            if ($reportLiveConfig->report_id_and_instance_id === $reportIdAndInstanceId) {

                unset($this->reportLiveConfigs[$key]);
                break;
            }
        }
    }

    public function getReportLiveConfigs(): Collection
    {
        return $this->reportLiveConfigs;
    }
}
