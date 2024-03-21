<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("update report_data_view_lib_versions set major_version = '8.x', version = '8.13.2' where id = 1");
        DB::statement("update report_data_view_lib_versions set major_version = '4.x', version = '4.4.2' where id = 3");
        DB::statement("update report_data_view_lib_versions set major_version = '7.x', version = '7.8.5' where id = 4");
        DB::statement("update report_data_view_lib_versions set major_version = '1.x', version = '1.0.4' where id in (5,6,7,8)");
    }
};
