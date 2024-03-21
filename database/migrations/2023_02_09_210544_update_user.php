<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        DB::statement("alter table users modify column email varchar(255) null;");
    }
};
