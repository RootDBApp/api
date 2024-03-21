<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cache_jobs', function (Blueprint $table) {
            $table->addColumn('boolean', 'activated')->default(true)->nullable(false);
            $table->addColumn('boolean', 'running')->default(false)->nullable(false);
        });
    }
};
