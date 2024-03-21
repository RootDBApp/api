<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('connector_databases', function (Blueprint $table) {
            $table->addColumn('boolean', 'available')->default('1');
        });

        DB::statement("INSERT INTO `rootdb-api`.`connector_databases` (id, name, available) VALUES (2, 'PostgreSQL', 1);");
    }
};
