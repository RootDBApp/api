<?php

namespace App\Models;

use App\Services\ConnectorService;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\ParameterInput
 *
 * @property int $id
 * @property int $conf_connector_id
 * @property int $parameter_input_type_id
 * @property int $parameter_input_data_type_id
 * @property string $name
 * @property string|null $query
 * @property string|null $query_default_value
 * @property string|null $default_value
 * @property int $custom_entry
 * @property-read ConfConnector $confConnector
 * @property-read ReportParameterInputDataType $parameterInputDataType
 * @property-read ReportParameterInputType $parameterInputType
 * @method static Builder|ReportParameterInput newModelQuery()
 * @method static Builder|ReportParameterInput newQuery()
 * @method static Builder|ReportParameterInput query()
 * @method static Builder|ReportParameterInput whereCustomEntry($value)
 * @method static Builder|ReportParameterInput whereDefaultValue($value)
 * @method static Builder|ReportParameterInput whereId($value)
 * @method static Builder|ReportParameterInput whereName($value)
 * @method static Builder|ReportParameterInput whereParameterInputDataTypeId($value)
 * @method static Builder|ReportParameterInput whereParameterInputTypeId($value)
 * @method static Builder|ReportParameterInput whereQuery($value)
 * @method static Builder|ReportParameterInput whereQueryDefaultValue($value)
 * @method static Builder|ReportParameterInput whereConfConnectorId($value)
 * @mixin Eloquent
 */
class ReportParameterInput extends ApiModel
{
    public $timestamps = false;

    protected $fillable = [
        'conf_connector_id',
        'parameter_input_type_id',
        'parameter_input_data_type_id',
        'name',
        'query',
        'query_default_value',
        'default_value'
    ];

    public static array $rules = [
        'conf_connector_id'            => 'required|integer|exists:conf_connectors,id',
        'parameter_input_type_id'      => 'required|integer|exists:report_parameter_input_types,id',
        'parameter_input_data_type_id' => 'required|integer|exists:report_parameter_input_data_types,id',
        'name'                         => 'required|between:2,255',
        'query'                        => 'string|nullable',
        'query_default_value'          => 'string|nullable',
        'default_value'                => 'string|nullable'
    ];

    public function confConnector(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ConfConnector');
    }

    public function parameterInputType(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportParameterInputType');
    }

    public function parameterInputDataType(): BelongsTo
    {
        return $this->belongsTo('\App\Models\ReportParameterInputDataType');
    }

    public function getDefaultValue(): string
    {
        if ((is_null($this->default_value) || mb_strlen($this->default_value) === 0)
            && (!is_null($this->query_default_value) && mb_strlen($this->query_default_value))) {

            if ($this->conf_connector_id > 1) {

                $connectorService = new ConnectorService();

                // Use current Organization ConfConnector for the special All Connectors ConfConnector
                if ($this->confConnector->global === true) {
                    // Can't work when report is public
                    //$connection = auth()->user()->getConnection();
                    // What's the point to run a query which should works on every db, let's use the current local one.
                    //$connection = $connectorService->getInstance(ConfConnector::where('global', '!=', 1)->get()->first())->getConnection();
                    return (string)current((array)DB::selectOne($this->query_default_value));

                } else {

                    $connection = $connectorService->getInstance($this->confConnector)->getConnection();
                }

                return (string)current((array)$connection->selectOne($this->query_default_value));
            }

            return (string)current((array)DB::selectOne($this->query_default_value));
        }

        return (string)$this->default_value;
    }

    public function getParameterValues(): Collection
    {
        if (mb_strlen($this->query) === 0) {

            return new Collection();
        }

        if ($this->conf_connector_id >= 1) {

            $connectorService = new ConnectorService();

            // Use current Organization ConfConnector for the special All Connectors ConfConnector
            if ($this->confConnector->global === true) {

                // Can't work when report is public
                //$connection = auth()->user()->getConnection();
                // What's the point to run a query which should works on every db, let's use the current local one.
                //$connection = $connectorService->getInstance(ConfConnector::where('global', '!=', 1)->get()->first())->getConnection();
                return Collection::make(DB::select($this->query));
            } else {

                $connection = $connectorService->getInstance($this->confConnector)->getConnection();
            }

            return Collection::make($connection->select($this->query));
        }

        return Collection::make(DB::select($this->query));
    }
}
