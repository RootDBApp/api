<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            insert into report_data_view_lib_types ( report_data_view_lib_version_id, label, name)
            values (3, 'bubble', 'Bubble');
        ");
    }
};
