<?php


namespace App\Services;

use App\Interfaces\ConnectorInterface;
use App\Jobs\ProcessReportJob;
use App\Jobs\ProcessReportDataViewJob;
use App\Models\ExecReportInfo;
use App\Models\ConfConnector;
use App\Models\ProcessSQLQueriesBuffer;
use App\Models\Report;
use App\Models\ReportDataView;
use App\Tools\ReportTools;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class CommonConnectorService implements ConnectorInterface
{
    public bool $initialized = false;
    public ConfConnector $confConnector;
    protected Connection $connection;
    protected string $tables_cache_key;
    protected string $prime_react_tree_db_cache_key;

    protected function backQuoteIfNeeded(string $string): string
    {
        return str_contains($string, '-') ? '`' . $string . '`' : $string;
    }

    public function execReport(Report $report, ExecReportInfo $execReportInfo): bool
    {
        if (App::environment() !== 'testing') {

            $execReportInfo->orderInputParametersValues($report);
            if ($execReportInfo->async) {

                ProcessReportJob::dispatch($report, $execReportInfo)->afterResponse();
            } else {

                ProcessReportJob::dispatchSync($report, $execReportInfo);
            }

            //if ($report->on_queue) {
            //
            //    ProcessReport::dispatch($report, $execReportInfo)->onQueue($queueService->getQueueQueryName());
            //} else {
            //
            //    ProcessReport::dispatchSync($report, $execReportInfo);
            //}
        }

        return true;
    }

    public function execReportDataView(ReportDataView $reportDataView, ExecReportInfo $execReportInfo): bool
    {
        if (App::environment() !== 'testing') {

            $execReportInfo->orderInputParametersValues($reportDataView->report);
            ProcessReportDataViewJob::dispatch($reportDataView, $execReportInfo)->afterResponse();

            //if ($reportDataView->on_queue) {
            //
            //    ProcessReportDataView::dispatch($reportDataView, $execReportInfo)->onQueue($queueService->getQueueQueryName());
            //} else {
            //
            //    ProcessReportDataView::dispatchSync($reportDataView, $execReportInfo, true);
            //}
        }

        return true;
    }

    public function getAutoComplete(): Collection
    {
        return new Collection();
    }

    public function getCliCommand(string $pre_set_queries, string $query): Process
    {
        return new Process([]);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getPrimeReactTreeDB(Request $request): bool|Collection
    {
        return new Collection();
    }

    public function getSchemas(): bool|Collection
    {
        return new Collection();
    }

    /**
     * Create a new config database connection.
     *
     * @param ConfConnector $confConnector
     *
     * @return ConnectorInterface
     */
    public function init(ConfConnector $confConnector): ConnectorInterface
    {
        $this->confConnector = $confConnector;

        $this->tables_cache_key = 'auto-complete-' . $this->confConnector->id;
        $this->prime_react_tree_db_cache_key = 'prime-react-tree-db-' . $this->confConnector->id;

        $this->initConnection($confConnector);

        $this->initialized = true;
        $this->connection = DB::connection($this->confConnector->name);

        return $this;
    }

    public function initConnection(ConfConnector $confConnector): void
    {
    }

    public function getGrants(): array
    {
        return [];
    }

    public function getSSLCypher(): string
    {
        return '';
    }

    public function parseCliResults(array &$results, ProcessSQLQueriesBuffer $processSQLQueriesBuffer): void
    {
    }

    public function replaceInputParameterVariables(ExecReportInfo $execReportInfo, string $query): string
    {
        return $query;
    }

    public function setInputParameterVariables(ExecReportInfo $execReportInfo, Connection &$connection): void
    {
    }

    public function updatePrimeReactTreeDB()
    {
    }

    public function updateSchemas()
    {
    }
}
