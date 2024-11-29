<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_data_view_js', function (Blueprint $table) {
            $table->addColumn('json', 'json_runtime_configuration')->default('{}')->comment(' We put here everything the view will require to run on the frontend side. For instance, the dynamic modules imports for JS code.');
        });
    }
};
