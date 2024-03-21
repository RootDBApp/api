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
use App\Models\Report as ReportModel;
use App\Models\ReportAndDataViewEvent;
use Illuminate\Support\Facades\Log;

class ReportError extends Report
{
    protected string $error;

    public function __construct(ExecReportInfo $execReportInfo, ReportModel $report, string $exception)
    {
        parent::__construct($execReportInfo, $report);
        $this->error = $exception;
    }

    public function broadcastWith(): array
    {
        Log::debug('ReportError [report ID ' . $this->report->id . ' ]  ' . PHP_EOL, [$this->error]);

        return (new ReportAndDataViewEvent(
            $this->report->id,
            $this->execReportInfo->instanceId,
            $this->report->name,
            0,
            '',
            [$this->error],
            [],
            0,
            $this->execReportInfo->reportCacheInfo
        ))->toArray();
    }
}
