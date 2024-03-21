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

namespace App\Events;

use App\Models\ExecReportInfo;
use App\Models\ReportAndDataViewEvent;
use App\Models\ReportDataView as ReportDataViewModel;

class ReportDataViewRunEnd extends ReportDataView
{
    private float $ms_elapsed;

    public function __construct(ExecReportInfo $execReportInfo, ReportDataViewModel $reportDataView, float $ms_elapsed)
    {
        parent::__construct($execReportInfo, $reportDataView);
        $this->ms_elapsed = $ms_elapsed;
    }

    public function broadcastWith(): array
    {
        return (new ReportAndDataViewEvent(
            $this->reportDataView->report_id,
            $this->execReportInfo->instanceId,
            $this->reportDataView->report->name,
            $this->reportDataView->id,
            $this->reportDataView->name,
            [],
            [],
            $this->ms_elapsed,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
