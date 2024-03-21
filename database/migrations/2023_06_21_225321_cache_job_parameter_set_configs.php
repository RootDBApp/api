<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cache_job_parameter_set_configs', function (Blueprint $table) {
            $table->string('value', 255)->after('report_parameter_id')->nullable();
        });
    }
};
