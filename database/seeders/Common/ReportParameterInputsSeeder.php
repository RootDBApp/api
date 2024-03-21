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
