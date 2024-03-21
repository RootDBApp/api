<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_caches', function (Blueprint $table) {

            $table->unsignedInteger('id', true)->unique();

            $table->string('memcached_key')->nullable(false);

            $table->unsignedInteger('report_id')->nullable(false);
            $table->foreign('report_id', 'fk__rcfu_report_id')->references('id')->on('reports')->onDelete('cascade');

            $table->string('input_parameters_hash')->nullable(false);

            $table->unsignedInteger('report_data_view_id')->nullable(false);
            $table->foreign('report_data_view_id', 'fk__rcfu_report_data_view_id')->references('id')->on('report_data_views')->onDelete('cascade');

            $table->timestamps();
        });
    }
};
