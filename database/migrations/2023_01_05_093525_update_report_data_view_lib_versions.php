<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        DB::statement("update report_data_view_lib_versions set version = '7.8.0' where id = 1");
        DB::statement("update report_data_view_lib_versions set major_version = '4.x', version = '4.1.1' where id = 3");
        DB::statement("update report_data_view_lib_versions set version = '7.8.0' where id = 4");
    }
};
