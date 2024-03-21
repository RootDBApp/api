<?php

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
use App\Models\DBTable;
use App\Models\DBSchema;
use App\Models\DBView;
use App\Models\PrimeReactTree;
use App\Models\PrimeReactTreeDb;
use App\Models\ProcessSQLQueriesBuffer;
use App\Models\Report;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use PDO;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Process\Process;

class MySQLConnectorService extends CommonConnectorService
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
                'mysql',
                '-u', $this->confConnector->username,
                '-p' . Crypt::decrypt($this->confConnector->password),
                '-h', $this->confConnector->host,
                $this->confConnector->database,
                '-e', $pre_set_queries . $query,
            ]);
        $process->setTimeout(300); // 5m
        $process->enableOutput();

        return $process;
    }

    public function getGrants(): array
    {
        $all_grants = [];

        // Exclude row which contains the password.
        foreach ($this->connection->select('SHOW GRANTS FOR CURRENT_USER;', []) as $grants) {

            $raw_grants = current((array)$grants);
            if (!mb_strstr($raw_grants, 'PASSWORD')) {
                $all_grants[] = $raw_grants;
            }
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

                Log::warning('[MySQLConnectorService::getPrimeReactTreeDB] Unable to delete cache files.', [$e->getMessage()]);
            }
        }

        if (Cache::has($this->prime_react_tree_db_cache_key)) {

            Log::debug('[MySQLConnectorService::getPrimeReactTreeDB] We have a PrimeReact Tree db cache file.',
                       ['$refresh_now' => $refresh_now, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);

            return new Collection(Cache::get($this->prime_react_tree_db_cache_key));
        } else {

            $refresh_now = true;
            Log::debug('[MySQLConnectorService::getPrimeReactTreeDB] We need to generate a PrimeReact Tree db cache file.',
                       ['$refresh_now' => true, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);
        }

        if ($refresh_now === true && $this->_prime_react_tree_db_refreshed === false) {

            $this->updatePrimeReactTreeDB();
            $this->_prime_react_tree_db_refreshed = true;
            return $this->getPrimeReactTreeDB($request);
        } else {

            Log::warning('[MySQLConnectorService::getTableSchemas] No refresh performed..',
                         ['$refresh_now' => true, '$this->_prime_react_tree_db_refreshed' => $this->_prime_react_tree_db_refreshed]);
        }

        return false;
    }

    public function getSchemas(): bool|Collection
    {
        $refresh_now = false;
        if (!Cache::has($this->tables_cache_key)) {

            Log::debug('[MySQLConnectorService::getSchemas] We need to generate a schemas cache file.',
                       ['$refresh_now' => false, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);
            $refresh_now = true;
        } else if (Cache::has($this->tables_cache_key)) {

            Log::debug('[MySQLConnectorService::getSchemas] We have a schemas cache file.',
                       ['$refresh_now' => false, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);

            $collection = new Collection(Cache::get($this->tables_cache_key));
            if ($collection->count() > 0) {

                return $collection;
            }

            $refresh_now = true;
            Log::warning('[MySQLConnectorService::getSchemas] Schemas cache file is empty, we try a refresh.',
                         ['$refresh_now' => true, '$this->_schemas_refreshed' => $this->_schemas_refreshed]);
        }

        if ($refresh_now === true && $this->_schemas_refreshed === false) {

            $this->updateSchemas();
            $this->_schemas_refreshed = true;
            return $this->getSchemas();
        } else {

            Log::warning('[MySQLConnectorService::getSchemas] No refresh performed..',
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

                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => (boolean)$confConnector->mysql_ssl_verify_server_cert,
                PDO::MYSQL_ATTR_SSL_CA                 => $confConnector->ssl_ca,
                PDO::MYSQL_ATTR_SSL_KEY                => $confConnector->ssl_key,
                PDO::MYSQL_ATTR_SSL_CERT               => $confConnector->ssl_cert,
                // to test with
                //\PDO::MYSQL_ATTR_SSL_CIPHER             => 'DHE-RSA-AES256-SHA',

                // or
                // \PDO::MYSQL_ATTR_SSL_CA     => base_path('storage/app/seeder_conf_connector_5_ca-cert.pem'),
                // \PDO::MYSQL_ATTR_SSL_KEY    => base_path('storage/app/seeder_conf_connector_5_service-1-client-key.pem'),
                // \PDO::MYSQL_ATTR_SSL_CERT   => base_path('storage/app/seeder_conf_connector_5_service-1-client-cert.pem'),

                // or
                // \PDO::MYSQL_ATTR_SSL_CA   => '/var/www/storage/app/seeder_conf_connector_5_ca-cert.pem',
                // \PDO::MYSQL_ATTR_SSL_KEY  => '/var/www/storage/app/seeder_conf_connector_5_service-1-client-key.pem',
                // \PDO::MYSQL_ATTR_SSL_CERT => '/var/www/storage/app/seeder_conf_connector_5_service-1-client-cert.pem',
            ];
        }

        Config::set(
            'database.connections.' . $confConnector->name,
            [
                'driver'         => 'mysql',
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
                'options'        => extension_loaded('pdo_mysql') ? $options_ssl : []
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

                //Log::debug(':' . $idx . '-' . $idx2 . ' >', [$line]);
                $values = explode("\t", $line);

                if ($line !== "") {

                    if ($idx === 0 && $idx2 === 0) {

                        $columns = $values;
                        //Log::debug('COLUMNS > ', [$columns]);
                    } else {

                        $result = [];
                        //Log::debug('VALUES >', [$values]);

                        foreach ($values as $column => $value) {

                            //Log::debug('column -> value', [$column, $value]);
                            $result[$columns[$column]] = str_replace('\n', ' ', $value);
                        }
                        $results[] = $result;
                    }
                }
            }
        }
    }

    public function setInputParameterVariables(ExecReportInfo $execReportInfo, Connection &$connection,): void
    {
        foreach ($execReportInfo->inputParameters() as $inputParameter) {

            Log::debug('handle input parameter : set @' . $inputParameter['name'] . ' = "' . $inputParameter['value'] . '";' . PHP_EOL);
            $connection->unprepared('set @' . $inputParameter['name'] . ' = "' . $inputParameter['value'] . '";');
        }
    }

    public function updatePrimeReactTreeDB(): void
    {
        $schemas = $this->getSchemas();
        if ($schemas === false) {

            Log::debug('[MySQLConnectorService::updatePrimeReactTreeDB]  Unable to get table schemas.');
            return;
        }

        Log::debug('[MySQLConnectorService::updatePrimeReactTreeDB] Updating PrimeReact Tree db cache file. (' . $schemas->count() . ' schemas(s))');

        /** @var PrimeReactTree[] $tree */
        $tree = [];
        /** @var DBSchema $schema */
        foreach ($schemas as $schema) {

            Log::debug('[MySQLConnectorService::updatePrimeReactTreeDB] Generated Tree for "' . $schema->name . '" schema, containing ' . count($schema->tables) . ' table(s)');

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
        Log::debug('[MySQLConnectorService::updateSchemas] Updating schemas cache file.');

        //
        // Get all tables Client can access.
        //
        $stmt = $this->connection->getPdo()
            ->prepare('
                SELECT TABLE_SCHEMA
                FROM INFORMATION_SCHEMA.SCHEMA_PRIVILEGES
                WHERE GRANTEE LIKE :grantee
                AND PRIVILEGE_TYPE = :privilege_type');

        $stmt->execute([
                           'grantee'        => '%' . $this->confConnector->username . '%',
                           'privilege_type' => 'SELECT'
                       ]);

        /** @var DBSchema[] $schemas */
        $schemas = [];

        while ($column_table_schema = $stmt->fetch(PDO::FETCH_OBJ)) {

            $current_table_schema = $column_table_schema->TABLE_SCHEMA;

            // Get primary keys & foreign keys
            $primaryKeyAndForeignKIndexesCollection = collect($this->_getPrimaryAndForeignKeys($current_table_schema));
            // Get all indexes
            $indexCollection = collect($this->_getIndexes($current_table_schema));

            // Get all column's tables
            $stmt2 = $this->connection->getPdo()
                ->prepare('
                    SELECT c.TABLE_SCHEMA, c.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_COMMENT, c.COLUMN_TYPE
                    FROM INFORMATION_SCHEMA.COLUMNS c
                    LEFT JOIN INFORMATION_SCHEMA.VIEWS v ON c.TABLE_NAME = v.TABLE_NAME
                    WHERE c.TABLE_SCHEMA = :table_schema
                    AND v.TABLE_NAME IS NULL
                    ORDER BY c.TABLE_SCHEMA, c.TABLE_NAME');

            $stmt2->execute(['table_schema' => $current_table_schema]);

            $current_table = '';
            /** @var DBTable[] $tables */
            $tables = [];
            /** @var DBColumn[] $colums */
            $columns = [];

            $initialized = false;
            while ($column = $stmt2->fetch(PDO::FETCH_OBJ)) {

                if ($initialized === false) {

                    $current_table = $column->TABLE_NAME;
                    $initialized = true;
                }

                // Table change
                if ($column->TABLE_NAME != $current_table) {

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
                    $column->COLUMN_NAME,
                    $column->COLUMN_COMMENT,
                    $column->COLUMN_TYPE);

                $current_table = $column->TABLE_NAME;
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
            $query = 'SELECT v.TABLE_SCHEMA, v.TABLE_NAME, c.COLUMN_NAME, c.COLUMN_COMMENT, c.COLUMN_TYPE
                      FROM INFORMATION_SCHEMA.VIEWS v
                      LEFT JOIN INFORMATION_SCHEMA.COLUMNS c ON v.TABLE_NAME = c.TABLE_NAME
                      WHERE v.TABLE_SCHEMA = \'' . $current_table_schema . '\'
                      ORDER BY v.TABLE_NAME, c.COLUMN_NAME';

            // @todo try to understand why we can't have all view's columns using PDO.
            //$stmt3 = $this->connection->getPdo()->prepare($query);
            //$stmt3->execute(['table_schema' => $current_table_schema]);

            $command = [
                'mysql',
                '-u', $this->confConnector->username,
                '-p' . Crypt::decrypt($this->confConnector->password),
                '-h', $this->confConnector->host,
                $this->confConnector->database,
                '-e', $query,
            ];

            $process = new Process($command);
            $process->setTimeout(300);
            $process->enableOutput();
            $process->run();

            $current_view = '';
            /** @var DBView[] $views */
            $views = [];
            /** @var DBColumn[] $colums */
            $columns = [];

            $initialized = false;
            foreach (explode("\n", $process->getOutput()) as $idx => $view_column_row) {

                if ($idx === 0) {
                    continue;
                }

                $view_column = explode("\t", $view_column_row);
                // 0 => "TABLE_SCHEMA"
                // 1 => "TABLE_NAME"
                // 2 => "COLUMN_NAME"
                // 3 => "COLUMN_COMMENT"
                // 4 => "COLUMN_TYPE"
                if (count($view_column) !== 5) {
                    continue;
                }

                $table_name = $view_column[1];
                $column_name = $view_column[2];
                $column_comment = $view_column[3];
                $column_type = $view_column[4];

                if ($initialized === false) {

                    $current_view = $table_name;
                    $initialized = true;
                }

                // Table change
                if ($table_name != $current_view) {

                    $views[] = new DBView(
                        $current_view, $columns
                    );

                    $columns = [];
                }

                $columns[] = new DBColumn(
                    $column_name,
                    $column_comment,
                    $column_type);

                $current_view = $table_name;
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
                SELECT  GROUP_CONCAT(COLUMN_NAME) AS COLUMN_NAMES, TABLE_NAME, INDEX_NAME, COMMENT, INDEX_TYPE
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = :table_schema
                GROUP BY TABLE_NAME, INDEX_NAME, COMMENT, INDEX_TYPE
                ORDER BY TABLE_NAME;
            ');

        $stmt->execute(['table_schema' => $table_schema]);

        while ($index = $stmt->fetch(PDO::FETCH_OBJ)) {

            $indexes[] = new DBIndex(
                $index->INDEX_NAME,
                $index->COMMENT,
                $index->COLUMN_NAMES . ' (' . $index->INDEX_TYPE . ')',
                $index->INDEX_TYPE,
                $index->TABLE_NAME
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
                SELECT GROUP_CONCAT(COLUMN_NAME) AS COLUMN_NAMES, TABLE_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA LIKE :table_schema
                GROUP BY TABLE_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME, REFERENCED_TABLE_NAME
            ");

        $stmt->execute(['table_schema' => $table_schema]);

        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {

            // It' a key
            if (is_null($row->REFERENCED_TABLE_NAME)) {

                $primaryAndForeignKeys[] = new DBPrimaryKey(
                    $row->CONSTRAINT_NAME,
                    $row->CONSTRAINT_NAME,
                    $row->COLUMN_NAMES,
                    $row->TABLE_NAME
                );

            } else {

                $primaryAndForeignKeys[] = new DBForeignKey(
                    $row->CONSTRAINT_NAME,
                    $row->CONSTRAINT_NAME,
                    $row->COLUMN_NAMES . ' -> ' . $row->REFERENCED_TABLE_NAME . '.' . $row->REFERENCED_COLUMN_NAME,
                    $row->TABLE_NAME
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
