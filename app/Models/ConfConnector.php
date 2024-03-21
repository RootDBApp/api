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

use App\Services\ConnectorService;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class ConfConnector
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property int $connector_database_id
 * @property int $organization_id
 * @property string $host
 * @property int $port
 * @property string $database
 * @property string $username
 * @property string $password
 * @property int $timeout
 * @property string $raw_grants
 * @property bool $seems_ok
 * @property bool $use_ssl
 * @property bool $global
 * @property string|null $ssl_ca
 * @property string|null $ssl_cert
 * @property string|null $ssl_key
 * @property string $ssl_cipher
 * @property-read ConnectorDatabase $connectorDatabase
 * @property-read Organization $organization
 * @property-read Collection|Report[] $reports
 * @property-read Collection|ReportParameterInput[] $reportParameterInputs
 * @property-read int|null $report_parameter_inputs_count
 * @method static Builder|ConfConnector newModelQuery()
 * @method static Builder|ConfConnector newQuery()
 * @method static Builder|ConfConnector query()
 * @method static Builder|ConfConnector whereConnectorDatabaseId($value)
 * @method static Builder|ConfConnector whereOrganizationId($value)
 * @method static Builder|ConfConnector whereDatabase($value)
 * @method static Builder|ConfConnector whereGlobal($value)
 * @method static Builder|ConfConnector whereHost($value)
 * @method static Builder|ConfConnector whereId($value)
 * @method static Builder|ConfConnector whereName($value)
 * @method static Builder|ConfConnector wherePassword($value)
 * @method static Builder|ConfConnector wherePort($value)
 * @method static Builder|ConfConnector whereTimeout($value)
 * @method static Builder|ConfConnector whereUsername($value)
 * @method static Builder|ConfConnector whereSslCa($value)
 * @method static Builder|ConfConnector whereSslCert($value)
 * @method static Builder|ConfConnector whereSslCipher($value)
 * @method static Builder|ConfConnector whereSslKey($value)
 * @method static Builder|ConfConnector whereUseSsl($value)
 * @property-read int|null $reports_count
 * @property int $mysql_ssl_verify_server_cert
 * @property string $pgsql_ssl_mode
 * @method static Builder|ConfConnector whereMysqlSslVerifyServerCert($value)
 * @method static Builder|ConfConnector wherePgsqlSslMode($value)
 * @mixin Eloquent
 */
class ConfConnector extends ApiModel
{
    public $timestamps = false;
    private string $last_error = '';

    protected $fillable = [
        'name',
        'connector_database_id',
        'organization_id',
        'host',
        'port',
        'database',
        'username',
        'password',
        'timeout',
        'use_ssl',
        'ssl_ca',
        'ssl_cert',
        'ssl_key',
        'global',
        'mysql_ssl_verify_server_cert',
        'pgsql_ssl_mode'
    ];

    public static array $rules = [
        'name'                         => 'required|string|min:2|max:255',
        'organization_id'              => 'required|integer|exists:organizations,id',
        'connector_database_id'        => 'required|integer|exists:connector_databases,id',
        'host'                         => 'required|string|max:255',
        'port'                         => 'required|integer',
        'database'                     => 'required|string|max:255',
        'username'                     => 'required|string|max:255',
        'password'                     => 'required|string|max:255',
        'timeout'                      => 'required|integer|max:120',
        'use_ssl'                      => 'boolean',
        'ssl_ca'                       => 'required_if:use_ssl,true,string',
        'ssl_cert'                     => 'required_if:use_ssl,true,string',
        'ssl_key'                      => 'required_if:use_ssl,true,string',
        'mysql_ssl_verify_server_cert' => 'required_if:use_ssl,true,string',
        'pgsql_ssl_mode'               => 'required_if:use_ssl,true,string',
    ];

    protected $casts = [
        'use_ssl' => 'boolean',
        'global'  => 'boolean',
    ];

    public function connectorDatabase(): BelongsTo
    {
        return $this->belongsTo('App\Models\ConnectorDatabase');
    }

    public function organization(): HasOne
    {
        return $this->hasOne('App\Models\Organization');
    }

    public function reportParameterInputs(): HasMany
    {
        return $this->hasMany('App\Models\ReportParameterInput');
    }

    public function reports(): HasMany
    {
        return $this->hasMany('\App\Models\Report');
    }

    public function test(ConnectorService $connectorService, bool $existingConfConnector = false): bool
    {
        $error = '';
        try {

            exec('ping -W 2 -c 1 ' . $this->host, $output, $status);
            //exec('nmap -Pn ' . $this->host . ' -p ' . $this->port . ' --host-timeout 5', $output, $status);

            if ($status === 127) {

                $error = 'Unable to reach the host ' . $this->host;
                throw new Exception($error);
            } else {


                foreach ($output as $output_line) {

                    if (mb_strstr($output_line, 'Failed to resolve') || mb_strstr($output_line, 'due to host timeout')) {

                        $error = 'Unable to reach the host ' . $this->host;
                        throw new Exception($error);
                    }

                    if (mb_strstr($output_line, $this->port . '/tcp') && !mb_strstr($output_line, 'open')) {

                        $error = 'Port ' . $this->port . ' seems to be closed.';
                        throw new Exception($error);
                    }
                }

                $connection = fsockopen($this->host, $this->port, $error_code, $error_message, 2);
                if (!is_resource($connection)) {

                    $error = 'Port ' . $this->port . ' seems to be closed.';
                    throw new Exception($error);
                }

                fclose($connection);

                $connectorService->getInstance($this)->getConnection()->getPdo();

                // For existing ConfConnector, we can check SSL status & grants.
                if ($existingConfConnector === true) {

                    $all_grants = implode('<br />', $connectorService->getInstance($this)->getGrants());

                    // Check SSL status.
                    if ($this->use_ssl === true) {

                        foreach ($connection->select('SHOW STATUS like \'Ssl_cipher\';', []) as $ssl_cipher) {

                            $this->ssl_cipher = $ssl_cipher->Value;
                            if (mb_strlen($this->ssl_cipher) > 3) {
                                $this->save();
                            }
                        }
                    }

                    $this->raw_grants = $all_grants;
                    $this->seems_ok = true;
                }
            }

        } catch (Exception $exception) {

            $this->last_error = 'Error : "' . (mb_strlen($error) > 5 ? $error : $exception->getMessage()) . '"';

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLastError(): string
    {
        return $this->last_error;
    }
}
