<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up()
    {
        DB::statement("alter table report_parameter_inputs modify query_default_value text null;");
    }
};
