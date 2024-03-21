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

use App\Models\ReportParameterInputDataType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        ReportParameterInputDataType::insert(
            [
                [
                    'connector_database_id' => 1,
                    'name'                  => 'char',
                    'type_name'             => 'char',
                    'custom_entry'          => 0,
                ],
                [
                    'connector_database_id' => 1,
                    'name'                  => 'double',
                    'type_name'             => 'double',
                    'custom_entry'          => 0,
                ],
                [
                    'connector_database_id' => 1,
                    'name'                  => 'float',
                    'type_name'             => 'float',
                    'custom_entry'          => 0,
                ],
                [
                    'connector_database_id' => 1,
                    'name'                  => 'year',
                    'type_name'             => 'year',
                    'custom_entry'          => 0,
                ]
            ]);
    }
};
