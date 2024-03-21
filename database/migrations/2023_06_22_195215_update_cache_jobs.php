<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cache_jobs', function (Blueprint $table) {
            $table->unsignedInteger('last_num_parameter_sets')->after('last_run_duration')->nullable();
            $table->unsignedInteger('last_cache_size_b')->after('last_num_parameter_sets')->nullable();
        });
    }
};
