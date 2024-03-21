<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        DB::statement("update report_data_view_lib_versions set version = '7.8.1' where id = 1");
        DB::statement("update report_data_view_lib_versions set version = '3.9.1' where id = 3");
        DB::statement("update report_data_view_lib_versions set version = '7.6.1' where id = 4");
    }
};
