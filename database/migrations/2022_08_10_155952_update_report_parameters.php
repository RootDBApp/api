<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('report_parameters', function (Blueprint $table) {
            $table->addColumn('boolean', 'available_public_access')->default(true);
        });
    }
};
