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

namespace App\Interfaces;

use App\Models\ExecReportInfo;
use App\Models\ConfConnector;
use App\Models\ProcessSQLQueriesBuffer;
use App\Models\Report;
use App\Models\ReportDataView;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

interface ConnectorInterface
{
    public function execReport(Report $report, ExecReportInfo $execReportInfo): bool;

    public function execReportDataView(ReportDataView $reportDataView, ExecReportInfo $execReportInfo): bool;

    /**
     * Return all auto-complete possible completions for all ConfConnector schemas.
     */
    public function getAutoComplete(): bool|Collection;

    public function getCliCommand(string $pre_set_queries, string $query): Process;

    public function getConnection(): Connection;

    /**
     * Return the prime-react json tree cache file, if not exists, call updatePrimeReactTreeDB.
     */
    public function getPrimeReactTreeDB(Request $request): bool|Collection;

    /**
     * Return the auto complete cache file, if not exists, call updateAutoCompleteCache.
     */
    public function getSchemas(): bool|Collection;

    /**
     * @return string[]
     */
    public function getGrants(): array;

    public function getSSLCypher(): string;

    public function init(ConfConnector $confConnector): ConnectorInterface;

    /**
     * It's here we use Illuminate\Support\Facades\Config::set() to set up the driver.
     */
    public function initConnection(ConfConnector $confConnector): void;

    /**
     * @param string[] $results Indexed by results[<column name>] = <column value>
     * @param ProcessSQLQueriesBuffer $processSQLQueriesBuffer
     * @return void
     */
    public function parseCliResults(array &$results, ProcessSQLQueriesBuffer $processSQLQueriesBuffer): void;

    /**
     * Replace @<input_parameter> by the input parameter value.
     *
     * @param ExecReportInfo $execReportInfo
     * @param string $query
     * @return string
     */
    public function replaceInputParameterVariables(ExecReportInfo $execReportInfo, string $query): string;

    /**
     * Used to declare a real variable for the input parameters, in the SQL dialect.
     *
     * @param ExecReportInfo $execReportInfo
     * @param Connection $connection
     * @return void
     */
    public function setInputParameterVariables(ExecReportInfo $execReportInfo, Connection &$connection): void;

    /**
     * Generate auto complete cache file.
     */
    public function updatePrimeReactTreeDB();

    /**
     * Generate auto complete cache file.
     */
    public function updateSchemas();
}
