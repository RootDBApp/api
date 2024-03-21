<?php

namespace Database\Seeders\Common;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportParameterInputsSeeder extends Seeder
{
    public function run(int $confConnectorId): void
    {
        DB::table('report_parameter_inputs')->insert(
            [
                [
                    'conf_connector_id'            => $confConnectorId,
                    'parameter_input_type_id'      => 1,
                    'parameter_input_data_type_id' => 2,
                    'name'                         => 'String',
                    'query'                        => null,
                    'query_default_value'          => null,
                    'default_value'                => null,
                    'custom_entry'                 => '0',
                ], [
                    'conf_connector_id'            => $confConnectorId,
                    'parameter_input_type_id'      => 1,
                    'parameter_input_data_type_id' => 1,
                    'name'                         => 'Number',
                    'query'                        => null,
                    'query_default_value'          => null,
                    'default_value'                => null,
                    'custom_entry'                 => '0',
                ], [
                    'conf_connector_id'            => $confConnectorId,
                    'parameter_input_type_id'      => 6,
                    'parameter_input_data_type_id' => 3,
                    'name'                         => 'Date - a month ago',
                    'query'                        => null,
                    'query_default_value'          => 'SELECT cast(date_add(NOW(), INTERVAL -1 MONTH) AS date)',
                    'default_value'                => null,
                    'custom_entry'                 => '0',
                ], [
                    'conf_connector_id'            => $confConnectorId,
                    'parameter_input_type_id'      => 6,
                    'parameter_input_data_type_id' => 3,
                    'name'                         => 'Date - today',
                    'query'                        => null,
                    'query_default_value'          => 'SELECT cast(NOW() AS date)',
                    'default_value'                => null,
                    'custom_entry'                 => '0',
                ], [
                    'conf_connector_id'            => $confConnectorId,
                    'parameter_input_type_id'      => 4,
                    'parameter_input_data_type_id' => 1,
                    'name'                         => '🔘 Yes / 🔘 No',
                    'query'                        => 'SELECT 1 as value, "Yes" as name UNION SELECT 0 as value, "No" as name',
                    'query_default_value'          => '',
                    'default_value'                => '0',
                    'custom_entry'                 => '0',
                ]
            ]
        );
    }
}
