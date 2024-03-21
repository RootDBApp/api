<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        DB::statement("alter table report_parameters modify forced_default_value varchar(2500) null; ");
        DB::statement("alter table report_parameter_inputs modify default_value varchar(2500) null;");
    }
};
