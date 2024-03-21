<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cache_job_parameter_set_configs', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('cache_job_id')->nullable(false);
            $table->foreign('cache_job_id', 'fk_cjpsc_id')->references('id')->on('cache_jobs')->onDelete('cascade');
            $table->unsignedInteger('report_parameter_id')->nullable(false);
            $table->foreign('report_parameter_id', 'fk_cjpsc_rp_id')->references('id')->on('report_parameters')->onDelete('cascade');
            $table->json('date_start_from_values')->nullable()->comment('{values: [default, 1-week, 2-weeks, 3-weeks, 4-weeks, 1-month, 2-months, 4-months, 5-months, 6-months, 1-year, 2-years, 3-years, 4-years, 5-years]}');
            $table->json('select_values')->nullable()->comment('{values: []} - it will generate one query for each value.');
            $table->json('multi_select_values')->nullable()->comment('{values: []} - generally used with IN (x,y,z) in WHERE statement.');
            $table->timestamps();
        });
    }
};
