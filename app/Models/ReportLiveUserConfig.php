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

class ReportLiveUserConfig
{
    public readonly int $report_id;
    public readonly string $report_id_and_instance_id;
    private bool $auto_refresh_activated;

    public function __construct(int $report_id, string $report_id_and_instance_id, bool $auto_refresh_activated = false)
    {
        $this->report_id = $report_id;
        $this->report_id_and_instance_id = $report_id_and_instance_id;
        $this->auto_refresh_activated = $auto_refresh_activated;
    }

    public function isAutoRefreshActivated(): bool
    {
        return $this->auto_refresh_activated;
    }

    public function setAutoRefreshActivated(bool $auto_refresh_activated): void
    {
        $this->auto_refresh_activated = $auto_refresh_activated;
    }

}
