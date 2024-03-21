<?php

use App\Models\ReportDataViewLibTypes;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        ReportDataViewLibTypes::insert(
            [
                [
                    'report_data_view_lib_version_id' => 4,
                    'label'                           => 'bar',
                    'name'                            => 'Bar'
                ],
                [
                    'report_data_view_lib_version_id' => 4,
                    'label'                           => 'line',
                    'name'                            => 'Line'
                ]
            ]
        );
    }
};
