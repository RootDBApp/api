<?php

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
