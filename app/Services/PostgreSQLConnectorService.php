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

namespace App\Services;

use App\Http\Resources\AceBuildCompletion as AceBuildCompletionResource;
use App\Models\AceBuildCompletion;
use App\Models\ExecReportInfo;
use App\Models\AutoCompleteAlias;
use App\Models\ConfConnector;
use App\Models\DBColumn;
use App\Models\DBForeignKey;
use App\Models\DBIndex;
use App\Models\DBPrimaryKey;
use App\Models\DBSchema;
use App\Models\DBTable;
use App\Models\DBView;
use App\Models\PrimeReactTree;
use App\Models\PrimeReactTreeDb;
use App\Models\ProcessSQLQueriesBuffer;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use PDO;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Process\Process;

class PostgreSQLConnectorService extends CommonConnectorService
{
    private bool $_schemas_refreshed = false;
    private bool $_prime_react_tree_db_refreshed = false;

    public function getAutoComplete(): Collection
    {
        /** @var AceBuildCompletion[]|Collection $words */
        $wordsCollection = new Collection();
        /** @var AutoCompleteAlias[]|Collection $autoCompleteAliasesCollection */
        $autoCompleteAliasesCollection = new Collection();
        /** @var AutoCompleteAlias[]|Collection $autoCompleteDBAliasesCollection */
        $autoCompleteDBAliasesCollection = new Collection();

        $tableSchemas = $this->getSchemas();
        if ($tableSchemas === false) {

            return $wordsCollection;
        }

        /** @var DBSchema[] $tableSchemas */
        foreach ($tableSchemas as $tableSchema) {

            $autoCompleteDBAlias = $this->_getAutoCompleteDBAlias($autoCompleteDBAliasesCollection, $tableSchema->name);

            $wordsCollection->add(
                AceBuildCompletionResource::make(
                    [
                        'value'   => $this->backQuoteIfNeeded($tableSchema->name),
                        'score'   => 100,
                        'meta'    => '@' . $this->confConnector->name,
                        'name'    => 'dbHelper_' . $this->confConnector->id,
                        'caption' => $tableSchema->name
                    ]
                )
            );

            $wordsCollection->add(
                AceBuildCompletionResource::make(
                    [
                        'value'   => $this->backQuoteIfNeeded($tableSchema->name),
                        'score'   => 99,
                        'meta'    => '@' . $this->confConnector->name,
                        'name'    => 'dbHelper_' . $this->confConnector->id,
                        'caption' => $tableSchema->name
                    ]
                )
            );

            // Handle tables.
            //
            foreach ($tableSchema->tables as $table) {

                //if (mb_substr($table->name, 0, 1) !== 'c') {
                //    continue;
                //}

                $autoCompleteAlias = $this->_getAutoCompleteAlias(
                    $autoCompleteAliasesCollection,
                    $autoCompleteDBAlias->value . '_' . $table->name
                );

                $wordsCollection->add(
                    AceBuildCompletionResource::make(
                        [
                            'value'   => $this->backQuoteIfNeeded($table->name) . ' ' . $this->backQuoteIfNeeded($autoCompleteAlias->value),
                            'score'   => 98,
                            'meta'    => '@' . $tableSchema->name,
                            'name'    => 'dbHelper_' . $this->confConnector->id,
                            'caption' => $table->name . ' ' . $autoCompleteAlias->value
                        ]
                    )
                );

                $wordsCollection->add(
                    AceBuildCompletionResource::make(
                        [
                            'value'   => $this->backQuoteIfNeeded($tableSchema->name) . '.' . $this->backQuoteIfNeeded($table->name) . ' ' . $this->backQuoteIfNeeded($autoCompleteAlias->value),
                            'score'   => 98,
                            'meta'    => '@' . $tableSchema->name,
                            'name'    => 'dbHelper_' . $this->confConnector->id,
                            'caption' => $tableSchema->name . '.' . $table->name . ' ' . $autoCompleteAlias->value
                        ]
                    )
                );


                /** @var DBIndex|DBForeignKey|DBPrimaryKey|DBColumn $column */
                foreach ($table->columns as $column) {

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($autoCompleteAlias->value) . '.' . $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $autoCompleteAlias->value . '.' . $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($table->name) . '.' . $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $table->name . '.' . $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );
                }
            }

            // Handle views.
            //
            foreach ($tableSchema->views as $view) {

                $autoCompleteAlias = $this->_getAutoCompleteAlias(
                    $autoCompleteAliasesCollection,
                    $autoCompleteDBAlias->value . '_v_' . $view->name
                );

                $wordsCollection->add(
                    AceBuildCompletionResource::make(
                        [
                            'value'   => $this->backQuoteIfNeeded($view->name) . ' ' . $this->backQuoteIfNeeded($autoCompleteAlias->value),
                            'score'   => 99,
                            'meta'    => '@' . $tableSchema->name,
                            'name'    => 'dbHelper_' . $this->confConnector->id,
                            'caption' => $view->name . ' ' . $autoCompleteAlias->value . ' (view)'
                        ]
                    )
                );

                $wordsCollection->add(
                    AceBuildCompletionResource::make(
                        [
                            'value'   => $this->backQuoteIfNeeded($tableSchema->name) . '.' . $this->backQuoteIfNeeded($view->name) . ' ' . $this->backQuoteIfNeeded($autoCompleteAlias->value),
                            'score'   => 99,
                            'meta'    => '@' . $tableSchema->name,
                            'name'    => 'dbHelper_' . $this->confConnector->id,
                            'caption' => $tableSchema->name . '.' . $view->name . ' ' . $autoCompleteAlias->value . ' (view)'
                        ]
                    )
                );

                foreach ($view->columns as $column) {

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($autoCompleteAlias->value) . '.' . $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $autoCompleteAlias->value . '.' . $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );

                    $wordsCollection->add(
                        AceBuildCompletionResource::make(
                            [
                                'value'   => $this->backQuoteIfNeeded($table->name) . '.' . $this->backQuoteIfNeeded($column->name),
                                'score'   => 97,
                                'meta'    => '@' . $tableSchema->name . '.' . $table->name,
                                'name'    => 'dbHelper_' . $this->confConnector->id,
                                'caption' => $table->name . '.' . $column->name . $this->_getAutoCompletionCaption($column)
                            ]
                        )
                    );
                }
            }
        }

        return $wordsCollection;
    }

    public function getCliCommand(string $pre_set_queries, string $query): Process
    {
        $process = new Process(
            [
                'psql',
                '-U', $this->confConnector->username,
                '-p', $this->confConnector->port,
                '-h', $this->confConnector->host,
                $this->confConnector->database,
                '-c', str_replace('`', null, $pre_set_queries . $query),
            ],
            null,
            ['PGPASSWORD' => Crypt::decrypt($this->confConnector->password)]
        );
        $process->setTimeout(300); // 5m
        $process->enableOutput();

        return $process;
    }

    public function getGrants(): array
    {
        $all_grants = [];

        // Exclude row which contains the password.
        foreach ($this->connection->select('
            SELECT rolname, rolsuper, rolinherit, rolcreaterole, rolcreatedb, rolcanlogin, rolreplication, rolconnlimit, rolvaliduntil, rolbypassrls, rolconfig
            FROM pg_roles
            WHERE rolname=\'localuser\'
            ', []) as $grants) {

            foreach ((array)$grants as $role => $value) {

                if (!mb_strstr($role, 'rolpassword')) {

                    $all_grants[] = '<strong>' . $role . '</strong>: ' . $value;
                }
            }
        }

        // Exclude row which contains the password.
        foreach ($this->connection->select('
                    SELECT concat(table_schema, \'.\', table_name) as table_name, string_agg(privilege_type, \', \')
                    FROM information_schema.role_table_grants
                    WHERE grantee = \'' . $this->confConnector->username . '\'
                    AND table_schema !~ \'information_schema\'
                    AND table_schema !~ \'^pg_\'
                    GROUP BY table_schema, table_name
                    ORDER BY table_name', []) as $grants) {

            $all_grants[] = '<strong>' . $grants->table_name . '</strong>: ' . $grants->string_agg;

        }

        return $all_grants;
    }

    public function getPrimeReactTreeDB(Request $request): bool|Collection
    {
        $refresh_now = $request->exists('refresh-cache');
        if ($this->_prime_react_tree_db_refreshed === false) {
            try {

                Cache::delete($this->tables_cache_key);
                Cache::delete($this->prime_react_tree_db_cache_key);
            } catch (InvalidArgumentException $e) {

                Log::warning('[PostgreSQLConnectorService::getPrimeReactTreeDB] Unable to delete cache files.', [$e->getMessage()]);
            }
        }

        if (Cache::has($this->prime_react_tree_db_cache_key)) {

            Log::debug('[PostgreSQLConnectorService::getPrimeReactTreeDB] We have a PrimeReact Tree db cache file.',
                       ['$refresh_now' => $refresh_now, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);

            return new Collection(Cache::get($this->prime_react_tree_db_cache_key));
        } else {

            $refresh_now = true;
            Log::debug('[PostgreSQLConnectorService::getPrimeReactTreeDB] We need to generate a PrimeReact Tree db cache file.',
                       ['$refresh_now' => true, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);
        }

        if ($refresh_now === true && $this->_prime_react_tree_db_refreshed === false) {

            $this->updatePrimeReactTreeDB();
            $this->_prime_react_tree_db_refreshed = true;
            return $this->getPrimeReactTreeDB($request);
        } else {

            Log::warning('[PostgreSQLConnectorService::getTableSchemas] No refresh performed..',
                         ['$refresh_now' => true, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);
        }

        return false;
    }

    public function getSchemas(): bool|Collection
    {
        $refresh_now = false;
        if (!Cache::has($this->tables_cache_key)) {

            Log::debug('[PostgreSQLConnectorService::getSchemas] We need to generate a schemas cache file.',
                       ['$refresh_now' => false, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);
            $refresh_now = true;
        } else if (Cache::has($this->tables_cache_key)) {

            Log::debug('[PostgreSQLConnectorService::getSchemas] We have a schemas cache file.',
                       ['$refresh_now' => false, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);

            $collection = new Collection(Cache::get($this->tables_cache_key));
            if ($collection->count() > 0) {

                return $collection;
            }

            $refresh_now = true;
            Log::warning('[PostgreSQLConnectorService::getSchemas] Schemas cache file is empty, we try a refresh.',
                         ['$refresh_now' => true, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);
        }

        if ($refresh_now === true && $this->_schemas_refreshed === false) {

            $this->updateSchemas();
            $this->_schemas_refreshed = true;
            return $this->getSchemas();
        } else {

            Log::warning('[PostgreSQLConnectorService::getSchemas] No refresh performed..',
                         ['$refresh_now' => $refresh_now, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);
        }

        return false;
    }

    public function getSSLCypher(): string
    {
        return '';
    }

    public function initConnection(ConfConnector $confConnector): void
    {
        $options_ssl = array_filter([PDO::MYSQL_ATTR_SSL_CA => '']);

        if ($confConnector->use_ssl === true) {

            $options_ssl = [
                'sslmode' => $confConnector->pgsql_ssl_mode,
                'options' => [
                    'sslrootcert' => $confConnector->ssl_ca,
                    'sslcert'     => $confConnector->ssl_cert,
                    'sslkey'      => $confConnector->ssl_key,
                ]
            ];
        }

        Config::set(
            'database.connections.' . $confConnector->name,
            [
                'driver'         => 'pgsql',
                'url'            => '',
                'host'           => $confConnector->host,
                'port'           => $confConnector->port,
                'database'       => $confConnector->database,
                'username'       => $confConnector->username,
                'password'       => Crypt::decrypt($confConnector->password),
                'unix_socket'    => '',
                //'charset'        => 'utf8mb4',
                //'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => true,
                'engine'         => null,
                'timeout'        => 2,
                'options'        => extension_loaded('pdo_pgsql') ? $options_ssl : []
            ]
        );
    }

    public function parseCliResults(array &$results, ProcessSQLQueriesBuffer $processSQLQueriesBuffer): void
    {
        $columns = [];

        foreach ($processSQLQueriesBuffer->stdout as $idx => $lines) {

            $encoding = mb_detect_encoding($lines, 'UTF-8, ISO-8859-1, WINDOWS-1252, WINDOWS-1251', true);
            if ($encoding != 'UTF-8') {
                $lines = iconv($encoding, 'UTF-8//IGNORE', $lines);
            }

            $lines = str_replace(['\","', "\n\r"], null, $lines);
            foreach (explode("\n", $lines) as $idx2 => $line) {

                if (preg_match('`^-.*\+|^-*$`', $line) || mb_strstr($line, 'rows)')) {
                    continue;
                }

                //Log::debug(':' . $idx . '-' . $idx2 . ' >', [$line]);
                $values = explode("|", $line);
                if ($line !== "") {

                    if ($idx === 0 && $idx2 === 0) {

                        $columns = $values;
                        //Log::debug('COLUMNS > ', [$columns]);
                    } else {

                        $result = [];
                        //Log::debug('VALUES >', [$values]);

                        foreach ($values as $column => $value) {

                            //Log::debug('column -> value', [$column, $value]);
                            $result[trim($columns[$column])] = str_replace('\n', ' ', trim($value));
                        }

                        $results[] = $result;
                    }
                }
            }
        }
    }

    public function replaceInputParameterVariables(ExecReportInfo $execReportInfo, string $query): string
    {
        foreach ($execReportInfo->inputParameters() as $inputParameter) {

            Log::debug('handle input parameter, replace: "@' . $inputParameter['name'] . '" with its value: "' . $inputParameter['value'] . '";' . PHP_EOL);
            $query = str_replace('@' . $inputParameter['name'], $inputParameter['value'], $query);
        }

        return $query;
    }

    public function updatePrimeReactTreeDB(): void
    {
        $schemas = $this->getSchemas();
        if ($schemas === false) {

            Log::debug('[PostgreSQLConnectorService::updatePrimeReactTreeDB]  Unable to get table schemas.');
            return;
        }

        Log::debug('[PostgreSQLConnectorService::updatePrimeReactTreeDB] Updating PrimeReact Tree db cache file. (' . $schemas->count() . ' schemas(s))');

        /** @var PrimeReactTree[] $tree */
        $tree = [];
        /** @var DBSchema $schema */
        foreach ($schemas as $schema) {

            Log::debug('[PostgreSQLConnectorService::updatePrimeReactTreeDB] Generated Tree for "' . $schema->name . '" schema, containing ' . count($schema->tables) . ' table(s)');

            /** @var PrimeReactTree[] $tree_children */
            $tree_children = [];

            //
            // TABLES
            //
            foreach ($schema->tables as $table) {

                /** @var PrimeReactTree[] $tree_children_columns */
                $tree_children_columns = [];
                foreach ($table->columns as $column) {

                    $tree_children_columns[] = new PrimeReactTreeDb(
                        $schema->name . $this->_getPrimeReactTreeIcon($column) . '-column-' . $table->name . '-' . $column->name . '-' . $this->_cleanUpStringForKey($column->type),
                        $column->name,
                        PrimeReactTreeDb::SIMPLE,
                        PrimeReactTreeDb::COLUMN,
                        $column->type,
                        $column->comment,
                        'pi pi-fw ' . $this->_getPrimeReactTreeIcon($column),
                        null,
                    );
                }

                $tree_children[] = new PrimeReactTreeDb(
                    $schema->name . '-' . $table->name,
                    $table->name,
                    null,
                    PrimeReactTreeDb::TABLE,
                    null,
                    null,
                    'pi pi-fw pi-table',
                    $tree_children_columns,
                );
            }


            //
            // VIEWS
            //
            if (count($schema->views) > 0) {

                /** @var PrimeReactTreeDb $views_tree */
                $views_tree_children = [];

                foreach ($schema->views as $view) {

                    /** @var PrimeReactTree[] $tree_children_columns */
                    $tree_children_columns = [];

                    foreach ($view->columns as $column) {

                        $tree_children_columns[] = new PrimeReactTreeDb(
                            $schema->name . '-view-' . $view->name . '-' . $column->name,
                            $column->name,
                            PrimeReactTreeDb::SIMPLE,
                            PrimeReactTreeDb::COLUMN,
                            $column->type,
                            $column->comment,
                            'pi pi-fw pi-file',
                            null,
                        );
                    }

                    $views_tree_children[] = new PrimeReactTreeDb(
                        $schema->name . '-view-' . $view->name,
                        $view->name,
                        null,
                        PrimeReactTreeDb::VIEW,
                        null,
                        null,
                        'pi pi-fw pi-table',
                        $tree_children_columns,
                    );
                }

                $tree_children[] = new PrimeReactTreeDb(
                    $schema->name . '-' . 'views',
                    'views',
                    null,
                    PrimeReactTreeDb::VIEWS_DIRECTORY,
                    null,
                    null,
                    'pi pi-fw pi-eye',
                    $views_tree_children
                );
            }

            $tree[] = new PrimeReactTreeDb(
                $schema->name,
                $schema->name,
                null,
                PrimeReactTreeDb::TABLE_SCHEMA,
                null,
                null,
                'pi pi-fw pi-book',
                $tree_children,
            );
        }

        Cache::put($this->prime_react_tree_db_cache_key, $tree, 3600);
    }

    public function updateSchemas(): void
    {
        Log::debug('[PostgreSQLConnectorService::updateSchemas] Updating schemas cache file.');

        //
        // Get all tables Client can access.
        //
        $stmt = $this->connection->getPdo()
            ->prepare('
                SELECT schema_name
                FROM information_schema.schemata
                WHERE  schema_name !~ \'information_schema\' AND schema_name !~ \'^pg_\' AND schema_name !~ \'^_pg_\'
                ORDER BY schema_name
            ');

        $stmt->execute();

        /** @var DBSchema[] $schemas */
        $schemas = [];

        while ($column_table_schema = $stmt->fetch(PDO::FETCH_OBJ)) {

            $current_table_schema = $column_table_schema->schema_name;

            // Get primary keys & foreign keys
            $primaryKeyAndForeignKIndexesCollection = collect($this->_getPrimaryAndForeignKeys($current_table_schema));
            // Get all indexes
            $indexCollection = collect($this->_getIndexes($current_table_schema));

            // Get all column's tables
            $stmt2 = $this->connection->getPdo()
                ->prepare('
                    SELECT c.column_name, c.data_type, c.table_name, pd.description
                    FROM information_schema.columns c
                    JOIN information_schema.tables t ON c.table_name = t.table_name AND t.table_type = \'BASE TABLE\'
                    JOIN pg_catalog.pg_namespace pn ON pn.nspname = :table_schema
                    JOIN pg_catalog.pg_class pc ON pc.relkind = \'r\' AND pn.oid = pc.relnamespace AND t.table_name = pc.relname
                    LEFT JOIN pg_catalog.pg_description pd ON pc.oid = pd.objoid AND c.ordinal_position = pd.objsubid
                    WHERE c.table_schema = :table_schema
                    ORDER BY c.table_schema, c.table_name
                    ');

            $stmt2->execute(['table_schema' => $current_table_schema]);

            $current_table = '';
            /** @var DBTable[] $tables */
            $tables = [];
            /** @var DBColumn[] $colums */
            $columns = [];

            $initialized = false;
            while ($column = $stmt2->fetch(PDO::FETCH_OBJ)) {

                if ($initialized === false) {

                    $current_table = $column->table_name;
                    $initialized = true;
                }

                // Table change
                if ($column->table_name != $current_table) {

                    $this->_getIndexesAndPrimaryAndForeignKeyForTable(
                        $columns,
                        $indexCollection,
                        $primaryKeyAndForeignKIndexesCollection,
                        $current_table
                    );

                    $tables[] = new DBTable(
                        $current_table, $columns
                    );

                    $columns = [];
                }

                // Normal column
                $columns[] = new DBColumn(
                    $column->column_name,
                    $column->description ?? '',
                    $column->data_type
                );

                $current_table = $column->table_name;
            }


            $this->_getIndexesAndPrimaryAndForeignKeyForTable(
                $columns,
                $indexCollection,
                $primaryKeyAndForeignKIndexesCollection,
                $current_table
            );

            $tables[] = new DBTable(
                $current_table, $columns
            );

            //
            // Get all views Client can access.
            //
            // @todo Comment at View level, not column level. See how we can display that on frontend.
            $stmt3 = $this->connection->getPdo()->prepare('
                SELECT v.table_schema, v.table_name, pa.attname AS column_name,  \'\' as column_comment, format_type(atttypid, atttypmod) AS column_type
                FROM information_schema.views v
                JOIN pg_catalog.pg_attribute pa ON pa.attrelid = CONCAT(\'' . $current_table_schema . '.\', v.table_name)::regclass
                JOIN information_schema.columns c ON v.table_schema = c.table_schema AND v.table_name = c.table_name AND pa.attname = c.column_name
                -- JOIN pg_catalog.pg_namespace pn ON pn.nspname = :table_schema
                -- JOIN pg_catalog.pg_class pc ON pc.relkind = \'v\' AND pn.oid = pc.relnamespace AND v.table_name = pc.relname
                -- LEFT JOIN pg_catalog.pg_description pd ON pc.oid = pd.objoid
                WHERE v.table_schema NOT IN (\'information_schema\', \'pg_catalog\')
                AND v.table_schema = :table_schema
                ORDER BY v.table_schema, v.table_name;
            ');
            $stmt3->execute(['table_schema' => $current_table_schema]);


            $current_view = '';
            /** @var DBView[] $views */
            $views = [];
            /** @var DBColumn[] $colums */
            $columns = [];

            $initialized = false;
            while ($view = $stmt3->fetch(PDO::FETCH_OBJ)) {

                if ($initialized === false) {

                    $current_view = $view->table_name;
                    $initialized = true;
                }

                // Table change
                if ($view->table_name != $current_view) {

                    $views[] = new DBView(
                        $current_view, $columns
                    );

                    $columns = [];
                }

                $columns[] = new DBColumn(
                    $view->column_name,
                    $view->column_comment ?? '',
                    $view->column_type);

                $current_view = $view->table_name;
            }

            if ($initialized) {

                $views[] = new DBView(
                    $current_view, $columns
                );
            }

            $schemas[] = new DBSchema(
                $current_table_schema,
                $tables,
                $views
            );
        }

        Cache::put($this->tables_cache_key, $schemas, 3600);
    }

    private function _cleanUpStringForKey(string $string): string
    {
        return str_replace(['(', ')', ',', ' ', '>', '<', '"', '\''], '-', $string);
    }

    private function _generateAlias(string $name, int $current_loop_num): AutoCompleteAlias
    {
        $alias = '';
        $parts = preg_split('/_|-|(?=[A-Z])/', $name);
        if (is_array($parts) && count($parts) > 0) {

            foreach ($parts as $idx => $part) {
                $alias .= mb_substr($part, 0, $current_loop_num);
            }
        }
        return new AutoCompleteAlias($alias);
    }

    private function _getAutoCompleteAlias(
        Collection &$autoCompleteAliases,
        string     $name,
        bool       $check_exists = true,
        int        $current_loop_num = 1
    ): AutoCompleteAlias
    {
        $autoCompleteAlias = $this->_generateAlias($name, $current_loop_num);

        if ($check_exists === true) {

            if ($autoCompleteAliases->filter(
                    function (AutoCompleteAlias $alias) use ($autoCompleteAlias) {

                        return $alias->value === $autoCompleteAlias->value;
                    })->count() > 0) {

                //echo '|| ' . $name . ' | ' . $autoCompleteAlias->value . '  ALREADY EXISTS || ' . $autoCompleteAlias->value . '<br/>';
                // To prevent infinite loop
                if ($current_loop_num <= 5) {

                    return $this->_getAutoCompleteAlias(
                        $autoCompleteAliases,
                        $name,
                        $check_exists,
                        ++$current_loop_num
                    );
                }

            } else {

                $autoCompleteAliases->add($autoCompleteAlias);
            }
        }

        //echo '|| ' . $name . ' | FINAL | ' . $autoCompleteAlias->value . ' | ' . $current_loop_num . ' || ' . '<br/>';
        return $autoCompleteAlias;
    }

    private function _getAutoCompletionCaption(DBColumn|DBForeignKey|DBIndex|DBPrimaryKey $column): string

    {

        if (is_a($column, 'App\Models\DBForeignKey')) {

            return ' (fk)';
        } else if (is_a($column, 'App\Models\DBIndex')) {

            return ' (index)';
        } else if (is_a($column, 'App\Models\DBPrimaryKey')) {

            return ' (primary key)';
        }

        return '';
    }

    private function _getAutoCompleteDBAlias(
        Collection &$autoCompleteDBAliases,
        string     $schema,
        int        $current_loop_num = 1
    ): AutoCompleteAlias
    {
        $autoCompleteDBAlias = $this->_generateAlias($schema, $current_loop_num);

        if ($autoCompleteDBAliases->filter(
                function (AutoCompleteAlias $alias) use ($autoCompleteDBAlias) {

                    return $alias->value === $autoCompleteDBAlias->value;
                })->count() > 0) {

            // To prevent infinite loop
            if ($current_loop_num <= 5) {

                return $this->_getAutoCompleteDBAlias(
                    $autoCompleteDBAliases,
                    $schema,
                    ++$current_loop_num
                );
            }

        } else {

            $autoCompleteDBAliases->add($autoCompleteDBAlias);
        }

        return $autoCompleteDBAlias;
    }

    private function _getIndexes(string $table_schema): array
    {
        /** @var DBIndex[] $indexes */
        $indexes = [];
        $stmt = $this->connection->getPdo()
            ->prepare('
                SELECT pc.relname            AS index_name,
                       pi.indrelid::regclass as table_name,
                       pa.amname             AS index_type,
                       ARRAY(SELECT pg_get_indexdef(pi.indexrelid, k + 1, true)
                             FROM generate_subscripts(pi.indkey, 1) AS k
                             ORDER BY k
                           )                 AS column_names,
                       \'\'                    as comment
                FROM pg_index AS pi
                         JOIN pg_class as pc ON pc.oid = pi.indexrelid
                         JOIN pg_am as pa ON pc.relam = pa.oid
                         JOIN pg_namespace as pn ON pn.oid = pc.relnamespace AND pn.nspname = ANY (current_schemas(false))
                WHERE pn.nspname = :table_schema
            ');

        $stmt->execute(['table_schema' => $table_schema]);

        while ($index = $stmt->fetch(PDO::FETCH_OBJ)) {

            $indexes[] = new DBIndex(
                $index->index_name,
                $index->comment ?? '',
                str_replace(['{', '}'], null, $index->column_names) . ' (' . $index->index_type . ')',
                $index->index_type,
                $index->table_name
            );
        }

        return $indexes;
    }

    /**
     * @param array $columns
     * @param Collection $indexCollection
     * @param Collection $primaryKeyAndForeignKIndexesCollection
     * @param string $table_name
     * @return void
     */
    private function _getIndexesAndPrimaryAndForeignKeyForTable(array &$columns, Collection $indexCollection, Collection $primaryKeyAndForeignKIndexesCollection, string $table_name): void
    {
        // Add table's indexes
        /** @var DBIndex[] $indexes */
        $indexes = $indexCollection->filter(
            function (DBIndex $index) use ($table_name) {

                return $index->table_name === $table_name;
            });

        foreach ($indexes as $index) {

            $columns[] = $index;
        }

        // Add table's primary and foreign keys
        /** @var DBForeignKey[]|DBPrimaryKey[] $primaryKeyAndForeignKIndexes */
        $primaryKeyAndForeignKIndexes = $primaryKeyAndForeignKIndexesCollection->filter(
            function (DBForeignKey|DBPrimaryKey $index) use ($table_name) {

                return $index->table_name === $table_name;
            });

        foreach ($primaryKeyAndForeignKIndexes as $primaryOrForeignKey) {

            $columns[] = $primaryOrForeignKey;
        }
    }

    private function _getPrimaryAndForeignKeys(string $table_schema): array
    {
        /** @var DBForeignKey[]|DBPrimaryKey $primaryAndForeignKeys */
        $primaryAndForeignKeys = [];

        $stmt = $this->connection->getPdo()
            ->prepare("
                SELECT string_agg(kc.column_name, ',') AS column_names, tc.table_name, tc.constraint_name, ccu.column_name as referenced_column_name, ccu.table_name as referenced_table_name
                FROM information_schema.table_constraints tc
                         JOIN information_schema.key_column_usage kc ON kc.table_name = tc.table_name AND kc.table_schema = tc.table_schema AND kc.constraint_name = tc.constraint_name
                         LEFT JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema AND tc.constraint_type = 'FOREIGN KEY'
                WHERE tc.table_schema = :table_schema
                  AND tc.constraint_type IN ('PRIMARY KEY', 'FOREIGN KEY')
                  AND kc.ordinal_position IS NOT NULL
                GROUP BY tc.table_schema, tc.table_name, tc.constraint_name, referenced_column_name, referenced_table_name, kc.column_name

            ");

        $stmt->execute(['table_schema' => $table_schema]);

        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {

            // It' a key
            if (is_null($row->referenced_table_name)) {

                $primaryAndForeignKeys[] = new DBPrimaryKey(
                    $row->constraint_name,
                    $row->constraint_name,
                    $row->column_names,
                    $row->table_name
                );

            } else {

                $primaryAndForeignKeys[] = new DBForeignKey(
                    $row->constraint_name,
                    $row->constraint_name,
                    $row->column_names . ' -> ' . $row->referenced_table_name . '.' . $row->referenced_column_name,
                    $row->table_name
                );
            }
        }

        return $primaryAndForeignKeys;
    }

    private function _getPrimeReactTreeIcon(DBColumn|DBForeignKey|DBIndex|DBPrimaryKey $column): string
    {

        if (is_a($column, 'App\Models\DBForeignKey')) {

            return 'pi-lock';
        } else if (is_a($column, 'App\Models\DBIndex')) {

            return 'pi-info';
        } else if (is_a($column, 'App\Models\DBPrimaryKey')) {

            return 'pi-key';
        }

        return 'pi-file';
    }

}
