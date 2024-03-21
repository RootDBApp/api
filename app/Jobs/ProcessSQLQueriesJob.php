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

use App\Events\SQLQueriesEnd;
use App\Events\SQLQueriesErrorReceived;
use App\Events\SQLQueriesResultsReceived;

use App\Events\SQLSubQueryEnd;
use App\Events\SQLSubQueryStart;
use App\Models\ConfConnector;
use App\Models\ProcessSQLQueriesBuffer;
use App\Services\ConnectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;
use Symfony\Component\Process\Process;

class ProcessSQLQueriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected string $queries;
    protected ConfConnector $confConnector;
    protected string $web_socket_session_id;
    protected int $draft_query_id;

    public function __construct(
        int           $draft_query_id,
        string        $queries,
        ConfConnector $confConnector,
        string        $web_socket_session_id,
    )
    {
        $this->draft_query_id = $draft_query_id;
        $this->queries = $queries;
        $this->confConnector = $confConnector;
        $this->web_socket_session_id = $web_socket_session_id;
    }

    public function handle(ConnectorService $connectorService)
    {
        // Remove commented queries, block comments & co
        $queries = preg_replace('`(\/\*.*\/)`m', null, $this->queries);
        $queries = preg_replace('`(\/\*(?s).*\*\/)`m', null, $queries);
        $queries = preg_replace('`.*(--.*)`', null, $queries);
        $queries = preg_replace('`.*(#.*)`', null, $queries);
        $queries = preg_replace('`\n`', ' ', $queries);
        $queries = preg_replace('`\r`', ' ', $queries);

        //Log::debug('---------------------------------------------------------------------------');
        //Log::debug('');
        //Log::debug('$queries', [$queries]);
        //Log::debug('');
        //Log::debug('---------------------------------------------------------------------------');

        // Split all queries.
        $queries = preg_split("/;+|(\\\G)+/", $queries, -1, PREG_SPLIT_NO_EMPTY);
        /** @var string[] $set_queries */
        $set_queries = [];

        foreach ($queries as $query_index => $query) {


            if (preg_match('`(set.*@.*=.*)`mi', $query, $match)) {

                Log::debug('ProcessSQLQueries::handle - SET query detected, remember it.', [$match, $set_queries]);
                $set_queries[] = $match[0];

            } else {

                $pre_set_queries = implode('; ', $set_queries) . '; ';

                Log::debug('ProcessSQLQueries::handle - query to be executed or not if it\'s commented: ', [$pre_set_queries . $query]);

                SQLSubQueryStart::dispatch($this->web_socket_session_id, $this->draft_query_id, $query_index);

                $process = $connectorService->getInstance($this->confConnector)->getCliCommand($pre_set_queries, $query);

                $processSQLQueriesBuffer = new ProcessSQLQueriesBuffer();
                $process->start();
                $process->wait(
                    function ($type, $buffer) use ($processSQLQueriesBuffer) {

                        if (Process::ERR === $type) {
                            $processSQLQueriesBuffer->stderr[] = $buffer;
                        } else {
                            $processSQLQueriesBuffer->stdout[] = $buffer;
                        }
                    }
                );

                //Log::debug('---------------------------------------------------------------------------');
                //Log::debug('');
                //Log::debug('stdout', [$processSQLQueriesBuffer->stdout]);
                //Log::debug('');
                //Log::debug('');
                //Log::debug('stderr', [$processSQLQueriesBuffer->stderr]);
                //Log::debug('');
                //Log::debug('---------------------------------------------------------------------------');

                if (count($processSQLQueriesBuffer->stderr) > 0) {

                    SQLQueriesErrorReceived::dispatch($this->web_socket_session_id, [], $this->draft_query_id, $query_index, $processSQLQueriesBuffer->stderr);
                    SQLSubQueryEnd::dispatch($this->web_socket_session_id, $this->draft_query_id, $query_index);
                    continue;
                }

                $results = [];
                $connectorService->getInstance($this->confConnector)->parseCliResults($results, $processSQLQueriesBuffer);

                SQLQueriesResultsReceived::dispatch($this->web_socket_session_id, $results, $this->draft_query_id, $query_index);

                SQLSubQueryEnd::dispatch($this->web_socket_session_id, $this->draft_query_id, $query_index);
            }
        }

        SQLQueriesEnd::dispatch($this->web_socket_session_id, $this->draft_query_id);
    }
}
