<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->float('num_runs')->default(0)->nullable(false);
            $table->float('num_seconds_all_run')->default(0)->nullable(false);
            $table->float('avg_seconds_by_run')->default(0)->nullable(false);
        });

        Schema::table('report_data_views', function (Blueprint $table) {
            $table->float('num_runs')->default(0)->nullable(false);
            $table->float('num_seconds_all_run')->default(0)->nullable(false);
            $table->float('avg_seconds_by_run')->default(0)->nullable(false);
        });
    }
};
